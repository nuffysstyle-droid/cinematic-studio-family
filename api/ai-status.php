<?php
/**
 * api/ai-status.php — KI-Task Status + Bild-Download (Kie.ai Flux Kontext)
 *
 * Pollt den Kie.ai Task-Status. Bei successFlag=1 (SUCCESS) wird das
 * generierte Bild heruntergeladen, MIME-geprüft, unter
 *   storage/jobs/{job_id}/replacements/slot_NN_ai_{rand}.jpg
 * gespeichert und meta.json mit den Replacement-Feldern aktualisiert —
 * identisch zum Format von replace-slot.php, sodass render-final.php
 * das Bild ohne jede Änderung verarbeiten kann.
 *
 * Methode:  GET
 * Parameter:
 *   job_id       string  job_YYYYMMDD_HHMMSS_xxxxxxxx
 *   slot_number  int     1–12
 *
 * API-Key: ausschließlich getenv('KIE_AI_API_KEY')
 *
 * successFlag-Mapping (Kie.ai):
 *   0 = GENERATING    → { status:"generating" }
 *   1 = SUCCESS       → Bild herunterladen, meta.json updaten → { status:"done" }
 *   2 = CREATE_TASK_FAILED → { status:"failed" }
 *   3 = GENERATE_FAILED   → { status:"failed" }
 *
 * Antworten:
 *   generating → { status:"generating", job_id, slot_number, task_id }
 *   done       → { status:"done", job_id, slot_number, task_id, replacement_file, model, completed_at }
 *   failed     → { status:"failed", job_id, slot_number, task_id, message, kie_flag }
 *   error      → { status:"error", message }
 *
 * Render-final.php und replace-slot.php bleiben unberührt.
 *
 * @since V2 AI Integration
 */

declare(strict_types=1);

header('Content-Type: application/json');

// ── Hilfsfunktionen ──────────────────────────────────────────────────────────

/**
 * HTTP-GET via file_get_contents.
 *
 * Hinweis: $http_response_header wird von PHP im lokalen Scope der
 * aufrufenden Funktion gesetzt — kein global nötig.
 */
function csf_kie_get(string $url, string $apiKey): array
{
    $opts = [
        'http' => [
            'method'          => 'GET',
            'header'          => 'Authorization: Bearer ' . $apiKey . "\r\n"
                               . 'Accept: application/json',
            'timeout'         => 20,
            'ignore_errors'   => true,
            'follow_location' => 1,
        ],
    ];

    $ctx  = stream_context_create($opts);
    $body = @file_get_contents($url, false, $ctx);

    if ($body === false) {
        return ['status' => 0, 'body' => '', 'data' => []];
    }

    $status = 0;
    if (!empty($http_response_header)) {
        preg_match('/\s(\d{3})\s/', $http_response_header[0], $m);
        $status = isset($m[1]) ? (int) $m[1] : 0;
    }

    return [
        'status' => $status,
        'body'   => $body,
        'data'   => json_decode($body, true) ?? [],
    ];
}

/**
 * JSON-Fehlerantwort ausgeben und beenden.
 * @return never
 */
function csf_ai_status_error(int $httpCode, string $message, array $extra = []): never
{
    http_response_code($httpCode);
    echo json_encode(
        array_merge(['status' => 'error', 'message' => $message], $extra),
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
    );
    exit;
}

// ── Methode ──────────────────────────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    csf_ai_status_error(405, 'Nur GET erlaubt.');
}

// ── API-Key ───────────────────────────────────────────────────────────────────
// Reihenfolge: getenv() → $_SERVER → $_ENV
// Apache mod_env benötigt PassEnv in apache.conf damit getenv() greift.

$apiKey = (string) (getenv('KIE_AI_API_KEY') ?: ($_SERVER['KIE_AI_API_KEY'] ?? $_ENV['KIE_AI_API_KEY'] ?? ''));
if ($apiKey === '') {
    csf_ai_status_error(503, 'KI-Generierung ist nicht konfiguriert. KIE_AI_API_KEY fehlt auf dem Server.');
}

