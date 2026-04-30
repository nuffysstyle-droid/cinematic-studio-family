<?php
/**
 * api/replace-slot.php — Slot-Ersetzung für Scene Replacement Editor
 *
 * Phase 2: Speichert ein Bild oder Video als Ersatz für einen Slot,
 * plus optionalen Text. Aktualisiert meta.json mit dem Replacement-Status.
 *
 * CORS: wird von Apache (Render) gesetzt — KEIN PHP-Header hier.
 *
 * Eingabe (POST, multipart/form-data):
 *   - job_id            string   Format: job_YYYYMMDD_HHMMSS_xxxxxxxx
 *   - slot_number       int      1 – 12
 *   - replacement_file  file     image/jpeg|png|webp oder video/mp4|webm|quicktime
 *                                (mind. eines von replacement_file ODER text muss gesetzt sein)
 *   - text              string   optional, max. 500 Zeichen
 *
 * Speicherort:
 *   storage/jobs/{job_id}/replacements/slot_NN_<rand>.{ext}
 *
 * meta.json wird mit LOCK_EX (Read-Modify-Write) aktualisiert. Falls noch
 * keine meta.json existiert (alter Job vor Phase 2), wird sie minimal angelegt.
 *
 * Antwort:
 *   { status, job_id, slot_number, replaced, replacement_file, replacement_type, text, updated_at }
 *
 * @since Phase 2 — Scene Replacement Editor
 */

declare(strict_types=1);

header("Content-Type: application/json");

// ── Methode prüfen ──────────────────────────────────────────────────────────
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "status"  => "error",
        "message" => "Methode nicht erlaubt — nur POST.",
    ], JSON_PRETTY_PRINT);
    exit;
}

// ── Eingaben einlesen + validieren ──────────────────────────────────────────
$jobId      = trim((string)($_POST["job_id"] ?? ""));
$slotNumber = (int)($_POST["slot_number"] ?? 0);
$text       = trim((string)($_POST["text"] ?? ""));

if (!preg_match('/^job_\d{8}_\d{6}_[a-f0-9]{8}$/', $jobId)) {
    http_response_code(400);
    echo json_encode([
        "status"  => "error",
        "message" => "Ungültige job_id.",
    ], JSON_PRETTY_PRINT);
    exit;
}

if ($slotNumber < 1 || $slotNumber > 12) {
    http_response_code(400);
    echo json_encode([
        "status"  => "error",
        "message" => "slot_number muss zwischen 1 und 12 liegen.",
    ], JSON_PRETTY_PRINT);
    exit;
}

if (mb_strlen($text) > 500) {
    http_response_code(400);
    echo json_encode([
        "status"  => "error",
        "message" => "text darf max. 500 Zeichen lang sein.",
    ], JSON_PRETTY_PRINT);
    exit;
}

$hasFile = !empty($_FILES["replacement_file"])
        && $_FILES["replacement_file"]["error"] !== UPLOAD_ERR_NO_FILE;

if (!$hasFile && $text === "") {
    http_response_code(400);
    echo json_encode([
        "status"  => "error",
        "message" => "Mindestens replacement_file ODER text muss gesetzt sein.",
    ], JSON_PRETTY_PRINT);
    exit;
}

// ── Pfade vorbereiten ───────────────────────────────────────────────────────
$storageRoot = realpath(__DIR__ . "/../storage");

if ($storageRoot === false) {
    http_response_code(500);
    echo json_encode([
        "status"  => "error",
        "message" => "Storage-Verzeichnis nicht gefunden.",
    ], JSON_PRETTY_PRINT);
    exit;
}

$jobDir     = $storageRoot . "/jobs/" . $jobId;
$replaceDir = $jobDir . "/replacements";
$metaPath   = $jobDir . "/meta.json";

