<?php
/**
 * api/stripe/webhook.php — Stripe Webhook Handler
 *
 * POST /api/stripe/webhook.php
 * Stripe-Signatur via HMAC-SHA256 verifiziert.
 *
 * Verarbeitete Events:
 *   checkout.session.completed → User-Plan upgraden, 50 Bonus-Kristalle gutschreiben
 *   customer.subscription.deleted → Plan auf 'free' zurücksetzen (Kündigung)
 *
 * Render Env-Vars:
 *   STRIPE_WEBHOOK_SECRET — whsec_... (Signing Secret aus Stripe Dashboard)
 */

declare(strict_types=1);

// Raw body MUSS VOR jeder Ausgabe gelesen werden
$rawBody = (string) file_get_contents('php://input');

require_once __DIR__ . '/../../includes/db.php';

header('Content-Type: application/json');

$webhookSecret = (string) (getenv('STRIPE_WEBHOOK_SECRET') ?: ($_SERVER['STRIPE_WEBHOOK_SECRET'] ?? ''));

if ($webhookSecret === '') {
    http_response_code(500);
    echo json_encode(['error' => 'Webhook secret not configured']);
    exit;
}

// ── Stripe-Signatur verifizieren ─────────────────────────────────────────────
// Header: Stripe-Signature: t=timestamp,v1=hmac_sha256(timestamp.body,secret)
$sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

if ($sigHeader === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Missing Stripe-Signature header']);
    exit;
}

$parts = [];
foreach (explode(',', $sigHeader) as $chunk) {
    $pair        = explode('=', $chunk, 2);
    $parts[$pair[0]] = $pair[1] ?? '';
}

$timestamp = $parts['t']  ?? '';
$v1Sig     = $parts['v1'] ?? '';

if ($timestamp === '' || $v1Sig === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid Stripe-Signature format']);
    exit;
}

// Replay-Attack-Schutz: max. 5 Minuten Toleranz
if (abs(time() - (int) $timestamp) > 300) {
    http_response_code(400);
    echo json_encode(['error' => 'Webhook timestamp too old']);
    exit;
}

$expectedSig = hash_hmac('sha256', $timestamp . '.' . $rawBody, $webhookSecret);
if (!hash_equals($expectedSig, $v1Sig)) {
    http_response_code(400);
    echo json_encode(['error' => 'Signature verification failed']);
    exit;
}

// ── Event verarbeiten ────────────────────────────────────────────────────────
$event = json_decode($rawBody, true) ?? [];
$type  = (string) ($event['type'] ?? '');

if ($type === 'checkout.session.completed') {
    $session    = $event['data']['object'] ?? [];
    $userId     = (int) ($session['client_reference_id'] ?? 0);
    $planTarget = (string) ($session['subscription_data']['metadata']['plan'] ?? 'starter');

    if (!in_array($planTarget, ['starter', 'pro'], true)) {
        $planTarget = 'starter';
    }

    if ($userId > 0) {
        $pdo = csf_db();

        // Plan upgraden (nur wenn aktuell Free — idempotent)
        $pdo->prepare(
            "UPDATE users SET plan = :plan, updated_at = datetime('now','utc')
             WHERE id = :id AND plan = 'free'"
        )->execute([':plan' => $planTarget, ':id' => $userId]);

        // 50 Bonus-Kristalle für Starter+ Upgrade
        if ($planTarget === 'starter') {
            $pdo->prepare(
                "UPDATE users SET crystals_balance = crystals_balance + 50 WHERE id = :id"
            )->execute([':id' => $userId]);
            $pdo->prepare(
                "INSERT INTO crystal_transactions (user_id, amount, action, note)
                 VALUES (:uid, 50, 'starter_bonus', 'Starter+ Upgrade Bonus')"
            )->execute([':uid' => $userId]);
        }
    }
}

if ($type === 'customer.subscription.deleted') {
    // Abo gekündigt → Plan auf Free zurücksetzen
    $sub    = $event['data']['object'] ?? [];
    $userId = (int) ($sub['metadata']['user_id'] ?? 0);

    if ($userId > 0) {
        csf_db()->prepare(
            "UPDATE users SET plan = 'free', updated_at = datetime('now','utc')
             WHERE id = :id AND plan != 'free'"
        )->execute([':id' => $userId]);
    }
}

http_response_code(200);
echo json_encode(['received' => true]);