// ── Eingaben ──────────────────────────────────────────────────────────────────

$jobId      = trim((string) ($_GET['job_id']      ?? ''));
$slotNumber = (int)          ($_GET['slot_number'] ?? 0);

if (!preg_match('/^job_\d{8}_\d{6}_[a-f0-9]{8}$/', $jobId)) {
    csf_ai_status_error(400, 'Ungültige job_id. Format: job_YYYYMMDD_HHMMSS_xxxxxxxx');
}

if ($slotNumber < 1 || $slotNumber > 12) {
    csf_ai_status_error(400, 'slot_number muss zwischen 1 und 12 liegen.');
}

// ── Pfade + Pfad-Traversal-Schutz ────────────────────────────────────────────

$storageRoot = realpath(__DIR__ . '/../storage');
if ($storageRoot === false) {
    csf_ai_status_error(500, 'Storage-Verzeichnis nicht gefunden.');
}

$jobDir     = $storageRoot . '/jobs/' . $jobId;
$metaPath   = $jobDir . '/meta.json';
$replaceDir = $jobDir . '/replacements';

$realJobDir = realpath($jobDir);
if ($realJobDir === false || strpos($realJobDir, $storageRoot) !== 0) {
    csf_ai_status_error(404, 'Job nicht gefunden.');
}

// ── meta.json lesen (LOCK_SH) ─────────────────────────────────────────────────

$fp = @fopen($metaPath, 'r');
if ($fp === false) {
    csf_ai_status_error(404, 'meta.json nicht gefunden.');
}

if (!flock($fp, LOCK_SH)) {
    fclose($fp);
    csf_ai_status_error(500, 'meta.json konnte nicht gesperrt werden.');
}

$rawContent = stream_get_contents($fp);
flock($fp, LOCK_UN);
fclose($fp);

$meta = ($rawContent !== false && $rawContent !== '')
          ? json_decode($rawContent, true)
          : null;

if (!is_array($meta)) {
    csf_ai_status_error(404, 'meta.json ungültig.');
}

// ── Slot finden ───────────────────────────────────────────────────────────────

$slotIndex = null;
foreach (($meta['slots'] ?? []) as $i => $s) {
    if ((int) ($s['slot'] ?? 0) === $slotNumber) {
        $slotIndex = $i;
        break;
    }
}

if ($slotIndex === null) {
    csf_ai_status_error(404, 'Slot ' . $slotNumber . ' nicht in meta.json gefunden.');
}

$slot   = $meta['slots'][$slotIndex];
$taskId = $slot['ai_task_id'] ?? null;

if (!is_string($taskId) || $taskId === '') {
    csf_ai_status_error(400, 'Kein AI-Task für Slot ' . $slotNumber . ' vorhanden. Bitte zuerst generate-ai.php aufrufen.');
}

// ── Bereits abgeschlossene Zustände direkt zurückgeben ────────────────────────

$currentStatus = $slot['ai_status'] ?? null;

