#!/usr/bin/env php
<?php
/**
 * bin/backup-db.php — SQLite-Backup der CVS-Datenbank per VACUUM INTO (P0.1)
 *
 * Umsetzung von DATABASE_ARCHITECTURE.md §6 (Backup & Restore):
 *   1) Snapshot der Quell-DB via VACUUM INTO nach
 *      render-data/backups/cinematic-YYYYMMDD-HHMMSS.db
 *   2) danach PRAGMA integrity_check auf dem Snapshot
 *   3) Rotation: maximal 7 erfolgreiche Backups bleiben erhalten
 *   4) Protokoll: jeder Lauf (Erfolg UND Fehler) wird einzeilig an
 *      render-data/backups/backup.log angehaengt (LOCK_EX, ohne Personenbezug)
 *   5) optional --restore-test: Restore-Probe des frischen Backups (Kopie als
 *      eigenstaendige DB oeffnen, integrity_check, Tabellen und Zeilenzahlen
 *      gegen die Quelle vergleichen); schlaegt die Probe fehl, wird das neue
 *      Backup verworfen — bestehende Backups bleiben unangetastet
 *
 * NUR CLI — kein HTTP-Endpunkt, kein Aufruf ueber URL.
 * Der spaetere Trigger (Render Cron o. ae.) wird separat entschieden.
 * Vorgesehener Produktions-Aufruf:
 *   php /var/www/html/bin/backup-db.php
 *
 * Optionen (fuer lokale Tests / Ops — ohne Optionen gilt Produktionsverhalten):
 *   --source=PFAD      Quell-DB   (Default: identisch zu csf_db_path() in includes/db.php)
 *   --backup-dir=PFAD  Zielordner (Default: <render-data>/backups)
 *   --keep=N           Rotations-Limit (Default: 7)
 *   --check-only=PFAD  Nur PRAGMA integrity_check einer Datei ausfuehren (kein Backup)
 *   --restore-test     Nach dem Backup eine lokale Restore-Probe ausfuehren
 *                      (nicht mit --check-only kombinierbar; nur aussagekraeftig,
 *                      solange niemand parallel in die Quell-DB schreibt)
 *   --help             Hilfe anzeigen
 *
 * Exit-Codes (eindeutig — bei Fehler niemals stilles Weiterlaufen):
 *    0  Erfolg
 *    1  Ungueltiger Aufruf (unbekannte Option / ungueltiger Wert)
 *    2  Quell-DB fehlt, ist nicht lesbar oder keine SQLite-Datei
 *    3  Backup-Verzeichnis nicht erstellbar / nicht beschreibbar
 *    4  VACUUM INTO fehlgeschlagen (unvollstaendige Datei wurde entfernt)
 *    5  integrity_check fehlgeschlagen (Backup wurde verworfen)
 *    6  Rotation fehlgeschlagen (das neue Backup selbst ist vorhanden)
 *    7  Anderer Backup-Lauf aktiv (Lock)
 *    8  Zieldatei existiert bereits (Namenskollision innerhalb einer Sekunde)
 *    9  SQLite aelter als 3.27 — VACUUM INTO nicht verfuegbar
 *   10  Unerwarteter Fehler / fehlende PHP-Voraussetzung
 *   11  Restore-Test fehlgeschlagen (das neue Backup wurde verworfen)
 *
 * Garantien:
 *   - Die Quell-DB wird ausschliesslich READ-ONLY geoeffnet (SQLITE_OPEN_READONLY)
 *     und zu keinem Zeitpunkt veraendert.
 *   - Ausgaben enthalten keine personenbezogenen Daten (nur Pfade/Groessen/Zaehler).
 *   - backup.log enthaelt ausschliesslich Zeitstempel, Ereignis, Dateinamen,
 *     Groessen, Laufzeiten, Zaehler, Exit-Codes und Fehlertexte — NIEMALS
 *     DB-Inhalte oder personenbezogene Daten. Fehler VOR der Log-Initialisierung
 *     (Usage-Fehler, --check-only, Verzeichnis nicht erstellbar) erscheinen nur
 *     auf STDERR.
 *   - Backups liegen in render-data/backups/: per .gitignore vom Repo ausgeschlossen,
 *     per Apache-Regel (*.db global deny, docker/apache.conf) vom Web abgeschirmt;
 *     zusaetzlich legt dieses Skript dort einmalig eine .htaccess (Require all denied) an.
 */

