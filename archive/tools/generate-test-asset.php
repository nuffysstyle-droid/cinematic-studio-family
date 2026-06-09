<?php
declare(strict_types=1);

/**
 * bin/generate-test-asset.php — End-to-End Test für Tokyo Dusk Asset
 *
 * Workflow:
 *   1. KIE_AI_API_KEY prüfen
 *   2. Kie.ai Generate API aufrufen
 *   3. Polling bis Status = SUCCESS
 *   4. Bild herunterladen
 *   5. Komprimierung / Resize auf 640×360
 *   6. Speichern in assets/showcase/portfolio/golden-social-v1.jpg
 *   7. Metriken ausgeben
 *
 * Usage:
 *   php bin/generate-test-asset.php
 */

// ── Konfiguration ─────────────────────────────────────────────────────────────

$PROMPT     = 'Tokyo cityscape rooftop view at golden hour, warm amber sunlight reflecting on glass buildings, cinematic color grading, subtle lens flare, urban photography style, shallow depth of field';
$MODEL      = 'flux-kontext-pro';
$ASPECT     = '16:9';
$OUT_FORMAT = 'jpeg';

$TARGET_DIR  = __DIR__ . '/../assets/showcase/portfolio';
$TARGET_FILE = $TARGET_DIR . '/golden-social-v1.jpg';
$TARGET_W    = 640;
$TARGET_H    = 360;
$TARGET_Q    = 85;

$MAX_POLL_SECONDS = 120;
$POLL_INTERVAL    = 5;

// ── API-Key ───────────────────────────────────────────────────────────────────

$apiKey = (string) (getenv('KIE_AI_API_KEY') ?: ($_SERVER['KIE_AI_API_KEY'] ?? $_ENV['KIE_AI_API_KEY'] ?? ''));

if ($apiKey === '') {
    fwrite(STDERR, "[ERROR] KIE_AI_API_KEY ist nicht gesetzt.\n");
    fwrite(STDERR, "        Bitte setze die Umgebungsvariable und versuche es erneut.\n");
    exit(1);
}

echo "[OK] KIE_AI_API_KEY vorhanden (Länge: " . strlen($apiKey) . ")\n";

// ── Hilfsfunktionen ───────────────────────────────────────────────────────────

function kiePost(string $url, array $payload, string $apiKey): array {
    $jsonBody = json_encode($payload, JSON_UNESCAPED_UNICODE);
    $headerLines = [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json',
        'Accept: application/json',
        'Content-Length: ' . strlen($jsonBody),
    ];
    $opts = [
        'http' => [
            'method'        => 'POST',
            'header'        => implode("\r\n", $headerLines),
            'content'       => $jsonBody,
            'timeout'       => 20,
            'ignore_errors' => true,
        ],
    ];
    $ctx  = stream_context_create($opts);
    $body = @file_get_contents($url, false, $ctx);
    $status = 0;
    if (!empty($http_response_header)) {
        preg_match('/\s(\d{3})\s/', $http_response_header[0], $m);
        $status = isset($m[1]) ? (int) $m[1] : 0;
    }
    return [
        'status' => $status,
        'body'   => $body,
        'data'   => json_decode($body ?: '{}', true) ?? [],
    ];
}

function kieGet(string $url, string $apiKey): array {
    $opts = [
        'http' => [
            'method'        => 'GET',
            'header'        => 'Authorization: Bearer ' . $apiKey . "\r\n" . 'Accept: application/json',
            'timeout'       => 20,
            'ignore_errors' => true,
        ],
    ];
    $ctx  = stream_context_create($opts);
    $body = @file_get_contents($url, false, $ctx);
    $status = 0;
    if (!empty($http_response_header)) {
        preg_match('/\s(\d{3})\s/', $http_response_header[0], $m);
        $status = isset($m[1]) ? (int) $m[1] : 0;
    }
    return [
        'status' => $status,
        'body'   => $body,
        'data'   => json_decode($body ?: '{}', true) ?? [],
    ];
}

function formatBytes(int $bytes): string {
    if ($bytes >= 1024 * 1024) return round($bytes / (1024 * 1024), 2) . ' MB';
    if ($bytes >= 1024) return round($bytes / 1024, 2) . ' KB';
    return $bytes . ' B';
}

