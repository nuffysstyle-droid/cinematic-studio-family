<?php
/**
 * api/animation-request.php — Animation Service Anfrage speichern
 * Speichert Animations-Anfragen in data/animation-requests.json
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

function reqInput(string $key): string {
    global $body;
    $val = $body[$key] ?? '';
    return is_string($val) ? trim(strip_tags($val)) : '';
}

function optInput(string $key, string $default = ''): string {
    global $body;
    $val = $body[$key] ?? $default;
    return is_string($val) ? trim(strip_tags($val)) : $default;
}

function boolInput(string $key, bool $default = false): bool {
    global $body;
    $val = $body[$key] ?? $default;
    if (is_bool($val)) return $val;
    if (is_string($val)) return in_array(strtolower($val), ['true', '1', 'yes', 'ja']);
    return (bool) $val;
}

// ── Pflichtfelder ─────────────────────────────────────────────────
$type        = reqInput('type');
$description = reqInput('description');

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

// ── Erlaubte Werte validieren ─────────────────────────────────────
const VALID_TYPES  = ['booster', 'multiplikator', 'logo', 'custom'];
const VALID_STYLES = ['neon', 'glitch', 'energy', 'fire', 'luxury', ''];
const VALID_SPEEDS = ['slow', 'normal', 'fast', ''];
const VALID_FORMATS = ['9:16', '1:1', '16:9', ''];

if (!in_array($type, VALID_TYPES, true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Ungültiger Animations-Typ.']);
    exit;
}

$style  = optInput('style', 'energy');
$speed  = optInput('speed', 'normal');
$loop   = boolInput('loop', true);
$format = optInput('format', '9:16');

if (!in_array($style,  VALID_STYLES,  true)) $style  = 'energy';
if (!in_array($speed,  VALID_SPEEDS,  true)) $speed  = 'normal';
if (!in_array($format, VALID_FORMATS, true)) $format = '9:16';

// Start-/Endbeschreibung (optional, für Transformationsanimationen)
$startDescription = optInput('start_description', '');
$endDescription   = optInput('end_description', '');

// Upload-URL (optional, für Logo Animation)
$logoUrl = optInput('logo_url', '');

// ── Anfrage aufbauen ──────────────────────────────────────────────
$now     = date('c');
$request = [
    'id'                => bin2hex(random_bytes(8)),
    'type'              => $type,
    'description'       => $description,
    'start_description' => $startDescription,
    'end_description'   => $endDescription,
    'style'             => $style,
    'speed'             => $speed,
    'loop'              => $loop,
    'format'            => $format,
    'logo_url'          => $logoUrl,
    'status'            => 'pending',
    'created_at'        => $now,
];

// ── In JSON speichern ─────────────────────────────────────────────
$path = DATA_PATH . 'animation-requests.json';

if (!is_dir(dirname($path))) {
    mkdir(dirname($path), 0755, true);
}

$existing = [];
if (file_exists($path)) {
    $raw      = file_get_contents($path);
    $decoded  = json_decode($raw, true);
    $existing = is_array($decoded) ? $decoded : [];
}

$existing[] = $request;
$json       = json_encode(array_values($existing), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

if (file_put_contents($path, $json, LOCK_EX) === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Anfrage konnte nicht gespeichert werden.']);
    exit;
}

// ── Response ──────────────────────────────────────────────────────
http_response_code(201);
echo json_encode([
    'success' => true,
    'message' => 'Animations-Anfrage gespeichert.',
    'id'      => $request['id'],
    'request' => $request,
], JSON_UNESCAPED_UNICODE);
