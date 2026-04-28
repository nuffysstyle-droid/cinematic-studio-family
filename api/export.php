<?php
/**
 * api/export.php — Zentraler Export-Endpunkt
 * Cinematic Studio Family
 *
 * Unterstützte Actions (V1):
 *   convert   → Video in Qualitäts-Preset exportieren (POST)
 *   thumbnail → Thumbnail aus Video extrahieren (POST)
 *   info      → Video-Metadaten auslesen (GET oder POST)
 *
 * Vorbereitet für spätere Integration:
 *   merge     → Stub (501) — aktuell in api/merge-clips.php
 *   status    → Stub (501) — kommt in TODO #30 (Polling)
 *
 * Input-Format (POST):  JSON-Body
 * Input-Format (GET):   Query-Parameter (nur für action=info)
 *
 * Response immer JSON:
 *   Erfolg: { success: true,  action, job_id, data: {...} }
 *   Fehler: { success: false, action, error }
 *
 * Job-Protokoll: data/export-jobs.json (LOCK_EX, max. 500 Einträge)
 *
 * Sicherheit:
 * - POST-only für convert/thumbnail
 * - Dateinamen gegen Upload-Muster validiert (/^[a-f0-9]{32}\.(mp4|webm|mov)$/i)
 * - Pfade via csf_validate_path() (realpath + CSF_STORAGE_ROOT)
 * - Eingaben nur aus storage/uploads/videos/
 * - Ausgaben nur in storage/exports/ oder storage/thumbnails/
 * - Preset-Whitelist, Offset-Bereinigung via Regex
 * - Keine Shell-Injection (alle FFmpeg-Args via escapeshellarg())
 *
 * @since Phase 4 — TODO #29
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

// ── Konstanten ────────────────────────────────────────────────────────────────

/** Erlaubte Actions und ob sie POST-only sind */
const EXPORT_ACTIONS = [
    'convert'   => ['post_only' => true],
    'thumbnail' => ['post_only' => true],
    'info'      => ['post_only' => false],
    'merge'     => ['post_only' => true],   // Stub — TODO #28 Integration
    'status'    => ['post_only' => false],  // Stub — TODO #30 Polling
];

/** Gültige Export-Presets für action=convert */
const EXPORT_VALID_PRESETS = ['720p', '1080p'];

/** Erlaubtes Dateinamen-Muster (aus api/upload.php: bin2hex(random_bytes(16)).ext) */
const EXPORT_FILE_PATTERN = '/^[a-f0-9]{32}\.(mp4|webm|mov)$/i';

/** Maximale Jobs in export-jobs.json */
const EXPORT_JOBS_MAX = 500;

// ── Request-Methode + Input parsen ────────────────────────────────────────────

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $input = $_GET;
} elseif ($method === 'POST') {
    $raw   = file_get_contents('php://input');
    $input = json_decode($raw ?: '{}', true);
    // Fallback auf $_POST (für Formular-Requests ohne Content-Type: application/json)
    if (!is_array($input) || empty($input)) {
        $input = $_POST;
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'action' => '', 'error' => 'Nur GET und POST erlaubt.']);
    exit;
}

if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'action' => '', 'error' => 'Ungültiger Request-Body.']);
    exit;
}

// ── Action bestimmen ──────────────────────────────────────────────────────────

$action = strtolower(trim((string)($input['action'] ?? '')));

if ($action === '') {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'action'  => '',
        'error'   => '"action" fehlt. Erlaubt: ' . implode(', ', array_keys(EXPORT_ACTIONS)),
    ]);
    exit;
}

if (!array_key_exists($action, EXPORT_ACTIONS)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'action'  => $action,
        'error'   => 'Unbekannte Action "' . $action . '". Erlaubt: '
                   . implode(', ', array_keys(EXPORT_ACTIONS)),
    ]);
    exit;
}

// ── POST-only prüfen ─────────────────────────────────────────────────────────

if (EXPORT_ACTIONS[$action]['post_only'] && $method !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'action'  => $action,
        'error'   => 'Action "' . $action . '" erfordert POST.',
    ]);
    exit;
}

// ── Stub-Actions ──────────────────────────────────────────────────────────────

if ($action === 'merge') {
    // Merge-Funktionalität bleibt in api/merge-clips.php (TODO #28).
    // Kann in einer späteren Phase hier integriert werden.
    http_response_code(501);
    echo json_encode([
        'success' => false,
        'action'  => 'merge',
        'error'   => 'action=merge: Nutze api/merge-clips.php. Integration geplant.',
    ]);
    exit;
}

if ($action === 'status') {
    // Job-Status-Polling kommt in TODO #30 (api/progress.php).
    http_response_code(501);
    echo json_encode([
        'success' => false,
        'action'  => 'status',
        'error'   => 'action=status: Polling-Endpunkt kommt in TODO #30 (api/progress.php).',
    ]);
    exit;
}

