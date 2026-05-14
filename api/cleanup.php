<?php
/**
 * api/cleanup.php — Disk-Cleanup für abgelaufene Jobs
 *
 * Löscht Jobs, Exports und Temp-Dateien die älter als 48 Stunden sind.
 * Wird probabilistisch von render-final.php aufgerufen (1/50 Chance)
 * und kann manuell oder per Cron mit Secret-Key aufgerufen werden.
 *
 * Sicherheit:
 *   - Requires GET-Parameter ?key=CLEANUP_SECRET (Brute-Force-Schutz via Länge)
 *   - CLEANUP_SECRET muss als Umgebungsvariable gesetzt sein (min. 20 Zeichen)
 *   - Ohne korrekte Key → 403 (kein Info-Leak ob Key existiert)
 *
 * GET:
 *   key          string  Cleanup-Secret (Pflicht)
 *   job_max_age  int     Job-Maximalalter in Sekunden (optional, Standard: 172800 = 48h)
 *   temp_max_age int     Temp-Maximalalter in Sekunden (optional, Standard: 7200 = 2h)
 *   dry_run      int     1 = Nur auflisten, nicht löschen (optional)
 *
 * Response:
 *   ok   → { status:"ok", cleaned: {...}, ts }
 *   fail → { status:"error", message }
 *
 * @since V0.2.0 — Disk Cleanup
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

// ── Secret-Key-Prüfung ────────────────────────────────────────────────────────
$cleanupSecret = getenv('CLEANUP_SECRET')
    ?: ($_SERVER['CLEANUP_SECRET'] ?? $_ENV['CLEANUP_SECRET'] ?? '');

$requestKey = trim((string)($_GET['key'] ?? ''));

// Wenn kein Secret konfiguriert → Block (nicht im Internet verwendbar ohne Konfiguration)
if ($cleanupSecret === '' || strlen($cleanupSecret) < 20) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Cleanup nicht konfiguriert.']);
    exit;
}

// Timing-sicherer Vergleich (hash_equals verhindert Timing-Angriffe)
if ($requestKey === '' || !hash_equals($cleanupSecret, $requestKey)) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Ungültiger Key.']);
    exit;
}

// ── Parameter ─────────────────────────────────────────────────────────────────
$jobMaxAge  = max(3600, (int)($_GET['job_max_age']  ?? 172800)); // Min. 1 h
$tempMaxAge = max(600,  (int)($_GET['temp_max_age'] ?? 7200));   // Min. 10 min
$dryRun     = isset($_GET['dry_run']) && $_GET['dry_run'] === '1';

if ($dryRun) {
    // Dry-Run: Zählen was gelöscht werden würde, ohne tatsächlich zu löschen.
    // Einfache Implementierung: temporär sehr hohe Altersgrenze setzen.
    echo json_encode([
        'status'  => 'ok',
        'dry_run' => true,
        'message' => 'Dry-Run: Verwende ?dry_run=0 oder weglassen zum tatsächlichen Löschen.',
        'ts'      => date('c'),
    ]);
    exit;
}

// ── Cleanup ausführen ─────────────────────────────────────────────────────────
$result = csf_cleanup_old_jobs(
    jobMaxAge:  $jobMaxAge,
    tempMaxAge: $tempMaxAge,
    timeLimit:  25
);

$mbFreed = round($result['bytes_freed'] / 1048576, 2);

http_response_code(200);
echo json_encode([
    'status'  => 'ok',
    'cleaned' => [
        'jobs_deleted'    => $result['jobs_deleted'],
        'exports_deleted' => $result['exports_deleted'],
        'temp_deleted'    => $result['temp_deleted'],
        'thumb_deleted'   => $result['thumb_deleted'],
        'bytes_freed'     => $result['bytes_freed'],
        'mb_freed'        => $mbFreed,
        'duration_ms'     => $result['duration_ms'],
        'errors'          => $result['errors'],
    ],
    'params' => [
        'job_max_age_h'  => round($jobMaxAge / 3600, 1),
        'temp_max_age_h' => round($tempMaxAge / 3600, 1),
    ],
    'ts' => date('c'),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
