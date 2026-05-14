<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/config.php';

header("Content-Type: application/json");

$storageRoot = realpath(__DIR__ . "/../storage") ?: (__DIR__ . "/../storage");
$uploadDir = $storageRoot . "/uploads/videos";
$thumbDir = $storageRoot . "/thumbnails";

if (!is_dir($uploadDir)) mkdir($uploadDir, 0775, true);
if (!is_dir($thumbDir)) mkdir($thumbDir, 0775, true);

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "status" => "error",
        "message" => "Bitte Video per POST hochladen. Feldname: video"
    ], JSON_PRETTY_PRINT);
    exit;
}

if (!isset($_FILES["video"])) {
    echo json_encode([
        "status" => "error",
        "message" => "Keine Videodatei erhalten. Feldname muss video sein."
    ], JSON_PRETTY_PRINT);
    exit;
}

$file = $_FILES["video"];

if ($file["error"] !== UPLOAD_ERR_OK) {
    echo json_encode([
        "status" => "error",
        "message" => "Upload fehlgeschlagen",
        "upload_error" => $file["error"]
    ], JSON_PRETTY_PRINT);
    exit;
}

// ── Größenlimit (konsistent mit MAX_UPLOAD_BYTES aus config.php) ───
if ((int)$file["size"] > MAX_UPLOAD_BYTES) {
    $limitMb = round(MAX_UPLOAD_BYTES / 1048576);
    http_response_code(413);
    echo json_encode([
        "status"   => "error",
        "message"  => "Datei zu groß. Limit: {$limitMb} MB.",
        "size"     => $file["size"],
        "limit"    => MAX_UPLOAD_BYTES,
    ], JSON_PRETTY_PRINT);
    exit;
}

// ── MIME-Prüfung via finfo (nicht dem Browser-Feld vertrauen) ──────
$finfo    = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($file["tmp_name"]);

$allowedMimes = ALLOWED_VIDEO_TYPES; // aus config.php
if (!in_array($mimeType, $allowedMimes, true)) {
    http_response_code(415);
    echo json_encode([
        "status"   => "error",
        "message"  => "Dateityp nicht erlaubt. Nur MP4, MOV, WebM, MKV.",
        "detected" => $mimeType,
    ], JSON_PRETTY_PRINT);
    exit;
}

$extMap = [
    'video/mp4'         => 'mp4',
    'video/quicktime'   => 'mov',
    'video/webm'        => 'webm',
    'video/x-matroska'  => 'mkv',
];
$ext = $extMap[$mimeType] ?? 'mp4';

// is_uploaded_file guard (path traversal)
if (!is_uploaded_file($file["tmp_name"])) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Ungültige Datei."], JSON_PRETTY_PRINT);
    exit;
}

$jobId = "job_" . date("Ymd_His") . "_" . bin2hex(random_bytes(4));
$jobUploadDir = $uploadDir . "/" . $jobId;
$jobThumbDir = $thumbDir . "/" . $jobId;

mkdir($jobUploadDir, 0775, true);
mkdir($jobThumbDir, 0775, true);

$inputPath = $jobUploadDir . "/input." . $ext;

if (!move_uploaded_file($file["tmp_name"], $inputPath)) {
    echo json_encode([
        "status" => "error",
        "message" => "Datei konnte nicht gespeichert werden"
    ], JSON_PRETTY_PRINT);
    exit;
}

$ffprobe = getenv("FFPROBE_PATH") ?: "/usr/bin/ffprobe";
$ffmpeg = getenv("FFMPEG_PATH") ?: "/usr/bin/ffmpeg";

$probeCmd = escapeshellcmd($ffprobe) . " -v error -show_entries format=duration:stream=width,height -of json " . escapeshellarg($inputPath);
$probeOutput = shell_exec($probeCmd);
$probeData = json_decode($probeOutput, true);

$duration = isset($probeData["format"]["duration"]) ? (float)$probeData["format"]["duration"] : 0;

$width = null;
$height = null;

if (!empty($probeData["streams"])) {
    foreach ($probeData["streams"] as $stream) {
        if (isset($stream["width"], $stream["height"])) {
            $width = $stream["width"];
            $height = $stream["height"];
            break;
        }
    }
}

if ($duration <= 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Videodauer konnte nicht gelesen werden",
        "probe_output" => $probeOutput
    ], JSON_PRETTY_PRINT);
    exit;
}

// Free-Plan-Limit: max. 15 Sekunden
if ($duration > 15) {
    echo json_encode([
        "status"   => "error",
        "message"  => "Demo-Modus: Videos dürfen maximal 15 Sekunden lang sein. Bitte ein kürzeres Video hochladen.",
        "duration" => round($duration, 2),
        "limit"    => 15,
    ], JSON_PRETTY_PRINT);
    exit;
}