// ── Eingabedatei auflösen (für alle verbleibenden Actions) ───────────────────

$filenameOrUrl = trim((string)($input['filename'] ?? $input['url'] ?? ''));

if ($filenameOrUrl === '') {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'action'  => $action,
        'error'   => '"filename" (oder "url") fehlt.',
    ]);
    exit;
}

$inputPath = export_resolve_input($filenameOrUrl);

if ($inputPath === null) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'action'  => $action,
        'error'   => 'Datei nicht gefunden oder nicht erlaubt. '
                   . 'Nur Dateien aus storage/uploads/videos/ mit korrektem Namen erlaubt.',
    ]);
    exit;
}

// ── FFmpeg-Verfügbarkeit (nur für actionsmit FFmpeg-Nutzung) ─────────────────

if (in_array($action, ['convert', 'thumbnail'], true)) {
    $ffmpegCheck = checkFfmpegAvailable();
    if (!$ffmpegCheck['available']) {
        http_response_code(503);
        echo json_encode([
            'success' => false,
            'action'  => $action,
            'error'   => 'FFmpeg nicht verfügbar: ' . $ffmpegCheck['error'],
        ]);
        exit;
    }
}

// ── Job-ID ────────────────────────────────────────────────────────────────────

$jobId = bin2hex(random_bytes(8)); // 16-stellige Hex-ID

// ═════════════════════════════════════════════════════════════════════════════
//  ACTION: convert
// ═════════════════════════════════════════════════════════════════════════════

if ($action === 'convert') {

    $preset     = trim((string)($input['preset'] ?? '1080p'));
    $outputName = trim((string)($input['output_name'] ?? ''));

    // Preset validieren
    if (!in_array($preset, EXPORT_VALID_PRESETS, true)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'action'  => $action,
            'error'   => 'Ungültiges Preset "' . $preset . '". Erlaubt: '
                       . implode(', ', EXPORT_VALID_PRESETS),
        ]);
        exit;
    }

    // Output-Name bereinigen
    if ($outputName !== '') {
        $outputName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $outputName);
        $outputName = trim($outputName, '_');
        $outputName = substr($outputName, 0, 60);
    }

    // Ausgabepfad
    $inputBasename = pathinfo(basename($inputPath), PATHINFO_FILENAME);
    $namePrefix    = ($outputName !== '' ? $outputName . '_' : $inputBasename . '_')
                   . $preset . '_' . $jobId;
    $outputFile    = $namePrefix . '.mp4';
    $outputPath    = STORAGE_PATH . 'exports/' . $outputFile;

    // Export ausführen
    $result = exportPreset($inputPath, $outputPath, $preset);

    $status = $result['success'] ? 'done' : 'failed';

    export_log_job([
        'id'         => $jobId,
        'action'     => 'convert',
        'input_file' => basename($inputPath),
        'output_file'=> $result['success'] ? $outputFile : null,
        'output_url' => $result['success'] ? BASE_URL . '/storage/exports/' . $outputFile : null,
        'preset'     => $preset,
        'status'     => $status,
        'created_at' => date('c'),
        'error'      => $result['success'] ? null : $result['error'],
    ]);

    if (!$result['success']) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'action'  => $action,
            'job_id'  => $jobId,
            'error'   => $result['error'],
        ]);
        exit;
    }

    $sizeBytes  = file_exists($outputPath) ? (int)filesize($outputPath) : 0;
    $outputUrl  = BASE_URL . '/storage/exports/' . $outputFile;

    echo json_encode([
        'success' => true,
        'action'  => $action,
        'job_id'  => $jobId,
        'data'    => [
            'url'        => $outputUrl,
            'filename'   => $outputFile,
            'preset'     => $preset,
            'label'      => $result['label'] ?? $preset,
            'size_bytes' => $sizeBytes,
        ],
    ]);
    exit;
}

// ═════════════════════════════════════════════════════════════════════════════
//  ACTION: thumbnail
// ═════════════════════════════════════════════════════════════════════════════

