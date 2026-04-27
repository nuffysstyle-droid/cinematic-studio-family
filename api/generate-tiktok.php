<?php
/**
 * api/generate-tiktok.php — TikTok Prompt Generator
 * Nutzt bestehende Prompt Engine (buildVideoPrompt + Modifier).
 * Kein externer API-Call — nur PHP String-Builder-Logik.
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/prompt-engine.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Nur POST erlaubt.']);
    exit;
}

// ── Input lesen ───────────────────────────────────────────────────
$raw  = file_get_contents('php://input');
$body = json_decode($raw, true) ?? [];

$input    = sanitizePromptInput($body['input']    ?? '');
$template = trim($body['template'] ?? 'viral_hook');
$style    = trim($body['style']    ?? 'cinematic');
$cta      = trim($body['cta']      ?? 'none');
$action   = trim($body['action']   ?? 'build');

// ── TikTok-Template → Video-Template mapping ──────────────────────
const TIKTOK_TEMPLATE_MAP = [
    'viral_hook'    => 'tiktok_hook',
    'tiktok_ad'     => 'tiktok_hook',
    'tiktok_shop'   => 'product_ad',
    'creator_intro' => 'tiktok_hook',
    'product_demo'  => 'product_ad',
    'story_clip'    => 'cinematic_scene',
];

$videoTemplate = TIKTOK_TEMPLATE_MAP[$template] ?? 'tiktok_hook';

// ── Stil-Modifier → Prompt-Zusatz ─────────────────────────────────
const STYLE_MODIFIERS = [
    'cinematic' => 'cinematic color grade, anamorphic film look, dramatic lighting',
    'energy'    => 'high energy, vibrant colors, dynamic fast motion, punchy',
    'dark'      => 'dark moody atmosphere, dramatic shadows, high contrast, neon accents',
    'luxury'    => 'luxury aesthetic, premium quality, elegant slow motion, gold tones',
    'emotional' => 'emotional storytelling, warm golden tones, intimate close-ups, soft light',
    'funny'     => 'playful energy, bright cheerful colors, fun dynamic movement, upbeat',
];

$styleMod = STYLE_MODIFIERS[$style] ?? STYLE_MODIFIERS['cinematic'];

// ── Hook-Vorschläge nach Template ─────────────────────────────────
const HOOK_SUGGESTIONS = [
    'viral_hook'    => [
        'POV: du siehst das zum ersten Mal …',
        'Niemand spricht über diesen Trick …',
        'Warte bis du das gesehen hast …',
        'Das verändert alles, was du dachtest zu wissen …',
    ],
    'tiktok_ad'     => [
        'Das hat mein Leben verändert — kein Witz.',
        'Stop scrollen. Das musst du sehen.',
        'So sieht Qualität aus. Punkt.',
        'Ich wünschte, ich hätte das früher gewusst.',
    ],
    'tiktok_shop'   => [
        'Das Produkt kauft gerade jeder — und das ist der Grund.',
        'Ich hab's getestet. Hier ist das Ergebnis.',
        'Dieses Ding ist überall ausverkauft — zum Glück nicht hier.',
        'Einmal bestellt, nie mehr ohne.',
    ],
    'creator_intro' => [
        'Das ist dein Zeichen, mir zu folgen.',
        'Ich zeige dir, was die meisten nie verstehen werden.',
        'Hey — du bist nicht zufällig hier.',
        'Wer bin ich? Das hier sagt alles.',
    ],
    'product_demo'  => [
        'Schau, was das in 5 Sekunden macht.',
        'Vorher vs. Nachher — du wirst es nicht glauben.',
        'So einfach. So effektiv. So krass.',
        'Das hätte ich früher gebraucht.',
    ],
    'story_clip'    => [
        'Die Geschichte, die niemand kannte — bis jetzt.',
        'Das passierte wirklich. Keine Bearbeitung.',
        'Ein Moment, der alles verändert hat.',
        'Manchmal reicht eine Szene, um alles zu sagen.',
    ],
];

// Hook auswählen (basierend auf input-Hash für Konsistenz)
$hookPool = HOOK_SUGGESTIONS[$template] ?? HOOK_SUGGESTIONS['viral_hook'];
$hookIdx  = $input !== '' ? (crc32($input) % count($hookPool)) : 0;
$hookIdx  = abs($hookIdx);
$hook     = $hookPool[$hookIdx];

// ── CTA-Vorschlag ─────────────────────────────────────────────────
const CTA_SUGGESTIONS = [
    'follow'    => '👆 Follow für mehr Content wie diesen!',
    'buy'       => '🛒 Jetzt kaufen — Link in Bio!',
    'comment'   => '💬 Kommentiere: Würdest du das ausprobieren? 👇',
    'share'     => '📤 Teile das mit jemandem, der das sehen muss!',
    'none'      => '',
];

$ctaSuggestion = CTA_SUGGESTIONS[$cta] ?? '';

// ── Prompt aufbauen ───────────────────────────────────────────────
function buildTikTokPrompt(string $input, string $videoTemplate, string $styleMod): array {
    $options = [
        'model'    => 'seedance_fast',
        'duration' => 8,
        'quality'  => 'normal',
        'mode'     => 'text',
    ];

    $result   = buildVideoPrompt($input, $videoTemplate, $options);
    $positive = $result['positive'];
    $negative = $result['negative'];

    // Stil-Modifier einbauen (Duplikat-Check via _appendToPrompt-Logik)
    if (stripos($positive, $styleMod) === false) {
        $positive .= ' | STYLE MODIFIER: ' . $styleMod;
    }

    // TikTok-spezifische Pflicht-Elemente
    $tiktokMust = 'vertical 9:16 format, mobile-first framing, bold visual hook in first 2 seconds';
    if (stripos($positive, '9:16') === false) {
        $positive .= ' | FORMAT: ' . $tiktokMust;
    }

    return ['positive' => $positive, 'negative' => $negative];
}

// ── Actions ───────────────────────────────────────────────────────
switch ($action) {

    case 'build':
        if ($input === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Beschreibung darf nicht leer sein.']);
            exit;
        }
        $result   = buildTikTokPrompt($input, $videoTemplate, $styleMod);
        $positive = $result['positive'];
        $negative = $result['negative'];
        break;

    case 'improve':
        if ($input === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Kein bestehender Prompt übergeben.']);
            exit;
        }
        $positive = improvePrompt($input);
        $negative = buildTikTokPrompt($input, $videoTemplate, $styleMod)['negative'];
        // Neuen Hook für verbesserten Prompt
        $hookPool = HOOK_SUGGESTIONS[$template] ?? HOOK_SUGGESTIONS['viral_hook'];
        $hook     = $hookPool[(abs(crc32($input . 'improve')) % count($hookPool))];
        break;

    case 'cinematic':
        if ($input === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Kein bestehender Prompt übergeben.']);
            exit;
        }
        $positive = cinematicUpgradePrompt($input);
        $negative = buildTikTokPrompt($input, $videoTemplate, $styleMod)['negative'];
        break;

    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => "Unbekannte action: \"{$action}\"."]);
        exit;
}

// ── Response ──────────────────────────────────────────────────────
echo json_encode([
    'success'  => true,
    'positive' => $positive,
    'negative' => $negative,
    'hook'     => $hook,
    'cta'      => $ctaSuggestion,
    'template' => $template,
    'style'    => $style,
    'action'   => $action,
], JSON_UNESCAPED_UNICODE);
