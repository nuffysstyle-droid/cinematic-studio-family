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
        // Knapp halten, keine Pfade leaken
        $response['ffmpeg']['error'] = 'unavailable';
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