if ($action === 'thumbnail') {

    $offset = trim((string)($input['offset'] ?? '00:00:01'));

    // Offset bereinigen: nur Ziffern, Doppelpunkte, Punkte erlaubt
    $offset = preg_replace('/[^0-9:.]/', '', $offset) ?: '00:00:01';

    // Ausgabepfad
    $inputBasename = pathinfo(basename($inputPath), PATHINFO_FILENAME);
    $outputFile    = $inputBasename . '_thumb_' . $jobId . '.jpg';
    $outputPath    = STORAGE_PATH . 'thumbnails/' . $outputFile;

    // Sicherstellen dass thumbnails/ existiert
    if (!is_dir(STORAGE_PATH . 'thumbnails')) {
        mkdir(STORAGE_PATH . 'thumbnails', 0755, true);
    }

    $result = generateThumbnail($inputPath, $outputPath, $offset);

    $status = $result['success'] ? 'done' : 'failed';

    export_log_job([
        'id'         => $jobId,
        'action'     => 'thumbnail',
        'input_file' => basename($inputPath),
        'output_file'=> $result['success'] ? $outputFile : null,
        'output_url' => $result['success'] ? BASE_URL . '/storage/thumbnails/' . $outputFile : null,
        'offset'     => $offset,
        'status'     => $status,
        'created_at' => date('c'),
        'error'      => $result['success'] ? null : $result['error'],
    ]);

    if (!$result['success']) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'action'  => $action,
            'job_id'  => $jobId,
            'error'   => $result['error'],
        ]);
        exit;
    }

    $sizeBytes = file_exists($outputPath) ? (int)filesize($outputPath) : 0;
    $outputUrl = BASE_URL . '/storage/thumbnails/' . $outputFile;

    echo json_encode([
        'success' => true,
        'action'  => $action,
        'job_id'  => $jobId,
        'data'    => [
            'url'        => $outputUrl,
            'filename'   => $outputFile,
            'offset'     => $offset,
            'size_bytes' => $sizeBytes,
        ],
    ]);
    exit;
}

// ═════════════════════════════════════════════════════════════════════════════
//  ACTION: info
// ═════════════════════════════════════════════════════════════════════════════

if ($action === 'info') {

    $result = getVideoInfo($inputPath);

    // Info-Jobs werden nicht geloggt (kein Output, kein FFmpeg)

    if (!$result['success']) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'action'  => $action,
            'error'   => $result['error'],
        ]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'action'  => $action,
        'data'    => $result['data'],
    ]);
    exit;
}

// ═════════════════════════════════════════════════════════════════════════════
//  INTERNE HELFER
// ═════════════════════════════════════════════════════════════════════════════

/**
 * Löst einen Dateinamen oder relativen URL zu einem validierten absoluten Pfad auf.
 *
 * Akzeptiert:
 *   - Reiner Dateiname:  "abc123...def.mp4"
 *   - Relativer URL:     "/storage/uploads/videos/abc123...def.mp4"
 *   - URL mit BASE_URL:  "http://localhost/storage/uploads/videos/abc123...def.mp4"
 *
 * Gibt null zurück wenn:
 *   - Dateiname nicht dem Upload-Muster entspricht
 *   - Datei nicht in storage/uploads/videos/ liegt
 *   - Pfad-Validierung fehlschlägt
 *   - Datei nicht existiert
 */
function export_resolve_input(string $filenameOrUrl): ?string
{
    // Basename extrahieren (entfernt jeden Pfadanteil)
    $basename = basename($filenameOrUrl);

    // Muss Upload-API-Muster entsprechen: 32 Hex-Zeichen + erlaubte Endung
    if (!preg_match(EXPORT_FILE_PATTERN, $basename)) {
        return null;
    }

    $videoDir = STORAGE_PATH . 'uploads/videos';
    $dirReal  = realpath($videoDir);

    if ($dirReal === false || !is_dir($dirReal)) {
        return null;
    }

    $absPath = $dirReal . DIRECTORY_SEPARATOR . $basename;

    // Pfad-Validierung via functions.php (realpath + CSF_STORAGE_ROOT)
    if (!csf_validate_path($absPath, mustExist: true)) {
        return null;
    }

    if (!file_exists($absPath)) {
        return null;
    }

    return $absPath;
}

/**
 * Schreibt einen Job-Eintrag in data/export-jobs.json.
 *
 * Verwendet LOCK_EX für Race-Condition-Schutz.
 * Begrenzt auf EXPORT_JOBS_MAX Einträge (älteste werden entfernt).
 *
 * @param array $job Assoziatives Array mit Job-Daten
 */
function export_log_job(array $job): void
{
    $file = DATA_PATH . 'export-jobs.json';

    // Datei anlegen wenn nicht vorhanden
    if (!file_exists($file)) {
        @file_put_contents($file, '[]');
    }

    $fp = @fopen($file, 'c+');
    if ($fp === false) {
        return; // Logging-Fehler sind nicht kritisch
    }

    flock($fp, LOCK_EX);

    $content = stream_get_contents($fp);
    $jobs    = json_decode($content ?: '[]', true);

    if (!is_array($jobs)) {
        $jobs = [];
    }

    // Alte Einträge trimmen (neueste behalten)
    if (count($jobs) >= EXPORT_JOBS_MAX) {
        $jobs = array_slice($jobs, -(EXPORT_JOBS_MAX - 1));
    }

    $jobs[] = $job;

    ftruncate($fp, 0);
    rewind($fp);
    fwrite($fp, json_encode($jobs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    flock($fp, LOCK_UN);
    fclose($fp);
}
