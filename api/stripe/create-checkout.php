<?php
/**
 * api/stripe/create-checkout.php — Stripe Checkout Session erstellen
 *
 * POST /api/stripe/create-checkout.php
 * Erfordert: eingeloggter User auf Free-Plan
 *
 * Antwort:
 *   200 → { status:"ok", url:"https://checkout.stripe.com/..." }
 *   400 → { status:"error", message:"..." }
 *   401 → nicht eingeloggt
 *   503 → Stripe nicht konfiguriert
 *
 * Render Env-Vars:
 *   STRIPE_SECRET_KEY       — sk_live_... oder sk_test_...
 *   STRIPE_PRICE_ID_STARTER — price_... (monatliches Abo)
 *   APP_URL                 — https://cinematic-studio-family.onrender.com
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/mailer.php'; // csf_app_url()

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Nur POST erlaubt.']);
    exit;
}

$user = csf_auth_user();
if ($user === null) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Login erforderlich.']);
    exit;
}

if ($user['plan'] !== 'free') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Plan bereits aktiv — kein Upgrade nötig.']);
    exit;
}

$stripeKey = (string) (getenv('STRIPE_SECRET_KEY')       ?: ($_SERVER['STRIPE_SECRET_KEY']       ?? ''));
$priceId   = (string) (getenv('STRIPE_PRICE_ID_STARTER') ?: ($_SERVER['STRIPE_PRICE_ID_STARTER'] ?? ''));

if ($stripeKey === '' || $priceId === '') {
    http_response_code(503);
    echo json_encode(['status' => 'error', 'message' => 'Stripe ist noch nicht konfiguriert. Bitte später erneut versuchen.']);
    exit;
}

$appUrl = csf_app_url();

$fields = [
    'mode'                                 => 'subscription',
    'line_items[0][price]'                 => $priceId,
    'line_items[0][quantity]'              => '1',
    'success_url'                          => $appUrl . '/dashboard.php?upgraded=1',
    'cancel_url'                           => 'https://cinematic-vision-studio.de/crystals.html',
    'client_reference_id'                  => (string) $user['id'],
    'customer_email'                       => $user['email'],
    'subscription_data[metadata][user_id]' => (string) $user['id'],
    'subscription_data[metadata][plan]'    => 'starter',
];

$body = http_build_query($fields);
$auth = base64_encode($stripeKey . ':');

$opts = [
    'http' => [
        'method'        => 'POST',
        'header'        => "Authorization: Basic {$auth}\r\n"
                         . "Content-Type: application/x-www-form-urlencoded\r\n"
                         . 'Content-Length: ' . strlen($body),
        'content'       => $body,
        'timeout'       => 15,
        'ignore_errors' => true,
    ],
];

$resp = @file_get_contents(
    'https://api.stripe.com/v1/checkout/sessions',
    false,
    stream_context_create($opts)
);

if ($resp === false) {
    http_response_code(502);
    echo json_encode(['status' => 'error', 'message' => 'Stripe nicht erreichbar. Bitte erneut versuchen.']);
    exit;
}

$status = 0;
if (!empty($http_response_header)) {
    preg_match('/\s(\d{3})\s/', $http_response_header[0], $m);
    $status = isset($m[1]) ? (int) $m[1] : 0;
}

$data = json_decode($resp, true) ?? [];

if ($status !== 200 || empty($data['url'])) {
    http_response_code(502);
    echo json_encode(['status' => 'error', 'message' => 'Checkout-Session konnte nicht erstellt werden.']);
    exit;
}

echo json_encode(['status' => 'ok', 'url' => $data['url']]);
