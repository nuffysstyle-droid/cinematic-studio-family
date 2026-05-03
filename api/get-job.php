<?php
/**
 * api/get-job.php — Job-Status für Scene Replacement Editor
 *
 * Phase 2: Liefert die aktuelle meta.json eines Jobs zurück. Dient dem
 * Frontend zur Wiederherstellung des Editor-Zustands nach Page-Reload
 * oder zum Polling von Slot-Status-Änderungen.
 *
 * CORS: wird von Apache (Render) gesetzt — KEIN PHP-Header hier.
 *
 * Eingabe (GET):
 *   - job_id  string  Format: job_YYYYMMDD_HHMMSS_xxxxxxxx
 *
 * Antwort (JSON):
 *   { status: "ok", job: { ...meta.json } }
 *   oder
 *   { status: "error", message: "..." }
 *
 * @since Phase 2 — Scene Replacement Editor
 */

declare(strict_types=1);

header("Content-Type: application/json");
header("Cache-Control: no-store, no-cache, must-revalidate");

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);
    echo json_encode([
        "status"  => "error",
        "message" => "Methode nicht erlaubt — nur GET.",
    ], JSON_PRETTY_PRINT);
    exit;
}

$jobId = trim((string)($_GET["job_id"] ?? ""));

if (!preg_match('/^job_\d{8}_\d{6}_[a-f0-9]{8}$/', $jobId)) {
    http_response_code(400);
    echo json_encode([
        "status"  => "error",
        "message" => "Ungültige job_id.",
    ], JSON_PRETTY_PRINT);
    exit;
}

$storageRoot = realpath(__DIR__ . "/../storage");
if ($storageRoot === false) {
    http_response_code(500);
    echo json_encode([
        "status"  => "error",
        "message" => "Storage-Verzeichnis nicht gefunden.",
    ], JSON_PRETTY_PRINT);
    exit;
}

$metaPath = $storageRoot . "/jobs/" . $jobId . "/meta.json";

// ── Export-Fallback-Helfer ────────────────────────────────────────────────────
// PHP-FPM SIGTERM kann render-final.php beenden bevor meta.json geschrieben wird.
// Die .mp4 liegt aber bereits in storage/exports/. Beide Helfer werden weiter
// unten bei fehlendem UND beschädigtem meta.json verwendet.
$findExportFile = function () use ($storageRoot, $jobId): ?string {
    $candidates = glob($storageRoot . '/exports/' . $jobId . '_final_*.mp4') ?: [];
    if (empty($candidates)) return null;
    usort($candidates, fn($a, $b) => (int)@filemtime($b) <=> (int)@filemtime($a));
    return $candidates[0];
};
$exportJobResponse = function (string $found) use ($jobId): void {
    echo json_encode([
        "status" => "ok",
        "job"    => [
            "job_id"           => $jobId,
            "final_video"      => "/storage/exports/" . basename($found),
            "final_filename"   => basename($found),
            "final_size_bytes" => (int)@filesize($found),
            "status"           => "done",
        ],
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
};

// ── meta.json fehlt → Export-Datei als Fallback ───────────────────────────────
if (!is_file($metaPath)) {
    $found = $findExportFile();
    if ($found !== null) { $exportJobResponse($found); exit; }
    http_response_code(404);
    echo json_encode([
        "status"  => "error",
        "message" => "Job nicht gefunden.",
    ], JSON_PRETTY_PRINT);
    exit;
}

// Defense in depth: realpath muss innerhalb storage/jobs/ liegen
$realMetaPath = realpath($metaPath);
$jobsRoot     = realpath($storageRoot . "/jobs");

if ($realMetaPath === false || $jobsRoot === false || strpos($realMetaPath, $jobsRoot) !== 0) {
    http_response_code(400);
    echo json_encode([
        "status"  => "error",
        "message" => "Pfadprüfung fehlgeschlagen.",
    ], JSON_PRETTY_PRINT);
    exit;
}

// LOCK_SH für konsistentes Lesen, falls parallel geschrieben wird
$fp = @fopen($realMetaPath, "r");
if ($fp === false) {
    http_response_code(500);
    echo json_encode([
        "status"  => "error",
        "message" => "meta.json konnte nicht geöffnet werden.",
    ], JSON_PRETTY_PRINT);
    exit;
}

if (!flock($fp, LOCK_SH)) {
    fclose($fp);
    http_response_code(500);
    echo json_encode([
        "status"  => "error",
        "message" => "meta.json konnte nicht gelesen werden (Lock).",
    ], JSON_PRETTY_PRINT);
    exit;
}

$content = stream_get_contents($fp);
flock($fp, LOCK_UN);
fclose($fp);

$meta = $content !== false ? json_decode($content, true) : null;

// ── meta.json leer/beschädigt → Export-Datei als Fallback ────────────────────
if (!is_array($meta)) {
    $found = $findExportFile();
    if ($found !== null) { $exportJobResponse($found); exit; }
    http_response_code(500);
    echo json_encode([
        "status"  => "error",
        "message" => "meta.json ist beschädigt.",
    ], JSON_PRETTY_PRINT);
    exit;
}

// Best-effort-Fallback: render-final.php schreibt final_video in meta.json — aber
// PHP-FPM kann den Prozess per SIGTERM beenden bevor der Write erreicht wird
// (ignore_user_abort schützt nur gegen Browser-Disconnect, nicht gegen SIGTERM).
// Die .mp4-Datei liegt dann bereits auf Disk, meta.json weiß es nur nicht.
// → Falls final_video fehlt: glob() auf storage/exports/{job_id}_final_*.mp4.
if (!isset($meta['final_video'])) {
    $exportsDir = $storageRoot . '/exports';
    $candidates = glob($exportsDir . '/' . $jobId . '_final_*.mp4') ?: [];
    if (!empty($candidates)) {
        usort($candidates, fn($a, $b) => (int)@filemtime($b) <=> (int)@filemtime($a));
        $found = $candidates[0];
        $meta['final_video']      = '/storage/exports/' . basename($found);
        $meta['final_filename']   = basename($found);
        $meta['final_size_bytes'] = (int)@filesize($found);
    }
}

echo json_encode([
    "status" => "ok",
    "job"    => $meta,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
