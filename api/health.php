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

// ── KI-Konfiguration (Wert nie ausgeben, nur Präsenz) ────────────────────────
$kieKey    = getenv('KIE_AI_API_KEY') ?: ($_SERVER['KIE_AI_API_KEY'] ?? $_ENV['KIE_AI_API_KEY'] ?? '');
$debugMode = isset($_GET['debug']) && $_GET['debug'] === '1';

$response['ai'] = [
    'kie_key_set'    => $kieKey !== '',
    'kie_key_source' => $kieKey !== ''
        ? (getenv('KIE_AI_API_KEY') ? 'getenv' : (isset($_SERVER['KIE_AI_API_KEY']) ? '_SERVER' : '_ENV'))
        : 'none',
];

// Verbose debug-Felder nur bei ?debug=1 — nicht in Produktion sichtbar
if ($debugMode) {
    $response['ai']['env_keys'] = array_values(array_filter(
        array_keys((array)(getenv() ?: [])),
        fn($k) => !str_starts_with($k, 'APACHE_') && !str_starts_with($k, 'HTTPS')
    ));
    $response['ai']['server_keys_custom'] = array_values(array_filter(
        array_keys($_SERVER),
        fn($k) => !str_starts_with($k, 'HTTP_') && !str_starts_with($k, 'APACHE_')
                && !in_array($k, ['SERVER_NAME','SERVER_PORT','SERVER_ADDR','SERVER_PROTOCOL',
                                   'REQUEST_METHOD','REQUEST_URI','QUERY_STRING','DOCUMENT_ROOT',
                                   'SCRIPT_FILENAME','SCRIPT_NAME','PHP_SELF','GATEWAY_INTERFACE',
                                   'SERVER_SOFTWARE','REMOTE_ADDR','REMOTE_PORT','REQUEST_TIME',
                                   'REQUEST_TIME_FLOAT','SERVER_SIGNATURE'], true)
    ));
}

// ── Storage-Nutzung (Job-Count, Export-Count) ─────────────────────────────────
$jobsDir    = __DIR__ . '/../storage/jobs';
$exportsDir = __DIR__ . '/../storage/exports';

$jobCount    = 0;
$exportCount = 0;
$exportBytes = 0;

if (is_dir($jobsDir)) {
    foreach ((array)@scandir($jobsDir) as $e) {
        if (str_starts_with((string)$e, 'job_') && is_dir($jobsDir . '/' . $e)) { $jobCount++; }
    }
}
if (is_dir($exportsDir)) {
    foreach ((array)@scandir($exportsDir) as $e) {
        $fp = $exportsDir . '/' . $e;
        if (str_starts_with((string)$e, 'job_') && is_file($fp)) {
            $exportCount++;
            $exportBytes += (int)filesize($fp);
        }
    }
}

$response['storage'] = [
    'active_jobs'    => $jobCount,
    'export_files'   => $exportCount,
    'export_mb'      => round($exportBytes / 1048576, 2),
];

// ── Manuelles Cleanup per ?cleanup=1 (nur im Debug-Modus) ────────────────────
if ($debugMode && isset($_GET['cleanup']) && $_GET['cleanup'] === '1') {
    $cleaned = csf_cleanup_old_jobs();
    $response['cleanup_run'] = $cleaned;
}

http_response_code($response['ok'] ? 200 : 503);
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
