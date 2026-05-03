<?php
/**
 * api/render-final.php — Final-Render für Scene Replacement Editor (MVP)
 *
 * Phase 3 MVP:
 *   - Originalvideo anhand der Slot-Daten schneiden
 *   - Nicht ersetzte Slots = Cut aus Original (start_seconds → end_seconds)
 *   - Ersetzte Bild-Slots = Loop für Slot-Dauer
 *   - Ersetzte Video-Slots = Trim auf Slot-Dauer
 *   - Alle Clips werden auf 1920×1080 / 30 fps / H.264 / yuv420p / -an normalisiert
 *   - FFmpeg concat-Demuxer (`-c copy -movflags +faststart`) → finales MP4
 *   - Output: storage/exports/{job_id}_final_<rand>.mp4
 *   - meta.json wird mit final_video + rendered_at angereichert (LOCK_EX)
 *
 * CORS: Apache regelt das — kein PHP-Header.
 *
 * Audio: V1 stumm (`-an`). Audio kommt in V2.
 *
 * Eingabe (POST, multipart oder x-www-form-urlencoded):
 *   - job_id  string  Format: job_YYYYMMDD_HHMMSS_xxxxxxxx
 *
 * Antwort (JSON):
 *   ok    → { status:"ok", job_id, filename, download_url, size_bytes,
 *            duration_seconds, slot_count, rendered_at }
 *   fail  → { status:"error", message, [stderr], [debug], [slot] }
 *
 * @since Phase 3 MVP
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

// Render kann lange dauern — PHP nicht zwischendurch killen.
@set_time_limit(0);
ignore_user_abort(true); // Render weiter, auch wenn HTTP-Connection tot ist (Render Free Timeout)

// ── Diagnose-Log + Shutdown-Handler (minimal) ───────────────────────────────
// Render killt bei langlaufenden Requests gelegentlich die Connection bevor
// PHP eine HTTP-Antwort liefert. Ein zusätzliches Log auf der Persistent Disk
// erlaubt post-mortem-Diagnose; ein Shutdown-Handler wandelt Fatal Errors in
// eine JSON-Antwort statt eines HTML-500-Fragmente.
$_csfStorageRoot = realpath(__DIR__ . '/../storage');
define('RENDER_LOG_PATH', $_csfStorageRoot ? $_csfStorageRoot . '/temp/render.log' : '');

function render_log(string $msg): void
{
    if (RENDER_LOG_PATH === '') return;
    @file_put_contents(
        RENDER_LOG_PATH,
        '[' . date('c') . '] ' . $msg . "\n",
        FILE_APPEND | LOCK_EX
    );
}

register_shutdown_function(function () {
    $err = error_get_last();
    if ($err === null) return;
    $fatalTypes = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR];
    if (!in_array((int)($err['type'] ?? 0), $fatalTypes, true)) return;

    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: application/json');
    }
    $msg = (string)($err['message'] ?? 'unknown fatal');
    $loc = basename((string)($err['file'] ?? '')) . ':' . (int)($err['line'] ?? 0);

    echo json_encode([
        'status'  => 'error',
        'message' => 'Render-Prozess abgebrochen (PHP Fatal): ' . $msg,
        'fatal'   => [
            'type'     => (int)($err['type'] ?? 0),
            'location' => $loc,
        ],
    ], JSON_UNESCAPED_SLASHES);

    render_log('FATAL @' . $loc . ' :: ' . $msg);
});

render_log('--- START render-final.php (PID ' . getmypid() . ') ---');

// ── Konstanten (MVP-Spec) ───────────────────────────────────────────────────
const RENDER_OUT_W      = 1920;
const RENDER_OUT_H      = 1080;
const RENDER_OUT_FPS    = 30;
const RENDER_CRF        = 20;
const RENDER_PRESET     = 'fast';
const RENDER_SLOT_TO    = 180;   // Sekunden Timeout pro Slot-Encode
const RENDER_CONCAT_TO  = 180;   // Sekunden Timeout für Concat
const RENDER_STDERR_TAIL = 800;  // Bytes vom stderr-Ende im Fehlerfall

// ── Fehler-Helfer ───────────────────────────────────────────────────────────
function render_fail(int $code, string $msg, array $extra = []): void
{
    render_log('FAIL ' . $code . ': ' . $msg);
    http_response_code($code);
    $resp = ['status' => 'error', 'message' => $msg] + $extra;
    echo json_encode($resp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

// ── Methode + Eingabe validieren ────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    render_fail(405, 'Methode nicht erlaubt — nur POST.');
}

$jobId = trim((string)($_POST['job_id'] ?? ''));
if (!preg_match('/^job_\d{8}_\d{6}_[a-f0-9]{8}$/', $jobId)) {
    render_fail(400, 'Ungültige job_id.');
}

// ── FFmpeg verfügbar? ───────────────────────────────────────────────────────
$ff = checkFfmpegAvailable();
if (empty($ff['available'])) {
    render_fail(503, 'FFmpeg nicht verfügbar.', [
        'ffmpeg_error' => (string)($ff['error'] ?? 'unknown'),
    ]);
}

// ── Pfade auflösen ──────────────────────────────────────────────────────────
$storageRoot = realpath(__DIR__ . '/../storage');
if ($storageRoot === false) {
    render_fail(500, 'Storage-Verzeichnis nicht gefunden.');
}

$jobDir     = $storageRoot . '/jobs/' . $jobId;
$metaPath   = $jobDir . '/meta.json';
$clipsDir   = $jobDir . '/clips';
$exportsDir = $storageRoot . '/exports';
$tempDir    = $storageRoot . '/temp';

if (!is_file($metaPath)) {
    render_fail(404, 'Job nicht gefunden — meta.json fehlt.');
}

// ── meta.json lesen (LOCK_SH) ───────────────────────────────────────────────
$fpRead = @fopen($metaPath, 'r');
if ($fpRead === false) {
    render_fail(500, 'meta.json konnte nicht geöffnet werden.');
}
if (!flock($fpRead, LOCK_SH)) {
    fclose($fpRead);
    render_fail(500, 'meta.json konnte nicht gesperrt werden (Read).');
}
$content = stream_get_contents($fpRead);
flock($fpRead, LOCK_UN);
fclose($fpRead);

$meta = ($content !== false && $content !== '') ? json_decode($content, true) : null;
if (!is_array($meta) || empty($meta['slots']) || !is_array($meta['slots'])) {
    render_fail(500, 'meta.json ist beschädigt oder enthält keine Slots.');
}

$slots     = $meta['slots'];
$slotCount = count($slots);

// ── Originalvideo finden ────────────────────────────────────────────────────
$originalDir = $storageRoot . '/uploads/videos/' . $jobId;
$candidates  = is_dir($originalDir) ? (glob($originalDir . '/input.*') ?: []) : [];

$originalPath = null;
foreach ($candidates as $c) {
    if (is_file($c)) {
        $originalPath = $c;
        break;
    }
}
if ($originalPath === null) {
    render_fail(404, 'Originalvideo nicht gefunden (storage/uploads/videos/{job_id}/input.*).');
}
if (!csf_validate_path($originalPath, true)) {
    render_fail(400, 'Pfad-Validierung Originalvideo fehlgeschlagen.');
}

// ── Verzeichnisse vorbereiten + alte Clips entfernen ────────────────────────
if (!csf_ensure_dir($clipsDir))   render_fail(500, 'clips/ konnte nicht erstellt werden.');
if (!csf_ensure_dir($exportsDir)) render_fail(500, 'exports/ konnte nicht erstellt werden.');
if (!csf_ensure_dir($tempDir))    render_fail(500, 'temp/ konnte nicht erstellt werden.');

foreach (glob($clipsDir . '/slot_*.mp4') ?: [] as $oldClip) {
    @unlink($oldClip);
}

// ── Scale-/Pad-/FPS-Filter (für jeden Slot identisch) ──────────────────────
$scaleFilter = sprintf(
    'scale=%d:%d:force_original_aspect_ratio=decrease,'
    . 'pad=%d:%d:(ow-iw)/2:(oh-ih)/2:color=black,'
    . 'setsar=1,fps=%d',
    RENDER_OUT_W, RENDER_OUT_H, RENDER_OUT_W, RENDER_OUT_H, RENDER_OUT_FPS
);

// Hilfsfunktion: replacement_file (URL ab /storage/...) → absoluter Disk-Pfad
$resolveReplacement = function (string $url) use ($storageRoot): ?string {
    if ($url === '' || $url[0] !== '/') return null;
    if (strpos($url, '/storage/') !== 0) return null;
    return $storageRoot . substr($url, strlen('/storage'));
};

// ── Slot-Clips erzeugen ─────────────────────────────────────────────────────
$clipPaths = [];
$debug     = [];
render_log('job=' . $jobId . ' slots=' . $slotCount . ' — start slot encoding loop');

foreach ($slots as $idx => $slot) {
    $slotNum = (int)($slot['slot'] ?? ($idx + 1));
    $slotPad = str_pad((string)$slotNum, 2, '0', STR_PAD_LEFT);
    $clipOut = $clipsDir . '/slot_' . $slotPad . '.mp4';
    render_log('job=' . $jobId . ' slot=' . $slotNum . ' — encode start');

    $start    = (float)($slot['start_seconds'] ?? 0);
    $end      = (float)($slot['end_seconds']   ?? 0);
    $duration = ($end > $start)
        ? ($end - $start)
        : (float)($slot['duration_seconds'] ?? 0);

    if ($duration <= 0) {
        render_fail(400, "Slot {$slotNum} hat keine gültige Dauer.", [
            'slot'  => $slotNum,
            'debug' => $debug,
        ]);
    }

    $replaced = !empty($slot['replaced']);
    $type     = $slot['replacement_type'] ?? null;
    $repUrl   = $slot['replacement_file']  ?? null;

    $args   = [];
    $source = 'original';

    if ($replaced && $type === 'image' && is_string($repUrl) && $repUrl !== '') {
        $imgPath = $resolveReplacement($repUrl);
        if ($imgPath === null || !csf_validate_path($imgPath, true)) {
            render_fail(400, "Slot {$slotNum}: Bild-Replacement-Pfad ungültig.", [
                'slot' => $slotNum, 'debug' => $debug,
            ]);
        }
        $source = 'image';
        $args = [
            '-loop',     '1',
            '-i',        $imgPath,
            '-t',        sprintf('%.3f', $duration),
            '-vf',       $scaleFilter,
            '-c:v',      'libx264',
            '-crf',      (string)RENDER_CRF,
            '-preset',   RENDER_PRESET,
            '-pix_fmt',  'yuv420p',
            '-an',
            '-y',
            $clipOut,
        ];
    } elseif ($replaced && $type === 'video' && is_string($repUrl) && $repUrl !== '') {
        $vidPath = $resolveReplacement($repUrl);
        if ($vidPath === null || !csf_validate_path($vidPath, true)) {
            render_fail(400, "Slot {$slotNum}: Video-Replacement-Pfad ungültig.", [
                'slot' => $slotNum, 'debug' => $debug,
            ]);
        }
        $source = 'video';
        $args = [
            '-i',        $vidPath,
            '-t',        sprintf('%.3f', $duration),
            '-vf',       $scaleFilter,
            '-c:v',      'libx264',
            '-crf',      (string)RENDER_CRF,
            '-preset',   RENDER_PRESET,
            '-pix_fmt',  'yuv420p',
            '-an',
            '-y',
            $clipOut,
        ];
    } else {
        // Original-Slot: Cut aus dem Originalvideo
        $args = [
            '-ss',       sprintf('%.3f', $start),
            '-i',        $originalPath,
            '-t',        sprintf('%.3f', $duration),
            '-vf',       $scaleFilter,
            '-c:v',      'libx264',
            '-crf',      (string)RENDER_CRF,
            '-preset',   RENDER_PRESET,
            '-pix_fmt',  'yuv420p',
            '-an',
            '-y',
            $clipOut,
        ];
    }

    render_log("Slot {$slotNum} encode start (source={$source}, dur=" . round($duration, 2) . 's)');

    $result = csf_ffmpeg_run($args, RENDER_SLOT_TO);

    // Race-Override (analog zu checkFfmpegAvailable): Render Free liefert
    // exit_code=-1 obwohl FFmpeg erfolgreich war. Wenn nichts getimeoutet
    // ist UND der Output existiert + nicht leer ist → Erfolg akzeptieren.
    $clipSize = is_file($clipOut) ? (int)@filesize($clipOut) : 0;
    if (empty($result['success']) && empty($result['timed_out']) && $clipSize > 0) {
        $result['success'] = true;
    }

    $debug[] = [
        'slot'       => $slotNum,
        'source'     => $source,           // 'original' | 'image' | 'video'
        'replaced'   => $replaced,
        'duration'   => round($duration, 3),
        'success'    => (bool)$result['success'],
        'exit_code'  => (int)$result['exit_code'],
        'timed_out'  => (bool)$result['timed_out'],
        'clip_bytes' => $clipSize,
    ];

    if (!$result['success'] || $clipSize <= 0) {
        render_log("Slot {$slotNum} encode FAIL exit={$result['exit_code']} bytes={$clipSize}");
        render_fail(500, "FFmpeg-Fehler bei Slot {$slotNum}.", [
            'slot'      => $slotNum,
            'source'    => $source,
            'stderr'    => substr(trim((string)$result['stderr']), -RENDER_STDERR_TAIL),
            'timed_out' => (bool)$result['timed_out'],
            'exit_code' => (int)$result['exit_code'],
            'clip_bytes' => $clipSize,
            'debug'     => $debug,
        ]);
    }

    render_log("Slot {$slotNum} encode OK ({$clipSize} bytes)");
    $clipPaths[] = $clipOut;
}

// ── Concat-Liste schreiben ──────────────────────────────────────────────────
$concatList = $tempDir . '/concat_' . $jobId . '_' . bin2hex(random_bytes(4)) . '.txt';

$lines = [];
foreach ($clipPaths as $cp) {
    // Single-Quote-Escape im FFmpeg-Filelist-Format
    $escaped = str_replace("'", "'\\''", $cp);
    $lines[] = "file '" . $escaped . "'";
}

if (file_put_contents($concatList, implode("\n", $lines)) === false) {
    render_fail(500, 'Concat-Liste konnte nicht geschrieben werden.', ['debug' => $debug]);
}

// ── Final-Output-Pfad ───────────────────────────────────────────────────────
$finalName = $jobId . '_final_' . bin2hex(random_bytes(3)) . '.mp4';
$finalPath = $exportsDir . '/' . $finalName;

$concatArgs = [
    '-f',         'concat',
    '-safe',      '0',
    '-i',         $concatList,
    '-c',         'copy',
    '-movflags',  '+faststart',
    '-y',
    $finalPath,
];

render_log('Concat start (' . count($clipPaths) . ' clips → ' . basename($finalPath) . ')');

$concatResult = csf_ffmpeg_run($concatArgs, RENDER_CONCAT_TO);
@unlink($concatList);

// Race-Override analog zum Slot-Encode.
$finalSizeProbe = is_file($finalPath) ? (int)@filesize($finalPath) : 0;
if (empty($concatResult['success']) && empty($concatResult['timed_out']) && $finalSizeProbe > 0) {
    $concatResult['success'] = true;
}

if (!$concatResult['success'] || $finalSizeProbe <= 0) {
    render_log("Concat FAIL exit={$concatResult['exit_code']} bytes={$finalSizeProbe}");
    render_fail(500, 'Concat fehlgeschlagen.', [
        'stderr'    => substr(trim((string)$concatResult['stderr']), -RENDER_STDERR_TAIL),
        'timed_out' => (bool)$concatResult['timed_out'],
        'exit_code' => (int)$concatResult['exit_code'],
        'final_bytes' => $finalSizeProbe,
        'debug'     => $debug,
    ]);
}

render_log("Concat OK ({$finalSizeProbe} bytes)");

// ── Cleanup Slot-Clips (nur bei Erfolg) ─────────────────────────────────────
foreach ($clipPaths as $cp) {
    @unlink($cp);
}
@rmdir($clipsDir);

// ── Final-Video-Info ────────────────────────────────────────────────────────
$finalSize = (int)@filesize($finalPath);

$totalDuration = 0.0;
foreach ($slots as $s) {
    $sStart = (float)($s['start_seconds']    ?? 0);
    $sEnd   = (float)($s['end_seconds']      ?? 0);
    $totalDuration += ($sEnd > $sStart)
        ? ($sEnd - $sStart)
        : (float)($s['duration_seconds'] ?? 0);
}

$downloadUrl = '/storage/exports/' . $finalName;
$nowIso      = date('c');

// ── meta.json mit final_video / rendered_at anreichern (LOCK_EX) ────────────
$fpWrite = @fopen($metaPath, 'c+');
if ($fpWrite !== false) {
    if (flock($fpWrite, LOCK_EX)) {
        $cur     = stream_get_contents($fpWrite);
        $curMeta = ($cur !== false && $cur !== '') ? json_decode($cur, true) : null;
        if (is_array($curMeta)) {
            $curMeta['final_video']      = $downloadUrl;
            $curMeta['final_filename']   = $finalName;
            $curMeta['final_size_bytes'] = $finalSize;
            $curMeta['rendered_at']      = $nowIso;
            $newJson = json_encode(
                $curMeta,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            );
            if ($newJson !== false) {
                ftruncate($fpWrite, 0);
                rewind($fpWrite);
                fwrite($fpWrite, $newJson);
                fflush($fpWrite);
            }
        }
        flock($fpWrite, LOCK_UN);
    }
    fclose($fpWrite);
}
// meta-Update ist best effort — bricht das Rendering nicht.

// ── Erfolgsantwort ──────────────────────────────────────────────────────────
render_log("DONE {$finalName} ({$finalSize} bytes, {$slotCount} slots)");

echo json_encode([
    'status'           => 'ok',
    'job_id'           => $jobId,
    'filename'         => $finalName,
    'download_url'     => $downloadUrl,
    'size_bytes'       => $finalSize,
    'duration_seconds' => round($totalDuration, 2),
    'slot_count'       => $slotCount,
    'rendered_at'      => $nowIso,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
