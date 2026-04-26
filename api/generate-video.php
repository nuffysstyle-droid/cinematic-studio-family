<?php
/**
 * api/generate-video.php — Video-Prompt Generator
 * Nutzt die Prompt Engine, gibt strukturierten Prompt + Meta zurück.
 * Noch keine externe Seedance/Kie.ai API in V1.
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
$template = trim($body['template'] ?? $_POST['template'] ?? 'cinematic_scene');
$action   = trim($body['action']   ?? $_POST['action']   ?? 'build');

$options  = [
    'model'    => trim($body['model']    ?? $_POST['model']    ?? 'seedance_standard'),
    'duration' => (int) ($body['duration'] ?? $_POST['duration'] ?? 8),
    'quality'  => trim($body['quality']  ?? $_POST['quality']  ?? 'normal'),
    'mode'     => trim($body['mode']     ?? $_POST['mode']     ?? 'text'),
];

// Template validieren
$validTemplates = getAvailableTemplates('video');
if (!in_array($template, $validTemplates, true)) {
    $template = 'cinematic_scene';
}

// Duration auf erlaubte Werte begrenzen
if (!in_array($options['duration'], [5, 8, 10, 15], true)) {
    $options['duration'] = 8;
}

// Basis-Prompt bauen
$result = buildVideoPrompt($input, $template, $options);

// Modifier anwenden
switch ($action) {
    case 'improve':
        $result['positive'] = improvePrompt($result['positive']);
        break;
    case 'fix_faces':
        $result['positive'] = fixFacesPrompt($result['positive']);
        break;
    case 'better_motion':
        $result['positive'] = betterMotionPrompt($result['positive']);
        break;
    case 'perfect_transition':
        $result['positive'] = perfectTransitionPrompt($result['positive']);
        break;
    case 'cinematic':
        $result['positive'] = cinematicUpgradePrompt($result['positive']);
        break;
}

echo json_encode([
    'success'  => true,
    'positive' => $result['positive'],
    'negative' => $result['negative'],
    'meta'     => $result['meta'],
]);
