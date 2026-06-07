<?php
/**
 * api/auth/forgot-password.php — Passwort-Reset
 *
 * POST /api/auth/forgot-password
 *
 * Modus 1 — Reset anfordern (nur email im Body):
 *   Body: { "email": "..." }
 *   → Token in DB speichern (password_resets Tabelle)
 *   → E-Mail senden (TODO: SMTP in V0.5) — aktuell nur Token gespeichert
 *   Antwort: immer 200 (verhindert User-Enumeration)
 *
 * Modus 2 — Neues Passwort setzen (token + new_password im Body):
 *   Body: { "token": "...", "new_password": "...", "new_password_confirm": "..." }
 *   → Token prüfen, Passwort updaten, Token löschen
 *   200 → { status:"ok" }
 *   400 → { status:"error", message:"..." }
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/rate_limit.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/mailer.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Nur POST erlaubt.']);
    exit;
}

// Rate Limit: 5 Reset-Anfragen/Stunde pro IP
$rl = csf_rate_limit_check('forgot_password', maxRequests: 5, windowSeconds: 3600);
if (!$rl['allowed']) {
    http_response_code(429);
    header('Retry-After: ' . $rl['reset_in']);
    // Gleiche Response wie Erfolg (verhindert Enumeration)
    echo json_encode(['status' => 'ok'], JSON_UNESCAPED_UNICODE);
    exit;
}

$raw  = file_get_contents('php://input');
$body = json_decode($raw ?: '{}', true) ?? [];

$pdo = csf_db();

// ── Schema für password_resets anlegen wenn nötig ─────────────────────────────
$pdo->exec("
    CREATE TABLE IF NOT EXISTS password_resets (
        id         INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id    INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
        token_hash TEXT    UNIQUE NOT NULL,
        expires_at INTEGER NOT NULL,
        created_at INTEGER NOT NULL DEFAULT (unixepoch())
    )
");

// ── Modus bestimmen ───────────────────────────────────────────────────────────
$token = trim((string)($body['token'] ?? ''));

if ($token !== '') {
    // ── Modus 2: Passwort mit Token zurücksetzen ──────────────────────────────
    // Token-Redemption darf NICHT durch fehlendes SMTP blockiert werden

    $newPassword        = (string)($body['new_password']         ?? '');
    $newPasswordConfirm = (string)($body['new_password_confirm'] ?? '');

    if ($newPassword === '' || $newPasswordConfirm === '') {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Bitte alle Felder ausfüllen.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($newPassword !== $newPasswordConfirm) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Passwörter stimmen nicht überein.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (mb_strlen($newPassword) < 8) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Passwort muss mindestens 8 Zeichen haben.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Token prüfen
    $tokenHash = hash('sha256', $token);
    $stmt = $pdo->prepare("
        SELECT user_id FROM password_resets
        WHERE token_hash = :th AND expires_at > :now
        LIMIT 1
    ");
    $stmt->execute([':th' => $tokenHash, ':now' => time()]);
    $row = $stmt->fetch();

    if (!$row) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Ungültiger oder abgelaufener Reset-Link.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $userId  = (int) $row['user_id'];
    $newHash = password_hash($newPassword, PASSWORD_ARGON2ID);

    $pdo->prepare("
        UPDATE users SET password_hash = :h, updated_at = datetime('now','utc') WHERE id = :id
    ")->execute([':h' => $newHash, ':id' => $userId]);

    // Token + alle Remember-Tokens löschen
    $pdo->prepare("DELETE FROM password_resets WHERE token_hash = :th")->execute([':th' => $tokenHash]);
    $pdo->prepare("DELETE FROM remember_tokens WHERE user_id = :uid")->execute([':uid' => $userId]);

    echo json_encode(['status' => 'ok', 'message' => 'Passwort erfolgreich zurückgesetzt.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// ── Modus 1: Reset-Link anfordern ─────────────────────────────────────────────
// SMTP muss für Versand konfiguriert sein
if (!csf_mail_is_configured()) {
    http_response_code(503);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Passwort-Reset ist aktuell nicht verfügbar. Kontaktiere den Support.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$email = strtolower(trim((string)($body['email'] ?? '')));

// Immer 200 zurückgeben (User-Enumeration verhindern)
$success = ['status' => 'ok'];

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode($success, JSON_UNESCAPED_UNICODE);
    exit;
}

$stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
$stmt->execute([':email' => $email]);
$user = $stmt->fetch();

if ($user) {
    // Altes Token löschen
    $pdo->prepare("DELETE FROM password_resets WHERE user_id = :uid")
        ->execute([':uid' => $user['id']]);

    // Neues Token
    $rawToken  = bin2hex(random_bytes(32)); // 64 Zeichen hex
    $tokenHash = hash('sha256', $rawToken);
    $expires   = time() + 3600; // 1 Stunde gültig

    $pdo->prepare("
        INSERT INTO password_resets (user_id, token_hash, expires_at)
        VALUES (:uid, :th, :exp)
    ")->execute([':uid' => $user['id'], ':th' => $tokenHash, ':exp' => $expires]);

    // Reset-Link per E-Mail senden
    $resetUrl = 'https://cinematic-studio-family.onrender.com/forgot-password.php?token=' . $rawToken;
    csf_mail_password_reset($email, $resetUrl);
}

echo json_encode($success, JSON_UNESCAPED_UNICODE);
