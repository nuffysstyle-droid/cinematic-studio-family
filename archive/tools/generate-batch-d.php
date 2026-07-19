<?php
declare(strict_types=1);
// Nur ueber die Kommandozeile ausfuehrbar.
// Ueber den Webserver erreichbar waere dieses Skript ein
// unauthentifizierter Ausloeser fuer kostenpflichtige Kie.ai-Generierungen:
// der Server-API-Key steht auf Render in der Umgebung, ein einzelner
// anonymer HTTP-Aufruf wuerde die komplette Batch-Schleife starten.
if (PHP_SAPI !== "cli") {
    http_response_code(404);
    exit;
}


/**
 * bin/generate-batch-d.php — Batch D: KI-Videos Thumbnails #1–#3
 *
 * Assets:
 *   #1 Viral Hook Clip    → viral-hook-v1.jpg (480x270)
 *   #2 Product Spotlight  → product-spotlight-v1.jpg (480x270)
 *   #3 Cinematic Track    → cinematic-track-v1.jpg (480x270)
 *
 * Usage:
 *   php bin/generate-batch-d.php
 */

$ASSETS = [
    [
        'name'     => 'Viral Hook Clip',
        'filename' => 'viral-hook-v1.jpg',
        'prompt'   => 'Young dancer in neon-lit urban alley, TikTok style vertical video frame, energetic pose, pink and blue backlight, viral energy, cinematic motion blur, 16:9 composition',
    ],
    [
        'name'     => 'Product Spotlight',
        'filename' => 'product-spotlight-v1.jpg',
        'prompt'   => 'Sleek product on rotating pedestal with dramatic spotlight, dark studio background, commercial photography look, premium feel, 16:9 composition',
    ],
    [
        'name'     => 'Cinematic Track',
        'filename' => 'cinematic-track-v1.jpg',
        'prompt'   => 'Musician silhouette against massive LED wall with abstract visuals, concert atmosphere, dramatic fog and stage lights, music video aesthetic, 16:9 composition',
    ],
];

$MODEL      = 'flux-kontext-pro';
$ASPECT     = '16:9';
$OUT_FORMAT = 'jpeg';
$TARGET_W   = 480;
$TARGET_H   = 270;
$TARGET_Q   = 85;
$TARGET_DIR = __DIR__ . '/../assets/showcase/thumbnails';
$MAX_POLL   = 120;
$POLL_INT   = 5;

$apiKey = (string) (getenv('KIE_AI_API_KEY') ?: ($_SERVER['KIE_AI_API_KEY'] ?? $_ENV['KIE_AI_API_KEY'] ?? ''));
if ($apiKey === '') {
    fwrite(STDERR, "[ERROR] KIE_AI_API_KEY ist nicht gesetzt.\n");
    exit(1);
}

