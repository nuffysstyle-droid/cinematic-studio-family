<?php
/**
 * api/generate-image.php — Bild-Prompt Generator
 * Nutzt die Prompt Engine, gibt strukturierten Prompt zurück.
 * Noch keine externe Bild-API in V1.
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/prompt-engine.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Methode nicht erlaubt.']);
    exit;
}

$body     = json_decode(file_get_contents('php://input'), true) ?? [];
$input    = sanitizePromptInput($body['input']    ?? $_POST['input']    ?? '');
$template = trim($body['template'] ?? $_POST['template'] ?? 'character');
$action   = trim($body['action']   ?? $_POST['action']   ?? 'build');

// Template validieren
$validTemplates = getAvailableTemplates('image');
if (!in_array($template, $validTemplates, true)) {
    $template = 'character';
}

// Basis-Prompt bauen
$result = buildImagePrompt($input, $template);

// Optionale Modifier anwenden
switch ($action) {
    case 'improve':
        $result['positive'] = improvePrompt($result['positive']);
        break;
    case 'cinematic':
        $result['positive'] = cinematicUpgradePrompt($result['positive']);
        break;
    case 'fix_faces':
        $result['positive'] = fixFacesPrompt($result['positive']);
        break;
}

echo json_encode([
    'success'  => true,
    'positive' => $result['positive'],
    'negative' => $result['negative'],
    'template' => $template,
    'action'   => $action,
]);