// ── Schritt 1: Task starten ───────────────────────────────────────────────────

echo "[1/6] Starte Kie.ai Generierung...\n";
echo "        Prompt: " . substr($PROMPT, 0, 60) . "...\n";
echo "        Modell:  " . $MODEL . "\n";

$startTime = microtime(true);

$generateResult = kiePost(
    'https://api.kie.ai/api/v1/flux/kontext/generate',
    [
        'prompt'          => $PROMPT,
        'model'           => $MODEL,
        'aspectRatio'     => $ASPECT,
        'outputFormat'    => $OUT_FORMAT,
        'safetyTolerance' => 2,
    ],
    $apiKey
);

if ($generateResult['status'] !== 200) {
    fwrite(STDERR, "[ERROR] Kie.ai Generate fehlgeschlagen. HTTP " . $generateResult['status'] . "\n");
    fwrite(STDERR, "        Response: " . $generateResult['body'] . "\n");
    exit(1);
}

$taskId = $generateResult['data']['data']['taskId'] ?? null;
if (!is_string($taskId) || $taskId === '') {
    fwrite(STDERR, "[ERROR] Keine taskId in der Antwort.\n");
    fwrite(STDERR, "        Response: " . $generateResult['body'] . "\n");
    exit(1);
}

echo "[OK] Task gestartet. taskId: " . substr($taskId, 0, 16) . "...\n";

// ── Schritt 2: Polling ────────────────────────────────────────────────────────

echo "[2/6] Warte auf Generierung (max. " . $MAX_POLL_SECONDS . "s)...\n";

$successFlag = null;
$pollElapsed = 0;

while ($pollElapsed < $MAX_POLL_SECONDS) {
    sleep($POLL_INTERVAL);
    $pollElapsed += $POLL_INTERVAL;

    $pollResult = kieGet(
        'https://api.kie.ai/api/v1/flux/kontext/record-info?taskId=' . urlencode($taskId),
        $apiKey
    );

    if ($pollResult['status'] !== 200) {
        echo "        Polling HTTP " . $pollResult['status'] . " — weiter...\n";
        continue;
    }

    $successFlag = $pollResult['data']['data']['successFlag'] ?? null;

    if ($successFlag === 0 || $successFlag === null) {
        echo "        Status: GENERATING (" . $pollElapsed . "s)...\n";
        continue;
    }

    if ($successFlag === 1) {
        echo "[OK] Generierung abgeschlossen nach " . $pollElapsed . "s.\n";
        break;
    }

    if ($successFlag === 2 || $successFlag === 3) {
        fwrite(STDERR, "[ERROR] Generierung fehlgeschlagen (flag=" . $successFlag . ").\n");
        exit(1);
    }
}

if ($successFlag !== 1) {
    fwrite(STDERR, "[ERROR] Timeout nach " . $MAX_POLL_SECONDS . "s. Generierung nicht abgeschlossen.\n");
    exit(1);
}

// ── Schritt 3: Bild herunterladen ─────────────────────────────────────────────

echo "[3/6] Lade Bild herunter...\n";

$resultImageUrl = $pollResult['data']['data']['response']['resultImageUrl'] ?? null;
if (!is_string($resultImageUrl) || $resultImageUrl === '') {
    fwrite(STDERR, "[ERROR] Keine resultImageUrl in der Antwort.\n");
    exit(1);
}

$imgCtx = stream_context_create([
    'http' => [
        'method'          => 'GET',
        'timeout'         => 25,
        'ignore_errors'   => false,
        'follow_location' => 1,
    ],
]);

$imgData = @file_get_contents($resultImageUrl, false, $imgCtx);

if ($imgData === false || strlen($imgData) < 100) {
    fwrite(STDERR, "[ERROR] Bild-Download fehlgeschlagen.\n");
    exit(1);
}

$rawSize   = strlen($imgData);
$rawSizeMB = round($rawSize / (1024 * 1024), 2);

echo "[OK] Bild heruntergeladen. Rohgröße: " . formatBytes($rawSize) . "\n";

// ── Schritt 4: Komprimierung + Resize ─────────────────────────────────────────

echo "[4/6] Komprimiere und skaliere auf " . $TARGET_W . "×" . $TARGET_H . "...\n";

