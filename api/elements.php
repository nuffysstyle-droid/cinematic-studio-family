<?php
/**
 * api/elements.php — Element Library CRUD
 * Aktionen: list, delete, update (vorbereitet)
 */

require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$body   = [];
if ($method === 'POST') {
    $raw  = file_get_contents('php://input');
    $body = ($raw ? json_decode($raw, true) : null) ?? [];
    if (!empty($_POST)) $body = array_merge($body, $_POST);
}

$action = trim($body['action'] ?? $_GET['action'] ?? '');
$path   = DATA_PATH . 'elements.json';

function loadElements(string $path): array {
    if (!file_exists($path)) return [];
    $data = json_decode(file_get_contents($path), true);
    return is_array($data) ? $data : [];
}

function saveElements(array $elements, string $path): bool {
    return file_put_contents(
        $path,
        json_encode(array_values($elements), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        LOCK_EX
    ) !== false;
}

switch ($action) {

    case 'list':
        $elements = loadElements($path);
        usort($elements, fn($a, $b) => strcmp($b['created_at'], $a['created_at']));
        echo json_encode(['success' => true, 'data' => $elements]);
        break;

    case 'delete':
        $id = trim($body['id'] ?? $_GET['id'] ?? '');
        if ($id === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => '"id" erforderlich.']);
            break;
        }
        $elements = loadElements($path);
        $filtered = array_filter($elements, fn($e) => $e['id'] !== $id);
        if (count($filtered) === count($elements)) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Element nicht gefunden.']);
            break;
        }
        if (!saveElements($filtered, $path)) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Löschen fehlgeschlagen.']);
            break;
        }
        echo json_encode(['success' => true, 'data' => ['deleted' => $id]]);
        break;

    case 'update':
        // TODO Phase 3: Felder aktualisieren
        http_response_code(501);
        echo json_encode(['success' => false, 'error' => 'Update wird in Phase 3 implementiert.']);
        break;

    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => "Unbekannte action: \"{$action}\"."]);
}
