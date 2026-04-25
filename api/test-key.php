<?php
/**
 * api/test-key.php — API-Key Session-Handling
 * Empfängt den Key, validiert Mindestlänge, speichert in Session.
 * Keine externe Prüfung in V1.
 */

require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Methode nicht erlaubt.']);
    exit;
}

// JSON-Body oder POST-Feld
$body   = json_decode(file_get_contents('php://input'), true) ?? [];
$apiKey = trim($body['api_key'] ?? $_POST['api_key'] ?? '');

if ($apiKey === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Kein API-Key angegeben.']);
    exit;
}

if (strlen($apiKey) < API_KEY_MIN_LENGTH) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'API-Key zu kurz. Bitte prüfe deinen Key.']);
    exit;
}

// Nur alphanumerische Zeichen, Bindestriche und Unterstriche erlaubt
if (!preg_match('/^[A-Za-z0-9\-_]+$/', $apiKey)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'API-Key enthält ungültige Zeichen.']);
    exit;
}

// In Session speichern — nie in DB, nie in Log
$_SESSION['api_key'] = $apiKey;

echo json_encode([
    'success' => true,
    'message' => 'API-Key wurde für diese Session gespeichert.',
]);