if ($currentStatus === 'done') {
    echo json_encode([
        'status'           => 'done',
        'job_id'           => $jobId,
        'slot_number'      => $slotNumber,
        'task_id'          => $taskId,
        'replacement_file' => $slot['replacement_file'] ?? null,
        'model'            => $slot['ai_model']         ?? null,
        'completed_at'     => $slot['ai_completed_at']  ?? null,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

if ($currentStatus === 'failed') {
    echo json_encode([
        'status'      => 'failed',
        'job_id'      => $jobId,
        'slot_number' => $slotNumber,
        'task_id'     => $taskId,
        'message'     => 'Generierung ist fehlgeschlagen.',
        'kie_flag'    => $slot['ai_flag'] ?? null,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// ── Kie.ai Poll ───────────────────────────────────────────────────────────────

$pollUrl   = 'https://api.kie.ai/api/v1/flux/kontext/record-info?taskId=' . urlencode($taskId);
$kieResult = csf_kie_get($pollUrl, $apiKey);

if ($kieResult['status'] === 0) {
    csf_ai_status_error(502, 'Kie.ai nicht erreichbar. Bitte erneut versuchen.');
}

if ($kieResult['status'] === 401 || $kieResult['status'] === 403) {
    csf_ai_status_error(401, 'KIE_AI_API_KEY ungültig oder abgelaufen.');
}

$kieData     = $kieResult['data'];
$successFlag = $kieData['data']['successFlag'] ?? null;

// ── Flag 0: noch am Generieren ────────────────────────────────────────────────

if ($successFlag === 0 || $successFlag === null) {
    echo json_encode([
        'status'      => 'generating',
        'job_id'      => $jobId,
        'slot_number' => $slotNumber,
        'task_id'     => $taskId,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// ── Flag 2 oder 3: Generierung fehlgeschlagen ─────────────────────────────────

if ($successFlag === 2 || $successFlag === 3) {
    $flagDesc = $successFlag === 2 ? 'CREATE_TASK_FAILED' : 'GENERATE_FAILED';

    // meta.json auf failed setzen
    $fp2 = @fopen($metaPath, 'c+');
    if ($fp2 !== false && flock($fp2, LOCK_EX)) {
        $raw2 = stream_get_contents($fp2);
        $m2   = ($raw2 !== false && $raw2 !== '') ? json_decode($raw2, true) : null;
        if (is_array($m2)) {
            foreach ($m2['slots'] as &$s2) {
                if ((int) ($s2['slot'] ?? 0) === $slotNumber) {
                    $s2['ai_status']  = 'failed';
                    $s2['ai_flag']    = $successFlag;
                    $s2['updated_at'] = date('c');
                    break;
                }
            }
            unset($s2);
            $encoded = json_encode($m2, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            if ($encoded !== false) {
                ftruncate($fp2, 0);
                rewind($fp2);
                fwrite($fp2, $encoded);
                fflush($fp2);
            }
        }
        flock($fp2, LOCK_UN);
        fclose($fp2);
    }

    // HTTP 200 — Frontend-Polling erwartet immer 200 (Status steht im Body)
    http_response_code(200);
    echo json_encode([
        'status'      => 'failed',
        'job_id'      => $jobId,
        'slot_number' => $slotNumber,
        'task_id'     => $taskId,
        'message'     => 'Kie.ai Generierung fehlgeschlagen (' . $flagDesc . ').',
        'kie_flag'    => $successFlag,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// ── Flag 1: Erfolg — Bild downloaden + speichern ─────────────────────────────

if ($successFlag !== 1) {
    csf_ai_status_error(502, 'Unbekannter successFlag von Kie.ai: ' . (string) $successFlag);
}

$resultImageUrl = $kieData['data']['response']['resultImageUrl'] ?? null;
if (!is_string($resultImageUrl) || $resultImageUrl === '') {
    csf_ai_status_error(502, 'Kie.ai successFlag=1, aber keine resultImageUrl in der Antwort.');
}

// ── SSRF-Schutz ──────────────────────────────────────────────────────────────
// 1. Schema: nur https:// erlaubt
if (!str_starts_with($resultImageUrl, 'https://')) {
    csf_ai_status_error(502, 'resultImageUrl hat ungültiges Schema (erwartet https://).');
}

// 2. Hostname darf nicht auf private/loopback/reservierte IP auflösen
//    (verhindert SSRF über z.B. https://169.254.169.254/... nach DNS-Auflösung)
$ssrfHost = parse_url($resultImageUrl, PHP_URL_HOST);
if (is_string($ssrfHost) && $ssrfHost !== '') {
    $resolved = @gethostbyname($ssrfHost);
    // gethostbyname() gibt bei Fehler den Host selbst zurück → dann Prüfung überspringen
    if ($resolved !== $ssrfHost) {
        $isPublic = filter_var(
            $resolved,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        );
        if ($isPublic === false) {
            csf_ai_status_error(502, 'resultImageUrl löst zu privater oder reservierter IP auf — SSRF blockiert.');
        }
    }
}

// Bild herunterladen (kein API-Key nötig — CDN-URL ist öffentlich)
$imgCtx = stream_context_create([
    'http' => [
        'method'          => 'GET',
        'timeout'         => 25,
        'ignore_errors'   => false,
        'follow_location' => 1,
    ],
]);
$imgData = @file_get_contents($resultImageUrl, false, $imgCtx);

if ($imgData === false || strlen($imgData) < 100) {
    csf_ai_status_error(502, 'Bild-Download von Kie.ai CDN fehlgeschlagen.');
}

// MIME via finfo prüfen — CDN-Content-Type ist nicht vertrauenswürdig
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime  = $finfo->buffer($imgData);

$allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
if (!in_array($mime, $allowedMimes, true)) {
    csf_ai_status_error(502, 'Heruntergeladenes Bild hat ungültigen MIME-Typ: ' . $mime . '.');
}

$extMap = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
$ext    = $extMap[$mime];

// Zielverzeichnis anlegen (falls noch nicht vorhanden)
if (!is_dir($replaceDir)) {
    @mkdir($replaceDir, 0775, true);
}

$slotPad  = str_pad((string) $slotNumber, 2, '0', STR_PAD_LEFT);
$rand     = bin2hex(random_bytes(4));
$filename = 'slot_' . $slotPad . '_ai_' . $rand . '.' . $ext;
$destPath = $replaceDir . '/' . $filename;

if (file_put_contents($destPath, $imgData) === false) {
    csf_ai_status_error(500, 'Bild konnte nicht auf dem Server gespeichert werden.');
}

// Relative URL für meta.json + Frontend (identisch zu replace-slot.php)
$relativeUrl = '/storage/jobs/' . $jobId . '/replacements/' . $filename;
$nowIso      = date('c');

// ── meta.json updaten (LOCK_EX) ───────────────────────────────────────────────

$fp3 = @fopen($metaPath, 'c+');
if ($fp3 === false || !flock($fp3, LOCK_EX)) {
    if ($fp3) fclose($fp3);
    @unlink($destPath);
    csf_ai_status_error(500, 'meta.json konnte nicht für Update gesperrt werden.');
}

$raw3 = stream_get_contents($fp3);
$m3   = ($raw3 !== false && $raw3 !== '') ? json_decode($raw3, true) : null;

if (!is_array($m3)) {
    flock($fp3, LOCK_UN);
    fclose($fp3);
    @unlink($destPath);
    csf_ai_status_error(500, 'meta.json beim Schreiben nicht lesbar.');
}

// Slot aktualisieren — Format identisch zu replace-slot.php
foreach ($m3['slots'] as &$s3) {
    if ((int) ($s3['slot'] ?? 0) === $slotNumber) {
        $s3['replaced']         = true;
        $s3['replacement_file'] = $relativeUrl;
        $s3['replacement_type'] = 'image';
        $s3['ai_status']        = 'done';
        $s3['ai_completed_at']  = $nowIso;
        $s3['updated_at']       = $nowIso;
        break;
    }
}
unset($s3);

$newContent = json_encode(
    $m3,
    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
);

if ($newContent === false) {
    flock($fp3, LOCK_UN);
    fclose($fp3);
    @unlink($destPath);
    csf_ai_status_error(500, 'meta.json Serialisierung fehlgeschlagen.');
}

ftruncate($fp3, 0);
rewind($fp3);
fwrite($fp3, $newContent);
fflush($fp3);
flock($fp3, LOCK_UN);
fclose($fp3);

// ── Erfolgsantwort ────────────────────────────────────────────────────────────

echo json_encode([
    'status'           => 'done',
    'job_id'           => $jobId,
    'slot_number'      => $slotNumber,
    'task_id'          => $taskId,
    'replacement_file' => $relativeUrl,
    'model'            => $slot['ai_model'] ?? null,
    'completed_at'     => $nowIso,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