declare(strict_types=1);

// ── CLI-Guard: niemals ueber HTTP ausfuehrbar ─────────────────────────────────
if (PHP_SAPI !== 'cli') {
    if (function_exists('http_response_code')) {
        http_response_code(403);
    }
    echo 'Forbidden';
    exit(10);
}

// ── Exit-Codes ────────────────────────────────────────────────────────────────
const CSF_BK_OK         = 0;
const CSF_BK_USAGE      = 1;
const CSF_BK_SOURCE     = 2;
const CSF_BK_TARGET_DIR = 3;
const CSF_BK_VACUUM     = 4;
const CSF_BK_INTEGRITY  = 5;
const CSF_BK_ROTATION   = 6;
const CSF_BK_LOCKED     = 7;
const CSF_BK_COLLISION  = 8;
const CSF_BK_SQLITE_OLD = 9;
const CSF_BK_UNEXPECTED = 10;
const CSF_BK_RESTORE    = 11;

const CSF_BK_KEEP_DEFAULT = 7;
const CSF_BK_LOG_NAME     = 'backup.log';

// ── Ausgabe-Helfer (keine personenbezogenen Daten ausgeben!) ──────────────────

function csf_bk_out(string $msg): void
{
    fwrite(STDOUT, $msg . PHP_EOL);
}

function csf_bk_warn(string $msg): void
{
    fwrite(STDERR, '[backup-db] WARNUNG: ' . $msg . PHP_EOL);
}

// ── Backup-Log (eine Zeile pro Ereignis: <ISO-Zeit> <EREIGNIS> <details>) ─────
// Enthaelt NIEMALS DB-Inhalte oder personenbezogene Daten — ausschliesslich
// Zeitstempel, Dateinamen, Groessen, Laufzeiten, Zaehler und Fehlertexte.

/** Merkt sich den Log-Pfad, sobald das Backup-Verzeichnis feststeht. */
function csf_bk_log_file(?string $set = null): ?string
{
    static $path = null;
    if ($set !== null) {
        $path = $set;
    }
    return $path;
}

/**
 * Haengt eine Logzeile an (FILE_APPEND | LOCK_EX). Vor der Log-Initialisierung
 * (Usage-Fehler, --check-only, Backup-Verzeichnis nicht verfuegbar) bewusst
 * ohne Wirkung; ein Log-Schreibfehler bricht das Backup selbst nicht ab.
 */
function csf_bk_log_write(string $event, string $details): void
{
    $file = csf_bk_log_file();
    if ($file === null) {
        return;
    }
    $details = trim((string)preg_replace('/\s+/', ' ', $details));
    $line    = date('c') . ' ' . $event . ' ' . $details . PHP_EOL;
    if (@file_put_contents($file, $line, FILE_APPEND | LOCK_EX) === false) {
        csf_bk_warn('Logeintrag nicht schreibbar: ' . $file);
    }
}

/** Fehler loggen, nach STDERR schreiben und mit eindeutigem Exit-Code beenden. */
function csf_bk_fail(int $code, string $msg): void
{
    csf_bk_log_write('FEHLER', 'exit=' . $code . ' grund=' . $msg);
    fwrite(STDERR, '[backup-db] FEHLER (Exit ' . $code . '): ' . $msg . PHP_EOL);
    exit($code);
}

/** Bytes menschenlesbar formatieren (nur fuer Log-Ausgabe). */
function csf_bk_size(int $bytes): string
{
    if ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 1, ',', '.') . ' MB';
    }
    if ($bytes >= 1024) {
        return number_format($bytes / 1024, 1, ',', '.') . ' KB';
    }
    return $bytes . ' B';
}

