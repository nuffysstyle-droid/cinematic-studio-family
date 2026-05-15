<?php
/**
 * bin/cleanup-cron.php — CLI-Cleanup-Script (Render Cron Job)
 *
 * Läuft täglich via Render Cron Service (render.yaml).
 * Löscht abgelaufene Jobs, Exports und Temp-Dateien.
 *
 * Aufruf: php /var/www/html/bin/cleanup-cron.php
 * Optionen:
 *   --job-max-age   Sek. (Standard: 172800 = 48h)
 *   --temp-max-age  Sek. (Standard: 7200  = 2h)
 *   --time-limit    Sek. (Standard: 60    = 1min)
 *
 * Exit-Code:
 *   0 = OK
 *   1 = Fehler (Storage nicht gefunden, etc.)
 */

declare(strict_types=1);

// ── CLI-Only-Guard ─────────────────────────────────────────────────────────
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Nur CLI-Aufruf erlaubt.' . PHP_EOL);
}

define('CLEANUP_CRON_START', microtime(true));

// ── Bootstrap ──────────────────────────────────────────────────────────────
$scriptDir = dirname(__DIR__);
require_once $scriptDir . '/includes/functions.php';

// ── CLI-Argumente parsen ───────────────────────────────────────────────────
$opts = getopt('', ['job-max-age:', 'temp-max-age:', 'time-limit:']);

$jobMaxAge  = max(3600, (int)($opts['job-max-age']  ?? 172800));
$tempMaxAge = max(600,  (int)($opts['temp-max-age'] ?? 7200));
$timeLimit  = max(10,   (int)($opts['time-limit']   ?? 60));

// ── Cleanup ausführen ──────────────────────────────────────────────────────
$ts = date('c');
echo "[{$ts}] CSF Cleanup Cron gestartet" . PHP_EOL;
echo "  job_max_age  = {$jobMaxAge}s (" . round($jobMaxAge / 3600, 1) . "h)" . PHP_EOL;
echo "  temp_max_age = {$tempMaxAge}s (" . round($tempMaxAge / 3600, 1) . "h)" . PHP_EOL;
echo "  time_limit   = {$timeLimit}s" . PHP_EOL;

$result = csf_cleanup_old_jobs(
    jobMaxAge:  $jobMaxAge,
    tempMaxAge: $tempMaxAge,
    timeLimit:  $timeLimit
);

$mbFreed = round($result['bytes_freed'] / 1048576, 2);

echo PHP_EOL;
echo "  ✓ Jobs gelöscht:    " . $result['jobs_deleted']    . PHP_EOL;
echo "  ✓ Exports gelöscht: " . $result['exports_deleted'] . PHP_EOL;
echo "  ✓ Temp gelöscht:    " . $result['temp_deleted']    . PHP_EOL;
echo "  ✓ Thumbs gelöscht:  " . $result['thumb_deleted']   . PHP_EOL;
echo "  ✓ Bytes freigegeben: " . $result['bytes_freed']    . " ({$mbFreed} MB)" . PHP_EOL;
echo "  ✓ Dauer:            " . $result['duration_ms']     . " ms" . PHP_EOL;

if (!empty($result['errors'])) {
    echo PHP_EOL;
    echo "  ⚠ Fehler:" . PHP_EOL;
    foreach ($result['errors'] as $err) {
        echo "    - {$err}" . PHP_EOL;
    }
}

$total = round((microtime(true) - CLEANUP_CRON_START) * 1000);
echo PHP_EOL;
echo "[" . date('c') . "] Cleanup abgeschlossen in {$total} ms" . PHP_EOL;

exit(0);
