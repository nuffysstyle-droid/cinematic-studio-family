<?php
/**
 * api/auth/change-password.php — Passwort ändern
 *
 * POST /api/auth/change-password
 * Body (JSON): { "current_password": "...", "new_password": "...", "new_password_confirm": "..." }
 *
 * Antwort:
 *   200 → { status:"ok", message:"Passwort erfolgreich geändert." }
 *   400 → { status:"error", message:"..." }
 *   401 → { status:"error", message:"Nicht eingeloggt." }
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/rate_limit.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Nur POST erlaubt.']);
    exit;
}

// Rate Limit: 10 Passwort-Änderungen/Stunde pro IP
$rl = csf_rate_limit_check('change_password', maxRequests: 10, windowSeconds: 3600);
if (!$rl['allowed']) {
    http_response_code(429);
    header('Retry-After: ' . $rl['reset_in']);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Zu viele Versuche. Bitte in ' . ceil($rl['reset_in'] / 60) . ' Minuten erneut versuchen.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Login prüfen
$user = csf_auth_require();

// Input lesen
$raw  = file_get_contents('php://input');
$body = json_decode($raw ?: '{}', true) ?? [];

$currentPassword   = (string)($body['current_password']    ?? '');
$newPassword       = (string)($body['new_password']         ?? '');
$newPasswordConfirm = (string)($body['new_password_confirm'] ?? '');

// Validierung
if ($currentPassword === '' || $newPassword === '' || $newPasswordConfirm === '') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Bitte alle Felder ausfüllen.'], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($newPassword !== $newPasswordConfirm) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Neue Passwörter stimmen nicht überein.'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (mb_strlen($newPassword) < 8) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Neues Passwort muss mindestens 8 Zeichen lang sein.'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (mb_strlen($newPassword) > 1000) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Passwort zu lang.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Aktuelles Passwort prüfen
$pdo  = csf_db();
$stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $user['id']]);
$row  = $stmt->fetch();

if (!$row || !password_verify($currentPassword, $row['password_hash'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Aktuelles Passwort ist falsch.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Neues Passwort hashen und speichern
$newHash = password_hash($newPassword, PASSWORD_ARGON2ID);
if ($newHash === false) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Passwort konnte nicht gehasht werden.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$pdo->prepare("
    UPDATE users
    SET    password_hash = :hash,
           updated_at    = datetime('now','utc')
    WHERE  id = :id
")->execute([':hash' => $newHash, ':id' => $user['id']]);

// Alle Remember-Tokens invalidieren (Security: neue Creds → alle Sessions)
$pdo->prepare("DELETE FROM remember_tokens WHERE user_id = :uid")
    ->execute([':uid' => $user['id']]);

echo json_encode([
    'status'  => 'ok',
    'message' => 'Passwort erfolgreich geändert.',
], JSON_UNESCAPED_UNICODE);