function csf_bk_usage(): void
{
    csf_bk_out(
        'Verwendung: php bin/backup-db.php [--source=PFAD] [--backup-dir=PFAD]' . PHP_EOL .
        '                                  [--keep=N] [--check-only=PFAD]' . PHP_EOL .
        '                                  [--restore-test] [--help]' . PHP_EOL .
        PHP_EOL .
        'Ohne Optionen: Produktionsverhalten (Quelle wie includes/db.php,' . PHP_EOL .
        'Ziel <render-data>/backups, Rotation 7). Nur CLI — kein HTTP-Aufruf.'
    );
}

// ── SQLite-Helfer ─────────────────────────────────────────────────────────────

/** Prueft den 16-Byte-Header ("SQLite format 3\0") einer Datei. */
function csf_bk_is_sqlite(string $path): bool
{
    $fh = @fopen($path, 'rb');
    if ($fh === false) {
        return false;
    }
    $header = fread($fh, 16);
    fclose($fh);
    return $header === "SQLite format 3\x00";
}

/**
 * Oeffnet eine SQLite-Datei strikt READ-ONLY.
 * Garantiert, dass ueber diese Verbindung nichts geschrieben werden kann.
 */
function csf_bk_open_readonly(string $path): PDO
{
    return new PDO('sqlite:' . $path, null, null, [
        PDO::ATTR_ERRMODE           => PDO::ERRMODE_EXCEPTION,
        PDO::SQLITE_ATTR_OPEN_FLAGS => PDO::SQLITE_OPEN_READONLY,
    ]);
}

/**
 * Fuehrt PRAGMA integrity_check aus und liefert alle Meldungszeilen.
 * Erfolg = genau eine Zeile mit Inhalt "ok".
 *
 * @return list<string>
 */
function csf_bk_integrity_check(string $path): array
{
    $pdo = csf_bk_open_readonly($path);
    $pdo->exec('PRAGMA busy_timeout = 30000');
    $rows = $pdo->query('PRAGMA integrity_check')->fetchAll(PDO::FETCH_COLUMN, 0);
    $pdo  = null; // Handle schliessen (Windows: sonst kein rename/unlink moeglich)
    return array_map('strval', $rows);
}

/**
 * Kuerzt integrity_check-Meldungen fuer eine lesbare einzeilige Log-Ausgabe.
 * (SQLite liefert bei Korruption teils sehr lange, mehrzeilige Meldungen.)
 *
 * @param list<string> $messages
 */
function csf_bk_summarize(array $messages, int $max = 3, int $maxLen = 160): string
{
    $parts = [];
    foreach (array_slice($messages, 0, $max) as $m) {
        $m = trim((string)preg_replace('/\s+/', ' ', $m));
        if (strlen($m) > $maxLen) {
            $m = substr($m, 0, $maxLen) . '...';
        }
        $parts[] = $m;
    }
    $rest = count($messages) - $max;
    return implode(' | ', $parts) . ($rest > 0 ? ' | ... +' . $rest . ' weitere' : '');
}

/**
 * Liste aller Nutzertabellen (ohne sqlite_*-Interna), alphabetisch sortiert.
 *
 * @return list<string>
 */
function csf_bk_list_tables(PDO $pdo): array
{
    $rows = $pdo->query(
        "SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%' ORDER BY name"
    )->fetchAll(PDO::FETCH_COLUMN, 0);
    return array_map('strval', $rows);
}

/**
 * Zeilenzahl je Tabelle. Tabellennamen stammen aus sqlite_master und werden
 * trotzdem defensiv als Identifier gequotet.
 *
 * @param  list<string> $tables
 * @return array<string, int>
 */
function csf_bk_count_rows(PDO $pdo, array $tables): array
{
    $counts = [];
    foreach ($tables as $table) {
        $sql = 'SELECT COUNT(*) FROM "' . str_replace('"', '""', $table) . '"';
        $counts[$table] = (int)$pdo->query($sql)->fetchColumn();
    }
    return $counts;
}

/**
 * Rollback: unvollstaendige/verworfene Backup-Datei samt moeglicher
 * SQLite-Begleitdateien entfernen. Beruehrt NIE die Quell-DB.
 */
function csf_bk_rollback(string $tmpPath): void
{
    foreach ([$tmpPath, $tmpPath . '-journal', $tmpPath . '-wal', $tmpPath . '-shm'] as $file) {
        if (is_file($file)) {
            @unlink($file);
        }
    }
}