// Defense in depth: Sicherstellen, dass jobDir wirklich unter storage/jobs liegt.
$jobsRoot = $storageRoot . "/jobs";
if (!is_dir($jobsRoot)) @mkdir($jobsRoot, 0775, true);

$realJobsRoot = realpath($jobsRoot);
if ($realJobsRoot === false) {
    http_response_code(500);
    echo json_encode([
        "status"  => "error",
        "message" => "jobs-Verzeichnis nicht initialisierbar.",
    ], JSON_PRETTY_PRINT);
    exit;
}

if (!is_dir($jobDir))     @mkdir($jobDir,     0775, true);
if (!is_dir($replaceDir)) @mkdir($replaceDir, 0775, true);

$realJobDir = realpath($jobDir);
if ($realJobDir === false || strpos($realJobDir, $realJobsRoot) !== 0) {
    http_response_code(400);
    echo json_encode([
        "status"  => "error",
        "message" => "Pfadprüfung fehlgeschlagen.",
    ], JSON_PRETTY_PRINT);
    exit;
}

// ── Datei-Upload (falls vorhanden) verarbeiten ──────────────────────────────
$relativeUrl = null;
$fileType    = null;
$destPath    = null;

if ($hasFile) {
    $upload = $_FILES["replacement_file"];

    if ($upload["error"] !== UPLOAD_ERR_OK) {
        http_response_code(500);
        echo json_encode([
            "status"  => "error",
            "message" => "Upload-Fehler.",
            "code"    => $upload["error"],
        ], JSON_PRETTY_PRINT);
        exit;
    }

    if (!is_uploaded_file($upload["tmp_name"])) {
        http_response_code(400);
        echo json_encode([
            "status"  => "error",
            "message" => "Ungültiger Upload.",
        ], JSON_PRETTY_PRINT);
        exit;
    }

    // MIME via finfo prüfen — niemals dem Browser-MIME vertrauen
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($upload["tmp_name"]);

    $allowedImages = ["image/jpeg", "image/png", "image/webp"];
    $allowedVideos = ["video/mp4", "video/webm", "video/quicktime"];
    $allAllowed    = array_merge($allowedImages, $allowedVideos);

    if (!in_array($mime, $allAllowed, true)) {
        http_response_code(415);
        echo json_encode([
            "status"  => "error",
            "message" => "Dateityp nicht erlaubt — erlaubt: jpg, png, webp, mp4, webm, mov.",
        ], JSON_PRETTY_PRINT);
        exit;
    }

    $isImage  = in_array($mime, $allowedImages, true);
    $fileType = $isImage ? "image" : "video";

    $maxBytes = $isImage ? 10 * 1024 * 1024 : 100 * 1024 * 1024;
    if ($upload["size"] > $maxBytes) {
        $limitMb = $maxBytes / 1024 / 1024;
        http_response_code(413);
        echo json_encode([
            "status"  => "error",
            "message" => "Datei zu groß. Limit: {$limitMb} MB.",
        ], JSON_PRETTY_PRINT);
        exit;
    }

    $extMap = [
        "image/jpeg"      => "jpg",
        "image/png"       => "png",
        "image/webp"      => "webp",
        "video/mp4"       => "mp4",
        "video/webm"      => "webm",
        "video/quicktime" => "mov",
    ];
    $ext      = $extMap[$mime];
    $slotPad  = str_pad((string)$slotNumber, 2, "0", STR_PAD_LEFT);
    $rand     = bin2hex(random_bytes(4));
    $filename = "slot_" . $slotPad . "_" . $rand . "." . $ext;
    $destPath = $replaceDir . "/" . $filename;

    if (!move_uploaded_file($upload["tmp_name"], $destPath)) {
        http_response_code(500);
        echo json_encode([
            "status"  => "error",
            "message" => "Datei konnte nicht gespeichert werden.",
        ], JSON_PRETTY_PRINT);
        exit;
    }

    $relativeUrl = "/storage/jobs/" . $jobId . "/replacements/" . $filename;
}

