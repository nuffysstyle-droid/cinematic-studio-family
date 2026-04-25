<?php
/**
 * api/upload.php — Sicherer Datei-Upload Endpunkt
 * Unterstützt: Bilder (JPEG, PNG, WEBP) und Videos (MP4, WEBM, MOV)
 */

require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

// Nur POST erlauben
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Methode nicht erlaubt.']);
    exit;
}

// Datei vorhanden?
if (empty($_FILES['file']) || $_FILES['file']['error'] === UPLOAD_ERR_NO_FILE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Keine Datei empfangen.']);
    exit;
}

$file = $_FILES['file'];

// Upload-Fehler des PHP-Interpreters abfangen
if ($file['error'] !== UPLOAD_ERR_OK) {
    $phpErrors = [
        UPLOAD_ERR_INI_SIZE   => 'Datei überschreitet upload_max_filesize.',
        UPLOAD_ERR_FORM_SIZE  => 'Datei überschreitet MAX_FILE_SIZE.',
        UPLOAD_ERR_PARTIAL    => 'Datei nur teilweise hochgeladen.',
        UPLOAD_ERR_NO_TMP_DIR => 'Kein temporäres Verzeichnis verfügbar.',
        UPLOAD_ERR_CANT_WRITE => 'Schreibfehler auf dem Server.',
        UPLOAD_ERR_EXTENSION  => 'Upload durch PHP-Extension blockiert.',
    ];
    $msg = $phpErrors[$file['error']] ?? 'Unbekannter Upload-Fehler.';
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $msg]);
    exit;
}

// Wirklich eine hochgeladene Datei (kein Path-Traversal)?
if (!is_uploaded_file($file['tmp_name'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Ungültige Datei.']);
    exit;
}

// ----------------------------------------------------------------
// MIME-Type per finfo prüfen (nicht dem Browser-MIME vertrauen)
// ----------------------------------------------------------------
$finfo    = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($file['tmp_name']);

$allowedImages = ['image/jpeg', 'image/png', 'image/webp'];
$allowedVideos = ['video/mp4', 'video/webm', 'video/quicktime'];
$allAllowed    = array_merge($allowedImages, $allowedVideos);

if (!in_array($mimeType, $allAllowed, true)) {
    http_response_code(415);
    echo json_encode(['success' => false, 'error' => 'Dateityp nicht erlaubt.']);
    exit;
}

$isImage = in_array($mimeType, $allowedImages, true);
$isVideo = in_array($mimeType, $allowedVideos, true);

// ----------------------------------------------------------------
// Größenlimit prüfen
// ----------------------------------------------------------------
$maxBytes = $isImage
    ? 10  * 1024 * 1024   // 10 MB für Bilder
    : 100 * 1024 * 1024;  // 100 MB für Videos

if ($file['size'] > $maxBytes) {
    $limitMb = $maxBytes / 1024 / 1024;
    http_response_code(413);
    echo json_encode(['success' => false, 'error' => "Datei zu groß. Limit: {$limitMb} MB."]);
    exit;
}

// ----------------------------------------------------------------
// Sichere Dateiendung ableiten (aus MIME, nicht aus Dateiname)
// ----------------------------------------------------------------
$extMap = [
    'image/jpeg'      => 'jpg',
    'image/png'       => 'png',
    'image/webp'      => 'webp',
    'video/mp4'       => 'mp4',
    'video/webm'      => 'webm',
    'video/quicktime' => 'mov',
];
$ext = $extMap[$mimeType];

// ----------------------------------------------------------------
// Sicheren eindeutigen Dateinamen erzeugen
// ----------------------------------------------------------------
$uniqueName = bin2hex(random_bytes(16)) . '.' . $ext;

// ----------------------------------------------------------------
// Zielverzeichnis bestimmen und anlegen
// ----------------------------------------------------------------
$subDir  = $isImage ? 'images' : 'videos';
$destDir = STORAGE_PATH . 'uploads/' . $subDir . '/';

if (!is_dir($destDir)) {
    if (!mkdir($destDir, 0755, true)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Verzeichnis konnte nicht erstellt werden.']);
        exit;
    }
}

$destPath = $destDir . $uniqueName;

// ----------------------------------------------------------------
// Datei verschieben
// ----------------------------------------------------------------
if (!move_uploaded_file($file['tmp_name'], $destPath)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Datei konnte nicht gespeichert werden.']);
    exit;
}

// ----------------------------------------------------------------
// Erfolg — URL relativ zur App-Root zurückgeben
// ----------------------------------------------------------------
$relativeUrl = BASE_URL . '/storage/uploads/' . $subDir . '/' . $uniqueName;

echo json_encode([
    'success'  => true,
    'url'      => $relativeUrl,
    'filename' => $uniqueName,
    'type'     => $isImage ? 'image' : 'video',
    'size'     => $file['size'],
]);
