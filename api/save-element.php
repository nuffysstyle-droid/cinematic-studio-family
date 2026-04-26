<?php
/**
 * api/save-element.php — Neues Element speichern
 * Speicherort: data/elements.json
 */

require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Methode nicht erlaubt.']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true) ?? [];
if (!empty($_POST)) $body = array_merge($body, $_POST);

function inputStr(string $key, array $body): string {
    $val = $body[$key] ?? '';
    return trim(strip_tags((string) $val));
}

$name        = inputStr('name',        $body);
$type        = inputStr('type',        $body);
$description = inputStr('description', $body);
$role        = inputStr('role',        $body);
$imageUrl    = inputStr('image_url',   $body);

// Validierung
if ($name === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Feld "name" ist erforderlich.']);
    exit;
}

$validTypes = ['character', 'car', 'product', 'creature', 'environment', 'logo', 'object', 'style_reference'];
if ($type === '' || !in_array($type, $validTypes, true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Ungültiger oder fehlender Typ.']);
    exit;
}

$validRoles = ['main_character', 'main_object', 'background', 'style_reference', ''];
if (!in_array($role, $validRoles, true)) {
    $role = '';
}

// Datei laden
$path     = DATA_PATH . 'elements.json';
$elements = [];
if (file_exists($path)) {
    $decoded  = json_decode(file_get_contents($path), true);
    $elements = is_array($decoded) ? $decoded : [];
}

$now     = date('c');
$element = [
    'id'          => bin2hex(random_bytes(8)),
    'name'        => $name,
    'type'        => $type,
    'role'        => $role,
    'description' => $description,
    'image_url'   => $imageUrl,
    'created_at'  => $now,
    'updated_at'  => $now,
];

$elements[] = $element;

if (file_put_contents($path, json_encode(array_values($elements), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX) === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Element konnte nicht gespeichert werden.']);
    exit;
}

http_response_code(201);
echo json_encode(['success' => true, 'data' => $element]);
