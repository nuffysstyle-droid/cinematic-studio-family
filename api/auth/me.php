<?php
/**
 * api/auth/me.php — Aktuellen User abrufen
 *
 * GET /api/auth/me
 *
 * Antwort:
 *   200 → { status:"ok", user: { id, email, plan, crystals_balance } }
 *   401 → { status:"error", message:"Nicht eingeloggt." }
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Nur GET erlaubt.']);
    exit;
}

$user = csf_auth_user();

if ($user === null) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Nicht eingeloggt.'], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode([
    'status' => 'ok',
    'user'   => $user,
], JSON_UNESCAPED_UNICODE);
