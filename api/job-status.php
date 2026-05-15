<?php
declare(strict_types=1);
/**
 * api/job-status.php — Universeller Job-Status-Endpunkt
 *
 * Erkennt den Job-Typ anhand des job_id-Formats und delegiert:
 *
 *   Format job_YYYYMMDD_HHMMSS_xxxxxxxx  → Render-Job  (meta.json in storage/jobs/)
 *   Alle anderen gültigen IDs            → Export-Job  (data/export-jobs.json)
 *
 * Methode: GET
 * Parameter:
 *   job_id  string  (required)
 *
 * Render-Job-Antwort:
 *   { status:"ok"|"error", job_type:"render", job:{...meta.json} }
 *
 * Export-Job-Antwort:
 *   { status:"ok"|"error", job_type:"export", job:{id, action, status, progress, ...} }
 *
 * Fehlerantwort:
 *   { status:"error", message:"..." }
 *
 * @since Phase 4 — TODO #16 / #30
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Nur GET erlaubt.']);
    exit;
}

$jobId = trim((string)($_GET['job_id'] ?? ''));

if ($jobId === '') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => '"job_id" fehlt.']);
    exit;
}

// ── Job-Typ erkennen ──────────────────────────────────────────────────────────

$isRenderJob = (bool)preg_match('/^job_\d{8}_\d{6}_[a-f0-9]{8}$/', $jobId);

if ($isRenderJob) {
    csf_handle_render_job($jobId);
} else {
    csf_handle_export_job($jobId);
}

// ═══════════════════════════════════════════════════════════════════════════════
//  RENDER-JOB  (storage/jobs/{job_id}/meta.json)
// ═══════════════════════════════════════════════════════════════════════════════

/**
 * Liest den Render-Job-Status aus meta.json.
 * Logik identisch zu api/get-job.php — hier als Funktion gekapselt.
 *
 * @return never
 */