// ── meta.json mit LOCK_EX aktualisieren ─────────────────────────────────────
$nowIso = date("c");

$fp = @fopen($metaPath, "c+");
if ($fp === false) {
    if ($destPath !== null) @unlink($destPath);
    http_response_code(500);
    echo json_encode([
        "status"  => "error",
        "message" => "meta.json konnte nicht geöffnet werden.",
    ], JSON_PRETTY_PRINT);
    exit;
}

if (!flock($fp, LOCK_EX)) {
    fclose($fp);
    if ($destPath !== null) @unlink($destPath);
    http_response_code(500);
    echo json_encode([
        "status"  => "error",
        "message" => "meta.json konnte nicht gesperrt werden.",
    ], JSON_PRETTY_PRINT);
    exit;
}

$content = stream_get_contents($fp);
$meta    = ($content !== false && $content !== "") ? json_decode($content, true) : null;

// Falls meta.json fehlt oder beschädigt: minimal initialisieren (Backwards-Compat)
if (!is_array($meta)) {
    $meta = [
        "job_id"     => $jobId,
        "created_at" => $nowIso,
        "video"      => null,
        "slot_count" => null,
        "slots"      => [],
    ];
}

// Slot finden — falls nicht vorhanden, neu anlegen
$slotIndex = null;
foreach (($meta["slots"] ?? []) as $i => $s) {
    if ((int)($s["slot"] ?? 0) === $slotNumber) {
        $slotIndex = $i;
        break;
    }
}

$textValue = $text !== "" ? $text : null;

if ($slotIndex === null) {
    $meta["slots"][] = [
        "slot"             => $slotNumber,
        "start_seconds"    => null,
        "end_seconds"      => null,
        "duration_seconds" => null,
        "thumbnail"        => null,
        "replace_allowed"  => true,
        "text_allowed"     => true,
        "replaced"         => true,
        "replacement_file" => $relativeUrl,
        "replacement_type" => $fileType,
        "text"             => $textValue,
        "updated_at"       => $nowIso,
    ];
} else {
    // Wenn KEINE neue Datei kommt, alte replacement_file behalten
    if ($relativeUrl !== null) {
        $meta["slots"][$slotIndex]["replacement_file"] = $relativeUrl;
        $meta["slots"][$slotIndex]["replacement_type"] = $fileType;
    }
    $meta["slots"][$slotIndex]["replaced"]   = true;
    $meta["slots"][$slotIndex]["text"]       = $textValue;
    $meta["slots"][$slotIndex]["updated_at"] = $nowIso;
}

$newContent = json_encode(
    $meta,
    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
);

if ($newContent === false) {
    flock($fp, LOCK_UN);
    fclose($fp);
    if ($destPath !== null) @unlink($destPath);
    http_response_code(500);
    echo json_encode([
        "status"  => "error",
        "message" => "meta.json konnte nicht serialisiert werden.",
    ], JSON_PRETTY_PRINT);
    exit;
}

ftruncate($fp, 0);
rewind($fp);
fwrite($fp, $newContent);
fflush($fp);
flock($fp, LOCK_UN);
fclose($fp);

// ── Erfolgsantwort ──────────────────────────────────────────────────────────
$slotForResponse = null;
foreach ($meta["slots"] as $s) {
    if ((int)($s["slot"] ?? 0) === $slotNumber) {
        $slotForResponse = $s;
        break;
    }
}

echo json_encode([
    "status"           => "ok",
    "job_id"           => $jobId,
    "slot_number"      => $slotNumber,
    "replaced"         => true,
    "replacement_file" => $slotForResponse["replacement_file"] ?? null,
    "replacement_type" => $slotForResponse["replacement_type"] ?? null,
    "text"             => $slotForResponse["text"] ?? null,
    "updated_at"       => $nowIso,
], JSON_PRETTY_PRINT);