/**
 * Restore-Test (P0.1): beweist, dass das soeben erstellte Backup als
 * eigenstaendige SQLite-DB nutzbar ist.
 *   1) Backup-Datei zu einer Restore-Kopie kopieren (simulierter Restore)
 *   2) Kopie als neue DB oeffnen (READ-ONLY) + PRAGMA integrity_check
 *   3) Tabellenlisten Quelle vs. Restore-Kopie vergleichen
 *   4) Zeilenzahl jeder Tabelle vergleichen
 * Schlaegt IRGENDEIN Schritt fehl: Restore-Kopie UND neues Backup werden
 * entfernt, FEHLER wird geloggt, Exit 11 — aeltere Backups bleiben unberuehrt.
 * Nur aussagekraeftig, solange niemand parallel in die Quell-DB schreibt.
 */
function csf_bk_restore_test(string $sourceReal, string $finalPath): void
{
    $rt0         = microtime(true);
    $restoreCopy = $finalPath . '.restore-test.tmp';
    csf_bk_out('[backup-db] Restore-Test gestartet (Kopie: ' . basename($restoreCopy) . ')');

    $fail = static function (string $msg) use ($restoreCopy, $finalPath): void {
        csf_bk_rollback($restoreCopy);
        csf_bk_rollback($finalPath);
        csf_bk_fail(
            CSF_BK_RESTORE,
            'Restore-Test fehlgeschlagen: ' . $msg
            . ' — neues Backup verworfen (Rollback), aeltere Backups unveraendert.'
        );
    };

    // 1) Simulierter Restore: Backup als neue, eigenstaendige Datei kopieren
    if (!@copy($finalPath, $restoreCopy)) {
        $fail('Restore-Kopie nicht erstellbar: ' . basename($restoreCopy));
    }

    $srcTables = [];
    $srcCounts = [];
    $rstCounts = [];
    try {
        // 2) Restore-Kopie als neue SQLite-DB oeffnen + integrity_check
        $integrity = csf_bk_integrity_check($restoreCopy);
        if ($integrity !== ['ok']) {
            $fail('integrity_check der Restore-Kopie ('
                . count($integrity) . ' Meldung(en)): ' . csf_bk_summarize($integrity));
        }

        // 3) + 4) Tabellen und Zeilenzahlen gegen die Quelle vergleichen
        $srcPdo = csf_bk_open_readonly($sourceReal);
        $srcPdo->exec('PRAGMA busy_timeout = 30000');
        $rstPdo = csf_bk_open_readonly($restoreCopy);

        $srcTables = csf_bk_list_tables($srcPdo);
        $rstTables = csf_bk_list_tables($rstPdo);
        if ($srcTables !== $rstTables) {
            $missing = array_diff($srcTables, $rstTables);
            $extra   = array_diff($rstTables, $srcTables);
            $srcPdo  = null;
            $rstPdo  = null; // Handles schliessen, sonst scheitert der Rollback (Windows)
            $fail('Tabellenlisten ungleich'
                . ($missing !== [] ? '; fehlt im Backup: ' . implode(',', $missing) : '')
                . ($extra !== [] ? '; zusaetzlich im Backup: ' . implode(',', $extra) : ''));
        }

        $srcCounts = csf_bk_count_rows($srcPdo, $srcTables);
        $rstCounts = csf_bk_count_rows($rstPdo, $rstTables);
        $srcPdo    = null;
        $rstPdo    = null;
    } catch (PDOException $e) {
        $srcPdo = null;
        $rstPdo = null;
        $fail('SQL-Fehler: ' . $e->getMessage());
    }

    $mismatch  = [];
    $totalRows = 0;
    foreach ($srcTables as $table) {
        if ($srcCounts[$table] !== $rstCounts[$table]) {
            $mismatch[] = $table . ' (Quelle ' . $srcCounts[$table] . ' / Backup ' . $rstCounts[$table] . ')';
        }
        $totalRows += $rstCounts[$table];
    }
    if ($mismatch !== []) {
        $fail('Zeilenzahlen ungleich: ' . implode(', ', $mismatch));
    }

    // Aufraeumen: Restore-Kopie entfernen (nicht fatal — liegt im geschuetzten Ordner)
    csf_bk_rollback($restoreCopy);
    if (is_file($restoreCopy)) {
        csf_bk_warn('Restore-Kopie konnte nicht entfernt werden: ' . basename($restoreCopy));
    }

    foreach ($srcTables as $table) {
        csf_bk_out('[backup-db]   Tabelle ' . $table . ': Quelle ' . $srcCounts[$table]
            . ' = Backup ' . $rstCounts[$table] . ' Zeile(n)');
    }
    $rtDuration = microtime(true) - $rt0;
    csf_bk_out(
        '[backup-db] Restore-Test OK: ' . count($srcTables) . ' Tabelle(n), '
        . $totalRows . ' Zeile(n) identisch, integrity_check: ok, '
        . number_format($rtDuration, 1, ',', '.') . ' s'
    );
    csf_bk_log_write(
        'RESTORE-TEST-OK',
        'datei=' . basename($finalPath) . ' tabellen=' . count($srcTables)
        . ' zeilen=' . $totalRows . ' integrity=ok dauer=' . number_format($rtDuration, 3, '.', '') . 's'
    );
}

