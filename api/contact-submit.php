<?php
/**
 * api/contact-submit.php — Projektbriefing-API
 *
 * Empfängt JSON per POST, validiert, sendet per mail().
 *
 * Sicherheit:
 *   - Origin-Allowlist statt Access-Control-Allow-Origin: *
 *   - IP-Rate-Limit über includes/rate_limit.php (fail-closed bei Speicherfehlern)
 *   - Honeypot-Feld "website" — Treffer werden neutral beantwortet, ohne Mailversand
 *   - Serverseitige Feldlängen-Limits + Body-Größenlimit
 *
 * Deployment-Hinweis IONOS: benötigt includes/rate_limit.php sowie ein
 * beschreibbares storage/-Verzeichnis neben api/ (wird bei Bedarf angelegt).
 */
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

// ── CORS: nur echte CVS-Origins ───────────────────────────────────────────────
// Das Formular läuft same-origin auf IONOS (kontakt.html) und auf der
// Render-App. Requests ohne Origin-Header (Same-Origin ohne CORS-Preflight,
// Server-zu-Server) bleiben zulässig, erhalten aber keine CORS-Freigabe.
const CSF_CONTACT_ALLOWED_ORIGINS = [
    'https://cinematic-vision-studio.de',
    'https://www.cinematic-vision-studio.de',
    'https://cinematic-studio-family.onrender.com',
];

$origin = (string) ($_SERVER['HTTP_ORIGIN'] ?? '');
if ($origin !== '') {
    if (!in_array($origin, CSF_CONTACT_ALLOWED_ORIGINS, true)) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'Origin nicht erlaubt.']);
        exit;
    }
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Vary: Origin');
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    if ($origin !== '') {
        header('Access-Control-Allow-Methods: POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
        header('Access-Control-Max-Age: 86400');
    }
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Nur POST erlaubt.']);
    exit;
}

// mbstring-Polyfill für IONOS
if (!function_exists('mb_substr')) {
    function mb_substr(string $str, int $start, ?int $length = null): string {
        return $length === null ? substr($str, $start) : substr($str, $start, $length);
    }
}
if (!function_exists('mb_strlen')) {
    function mb_strlen(string $str): int {
        return strlen($str);
    }
}

// ── Rate-Limit: 5 Anfragen pro Stunde und IP (fail-closed) ───────────────────
// rate_limit.php blockt selbst, wenn das Counter-Verzeichnis nicht beschreibbar
// ist oder der Lock fehlschlägt (allowed=false) — kein Fail-Open möglich.
if (!defined('CSF_STORAGE_ROOT')) {
    define('CSF_STORAGE_ROOT', realpath(__DIR__ . '/../storage') ?: __DIR__ . '/../storage');
}
require_once __DIR__ . '/../includes/rate_limit.php';

$rl = csf_rate_limit_check('contact_submit', maxRequests: 5, windowSeconds: 3600);
if (!$rl['allowed']) {
    http_response_code(429);
    header('Retry-After: ' . $rl['reset_in']);
    echo json_encode(['ok' => false, 'error' => 'Zu viele Anfragen. Bitte versuche es später erneut.']);
    exit;
}

// Probabilistisches Aufräumen abgelaufener Counter (1/50)
if (random_int(1, 50) === 1) {
    csf_rate_limit_cleanup();
}

// ── Body lesen (Größenlimit 32 KB) ───────────────────────────────────────────
$raw = (string) file_get_contents('php://input');
if (strlen($raw) > 32768) {
    http_response_code(413);
    echo json_encode(['ok' => false, 'error' => 'Anfrage zu groß.']);
    exit;
}

$input = json_decode($raw, true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Ungültige Daten.']);
    exit;
}

// ── Honeypot: unsichtbares Feld "website" füllen nur Bots ────────────────────
// Neutral wie ein Erfolg antworten, damit Bots keinen Unterschied sehen —
// aber keine Mail versenden.
if (trim((string) ($input['website'] ?? '')) !== '') {
    echo json_encode(['ok' => true, 'message' => 'Brief gesendet! Wir melden uns innerhalb von 24 Stunden.']);
    exit;
}

$name         = mb_substr(trim(strip_tags((string)($input['name']          ?? ''))), 0, 100);
$emailIn      = trim((string)($input['email']         ?? ''));
$projectType  = mb_substr(trim(strip_tags((string)($input['project_type']  ?? ''))), 0, 50);
$platform     = mb_substr(trim(strip_tags((string)($input['platform']      ?? ''))), 0, 50);
$length       = mb_substr(trim(strip_tags((string)($input['length']        ?? ''))), 0, 20);
$style        = mb_substr(trim(strip_tags((string)($input['style']         ?? ''))), 0, 50);
$message      = mb_substr(trim(strip_tags((string)($input['message']       ?? ''))), 0, 3000);
$audience     = mb_substr(trim(strip_tags((string)($input['audience']      ?? ''))), 0, 300);
$music        = mb_substr(trim(strip_tags((string)($input['music']         ?? ''))), 0, 200);
$references   = mb_substr(trim(strip_tags((string)($input['references']    ?? ''))), 0, 1000);
$hasFiles     = !empty($input['has_files']);

// E-Mail: Längenlimit (RFC 5321) + Formatprüfung — filter_var blockt CR/LF
// und damit Header-Injection über Reply-To.
if (mb_strlen($emailIn) > 254) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'E-Mail-Adresse ist zu lang.']);
    exit;
}

if ($name === '' || !filter_var($emailIn, FILTER_VALIDATE_EMAIL) || $projectType === '' || mb_strlen($message) < 10) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'Bitte fülle alle Pflichtfelder aus (Name, E-Mail, Projekt-Typ, Kernbotschaft mind. 10 Zeichen).']);
    exit;
}

$to      = 'info@cinematic-vision-studio.de';
$subj    = '=?UTF-8?B?' . base64_encode('Projektbriefing: ' . ($projectType !== '' ? ucfirst($projectType) : 'Neues Projekt')) . '?=';

$bodyLines = [
    "Name: {$name}",
    "E-Mail: {$emailIn}",
    "",
    "Projekt-Typ: " . ($projectType ?: '–'),
    "Plattform: " . ($platform ?: '–'),
    "Länge: " . ($length ?: '–') . " Sek.",
    "Stil: " . ($style ?: '–'),
    "",
    "Kernbotschaft:",
    $message,
    "",
    "Zielgruppe: " . ($audience ?: '–'),
    "Musik-Vorliebe: " . ($music ?: '–'),
    "",
    "Referenzen:",
    $references ?: '–',
    "",
    "Dateien angehängt: " . ($hasFiles ? 'Ja' : 'Nein'),
];
$body    = implode("\r\n", $bodyLines);
$headers = implode("\r\n", [
    'From: noreply@cinematic-vision-studio.de',
    'Reply-To: ' . $emailIn,
    'Content-Type: text/plain; charset=UTF-8',
    'MIME-Version: 1.0',
    'X-Mailer: CinematicStudio/1.0',
]);

if (@mail($to, $subj, $body, $headers)) {
    echo json_encode(['ok' => true, 'message' => 'Brief gesendet! Wir melden uns innerhalb von 24 Stunden.']);
} else {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'E-Mail konnte nicht gesendet werden. Bitte schreib uns direkt: info@cinematic-vision-studio.de']);
}
