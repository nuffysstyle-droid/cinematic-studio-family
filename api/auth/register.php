<?php
/**
 * api/auth/register.php — User-Registrierung
 *
 * POST /api/auth/register
 * Body (JSON): { "email": "...", "password": "...", "password_confirm": "..." }
 *
 * Antwort:
 *   201 → { status:"ok", message:"Registrierung erfolgreich." }
 *   400 → { status:"error", message:"..." }
 *   429 → { status:"error", message:"Zu viele Anfragen." }
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/rate_limit.php';
require_once __DIR__ . '/../../includes/auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Nur POST erlaubt.']);
    exit;
}

// Rate Limit: 10 Registrierungen/Stunde pro IP
$rl = csf_rate_limit_check('register', maxRequests: 10, windowSeconds: 3600);
if (!$rl['allowed']) {
    http_response_code(429);
    header('Retry-After: ' . $rl['reset_in']);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Zu viele Registrierungsversuche. Bitte in ' . ceil($rl['reset_in'] / 60) . ' Minuten erneut versuchen.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Input lesen
$raw  = file_get_contents('php://input');
$body = json_decode($raw ?: '{}', true) ?? [];

$email           = trim((string) ($body['email']            ?? ''));
$password        = (string) ($body['password']              ?? '');
$passwordConfirm = (string) ($body['password_confirm']      ?? '');

// Validierung
if ($email === '' || $password === '') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'E-Mail und Passwort sind erforderlich.'], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($password !== $passwordConfirm) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Passwörter stimmen nicht überein.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$result = csf_auth_register($email, $password);

if (!$result['ok']) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $result['error']], JSON_UNESCAPED_UNICODE);
    exit;
}

http_response_code(201);
echo json_encode([
    'status'  => 'ok',
    'message' => 'Registrierung erfolgreich. Du kannst dich jetzt einloggen.',
], JSON_UNESCAPED_UNICODE);
