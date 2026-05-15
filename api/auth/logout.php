<?php
/**
 * api/auth/logout.php — User-Logout
 *
 * POST /api/auth/logout
 * Body: (leer)
 *
 * Antwort:
 *   200 → { status:"ok", message:"Erfolgreich abgemeldet." }
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Nur POST erlaubt.']);
    exit;
}

csf_auth_logout();

echo json_encode([
    'status'  => 'ok',
    'message' => 'Erfolgreich abgemeldet.',
], JSON_UNESCAPED_UNICODE);
