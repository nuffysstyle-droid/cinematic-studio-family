<?php
/**
 * api/contact-submit.php — Projektbriefing-API
 *
 * Empfängt JSON per POST, validiert, sendet per mail().
 * CORS-freigeschaltet für Aufruf aus statischem HTML.
 */
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Nur POST erlaubt.']);
    exit;
}

// mbstring-Polyfill für IONOS
if (!function_exists('mb_substr')) {
    function mb_substr(string $str, int $start, ?int $length = null): string {
        return $length === null ? substr($str, $start) : substr($str, $start, $length);
    }
}
if (!function_exists('mb_strlen')) {
    function mb_strlen(string $str): int {
        return strlen($str);
    }
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Ungültige Daten.']);
    exit;
}

$name         = mb_substr(trim(strip_tags((string)($input['name']          ?? ''))), 0, 100);
$emailIn      = trim((string)($input['email']         ?? ''));
$projectType  = mb_substr(trim(strip_tags((string)($input['project_type']  ?? ''))), 0, 50);
$platform     = mb_substr(trim(strip_tags((string)($input['platform']      ?? ''))), 0, 50);
$length       = mb_substr(trim(strip_tags((string)($input['length']        ?? ''))), 0, 20);
$style        = mb_substr(trim(strip_tags((string)($input['style']         ?? ''))), 0, 50);
$message      = mb_substr(trim(strip_tags((string)($input['message']       ?? ''))), 0, 3000);
$audience     = mb_substr(trim(strip_tags((string)($input['audience']      ?? ''))), 0, 300);
$music        = mb_substr(trim(strip_tags((string)($input['music']         ?? ''))), 0, 200);
$references   = mb_substr(trim(strip_tags((string)($input['references']    ?? ''))), 0, 1000);
$hasFiles     = !empty($input['has_files']);

if ($name === '' || !filter_var($emailIn, FILTER_VALIDATE_EMAIL) || $projectType === '' || mb_strlen($message) < 10) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'Bitte fülle alle Pflichtfelder aus (Name, E-Mail, Projekt-Typ, Kernbotschaft mind. 10 Zeichen).']);
    exit;
}

$to      = 'info@cinematic-vision-studio.de';
$subj    = '=?UTF-8?B?' . base64_encode('Projektbriefing: ' . ($projectType !== '' ? ucfirst($projectType) : 'Neues Projekt')) . '?=';

$bodyLines = [
    "Name: {$name}",
    "E-Mail: {$emailIn}",
    "",
    "Projekt-Typ: " . ($projectType ?: '–'),
    "Plattform: " . ($platform ?: '–'),
    "Länge: " . ($length ?: '–') . " Sek.",
    "Stil: " . ($style ?: '–'),
    "",
    "Kernbotschaft:",
    $message,
    "",
    "Zielgruppe: " . ($audience ?: '–'),
    "Musik-Vorliebe: " . ($music ?: '–'),
    "",
    "Referenzen:",
    $references ?: '–',
    "",
    "Dateien angehängt: " . ($hasFiles ? 'Ja' : 'Nein'),
];
$body    = implode("\r\n", $bodyLines);
$headers = implode("\r\n", [
    'From: noreply@cinematic-vision-studio.de',
    'Reply-To: ' . $emailIn,
    'Content-Type: text/plain; charset=UTF-8',
    'MIME-Version: 1.0',
    'X-Mailer: CinematicStudio/1.0',
]);

if (@mail($to, $subj, $body, $headers)) {
    echo json_encode(['ok' => true, 'message' => 'Brief gesendet! Wir melden uns innerhalb von 24 Stunden.']);
} else {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'E-Mail konnte nicht gesendet werden. Bitte schreib uns direkt: info@cinematic-vision-studio.de']);
}
