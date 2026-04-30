<?php
header("Content-Type: application/json");

$storageRoot = __DIR__ . "/../storage";
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

$allowed = ["mp4", "mov", "webm", "mkv"];
$ext = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));

if (!in_array($ext, $allowed)) {
    echo json_encode([
        "status" => "error",
        "message" => "Dateityp nicht erlaubt",
        "allowed" => $allowed
    ], JSON_PRETTY_PRINT);
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

$slotCount = 10;
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
        "thumbnail" => "/storage/thumbnails/" . $jobId . "/" . $thumbFile,
        "replace_allowed" => true,
        "text_allowed" => true
    ];
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
    "slots" => $slots
], JSON_PRETTY_PRINT);