function csf_handle_render_job(string $jobId): never
{
    $storageRoot = realpath(__DIR__ . '/../storage');
    if ($storageRoot === false) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Storage-Verzeichnis nicht gefunden.']);
        exit;
    }

    $metaPath = $storageRoot . '/jobs/' . $jobId . '/meta.json';

    // Export-Fallback: PHP-FPM SIGTERM kann render-final.php beenden bevor meta.json
    // vollständig geschrieben wurde — .mp4 liegt dann bereits auf Disk.
    $findExport = function () use ($storageRoot, $jobId): ?string {
        $candidates = glob($storageRoot . '/exports/' . $jobId . '_final_*.mp4') ?: [];
        if (empty($candidates)) return null;
        usort($candidates, fn($a, $b) => (int)@filemtime($b) <=> (int)@filemtime($a));
        return $candidates[0];
    };

    // meta.json fehlt → Export-Fallback
    if (!is_file($metaPath)) {
        $found = $findExport();
        if ($found !== null) {
            echo json_encode([
                'status'   => 'ok',
                'job_type' => 'render',
                'job'      => [
                    'job_id'           => $jobId,
                    'final_video'      => '/storage/exports/' . basename($found),
                    'final_filename'   => basename($found),
                    'final_size_bytes' => (int)@filesize($found),
                    'status'           => 'done',
                ],
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            exit;
        }
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Render-Job nicht gefunden.']);
        exit;
    }

    // Pfad-Traversal-Schutz
    $realMetaPath = realpath($metaPath);
    $jobsRoot     = realpath($storageRoot . '/jobs');
    if ($realMetaPath === false || $jobsRoot === false || !str_starts_with($realMetaPath, $jobsRoot)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Pfadprüfung fehlgeschlagen.']);
        exit;
    }

    // LOCK_SH lesen
    $fp = @fopen($realMetaPath, 'r');
    if ($fp === false) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'meta.json konnte nicht geöffnet werden.']);
        exit;
    }
    if (!flock($fp, LOCK_SH)) {
        fclose($fp);
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'meta.json-Lock fehlgeschlagen.']);
        exit;
    }
    $content = stream_get_contents($fp);
    flock($fp, LOCK_UN);
    fclose($fp);

    $meta = ($content !== false) ? json_decode($content, true) : null;

    // meta.json beschädigt → Export-Fallback
    if (!is_array($meta)) {
        $found = $findExport();
        if ($found !== null) {
            echo json_encode([
                'status'   => 'ok',
                'job_type' => 'render',
                'job'      => [
                    'job_id'           => $jobId,
                    'final_video'      => '/storage/exports/' . basename($found),
                    'final_filename'   => basename($found),
                    'final_size_bytes' => (int)@filesize($found),
                    'status'           => 'done',
                ],
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            exit;
        }
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'meta.json ist beschädigt.']);
        exit;
    }

    // Best-effort-Fallback: final_video fehlt in meta.json aber .mp4 liegt auf Disk
    if (!isset($meta['final_video'])) {
        $candidates = glob($storageRoot . '/exports/' . $jobId . '_final_*.mp4') ?: [];
        if (!empty($candidates)) {
            usort($candidates, fn($a, $b) => (int)@filemtime($b) <=> (int)@filemtime($a));
            $found = $candidates[0];
            $meta['final_video']      = '/storage/exports/' . basename($found);
            $meta['final_filename']   = basename($found);
            $meta['final_size_bytes'] = (int)@filesize($found);
        }
    }

    echo json_encode([
        'status'   => 'ok',
        'job_type' => 'render',
        'job'      => $meta,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

// ═══════════════════════════════════════════════════════════════════════════════
//  EXPORT-JOB  (data/export-jobs.json)
// ═══════════════════════════════════════════════════════════════════════════════

/**
 * Liest den Export-Job-Status aus data/export-jobs.json.
 * Logik identisch zu api/progress.php — hier als Funktion gekapselt.
 *
 * @return never
 */
function csf_handle_export_job(string $jobId): never
{
    // Export-IDs: nur [a-zA-Z0-9_-], max. 64 Zeichen
    if (!preg_match('/^[a-zA-Z0-9_\-]{1,64}$/', $jobId)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => '"job_id" enthält ungültige Zeichen.']);
        exit;
    }

    $jobsFile = DATA_PATH . 'export-jobs.json';

    if (!file_exists($jobsFile)) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Export-Job nicht gefunden.']);
        exit;
    }

    $fp = @fopen($jobsFile, 'r');
    if ($fp === false) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Job-Protokoll konnte nicht gelesen werden.']);
        exit;
    }
    flock($fp, LOCK_SH);
    $content = stream_get_contents($fp);
    flock($fp, LOCK_UN);
    fclose($fp);

    $jobs = json_decode($content ?: '[]', true);
    if (!is_array($jobs)) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Job-Protokoll ist beschädigt.']);
        exit;
    }

    $found = null;
    foreach (array_reverse($jobs) as $job) {
        if (is_array($job) && isset($job['id']) && $job['id'] === $jobId) {
            $found = $job;
            break;
        }
    }

    if ($found === null) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Export-Job nicht gefunden.']);
        exit;
    }

    $jobStatus = (string)($found['status'] ?? 'pending');
    $progress  = match ($jobStatus) {
        'done'    => 100,
        'failed'  => 100,
        'running' => 50,
        default   => 0,
    };

    $jobData = [
        'id'         => (string)($found['id']     ?? $jobId),
        'action'     => (string)($found['action'] ?? ''),
        'status'     => $jobStatus,
        'progress'   => $progress,
        'output_url' => isset($found['output_url']) ? (string)$found['output_url'] : null,
        'error'      => isset($found['error'])      ? (string)$found['error']      : null,
        'created_at' => (string)($found['created_at'] ?? ''),
    ];

    if (!empty($found['preset'])) {
        $jobData['preset'] = (string)$found['preset'];
    }
    if (!empty($found['offset'])) {
        $jobData['offset'] = (string)$found['offset'];
    }

    echo json_encode([
        'status'   => 'ok',
        'job_type' => 'export',
        'job'      => $jobData,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}
