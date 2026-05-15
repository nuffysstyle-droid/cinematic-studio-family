<?php
/**
 * api/generate-ai.php — KI-Bildgenerierung (Kie.ai Flux Kontext)
 *
 * Startet einen asynchronen Task bei Kie.ai und speichert die taskId in
 * meta.json. Das eigentliche Bild wird über api/ai-status.php abgerufen.
 *
 * Methode:  POST — application/json ODER multipart/form-data
 * Eingabe:
 *   job_id       string   job_YYYYMMDD_HHMMSS_xxxxxxxx
 *   slot_number  int      1–12
 *   prompt       string   1–500 Zeichen
 *   model        string?  "flux-kontext-pro" (Standard) | "flux-kontext-max"
 *
 * API-Key: ausschließlich über getenv('KIE_AI_API_KEY').
 *          Kein BYOK, kein POST-Feld, kein Session-Feld.
 *
 * Limits:
 *   - max. 1 KI-Generierung pro Slot  (pending oder done → 409)
 *   - max. 3 KI-Generierungen pro Job (ai_generated-Zählung)
 *
 * Gespeicherte Meta-Felder pro Slot:
 *   ai_generated, ai_provider, ai_model, ai_status,
 *   ai_task_id, ai_prompt, ai_created_at
 *
 * Antwort (pending):
 *   { status:"pending", job_id, slot_number, task_id, model, started_at }
 *
 * Render-final.php und replace-slot.php bleiben unberührt.
 *
 * @since V2 AI Integration
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/rate_limit.php';

header('Content-Type: application/json');

// ── Hilfsfunktionen ──────────────────────────────────────────────────────────

/**
 * HTTP-POST via file_get_contents (kein cURL erforderlich).
 *
 * Gibt ['status'=>int, 'body'=>string, 'data'=>array] zurück.
 * Der $apiKey wird ausschließlich im Authorization-Header verwendet —
 * nie in Logs, nie im Response-Body.
 *
 * Hinweis: $http_response_header ist eine PHP-interne Variable, die
 * file_get_contents im lokalen Scope der aufrufenden Funktion setzt.
 */
function csf_kie_post(string $url, array $payload, string $apiKey): array
{
    $jsonBody = json_encode($payload, JSON_UNESCAPED_UNICODE);
    if ($jsonBody === false) {
        return ['status' => 0, 'body' => '', 'data' => []];
    }

    $headerLines = [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json',
        'Accept: application/json',
        'Content-Length: ' . strlen($jsonBody),
    ];

    $opts = [
        'http' => [
            'method'          => 'POST',
            'header'          => implode("\r\n", $headerLines),
            'content'         => $jsonBody,
            'timeout'         => 20,
            'ignore_errors'   => true,   // Body auch bei 4xx/5xx liefern
            'follow_location' => 1,
        ],
    ];

    $ctx  = stream_context_create($opts);
    $body = @file_get_contents($url, false, $ctx);

    if ($body === false) {
        return ['status' => 0, 'body' => '', 'data' => []];
    }

    // $http_response_header wird von PHP im lokalen Scope gesetzt
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
 * JSON-Fehlermeldung ausgeben und Ausführung beenden.
 *
 * @param int    $httpCode HTTP-Statuscode
 * @param string $message  Lesbare Fehlermeldung (kein API-Key, kein Stack-Trace)
 * @param array  $extra    Optionale Zusatzfelder (z. B. kie_code, kie_msg)
 * @return never
 */
function csf_ai_gen_error(int $httpCode, string $message, array $extra = []): never
{
    http_response_code($httpCode);
    echo json_encode(
        array_merge(['status' => 'error', 'message' => $message], $extra),
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
    );
    exit;
}

// ── Methode ──────────────────────────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    csf_ai_gen_error(405, 'Nur POST erlaubt.');
}

// ── Rate Limiting (IP-basiert) ────────────────────────────────────────────────
// KI-Generierungen kosten Geld → strenger limitieren: 10/Stunde pro IP

$rl = csf_rate_limit_check('generate_ai', maxRequests: 10, windowSeconds: 3600);

