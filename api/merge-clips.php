<?php
/**
 * api/merge-clips.php — Multi-Scene Clip-Merge Endpunkt
 * Cinematic Studio Family
 *
 * Empfängt eine Liste von Video-Dateinamen, validiert alle Pfade,
 * führt die Clips via mergeClips() zusammen und wendet exportPreset() an.
 *
 * Methode:  POST (JSON-Body)
 * Input:    { clips: string[], preset: "720p"|"1080p", output_name?: string }
 * Output:   { success: bool, url: string, filename: string,
 *             preset: string, clip_count: int, size_bytes: int }
 *
 * Sicherheit:
 * - POST only
 * - Dateinamen gegen Regex validiert (nur zufällige hex-Namen aus upload.php)
 * - Pfade mit csf_validate_path() gegen Directory-Traversal geprüft
 * - Nur Dateien aus storage/uploads/videos/ erlaubt
 * - Output ausschließlich nach storage/exports/
 * - Keine Shell-Injection (alle Args via escapeshellarg() in functions.php)
 *
 * @since Phase 4 — TODO #28
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

// ── Methode ───────────────────────────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Nur POST erlaubt.']);
    exit;
}

// ── Input parsen ──────────────────────────────────────────────────────────────

$raw  = file_get_contents('php://input');
$body = json_decode($raw ?: '{}', true);

if (!is_array($body)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Ungültiger JSON-Body.']);
    exit;
}

$clips      = $body['clips']       ?? [];
$preset     = trim((string)($body['preset']      ?? '1080p'));
$outputName = trim((string)($body['output_name'] ?? ''));

// ── Clips validieren ─────────────────────────────────────────────────────────

if (!is_array($clips)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => '"clips" muss ein Array sein.']);
    exit;
}

$clipCount = count($clips);

if ($clipCount < 2) {
    http_response_code(400);
    echo json_encode(['success' => false,
        'error' => 'Mindestens 2 Clips erforderlich (übergeben: ' . $clipCount . ').']);
    exit;
}

if ($clipCount > 20) {
    http_response_code(400);
    echo json_encode(['success' => false,
        'error' => 'Maximal 20 Clips pro Export (übergeben: ' . $clipCount . ').']);
    exit;
}

// ── Preset validieren ─────────────────────────────────────────────────────────

$allowedPresets = ['720p', '1080p'];
if (!in_array($preset, $allowedPresets, true)) {
    // Sicherer Fallback statt Fehler
    $preset = '1080p';
}

// ── Output-Name bereinigen ────────────────────────────────────────────────────
// Nur Buchstaben, Ziffern, Unterstrich, Bindestrich — kein Pfad-Traversal möglich

if ($outputName !== '') {
    $outputName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $outputName);
    $outputName = trim($outputName, '_');
    $outputName = substr($outputName, 0, 60);
}

// ── Clip-Pfade aufbauen und validieren ───────────────────────────────────────

$videoDir = STORAGE_PATH . 'uploads/videos';

// Verzeichnis muss existieren
if (!is_dir($videoDir)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Upload-Verzeichnis nicht gefunden.']);
    exit;
}

$videoDirReal = realpath($videoDir);
$clipPaths    = [];

foreach ($clips as $i => $filename) {
    // Typ-Sicherheit
    if (!is_string($filename)) {
        http_response_code(400);
        echo json_encode(['success' => false,
            'error' => 'Clip #' . $i . ': Dateiname muss ein String sein.']);
        exit;
    }

    // Nur der reine Dateiname, kein Pfad-Anteil
    $basename = basename($filename);

    // Muss dem Upload-Muster entsprechen: 32 Hex-Zeichen + erlaubte Endung
    // api/upload.php erzeugt: bin2hex(random_bytes(16)) . '.' . $ext
    if (!preg_match('/^[a-f0-9]{32}\.(mp4|webm|mov)$/i', $basename)) {
        http_response_code(400);
        echo json_encode(['success' => false,
            'error' => 'Clip #' . $i . ': ungültiger Dateiname "' . $basename . '".']);
        exit;
    }

    $absPath = $videoDirReal . DIRECTORY_SEPARATOR . $basename;

    // csf_validate_path() prüft realpath + Storage-Root
    if (!csf_validate_path($absPath, mustExist: true)) {
        http_response_code(400);
        echo json_encode(['success' => false,
            'error' => 'Clip #' . $i . ': Datei nicht im erlaubten Bereich.']);
        exit;
    }

    if (!file_exists($absPath)) {
        http_response_code(404);
        echo json_encode(['success' => false,
            'error' => 'Clip #' . $i . ' nicht gefunden: ' . $basename]);
        exit;
    }

    $clipPaths[] = $absPath;
}

// ── Ausgabepfade bestimmen ───────────────────────────────────────────────────

$exportsDir = STORAGE_PATH . 'exports';

// Sicherstellen dass exports/ existiert
if (!is_dir($exportsDir) && !mkdir($exportsDir, 0755, true)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Export-Verzeichnis konnte nicht erstellt werden.']);
    exit;
}

$uniqueId    = bin2hex(random_bytes(8));
$namePrefix  = ($outputName !== '' ? $outputName . '_' : '') . $uniqueId;

// Zwischendatei: concat demuxer ohne Re-encode
$mergedPath  = $exportsDir . '/' . $namePrefix . '_raw.mp4';

// Enddatei: re-encodiert mit gewähltem Preset
$finalName   = $namePrefix . '_' . $preset . '.mp4';
$finalPath   = $exportsDir . '/' . $finalName;

// ── FFmpeg verfügbar? ─────────────────────────────────────────────────────────

$ffmpegCheck = checkFfmpegAvailable();
if (!$ffmpegCheck['available']) {
    http_response_code(503);
    echo json_encode(['success' => false,
        'error' => 'FFmpeg nicht verfügbar: ' . $ffmpegCheck['error']]);
    exit;
}

// ── Schritt 1: Clips zusammenführen ──────────────────────────────────────────

$mergeResult = mergeClips($clipPaths, $mergedPath);

if (!$mergeResult['success']) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Clips konnten nicht zusammengeführt werden: ' . $mergeResult['error']
                   . ' — Tipp: Alle Clips sollten dasselbe Format und dieselbe Auflösung haben.',
    ]);
    exit;
}

// ── Schritt 2: Preset anwenden ────────────────────────────────────────────────

$exportResult = exportPreset($mergedPath, $finalPath, $preset);

// Zwischendatei aufräumen (unabhängig vom Export-Ergebnis)
if (file_exists($mergedPath)) {
    @unlink($mergedPath);
}

if (!$exportResult['success']) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Export-Preset fehlgeschlagen: ' . $exportResult['error'],
    ]);
    exit;
}

// ── Erfolg ────────────────────────────────────────────────────────────────────

$sizeBytes = file_exists($finalPath) ? (int)filesize($finalPath) : 0;
$url       = BASE_URL . '/storage/exports/' . $finalName;

http_response_code(200);
echo json_encode([
    'success'    => true,
    'url'        => $url,
    'filename'   => $finalName,
    'preset'     => $preset,
    'clip_count' => $clipCount,
    'size_bytes' => $sizeBytes,
]);
