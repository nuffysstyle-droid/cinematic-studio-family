<?php
/**
 * bin/cleanup-cron.php — Täglicher Disk-Cleanup (Render Cron-Job)
 *
 * Wird von render.yaml täglich um 03:00 UTC via Docker gestartet:
 *   startCommand: php /var/www/html/bin/cleanup-cron.php
 *
 * Löscht:
 *   - Jobs + Exports älter als 48h  (storage/jobs/, storage/exports/)
 *   - Thumbnails ohne zugehörigen Job
 *   - Temp-Dateien älter als 2h     (storage/temp/)
 *
 * Schreibt Ergebnis nach stdout (Render-Logs).
 * Exit-Code 0 = Erfolg, 1 = Fehler.
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "cleanup-cron.php darf nur via CLI ausgeführt werden.\n");
    exit(1);
}

$root = dirname(__DIR__);
require_once $root . '/includes/config.php';
require_once $root . '/includes/functions.php';

echo '[' . date('c') . '] CSF Disk-Cleanup gestartet' . PHP_EOL;

try {
    $result = csf_cleanup_old_jobs();

    echo '[' . date('c') . '] Cleanup abgeschlossen:' . PHP_EOL;
    echo '  Jobs entfernt:       ' . (int)($result['jobs_removed']    ?? 0) . PHP_EOL;
    echo '  Exports entfernt:    ' . (int)($result['exports_removed'] ?? 0) . PHP_EOL;
    echo '  Thumbnails entfernt: ' . (int)($result['thumbs_removed']  ?? 0) . PHP_EOL;
    echo '  Temp entfernt:       ' . (int)($result['temp_removed']    ?? 0) . PHP_EOL;
    echo '  Bytes freigegeben:   ' . number_format((int)($result['bytes_freed'] ?? 0)) . PHP_EOL;

    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, '[' . date('c') . '] Cleanup FEHLER: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
