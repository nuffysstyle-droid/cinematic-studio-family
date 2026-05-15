<?php
/**
 * api/auth/login.php — User-Login
 *
 * POST /api/auth/login
 * Body (JSON): { "email": "...", "password": "...", "remember": true/false }
 *
 * Antwort:
 *   200 → { status:"ok", user: { id, email, plan, crystals_balance } }
 *   400 → { status:"error", message:"...", remaining_tries?: int }
 *   429 → { status:"error", message:"..." }
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

// Rate Limit: 20 Login-Versuche/Stunde pro IP (Brute-Force per IP)
// Feineres Limit per Versuch: csf_auth_login() prüft 5/15min
$rl = csf_rate_limit_check('login', maxRequests: 20, windowSeconds: 3600);
if (!$rl['allowed']) {
    http_response_code(429);
    header('Retry-After: ' . $rl['reset_in']);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Zu viele Login-Versuche. Bitte in ' . ceil($rl['reset_in'] / 60) . ' Minuten erneut versuchen.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Input lesen
$raw      = file_get_contents('php://input');
$body     = json_decode($raw ?: '{}', true) ?? [];
$email    = trim((string) ($body['email']    ?? ''));
$password = (string) ($body['password']      ?? '');
$remember = (bool)   ($body['remember']      ?? false);

if ($email === '' || $password === '') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'E-Mail und Passwort sind erforderlich.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$result = csf_auth_login($email, $password, $remember);

if (!$result['ok']) {
    http_response_code(400);
    $resp = ['status' => 'error', 'message' => $result['error']];
    if (isset($result['remaining_tries'])) {
        $resp['remaining_tries'] = $result['remaining_tries'];
    }
    echo json_encode($resp, JSON_UNESCAPED_UNICODE);
    exit;
}

$user = csf_auth_user();

echo json_encode([
    'status' => 'ok',
    'user'   => $user,
], JSON_UNESCAPED_UNICODE);