if (!$rl['allowed']) {
    http_response_code(429);
    header('Retry-After: ' . $rl['reset_in']);
    header('X-RateLimit-Limit: ' . $rl['limit']);
    header('X-RateLimit-Remaining: 0');
    header('X-RateLimit-Reset: ' . $rl['reset_at']);
    echo json_encode([
        'status'    => 'error',
        'message'   => 'Zu viele KI-Generierungen. Bitte in ' . ceil($rl['reset_in'] / 60) . ' Minuten erneut versuchen.',
        'reset_in'  => $rl['reset_in'],
        'reset_at'  => $rl['reset_at'],
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// Probabilistisches Cleanup (1/100 Requests)
if (random_int(1, 100) === 1) {
    csf_rate_limit_cleanup();
}

// ── Eingaben lesen (JSON-Body oder multipart/form-data) ──────────────────────

$jsonBody    = [];
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (str_contains($contentType, 'application/json')) {
    $raw      = file_get_contents('php://input');
    $jsonBody = json_decode($raw ?: '{}', true) ?? [];
}

$jobId      = trim((string) ($jsonBody['job_id']      ?? $_POST['job_id']      ?? ''));
$slotNumber = (int)          ($jsonBody['slot_number'] ?? $_POST['slot_number'] ?? 0);
$prompt     = trim((string) ($jsonBody['prompt']      ?? $_POST['prompt']      ?? ''));
$model      = trim((string) ($jsonBody['model']       ?? $_POST['model']       ?? 'flux-kontext-pro'));

// ── Eingabe-Validierung ───────────────────────────────────────────────────────
// Läuft vor dem API-Key-Check, damit Clients klares Feedback zu ihren Eingaben
// erhalten — unabhängig davon, ob der Server konfiguriert ist.

if (!preg_match('/^job_\d{8}_\d{6}_[a-f0-9]{8}$/', $jobId)) {
    csf_ai_gen_error(400, 'Ungültige job_id. Format: job_YYYYMMDD_HHMMSS_xxxxxxxx');
}

if ($slotNumber < 1 || $slotNumber > 12) {
    csf_ai_gen_error(400, 'slot_number muss zwischen 1 und 12 liegen.');
}

if ($prompt === '') {
    csf_ai_gen_error(400, 'prompt darf nicht leer sein.');
}

if (mb_strlen($prompt) > 500) {
    csf_ai_gen_error(400, 'prompt darf maximal 500 Zeichen lang sein.');
}

const KIE_ALLOWED_MODELS = ['flux-kontext-pro', 'flux-kontext-max'];

if (!in_array($model, KIE_ALLOWED_MODELS, true)) {
    csf_ai_gen_error(400, 'Ungültiges Modell. Erlaubt: ' . implode(', ', KIE_ALLOWED_MODELS) . '.');
}

// ── API-Key (ausschließlich Umgebungsvariable) ────────────────────────────────
// Reihenfolge: getenv() → $_SERVER → $_ENV
// Apache mod_env benötigt PassEnv in apache.conf damit getenv() greift.

$apiKey = (string) (getenv('KIE_AI_API_KEY') ?: ($_SERVER['KIE_AI_API_KEY'] ?? $_ENV['KIE_AI_API_KEY'] ?? ''));
if ($apiKey === '') {
    csf_ai_gen_error(503, 'KI-Generierung ist nicht konfiguriert. KIE_AI_API_KEY fehlt auf dem Server.');
}

// ── Pfade + Pfad-Traversal-Schutz ────────────────────────────────────────────

$storageRoot = realpath(__DIR__ . '/../storage');
if ($storageRoot === false) {
    csf_ai_gen_error(500, 'Storage-Verzeichnis nicht gefunden.');
}

$jobsRoot = $storageRoot . '/jobs';
if (!is_dir($jobsRoot)) {
    @mkdir($jobsRoot, 0775, true);
}

$realJobsRoot = realpath($jobsRoot);
if ($realJobsRoot === false) {
    csf_ai_gen_error(500, 'jobs-Verzeichnis nicht initialisierbar.');
}

$jobDir   = $jobsRoot . '/' . $jobId;
$metaPath = $jobDir   . '/meta.json';

// Verzeichnis muss existieren (Job muss zuvor über upload.php angelegt worden sein)
$realJobDir = realpath($jobDir);
if ($realJobDir === false) {
    csf_ai_gen_error(404, 'Job nicht gefunden.');
}
if (strpos($realJobDir, $realJobsRoot) !== 0) {
    csf_ai_gen_error(400, 'Pfadprüfung fehlgeschlagen.');
}

// ── meta.json mit LOCK_EX lesen ───────────────────────────────────────────────

$fp = @fopen($metaPath, 'c+');
if ($fp === false) {
    csf_ai_gen_error(404, 'Job-Metadaten nicht gefunden (meta.json).');
}

if (!flock($fp, LOCK_EX)) {
    fclose($fp);
    csf_ai_gen_error(500, 'meta.json konnte nicht gesperrt werden.');
}

$rawContent = stream_get_contents($fp);
$meta       = ($rawContent !== false && $rawContent !== '')
                ? json_decode($rawContent, true)
                : null;

if (!is_array($meta)) {
    flock($fp, LOCK_UN);
    fclose($fp);
    csf_ai_gen_error(404, 'meta.json ist ungültig oder leer.');
}

// ── Limit-Prüfung: per Slot ───────────────────────────────────────────────────

$slotIndex = null;
foreach (($meta['slots'] ?? []) as $i => $s) {
    if ((int) ($s['slot'] ?? 0) === $slotNumber) {
        $slotIndex = $i;
        break;
    }
}

if ($slotIndex !== null) {
    $existingStatus = $meta['slots'][$slotIndex]['ai_status'] ?? null;
    if ($existingStatus === 'pending' || $existingStatus === 'done') {
        flock($fp, LOCK_UN);
        fclose($fp);
        csf_ai_gen_error(
            409,
            'Für Slot ' . $slotNumber . ' wurde bereits eine KI-Generierung gestartet (Status: ' . $existingStatus . ').'
                . ' Bitte ai-status.php abfragen.',
            ['current_status' => $existingStatus]
        );
    }
}

// ── Limit-Prüfung: per Job (max. 3) ──────────────────────────────────────────

$aiGenCount = 0;
foreach (($meta['slots'] ?? []) as $s) {
    if (!empty($s['ai_generated'])) {
        $aiGenCount++;
    }
}

if ($aiGenCount >= 3) {
    flock($fp, LOCK_UN);
    fclose($fp);
    csf_ai_gen_error(
        429,
        'Maximale Anzahl KI-Generierungen pro Job (3) erreicht.',
        ['ai_gen_count' => $aiGenCount]
    );
}

// ── Kie.ai Generate — API-Aufruf ──────────────────────────────────────────────

$kiePayload = [
    'prompt'           => $prompt,
    'model'            => $model,
    'aspectRatio'      => '16:9',
    'outputFormat'     => 'jpeg',
    'safetyTolerance'  => 2,
];

$kieResult = csf_kie_post(
    'https://api.kie.ai/api/v1/flux/kontext/generate',
    $kiePayload,
    $apiKey
);

// Verbindungsfehler
if ($kieResult['status'] === 0) {
    flock($fp, LOCK_UN);
    fclose($fp);
    csf_ai_gen_error(502, 'Kie.ai nicht erreichbar. Bitte erneut versuchen.');
}

// Auth-Fehler
if ($kieResult['status'] === 401 || $kieResult['status'] === 403) {
    flock($fp, LOCK_UN);
    fclose($fp);
    csf_ai_gen_error(401, 'KIE_AI_API_KEY ungültig oder abgelaufen.');
}

// Rate-Limit
if ($kieResult['status'] === 429) {
    flock($fp, LOCK_UN);
    fclose($fp);
    csf_ai_gen_error(429, 'Kie.ai Rate Limit erreicht. Bitte kurz warten.');
}

// Sonstige Fehler
if ($kieResult['status'] !== 200) {
    flock($fp, LOCK_UN);
    fclose($fp);
    // Nur Kie.ai-eigene Fehlercodes ausgeben — kein API-Key im Response
    csf_ai_gen_error(502, 'Kie.ai hat einen unbekannten Fehler zurückgegeben.', [
        'kie_code' => $kieResult['data']['code'] ?? null,
        'kie_msg'  => $kieResult['data']['msg']  ?? null,
    ]);
}

// taskId extrahieren
$taskId = $kieResult['data']['data']['taskId'] ?? null;
if (!is_string($taskId) || $taskId === '') {
    flock($fp, LOCK_UN);
    fclose($fp);
    csf_ai_gen_error(502, 'Kie.ai hat keine taskId in der Antwort zurückgegeben.');
}

// ── meta.json updaten (Lock ist noch gehalten) ────────────────────────────────

$nowIso = date('c');

if ($slotIndex === null) {
    // Slot neu anlegen (noch kein Eintrag für diesen Slot)
    $meta['slots'][] = [
        'slot'             => $slotNumber,
        'start_seconds'    => null,
        'end_seconds'      => null,
        'duration_seconds' => null,
        'thumbnail'        => null,
        'replace_allowed'  => true,
        'text_allowed'     => true,
        'replaced'         => false,
        'replacement_file' => null,
        'replacement_type' => null,
        'text'             => null,
        'ai_generated'     => true,
        'ai_provider'      => 'kie.ai',
        'ai_model'         => $model,
        'ai_status'        => 'pending',
        'ai_task_id'       => $taskId,
        'ai_prompt'        => $prompt,
        'ai_created_at'    => $nowIso,
        'updated_at'       => $nowIso,
    ];
} else {
    // Bestehenden Slot um AI-Felder ergänzen
    $meta['slots'][$slotIndex]['ai_generated']  = true;
    $meta['slots'][$slotIndex]['ai_provider']   = 'kie.ai';
    $meta['slots'][$slotIndex]['ai_model']      = $model;
    $meta['slots'][$slotIndex]['ai_status']     = 'pending';
    $meta['slots'][$slotIndex]['ai_task_id']    = $taskId;
    $meta['slots'][$slotIndex]['ai_prompt']     = $prompt;
    $meta['slots'][$slotIndex]['ai_created_at'] = $nowIso;
    $meta['slots'][$slotIndex]['updated_at']    = $nowIso;
}

$newContent = json_encode(
    $meta,
    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
);

if ($newContent === false) {
    flock($fp, LOCK_UN);
    fclose($fp);
    csf_ai_gen_error(500, 'meta.json konnte nicht serialisiert werden.');
}

ftruncate($fp, 0);
rewind($fp);
fwrite($fp, $newContent);
fflush($fp);
flock($fp, LOCK_UN);
fclose($fp);

// ── Antwort ───────────────────────────────────────────────────────────────────

echo json_encode([
    'status'      => 'pending',
    'job_id'      => $jobId,
    'slot_number' => $slotNumber,
    'task_id'     => $taskId,
    'model'       => $model,
    'started_at'  => $nowIso,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