/*
|--------------------------------------------------------------------------
| Slot-Logik (Free-Plan-kompatibel: max. 3 Slots)
|--------------------------------------------------------------------------
| Kurze Videos bekommen 2 Slots, alle anderen 3.
| Limit verhindert OOM-Crashes auf dem 512-MB-Free-Container.
*/
if ($duration <= 6) {
    $slotCount = 2;
} else {
    $slotCount = 3;
}

$slotLength = $duration / $slotCount;
$slots = [];

for ($i = 0; $i < $slotCount; $i++) {
    $start = $i * $slotLength;
    $end = min(($i + 1) * $slotLength, $duration);
    $middle = $start + (($end - $start) / 2);

    $thumbFile = "slot_" . str_pad((string)($i + 1), 2, "0", STR_PAD_LEFT) . ".jpg";
    $thumbPath = $jobThumbDir . "/" . $thumbFile;

    $thumbCmd = escapeshellcmd($ffmpeg)
        . " -y -ss " . escapeshellarg((string)$middle)
        . " -i " . escapeshellarg($inputPath)
        . " -frames:v 1 -q:v 2 "
        . escapeshellarg($thumbPath)
        . " 2>&1";

    shell_exec($thumbCmd);

    $slots[] = [
        "slot" => $i + 1,
        "start_seconds" => round($start, 2),
        "end_seconds" => round($end, 2),
        "duration_seconds" => round($end - $start, 2),
        "thumbnail" => "/storage/thumbnails/" . $jobId . "/" . $thumbFile,
        "replace_allowed" => true,
        "text_allowed" => true
    ];
}

/*
|--------------------------------------------------------------------------
| Phase 2: meta.json + jobs/{job_id}/ vorbereiten (Scene Replacement Editor)
|--------------------------------------------------------------------------
| Wir lassen die bestehenden Pfade (uploads/videos/{job_id}, thumbnails/{job_id})
| bewusst unverändert. Phase 2 fügt nur einen separaten jobs/{job_id}/-Ordner
| hinzu, der den Replacement-Status (meta.json) und die Ersatz-Dateien hält.
*/
$jobsRoot   = $storageRoot . "/jobs";
$jobDir     = $jobsRoot . "/" . $jobId;
$replaceDir = $jobDir . "/replacements";

if (!is_dir($jobsRoot)   && !mkdir($jobsRoot,   0775, true) && !is_dir($jobsRoot)) {
    echo json_encode(["status"=>"error","message"=>"storage/jobs konnte nicht erstellt werden."], JSON_PRETTY_PRINT);
    exit;
}
if (!is_dir($jobDir)     && !mkdir($jobDir,     0775, true) && !is_dir($jobDir)) {
    echo json_encode(["status"=>"error","message"=>"Job-Verzeichnis konnte nicht erstellt werden."], JSON_PRETTY_PRINT);
    exit;
}
if (!is_dir($replaceDir) && !mkdir($replaceDir, 0775, true) && !is_dir($replaceDir)) {
    echo json_encode(["status"=>"error","message"=>"Replacements-Verzeichnis konnte nicht erstellt werden."], JSON_PRETTY_PRINT);
    exit;
}

$slotsForMeta = [];
foreach ($slots as $s) {
    $slotsForMeta[] = $s + [
        "replaced"         => false,
        "replacement_file" => null,
        "replacement_type" => null,
        "text"             => null,
        "updated_at"       => null,
    ];
}

$meta = [
    "job_id"     => $jobId,
    "created_at" => date("c"),
    "video"      => [
        "original_name"    => $file["name"],
        "duration_seconds" => round($duration, 2),
        "width"            => $width,
        "height"           => $height,
    ],
    "slot_count" => $slotCount,
    "slots"      => $slotsForMeta,
];

$metaPath = $jobDir . "/meta.json";
$metaJson = json_encode(
    $meta,
    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
);

if ($metaJson !== false) {
    $written = file_put_contents($metaPath, $metaJson, LOCK_EX);
    if ($written === false) {
        echo json_encode([
            "status"  => "error",
            "message" => "meta.json konnte nicht geschrieben werden — Storage-Fehler.",
        ], JSON_PRETTY_PRINT);
        exit;
    }
}

echo json_encode([
    "status" => "ok",
    "job_id" => $jobId,
    "video" => [
        "original_name" => $file["name"],
        "duration_seconds" => round($duration, 2),
        "width" => $width,
        "height" => $height
    ],
    "slot_count" => $slotCount,
    "slot_logic" => "auto_by_duration",
    "slots" => $slots
], JSON_PRETTY_PRINT);