function kiePost(string $url, array $payload, string $apiKey): array {
    $jsonBody = json_encode($payload, JSON_UNESCAPED_UNICODE);
    $opts = [
        'http' => [
            'method'        => 'POST',
            'header'        => "Authorization: Bearer {$apiKey}\r\nContent-Type: application/json\r\nAccept: application/json\r\nContent-Length: " . strlen($jsonBody),
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
    return ['status' => $status, 'body' => $body, 'data' => json_decode($body ?: '{}', true) ?? []];
}

function kieGet(string $url, string $apiKey): array {
    $opts = [
        'http' => [
            'method'        => 'GET',
            'header'        => "Authorization: Bearer {$apiKey}\r\nAccept: application/json",
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
    return ['status' => $status, 'body' => $body, 'data' => json_decode($body ?: '{}', true) ?? []];
}

function formatBytes(int $bytes): string {
    if ($bytes >= 1024 * 1024) return round($bytes / (1024 * 1024), 2) . ' MB';
    if ($bytes >= 1024) return round($bytes / 1024, 2) . ' KB';
    return $bytes . ' B';
}

$results = [];

foreach ($ASSETS as $idx => $asset) {
    echo "\n═══════════════════════════════════════════════════════\n";
    echo "  BATCH D — " . ($idx + 1) . "/3: " . $asset['name'] . "\n";
    echo "═══════════════════════════════════════════════════════\n";

    $startTime = microtime(true);

    echo "[1/4] Generiere...\n";
    $gen = kiePost('https://api.kie.ai/api/v1/flux/kontext/generate', [
        'prompt'          => $asset['prompt'],
        'model'           => $MODEL,
        'aspectRatio'     => $ASPECT,
        'outputFormat'    => $OUT_FORMAT,
        'safetyTolerance' => 2,
    ], $apiKey);

    if ($gen['status'] !== 200) {
        echo "[ERROR] Generate fehlgeschlagen: HTTP " . $gen['status'] . "\n";
        $results[] = ['name' => $asset['name'], 'status' => 'FAILED', 'error' => 'HTTP ' . $gen['status']];
        continue;
    }

    $taskId = $gen['data']['data']['taskId'] ?? null;
    if (!$taskId) {
        echo "[ERROR] Keine taskId\n";
        $results[] = ['name' => $asset['name'], 'status' => 'FAILED', 'error' => 'No taskId'];
        continue;
    }

    echo "[2/4] Warte auf Generierung...\n";
    $successFlag = null;
    $elapsed = 0;
    while ($elapsed < $MAX_POLL) {
        sleep($POLL_INT);
        $elapsed += $POLL_INT;
        $poll = kieGet('https://api.kie.ai/api/v1/flux/kontext/record-info?taskId=' . urlencode($taskId), $apiKey);
        $successFlag = $poll['data']['data']['successFlag'] ?? null;
        if ($successFlag === 1) {
            echo "        Fertig nach " . $elapsed . "s\n";
            break;
        }
        if ($successFlag === 2 || $successFlag === 3) {
            echo "[ERROR] Generierung fehlgeschlagen (flag=" . $successFlag . ")\n";
            break;
        }
    }

    if ($successFlag !== 1) {
        $results[] = ['name' => $asset['name'], 'status' => 'FAILED', 'error' => 'Polling failed'];
        continue;
    }

    echo "[3/4] Lade herunter...\n";
    $imgUrl = $poll['data']['data']['response']['resultImageUrl'] ?? null;
    if (!$imgUrl) {
        echo "[ERROR] Keine Bild-URL\n";
        $results[] = ['name' => $asset['name'], 'status' => 'FAILED', 'error' => 'No URL'];
        continue;
    }

    $imgData = @file_get_contents($imgUrl, false, stream_context_create([
        'http' => ['method' => 'GET', 'timeout' => 25, 'follow_location' => 1],
    ]));
    if ($imgData === false || strlen($imgData) < 100) {
        echo "[ERROR] Download fehlgeschlagen\n";
        $results[] = ['name' => $asset['name'], 'status' => 'FAILED', 'error' => 'Download failed'];
        continue;
    }

    $rawSize = strlen($imgData);
    $rawInfo = @getimagesizefromstring($imgData);
    $rawW = $rawInfo[0] ?? 0;
    $rawH = $rawInfo[1] ?? 0;

    echo "[4/4] Komprimiere " . $rawW . "x" . $rawH . " → " . $TARGET_W . "x" . $TARGET_H . "...\n";
    $src = @imagecreatefromstring($imgData);
    if ($src === false) {
        echo "[ERROR] GD Dekodierung fehlgeschlagen\n";
        $results[] = ['name' => $asset['name'], 'status' => 'FAILED', 'error' => 'GD decode'];
        continue;
    }

    $dst = imagecreatetruecolor($TARGET_W, $TARGET_H);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $TARGET_W, $TARGET_H, $rawW, $rawH);

    $targetFile = $TARGET_DIR . '/' . $asset['filename'];
    imagejpeg($dst, $targetFile, $TARGET_Q);
    imagedestroy($src);
    imagedestroy($dst);

    $finalSize = filesize($targetFile);
    $totalTime = round(microtime(true) - $startTime, 1);

    echo "[OK] Gespeichert: " . $asset['filename'] . "\n";
    echo "        Roh: " . formatBytes($rawSize) . " (" . $rawW . "x" . $rawH . ")\n";
    echo "        Final: " . formatBytes($finalSize) . " (" . $TARGET_W . "x" . $TARGET_H . ")\n";
    echo "        Zeit: " . $totalTime . "s\n";

    $results[] = [
        'name'      => $asset['name'],
        'status'    => 'OK',
        'filename'  => $asset['filename'],
        'rawSize'   => $rawSize,
        'finalSize' => $finalSize,
        'rawW'      => $rawW,
        'rawH'      => $rawH,
        'time'      => $totalTime,
    ];
}

echo "\n═══════════════════════════════════════════════════════\n";
echo "  BATCH D — GESAMTBERICHT\n";
echo "═══════════════════════════════════════════════════════\n";

$okCount = 0;
$failCount = 0;
foreach ($results as $r) {
    $icon = $r['status'] === 'OK' ? '✅' : '❌';
    echo "\n" . $icon . " " . $r['name'] . "\n";
    if ($r['status'] === 'OK') {
        echo "   Datei:   " . $r['filename'] . "\n";
        echo "   Roh:     " . formatBytes($r['rawSize']) . " (" . $r['rawW'] . "x" . $r['rawH'] . ")\n";
        echo "   Final:   " . formatBytes($r['finalSize']) . " (" . $TARGET_W . "x" . $TARGET_H . ")\n";
        echo "   Zeit:    " . $r['time'] . "s\n";
        $okCount++;
    } else {
        echo "   Fehler:  " . ($r['error'] ?? 'Unknown') . "\n";
        $failCount++;
    }
}

echo "\n═══════════════════════════════════════════════════════\n";
echo "  Erfolgreich: " . $okCount . "/" . count($results) . "\n";
echo "  Fehlgeschlagen: " . $failCount . "/" . count($results) . "\n";
echo "═══════════════════════════════════════════════════════\n";