if (!is_dir($TARGET_DIR)) {
    @mkdir($TARGET_DIR, 0775, true);
}

$tempFile = sys_get_temp_dir() . '/kie_raw_' . uniqid() . '.jpg';
file_put_contents($tempFile, $imgData);

// Auflösung des Rohbilds ermitteln
$rawInfo = @getimagesize($tempFile);
$rawW    = $rawInfo[0] ?? 0;
$rawH    = $rawInfo[1] ?? 0;

if ($rawW === 0 || $rawH === 0) {
    fwrite(STDERR, "[ERROR] Konnte Rohbild nicht lesen.\n");
    @unlink($tempFile);
    exit(1);
}

echo "        Rohauflösung: " . $rawW . "×" . $rawH . "\n";

// GD-basierte Resize + Komprimierung
$src = @imagecreatefromstring($imgData);
if ($src === false) {
    fwrite(STDERR, "[ERROR] GD konnte Bild nicht dekodieren.\n");
    @unlink($tempFile);
    exit(1);
}

$dst = @imagecreatetruecolor($TARGET_W, $TARGET_H);
if ($dst === false) {
    fwrite(STDERR, "[ERROR] GD konnte Zielbild nicht erstellen.\n");
    imagedestroy($src);
    @unlink($tempFile);
    exit(1);
}

imagecopyresampled($dst, $src, 0, 0, 0, 0, $TARGET_W, $TARGET_H, $rawW, $rawH);

$saveOk = @imagejpeg($dst, $TARGET_FILE, $TARGET_Q);

imagedestroy($src);
imagedestroy($dst);
@unlink($tempFile);

if (!$saveOk) {
    fwrite(STDERR, "[ERROR] Konnte komprimiertes Bild nicht speichern.\n");
    exit(1);
}

$finalSize   = filesize($TARGET_FILE);
$finalSizeKB = round($finalSize / 1024, 1);

echo "[OK] Bild gespeichert: " . $TARGET_FILE . "\n";
echo "        Finale Größe: " . formatBytes($finalSize) . "\n";
echo "        Finale Auflösung: " . $TARGET_W . "×" . $TARGET_H . " (Qualität " . $TARGET_Q . ")\n";

// ── Schritt 5: Verifizierung ──────────────────────────────────────────────────

echo "[5/6] Verifiziere gespeicherte Datei...\n";

$verifyInfo = @getimagesize($TARGET_FILE);
$verifyW    = $verifyInfo[0] ?? 0;
$verifyH    = $verifyInfo[1] ?? 0;
$verifyMime = $verifyInfo['mime'] ?? 'unknown';

if ($verifyW !== $TARGET_W || $verifyH !== $TARGET_H) {
    fwrite(STDERR, "[ERROR] Verifizierung fehlgeschlagen: " . $verifyW . "×" . $verifyH . " statt " . $TARGET_W . "×" . $TARGET_H . "\n");
    exit(1);
}

if ($verifyMime !== 'image/jpeg') {
    fwrite(STDERR, "[ERROR] Verifizierung fehlgeschlagen: MIME-Typ ist " . $verifyMime . "\n");
    exit(1);
}

echo "[OK] Verifizierung bestanden.\n";
echo "        MIME: " . $verifyMime . "\n";
echo "        Abmessungen: " . $verifyW . "×" . $verifyH . "\n";

// ── Schritt 6: Zusammenfassung ────────────────────────────────────────────────

$totalTime = round(microtime(true) - $startTime, 1);

echo "\n═══════════════════════════════════════════════════════\n";
echo "  TOKYO DUSK — END-TO-END TEST ERGEBNIS\n";
echo "═══════════════════════════════════════════════════════\n";
echo "  Rohgröße:         " . formatBytes($rawSize) . " (" . $rawW . "×" . $rawH . ")\n";
echo "  Finale Größe:     " . formatBytes($finalSize) . " (" . $finalSizeKB . " KB)\n";
echo "  Finale Auflösung: " . $TARGET_W . "×" . $TARGET_H . " px\n";
echo "  Gesamtzeit:       " . $totalTime . " s\n";
echo "  Zieldatei:        " . $TARGET_FILE . "\n";
echo "  Status:           ✅ ERFOLGREICH\n";
echo "═══════════════════════════════════════════════════════\n";

exit(0);
