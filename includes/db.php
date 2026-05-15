<?php
/**
 * includes/db.php — SQLite Datenbankverbindung & Schema-Init
 *
 * Cinematic Vision Studio — V1 Datenbankschicht
 *
 * SQLite wird auf dem Render Persistent Disk gespeichert.
 * Pfad: /var/www/html/render-data/cinematic.db (Render)
 *       storage/cinematic.db (lokal)
 *
 * Öffentliche Funktionen:
 *   csf_db()              → PDO-Singleton (lazy connect)
 *   csf_db_init()         → Schema anlegen wenn nötig (idempotent)
 *   csf_db_transaction()  → Callable in Transaktion ausführen
 *
 * Schema-Versionen werden in der user_version PRAGMA verwaltet.
 * Migration: ALTER TABLE-Statements in csf_db_migrate().
 *
 * @since V0.3.0 — Login + Auth
 */

declare(strict_types=1);

// ── DB-Pfad ───────────────────────────────────────────────────────────────────

/**
 * SQLite-Datenbankpfad.
 *
 * Render Persistent Disk liegt unter /var/www/html/render-data/
 * Im lokalen Dev: storage/cinematic.db
 */
function csf_db_path(): string
{
    // Render Persistent Disk (gesetzt in render.yaml als mountPath)
    $renderDisk = '/var/www/html/render-data';
    if (is_dir($renderDisk) && is_writable($renderDisk)) {
        return $renderDisk . '/cinematic.db';
    }

    // Lokale Dev-Umgebung
    $local = __DIR__ . '/../storage/cinematic.db';
    return $local;
}

// ── PDO-Singleton ─────────────────────────────────────────────────────────────

/** @var PDO|null */
$_csfDbPdo = null;

/**
 * Gibt die PDO-Singleton-Instanz zurück.
 * Beim ersten Aufruf: Verbindung herstellen + Schema initialisieren.
 *
 * @throws RuntimeException wenn Verbindung fehlschlägt
 * @return PDO
 */
function csf_db(): PDO
{
    global $_csfDbPdo;

    if ($_csfDbPdo !== null) {
        return $_csfDbPdo;
    }

    $path = csf_db_path();
    $dir  = dirname($path);

    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }

    try {
        $pdo = new PDO('sqlite:' . $path, options: [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);

        // SQLite Performance-Optimierungen
        $pdo->exec('PRAGMA journal_mode = WAL');      // Write-Ahead Logging
        $pdo->exec('PRAGMA synchronous = NORMAL');    // Schneller als FULL, sicher genug
        $pdo->exec('PRAGMA foreign_keys = ON');       // FK-Enforcement
        $pdo->exec('PRAGMA busy_timeout = 5000');     // 5s warten bei Lock

        $_csfDbPdo = $pdo;

        // Schema beim ersten Aufruf initialisieren
        csf_db_init($pdo);

    } catch (PDOException $e) {
        // Kein Stack-Trace nach außen — nur generische Fehlermeldung
        throw new RuntimeException('Datenbankverbindung fehlgeschlagen.');
    }

    return $_csfDbPdo;
}

// ── Schema-Init ───────────────────────────────────────────────────────────────

/** Aktuelle Schema-Zielversion */
const CSF_DB_SCHEMA_VERSION = 1;

/**
 * Legt alle Tabellen an (idempotent durch IF NOT EXISTS).
 * Führt Migrationen aus wenn schema_version < CSF_DB_SCHEMA_VERSION.
 *
 * @internal
 */
function csf_db_init(PDO $pdo): void
{
    // Aktuelle Schema-Version prüfen
    $currentVersion = (int) $pdo->query('PRAGMA user_version')->fetchColumn();

    if ($currentVersion >= CSF_DB_SCHEMA_VERSION) {
        return; // Bereits aktuell
    }

    $pdo->exec('BEGIN EXCLUSIVE');

    try {
        // ── users ─────────────────────────────────────────────────────────────
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id                 INTEGER PRIMARY KEY AUTOINCREMENT,
                email              TEXT    UNIQUE NOT NULL COLLATE NOCASE,
                password_hash      TEXT    NOT NULL,
                plan               TEXT    NOT NULL DEFAULT 'free'
                                   CHECK(plan IN ('free','starter','pro')),
                crystals_balance   INTEGER NOT NULL DEFAULT 0
                                   CHECK(crystals_balance >= 0),
                email_verified     INTEGER NOT NULL DEFAULT 0,
                remember_token     TEXT    UNIQUE,
                remember_expires   INTEGER,
                created_at         TEXT    NOT NULL DEFAULT (datetime('now','utc')),
                updated_at         TEXT    NOT NULL DEFAULT (datetime('now','utc'))
            )
        ");

        // Index auf email (SELECT häufig)
        $pdo->exec("
            CREATE INDEX IF NOT EXISTS idx_users_email
            ON users (email COLLATE NOCASE)
        ");

        // ── crystal_transactions ──────────────────────────────────────────────
        // Vollständiges Audit-Log aller Kristall-Bewegungen
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS crystal_transactions (
                id         INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id    INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
                amount     INTEGER NOT NULL,          -- positiv = gut, negativ = Kosten
                action     TEXT    NOT NULL,          -- 'generate_ai', 'purchase', 'bonus', ...
                job_id     TEXT,                      -- Referenz zu storage/jobs/
                note       TEXT,                      -- optionale Beschreibung
                created_at TEXT NOT NULL DEFAULT (datetime('now','utc'))
            )
        ");

        $pdo->exec("
            CREATE INDEX IF NOT EXISTS idx_crystal_tx_user
            ON crystal_transactions (user_id, created_at DESC)
        ");

        // ── login_attempts ────────────────────────────────────────────────────
        // Rate Limiting für Login (IP-basiert, kein User-Bezug)
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS login_attempts (
                id           INTEGER PRIMARY KEY AUTOINCREMENT,
                ip_hash      TEXT    NOT NULL,
                attempted_at INTEGER NOT NULL DEFAULT (unixepoch())
            )
        ");

        $pdo->exec("
            CREATE INDEX IF NOT EXISTS idx_login_attempts_ip
            ON login_attempts (ip_hash, attempted_at DESC)
        ");

        // ── remember_tokens ───────────────────────────────────────────────────
        // \"Eingeloggt bleiben\" — gespeichert als HMAC-Hash, nie plaintext
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS remember_tokens (
                id         INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id    INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
                token_hash TEXT    UNIQUE NOT NULL,
                expires_at INTEGER NOT NULL,
                created_at INTEGER NOT NULL DEFAULT (unixepoch())
            )
        ");

        $pdo->exec("
            CREATE INDEX IF NOT EXISTS idx_remember_token
            ON remember_tokens (token_hash)
        ");

        // ── Schema-Version setzen ─────────────────────────────────────────────
        $pdo->exec('PRAGMA user_version = ' . CSF_DB_SCHEMA_VERSION);

        $pdo->exec('COMMIT');

    } catch (Throwable $e) {
        $pdo->exec('ROLLBACK');
        throw $e;
    }
}

// ── Transaktions-Helfer ───────────────────────────────────────────────────────

/**
 * Führt einen Callable in einer DB-Transaktion aus.
 *
 * Rollt automatisch zurück bei Exception.
 * Gibt den Rückgabewert des Callables zurück.
 *
 * @template T
 * @param  callable(): T $fn
 * @return T
 * @throws Throwable
 */
function csf_db_transaction(callable $fn): mixed
{
    $pdo = csf_db();
    $pdo->beginTransaction();
    try {
        $result = $fn();
        $pdo->commit();
        return $result;
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}
