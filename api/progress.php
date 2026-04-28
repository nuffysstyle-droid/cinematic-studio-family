<?php
/**
 * api/progress.php — Export-Job Polling-Endpunkt
 * Cinematic Studio Family
 *
 * Gibt den aktuellen Status eines Export-Jobs aus data/export-jobs.json zurück.
 * Wird von der UI per Polling abgefragt (kein echtes Async-Processing in V1).
 *
 * Methode:  GET oder POST
 * Parameter: job_id (required)
 *
 * Response Erfolg:
 *   {
 *     "success": true,
 *     "job": {
 *       "id":         string,
 *       "action":     "convert" | "thumbnail" | "info" | ...,
 *       "status":     "done" | "failed" | "running" | "pending",
 *       "progress":   0–100,
 *       "output_url": string | null,
 *       "error":      string | null,
 *       "created_at": string
 *     }
 *   }
 *
 * Response Fehler:
 *   { "success": false, "error": "..." }
 *
 * Progress-Logik V1:
 *   done    → 100
 *   failed  → 100
 *   pending → 0
 *   running → 50  (kein echter Worker — schätzen bis TODO #30 erweitert wird)
 *
 * Sicherheit:
 *   - job_id: nur [a-zA-Z0-9_-], max. 64 Zeichen
 *   - Kein Dateisystem-Pfad in der Antwort
 *   - Kein Shell-Aufruf
 *   - Schreibzugriff auf export-jobs.json: keiner (nur Lesen)
 *
 * @since Phase 4 — TODO #30
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

// ── Input lesen (GET oder POST) ───────────────────────────────────────────────

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $jobId = trim((string)($_GET['job_id'] ?? ''));
} elseif ($method === 'POST') {
    $raw   = file_get_contents('php://input');
    $body  = json_decode($raw ?: '{}', true);
    if (is_array($body) && isset($body['job_id'])) {
        $jobId = trim((string)$body['job_id']);
    } else {
        $jobId = trim((string)($_POST['job_id'] ?? ''));
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Nur GET und POST erlaubt.']);
    exit;
}

// ── job_id validieren ─────────────────────────────────────────────────────────

if ($jobId === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => '"job_id" fehlt.']);
    exit;
}

// Nur alphanumerische Zeichen, Unterstrich und Bindestrich — kein Traversal möglich
if (!preg_match('/^[a-zA-Z0-9_\-]{1,64}$/', $jobId)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error'   => '"job_id" enthält ungültige Zeichen (erlaubt: a-z, A-Z, 0-9, _, -).',
    ]);
    exit;
}

// ── export-jobs.json laden ────────────────────────────────────────────────────

$jobsFile = DATA_PATH . 'export-jobs.json';

if (!file_exists($jobsFile)) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Job nicht gefunden.']);
    exit;
}

// Shared Lock für sicheres Lesen (kein exklusives Lock nötig)
$fp = @fopen($jobsFile, 'r');

if ($fp === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Job-Protokoll konnte nicht gelesen werden.']);
    exit;
}

flock($fp, LOCK_SH);
$content = stream_get_contents($fp);
flock($fp, LOCK_UN);
fclose($fp);

$jobs = json_decode($content ?: '[]', true);

if (!is_array($jobs)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Job-Protokoll ist beschädigt.']);
    exit;
}

// ── Job suchen ────────────────────────────────────────────────────────────────

$found = null;

// Neueste Jobs zuerst prüfen (array_reverse — keine Kopie des Arrays nötig)
foreach (array_reverse($jobs) as $job) {
    if (is_array($job) && isset($job['id']) && $job['id'] === $jobId) {
        $found = $job;
        break;
    }
}

if ($found === null) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Job nicht gefunden.']);
    exit;
}

// ── Progress berechnen ────────────────────────────────────────────────────────

$status = (string)($found['status'] ?? 'pending');

$progress = match ($status) {
    'done'    => 100,
    'failed'  => 100,
    'pending' => 0,
    'running' => 50,   // V1: kein echter Worker — Schätzwert
    default   => 0,
};

// ── Antwort zusammenstellen ───────────────────────────────────────────────────
// Nur freigegebene Felder — keine internen Dateisystem-Pfade

$response = [
    'id'         => (string)($found['id']         ?? $jobId),
    'action'     => (string)($found['action']      ?? ''),
    'status'     => $status,
    'progress'   => $progress,
    'output_url' => isset($found['output_url']) && $found['output_url'] !== null
                        ? (string)$found['output_url']
                        : null,
    'error'      => isset($found['error']) && $found['error'] !== null
                        ? (string)$found['error']
                        : null,
    'created_at' => (string)($found['created_at'] ?? ''),
];

// Preset/Offset als optionale Metadaten (kein interner Pfad)
if (!empty($found['preset'])) {
    $response['preset'] = (string)$found['preset'];
}
if (!empty($found['offset'])) {
    $response['offset'] = (string)$found['offset'];
}

echo json_encode([
    'success' => true,
    'job'     => $response,
]);
