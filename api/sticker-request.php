<?php
/**
 * api/sticker-request.php — Sticker Service Anfrage speichern
 * Speichert Sticker-Anfragen in data/sticker-requests.json
 * Kein externer API-Call — reines Service-Request-Handling.
 */

require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Nur POST erlaubt.']);
    exit;
}

// ── Input ─────────────────────────────────────────────────────────
$raw  = file_get_contents('php://input');
$body = json_decode($raw, true) ?? [];

function stkInput(string $key, string $default = ''): string {
    global $body;
    $val = $body[$key] ?? $default;
    return is_string($val) ? trim(strip_tags($val)) : $default;
}

// ── Pflichtfelder ─────────────────────────────────────────────────
$type        = stkInput('type');
$description = stkInput('description');

if ($type === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Feld "type" ist erforderlich.']);
    exit;
}

if ($description === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Feld "description" ist erforderlich.']);
    exit;
}

// ── Whitelist Validierung ─────────────────────────────────────────
const VALID_STK_TYPES   = ['emoji', 'text', 'logo', 'reaction', 'custom'];
const VALID_STK_STYLES  = ['neon', 'glow', 'gold', 'fire', 'minimal', 'cartoon', ''];
const VALID_STK_SIZES   = ['small', 'medium', 'large', ''];
const VALID_STK_FORMATS = ['png_transparent', '1:1', '9:16', ''];

if (!in_array($type, VALID_STK_TYPES, true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Ungültiger Sticker-Typ.']);
    exit;
}

$style    = stkInput('style',  'neon');
$size     = stkInput('size',   'medium');
$format   = stkInput('format', 'png_transparent');
$text     = stkInput('text',   '');
$logoUrl  = stkInput('logo_url', '');

if (!in_array($style,  VALID_STK_STYLES,  true)) $style  = 'neon';
if (!in_array($size,   VALID_STK_SIZES,   true)) $size   = 'medium';
if (!in_array($format, VALID_STK_FORMATS, true)) $format = 'png_transparent';

// ── Anfrage aufbauen ──────────────────────────────────────────────
$request = [
    'id'          => bin2hex(random_bytes(8)),
    'type'        => $type,
    'description' => $description,
    'text'        => $text,
    'style'       => $style,
    'size'        => $size,
    'format'      => $format,
    'logo_url'    => $logoUrl,
    'status'      => 'pending',
    'created_at'  => date('c'),
];

// ── In JSON speichern ─────────────────────────────────────────────
$path = DATA_PATH . 'sticker-requests.json';

if (!is_dir(dirname($path))) {
    mkdir(dirname($path), 0755, true);
}

$existing = [];
if (file_exists($path)) {
    $decoded  = json_decode(file_get_contents($path), true);
    $existing = is_array($decoded) ? $decoded : [];
}

$existing[] = $request;
$json       = json_encode(array_values($existing), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

if (file_put_contents($path, $json, LOCK_EX) === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Anfrage konnte nicht gespeichert werden.']);
    exit;
}

http_response_code(201);
echo json_encode([
    'success' => true,
    'message' => 'Sticker-Anfrage gespeichert.',
    'id'      => $request['id'],
    'request' => $request,
], JSON_UNESCAPED_UNICODE);
