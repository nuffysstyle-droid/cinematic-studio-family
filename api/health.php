<?php
/**
 * api/health.php — Smoketest-Endpoint für Cinematic Studio Family
 *
 * Liefert maschinen-lesbaren Status nach Deploy:
 *   - PHP-Version
 *   - FFmpeg verfügbar + Version
 *   - Storage beschreibbar
 *
 * Zweck: Manueller / automatischer Smoketest nach Render-Deploy.
 * Pfad ist NICHT der Render-Healthcheck (das bleibt /index.php) —
 * dieser Endpoint ist absichtlich detaillierter.
 *
 * Sicherheit: Keine internen Pfade leaken, keine Auth nötig (read-only Status).
 *
 * @since Phase 5 — TODO #38 (Render Deployment)
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

require_once __DIR__ . '/../includes/functions.php';

$response = [
    'ok'               => true,
    'php'              => PHP_VERSION,
    'ffmpeg'           => [
        'available' => false,
        'version'   => '',
    ],
    'storage_writable' => false,
];

// ── FFmpeg-Check ─────────────────────────────────────────────────────────────
try {
    $ff = checkFfmpegAvailable();
    $response['ffmpeg']['available'] = (bool)($ff['available'] ?? false);
    $response['ffmpeg']['version']   = (string)($ff['version']   ?? '');
    if (!$response['ffmpeg']['available']) {
        // Diagnose-Modus: konkreten Fehler + Binary-Pfad ausgeben.
        // /usr/bin/ffmpeg ist ein bekannter Standard-Pfad und kein Security-Leak;
        // ohne diese Info ist Render-Debugging nicht möglich.
        $response['ffmpeg']['error']          = (string)($ff['error'] ?? 'unavailable');
        $response['ffmpeg']['bin']            = (string)($ff['bin']   ?? '');
        $response['ffmpeg']['bin_exists']     = isset($ff['bin']) && file_exists($ff['bin']);
        $response['ffmpeg']['bin_exec']       = isset($ff['bin']) && is_executable($ff['bin']);
        $response['ffmpeg']['exit_code']      = (int)($ff['exit_code'] ?? -1);
        $response['ffmpeg']['timed_out']      = (bool)($ff['timed_out'] ?? false);
        $response['ffmpeg']['stdout_preview'] = (string)($ff['stdout_preview'] ?? '');
        $response['ffmpeg']['stderr_preview'] = (string)($ff['stderr_preview'] ?? '');
        $response['ffmpeg']['command']        = (string)($ff['command'] ?? '');
        $response['ok'] = false;
    }
} catch (Throwable $e) {
    $response['ffmpeg']['error'] = 'check_failed';
    $response['ok'] = false;
}

// ── Storage-Writability ──────────────────────────────────────────────────────
// Schreibtest in storage/temp/ — dort landen ohnehin nur kurzlebige Dateien.
$storageTemp = __DIR__ . '/../storage/temp';
$probe = $storageTemp . '/.health-' . bin2hex(random_bytes(4));
if (is_dir($storageTemp) && @file_put_contents($probe, 'ok') !== false) {
    $response['storage_writable'] = true;
    @unlink($probe);
} else {
    $response['ok'] = false;
}

http_response_code($response['ok'] ? 200 : 503);
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
