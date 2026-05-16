<?php
/**
 * api/settings/quality.php — Export-Qualität setzen
 *
 * POST /api/settings/quality
 * Body: { "quality": "720p" | "1080p" }
 *
 * Regeln:
 *   - Free-Plan → max. 720p (1080p wird auf 720p zurückgesetzt)
 *   - Starter+ / Pro → 1080p erlaubt
 *   - Nicht eingeloggt → 720p (Demo-Modus)
 *
 * Antwort:
 *   200 → { status:"ok", quality:"720p"|"1080p", plan:"free"|"starter"|"pro", upgraded:bool }
 *   400 → { status:"error", message:"..." }
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

$raw     = file_get_contents('php://input');
$body    = json_decode($raw ?: '{}', true) ?? [];
$quality = (string)($body['quality'] ?? '720p');

if (!in_array($quality, ['720p', '1080p'], true)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Ungültige Qualität. Erlaubt: 720p, 1080p.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Plan prüfen
$user    = csf_auth_user();
$plan    = $user['plan'] ?? 'free';
$canHD   = in_array($plan, ['starter', 'pro'], true);

// Free-Plan auf 720p begrenzen
$effective = ($quality === '1080p' && !$canHD) ? '720p' : $quality;

// Session setzen
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['export_quality'] = $effective;

echo json_encode([
    'status'    => 'ok',
    'quality'   => $effective,
    'plan'      => $plan,
    'requested' => $quality,
    'capped'    => ($quality === '1080p' && !$canHD),
    'can_hd'    => $canHD,
], JSON_UNESCAPED_UNICODE);