// ── Pfad-Aufloesung (identisch zu includes/db.php csf_db_path()) ──────────────

/**
 * Quell-DB: Render Persistent Disk, sonst lokales storage/.
 * Bewusst NICHT includes/db.php eingebunden — csf_db() wuerde Schema-Init
 * und damit Schreibzugriffe ausloesen. Dieses Skript bleibt strikt lesend.
 */
function csf_bk_default_source(string $appRoot): string
{
    $renderDisk = '/var/www/html/render-data';
    if (is_dir($renderDisk) && is_writable($renderDisk)) {
        return $renderDisk . '/cinematic.db';
    }
    return $appRoot . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'cinematic.db';
}

/** Zielordner: <render-data>/backups (auf Render = Persistent Disk). */
function csf_bk_default_backup_dir(string $appRoot): string
{
    $renderDisk = '/var/www/html/render-data';
    if (is_dir($renderDisk) && is_writable($renderDisk)) {
        return $renderDisk . '/backups';
    }
    return $appRoot . DIRECTORY_SEPARATOR . 'render-data' . DIRECTORY_SEPARATOR . 'backups';
}

// ── Hauptprogramm ─────────────────────────────────────────────────────────────

try {
    // PHP-Voraussetzungen: ohne Read-Only-Garantie wird NICHT gearbeitet (fail-closed).
    if (!extension_loaded('pdo_sqlite')) {
        csf_bk_fail(CSF_BK_UNEXPECTED, 'PHP-Extension pdo_sqlite fehlt.');
    }
    if (!defined('PDO::SQLITE_ATTR_OPEN_FLAGS') || !defined('PDO::SQLITE_OPEN_READONLY')) {
        csf_bk_fail(CSF_BK_UNEXPECTED, 'PDO-SQLite ohne Read-Only-Unterstuetzung — Abbruch zum Schutz der Quell-DB.');
    }

    $appRoot = dirname(__DIR__);

    // ── Optionen strikt parsen (unbekannte Optionen = harter Fehler) ──────────
    $opt = [
        'source'       => null,
        'backup-dir'   => null,
        'keep'         => CSF_BK_KEEP_DEFAULT,
        'check-only'   => null,
        'restore-test' => false,
    ];

    foreach (array_slice($argv, 1) as $arg) {
        if ($arg === '--help' || $arg === '-h') {
            csf_bk_usage();
            exit(CSF_BK_OK);
        }
        if ($arg === '--restore-test') {
            $opt['restore-test'] = true;
            continue;
        }
        if (preg_match('/^--(source|backup-dir|check-only)=(.+)$/', $arg, $m) === 1) {
            $opt[$m[1]] = $m[2];
            continue;
        }
        if (preg_match('/^--keep=(\d+)$/', $arg, $m) === 1 && (int)$m[1] >= 1) {
            $opt['keep'] = (int)$m[1];
            continue;
        }
        fwrite(STDERR, '[backup-db] Ungueltige oder unbekannte Option: ' . $arg . PHP_EOL);
        csf_bk_usage();
        exit(CSF_BK_USAGE);
    }

    if ($opt['check-only'] !== null && $opt['restore-test'] === true) {
        fwrite(STDERR, '[backup-db] --check-only und --restore-test sind nicht kombinierbar.' . PHP_EOL);
        csf_bk_usage();
        exit(CSF_BK_USAGE);
    }

    // ── Modus: --check-only (nur integrity_check, kein Backup, kein Lock) ─────
    if ($opt['check-only'] !== null) {
        $checkPath = $opt['check-only'];
        if (!is_file($checkPath) || !is_readable($checkPath)) {
            csf_bk_fail(CSF_BK_SOURCE, 'Datei nicht gefunden oder nicht lesbar: ' . $checkPath);
        }
        if (!csf_bk_is_sqlite($checkPath)) {
            csf_bk_fail(CSF_BK_SOURCE, 'Keine gueltige SQLite-Datei (Header-Pruefung): ' . $checkPath);
        }
        try {
            $result = csf_bk_integrity_check($checkPath);
        } catch (PDOException $e) {
            csf_bk_fail(CSF_BK_INTEGRITY, 'integrity_check nicht ausfuehrbar: ' . $e->getMessage());
        }
        if ($result === ['ok']) {
            csf_bk_out('[backup-db] integrity_check: ok (' . basename($checkPath) . ')');
            exit(CSF_BK_OK);
        }
        csf_bk_fail(
            CSF_BK_INTEGRITY,
            'integrity_check meldet ' . count($result) . ' Meldung(en): ' . csf_bk_summarize($result)
        );
    }

    // ── Backup-Verzeichnis vorbereiten (zuerst — dort liegt auch das Log) ─────
    $backupDir = $opt['backup-dir'] ?? csf_bk_default_backup_dir($appRoot);
    if (!is_dir($backupDir)) {
        if (!@mkdir($backupDir, 0700, true) && !is_dir($backupDir)) {
            csf_bk_fail(CSF_BK_TARGET_DIR, 'Backup-Verzeichnis nicht erstellbar: ' . $backupDir);
        }
        @chmod($backupDir, 0700);
    }
    $backupDirReal = realpath($backupDir);
    if ($backupDirReal === false || !is_dir($backupDirReal)) {
        csf_bk_fail(CSF_BK_TARGET_DIR, 'Backup-Verzeichnis nicht aufloesbar: ' . $backupDir);
    }
    $backupDir = $backupDirReal;
    if (!is_writable($backupDir)) {
        csf_bk_fail(CSF_BK_TARGET_DIR, 'Backup-Verzeichnis nicht beschreibbar: ' . $backupDir);
    }

    // Defense-in-depth: .htaccess gegen Web-Zugriff (zusaetzlich zur globalen
    // *.db-Sperre in docker/apache.conf). Nicht fatal, falls nicht anlegbar.
    $htaccess = $backupDir . DIRECTORY_SEPARATOR . '.htaccess';
    if (!is_file($htaccess)) {
        $written = @file_put_contents(
            $htaccess,
            "# Backups niemals per HTTP ausliefern (Zusatzschutz zu docker/apache.conf)\n" .
            "Require all denied\n",
            LOCK_EX
        );
        if ($written === false) {
            csf_bk_warn('.htaccess im Backup-Verzeichnis konnte nicht angelegt werden (Web-Schutz haengt an docker/apache.conf).');
        }
    }

    // ── Backup-Log initialisieren: ab hier werden Erfolg UND Fehler geloggt ───
    csf_bk_log_file($backupDir . DIRECTORY_SEPARATOR . CSF_BK_LOG_NAME);

    // ── Quell-DB pruefen (READ-ONLY, wird nie veraendert) ─────────────────────
    $source     = $opt['source'] ?? csf_bk_default_source($appRoot);
    $sourceReal = realpath($source);
    if ($sourceReal === false || !is_file($sourceReal)) {
        csf_bk_fail(CSF_BK_SOURCE, 'Quell-DB nicht gefunden: ' . $source);
    }
    if (!is_readable($sourceReal)) {
        csf_bk_fail(CSF_BK_SOURCE, 'Quell-DB nicht lesbar: ' . $sourceReal);
    }
    if (!csf_bk_is_sqlite($sourceReal)) {
        csf_bk_fail(CSF_BK_SOURCE, 'Keine gueltige SQLite-Datei (Header-Pruefung): ' . $sourceReal);
    }
    $sourceSize = (int)filesize($sourceReal);

    // ── Lock: nur ein Backup-Lauf gleichzeitig ────────────────────────────────
    $lockPath   = $backupDir . DIRECTORY_SEPARATOR . '.backup.lock';
    $lockHandle = @fopen($lockPath, 'c');
    if ($lockHandle === false) {
        csf_bk_fail(CSF_BK_TARGET_DIR, 'Lock-Datei nicht anlegbar: ' . $lockPath);
    }
    if (!flock($lockHandle, LOCK_EX | LOCK_NB)) {
        csf_bk_fail(CSF_BK_LOCKED, 'Es laeuft bereits ein Backup (Lock aktiv): ' . $lockPath);
    }

    // ── Quelle READ-ONLY oeffnen + SQLite-Version pruefen ─────────────────────
    try {
        $src = csf_bk_open_readonly($sourceReal);
    } catch (PDOException $e) {
        csf_bk_fail(CSF_BK_SOURCE, 'Quell-DB nicht lesbar (PDO): ' . $e->getMessage());
    }
    $src->exec('PRAGMA busy_timeout = 30000');

    $sqliteVersion = (string)$src->query('SELECT sqlite_version()')->fetchColumn();
    if (version_compare($sqliteVersion, '3.27.0', '<')) {
        csf_bk_fail(CSF_BK_SQLITE_OLD, 'SQLite ' . $sqliteVersion . ' < 3.27 — VACUUM INTO nicht verfuegbar.');
    }

    // ── Zielnamen bestimmen: cinematic-YYYYMMDD-HHMMSS.db ─────────────────────
    $stamp     = date('Ymd-His');
    $finalPath = $backupDir . DIRECTORY_SEPARATOR . 'cinematic-' . $stamp . '.db';
    $tmpPath   = $finalPath . '.tmp';

    if (file_exists($finalPath) || file_exists($tmpPath)) {
        csf_bk_fail(CSF_BK_COLLISION, 'Zieldatei existiert bereits (Kollision): ' . $finalPath);
    }

    // Verwaiste .tmp-Dateien aus abgestuerzten Laeufen nur melden, nie loeschen.
    foreach (glob($backupDir . DIRECTORY_SEPARATOR . 'cinematic-*.db.tmp') ?: [] as $stale) {
        csf_bk_warn('Verwaiste Temp-Datei aus fruehem Lauf gefunden (nicht geloescht): ' . basename($stale));
    }

    csf_bk_out('[backup-db] ' . date('c') . ' Backup gestartet'
        . ($opt['restore-test'] === true ? ' (mit Restore-Test)' : ''));
    csf_bk_out('[backup-db] Quelle: ' . $sourceReal . ' (' . csf_bk_size($sourceSize) . ', SQLite ' . $sqliteVersion . ')');
    csf_bk_out('[backup-db] Ziel:   ' . $finalPath);

    // ── 1) Snapshot: VACUUM INTO (liest die Quelle nur, schreibt NUR die Kopie)
    $t0 = microtime(true);
    try {
        $src->exec('VACUUM INTO ' . $src->quote($tmpPath));
    } catch (PDOException $e) {
        $src = null;
        csf_bk_rollback($tmpPath);
        csf_bk_fail(CSF_BK_VACUUM, 'VACUUM INTO fehlgeschlagen: ' . $e->getMessage() . ' — unvollstaendiges Backup entfernt (Rollback).');
    }
    $src = null; // Quelle sofort wieder schliessen

    if (!is_file($tmpPath) || (int)filesize($tmpPath) === 0) {
        csf_bk_rollback($tmpPath);
        csf_bk_fail(CSF_BK_VACUUM, 'Backup-Datei fehlt oder ist leer nach VACUUM INTO — Rollback ausgefuehrt.');
    }

    // ── 2) Verifikation: PRAGMA integrity_check auf dem Snapshot ──────────────
    try {
        $integrity = csf_bk_integrity_check($tmpPath);
    } catch (PDOException $e) {
        csf_bk_rollback($tmpPath);
        csf_bk_fail(CSF_BK_INTEGRITY, 'integrity_check nicht ausfuehrbar: ' . $e->getMessage() . ' — Backup verworfen (Rollback).');
    }
    if ($integrity !== ['ok']) {
        csf_bk_rollback($tmpPath);
        csf_bk_fail(
            CSF_BK_INTEGRITY,
            'integrity_check fehlgeschlagen (' . count($integrity) . ' Meldung(en)): '
            . csf_bk_summarize($integrity) . ' — Backup verworfen (Rollback).'
        );
    }

    // ── Finalisieren: erst nach bestandener Pruefung den endgueltigen Namen ───
    if (!@rename($tmpPath, $finalPath)) {
        csf_bk_rollback($tmpPath);
        csf_bk_fail(CSF_BK_VACUUM, 'Backup konnte nicht finalisiert werden (rename): ' . $finalPath);
    }
    @chmod($finalPath, 0600);

    $duration   = microtime(true) - $t0;
    $backupSize = (int)filesize($finalPath);
    csf_bk_out(
        '[backup-db] OK: ' . basename($finalPath)
        . ' (' . csf_bk_size($backupSize) . ', integrity_check: ok, '
        . number_format($duration, 1, ',', '.') . ' s)'
    );
    csf_bk_log_write(
        'BACKUP-OK',
        'datei=' . basename($finalPath) . ' groesse=' . $backupSize . 'B'
        . ' dauer=' . number_format($duration, 3, '.', '') . 's integrity=ok'
    );

    // ── Restore-Test (nur mit --restore-test): VOR der Rotation, damit bei
    //    einem Fehlschlag keine aelteren Backups rotiert/geloescht werden ──────
    if ($opt['restore-test'] === true) {
        csf_bk_restore_test($sourceReal, $finalPath);
    }

    // ── 3) Rotation: nur erfolgreiche Backups zaehlen, max. --keep behalten ───
    $backups = [];
    foreach (scandir($backupDir) ?: [] as $entry) {
        if (preg_match('/^cinematic-\d{8}-\d{6}\.db$/', $entry) === 1
            && is_file($backupDir . DIRECTORY_SEPARATOR . $entry)
        ) {
            $backups[] = $entry;
        }
    }
    rsort($backups, SORT_STRING); // neueste zuerst (Zeitstempel im Namen)

    $expired      = array_slice($backups, $opt['keep']);
    $deletedCount = 0;
    $deleteFailed = [];
    foreach ($expired as $entry) {
        $path = $backupDir . DIRECTORY_SEPARATOR . $entry;
        // Paranoia-Guard: niemals die Quell-DB loeschen (z. B. bei --source
        // auf eine Datei innerhalb des Backup-Ordners).
        $pathReal = realpath($path);
        if ($pathReal !== false && strcasecmp($pathReal, $sourceReal) === 0) {
            csf_bk_warn('Rotation uebersprungen (Datei ist die Quell-DB): ' . $entry);
            continue;
        }
        if (@unlink($path)) {
            $deletedCount++;
        } else {
            $deleteFailed[] = $entry;
        }
    }

    if ($deleteFailed !== []) {
        csf_bk_fail(
            CSF_BK_ROTATION,
            'Rotation fehlgeschlagen fuer: ' . implode(', ', $deleteFailed)
            . ' — das neue Backup selbst wurde erfolgreich erstellt.'
        );
    }

    csf_bk_out(
        '[backup-db] Rotation: ' . (count($backups) - $deletedCount)
        . ' Backup(s) behalten (Limit ' . $opt['keep'] . '), '
        . $deletedCount . ' geloescht'
    );
    csf_bk_out('[backup-db] ' . date('c') . ' Backup abgeschlossen (Exit 0)');
    exit(CSF_BK_OK);

} catch (Throwable $e) {
    csf_bk_fail(CSF_BK_UNEXPECTED, 'Unerwarteter Fehler: ' . $e->getMessage());
}
