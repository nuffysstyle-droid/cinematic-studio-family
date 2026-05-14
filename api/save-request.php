<?php
/**
 * api/save-request.php — Video-Anfragen aus ready-videos.php speichern
 *
 * Speichert Kontaktanfragen (E-Mail/Telegram + Beschreibung + Kontext)
 * in storage/requests.json (LOCK_EX). Kein E-Mail-Versand in V1.
 *
 * POST:
 *   contact  string  E-Mail oder Telegram (max 200 Zeichen, Pflicht)
 *   message  string  Beschreibung (max 1000 Zeichen, optional)
 *   context  string  Welches Video / Kontext (max 300 Zeichen, optional)
 *
 * Response:
 *   ok    → { status:"ok", id }
 *   fail  → { status:"error", message }
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Nur POST erlaubt.']);
    exit;
}

// ── Eingabe ──────────────────────────────────────────────────────────────────
$contact = trim((string)($_POST['contact'] ?? ''));
$message = trim((string)($_POST['message'] ?? ''));
$context = trim((string)($_POST['context'] ?? ''));

if ($contact === '') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Kontakt (E-Mail oder Telegram) fehlt.']);
    exit;
}
if (mb_strlen($contact) > 200) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Kontakt zu lang (max. 200 Zeichen).']);
    exit;
}
if (mb_strlen($message) > 1000) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Nachricht zu lang (max. 1000 Zeichen).']);
    exit;
}

// ── Speicherpfad ─────────────────────────────────────────────────────────────
$storageRoot = realpath(__DIR__ . '/../storage');
if ($storageRoot === false) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Storage nicht verfügbar.']);
    exit;
}

$requestsFile = $storageRoot . '/requests.json';

// ── Bestehende Einträge lesen ─────────────────────────────────────────────────
$fp = @fopen($requestsFile, 'c+');
if ($fp === false) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Requests-Datei konnte nicht geöffnet werden.']);
    exit;
}

if (!flock($fp, LOCK_EX)) {
    fclose($fp);
    http_response_code(503);
    echo json_encode(['status' => 'error', 'message' => 'Datei gesperrt — bitte erneut versuchen.']);
    exit;
}

$existing = [];
$raw = stream_get_contents($fp);
if ($raw !== false && $raw !== '') {
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) {
        $existing = $decoded;
    }
}

// ── Neuen Eintrag anhängen ────────────────────────────────────────────────────
$id = 'req_' . date('Ymd_His') . '_' . bin2hex(random_bytes(3));

$entry = [
    'id'         => $id,
    'contact'    => $contact,
    'message'    => $message,
    'context'    => $context,
    'created_at' => date('c'),
    'ip_hash'    => hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '') . 'csf_salt'),
];

$existing[] = $entry;

$newJson = json_encode($existing, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if ($newJson !== false) {
    ftruncate($fp, 0);
    rewind($fp);
    fwrite($fp, $newJson);
    fflush($fp);
}

flock($fp, LOCK_UN);
fclose($fp);

echo json_encode(['status' => 'ok', 'id' => $id], JSON_UNESCAPED_SLASHES);
