<?php
/**
 * api/generate-trailer.php — Cinematic Trailer Prompt Builder
 * Erzeugt Timeline-Struktur + Szenen-Prompts + Gesamt-Prompt.
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

// ── Input ─────────────────────────────────────────────────────────
$raw  = file_get_contents('php://input');
$body = json_decode($raw, true) ?? [];

function trInput(string $key, string $default = ''): string {
    global $body;
    $val = $body[$key] ?? $default;
    return is_string($val) ? trim(strip_tags($val)) : $default;
}

$input       = sanitizePromptInput(trInput('input'));
$template    = trInput('template',    'blockbuster');
$musicStyle  = trInput('music_style', 'epic');
$pacing      = trInput('pacing',      'trailer');
$duration    = (int) trInput('duration', '15');
$action      = trInput('action',      'build');

// ── Validierung ───────────────────────────────────────────────────
const VALID_TR_TEMPLATES = ['blockbuster', 'action', 'horror', 'drama', 'documentary', 'tiktok_trailer'];
const VALID_TR_MUSIC     = ['epic', 'dark', 'emotional', 'hybrid', 'fast_cuts', 'slow_build'];
const VALID_TR_PACING    = ['slow', 'medium', 'fast', 'trailer'];
const VALID_TR_DURATIONS = [10, 15, 30];

if (!in_array($template,   VALID_TR_TEMPLATES, true)) $template   = 'blockbuster';
if (!in_array($musicStyle, VALID_TR_MUSIC,     true)) $musicStyle = 'epic';
if (!in_array($pacing,     VALID_TR_PACING,    true)) $pacing     = 'trailer';
if (!in_array($duration,   VALID_TR_DURATIONS, true)) $duration   = 15;

// ── Template → VIDEO_TEMPLATE Mapping ────────────────────────────
const TRAILER_TEMPLATE_MAP = [
    'blockbuster'    => 'blockbuster',
    'action'         => 'action_trailer',
    'horror'         => 'horror_creature',
    'drama'          => 'cinematic_scene',
    'documentary'    => 'cinematic_scene',
    'tiktok_trailer' => 'tiktok_hook',
];

// ── Musik-Stil → Prompt-Modifier ─────────────────────────────────
const MUSIC_MODIFIERS = [
    'epic'       => 'epic orchestral score, Hans Zimmer style, massive build',
    'dark'       => 'dark brooding score, minor keys, tension building, ominous',
    'emotional'  => 'emotional piano and strings, intimate, powerful crescendo',
    'hybrid'     => 'hybrid trailer score, electronic + orchestral, modern cinematic',
    'fast_cuts'  => 'fast-paced electronic beats, synced cuts, high BPM energy',
    'slow_build' => 'slow atmospheric build, silence before impact, dramatic swell',
];

// ── Schnitt-Rhythmus → Pacing-Descriptor ─────────────────────────
const PACING_DESCRIPTORS = [
    'slow'    => 'slow deliberate cuts, long takes, atmospheric buildup',
    'medium'  => 'balanced pacing, steady rhythm, building momentum',
    'fast'    => 'rapid cuts, kinetic energy, quick intercutting',
    'trailer' => 'professional trailer pacing: slow open, accelerating middle, rapid finale',
];

// ── Timeline-Blueprints je Dauer ─────────────────────────────────
function buildTimeline(string $input, string $template, int $duration): array {
    $timelines = [
        10 => [
            ['ts' => '0s',  'act' => 'Hook',       'desc' => 'Visueller Hook — sofortige Aufmerksamkeit'],
            ['ts' => '3s',  'act' => 'Aufbau',     'desc' => 'Welt etablieren, Spannung aufbauen'],
            ['ts' => '6s',  'act' => 'Eskalation', 'desc' => 'Konflikt oder Action entfaltet sich'],
            ['ts' => '8s',  'act' => 'Finale',     'desc' => 'Höhepunkt — stärkste Szene'],
            ['ts' => '9s',  'act' => 'Cut',         'desc' => 'Cut to black / Titelkarte'],
        ],
        15 => [
            ['ts' => '0s',  'act' => 'Hook',        'desc' => 'Starker visueller Einstieg — packt sofort'],
            ['ts' => '3s',  'act' => 'Movement',    'desc' => 'Erste Bewegung, Welt öffnet sich'],
            ['ts' => '6s',  'act' => 'Impact',      'desc' => 'Erster großer Moment — Wendepunkt'],
            ['ts' => '9s',  'act' => 'Main Action', 'desc' => 'Haupt-Aktion, Konflikt auf dem Höhepunkt'],
            ['ts' => '12s', 'act' => 'Finale',      'desc' => 'Stärkste visuelle Aussage des Trailers'],
            ['ts' => '14s', 'act' => 'Cut',          'desc' => 'Cut to black — maximale Wirkung'],
        ],
        30 => [
            ['ts' => '0s',  'act' => 'Hook',        'desc' => 'Sofortiger Einstieg, keine Verzögerung'],
            ['ts' => '4s',  'act' => 'Akt 1',       'desc' => 'Welt und Protagonist etablieren'],
            ['ts' => '8s',  'act' => 'Turning Point','desc' => 'Konflikt entsteht — alles ändert sich'],
            ['ts' => '12s', 'act' => 'Akt 2',       'desc' => 'Eskalation, erste Konsequenzen'],
            ['ts' => '18s', 'act' => 'Klimax',       'desc' => 'Höhepunkt der Action / des Dramas'],
            ['ts' => '24s', 'act' => 'Finale',       'desc' => 'Auflösung oder cliffhanger'],
            ['ts' => '28s', 'act' => 'Title Card',   'desc' => 'Titel + Cut to black'],
        ],
    ];

    $beats = $timelines[$duration] ?? $timelines[15];

    // Szenen-Prompt pro Beat generieren
    $videoTemplate = TRAILER_TEMPLATE_MAP[$template] ?? 'blockbuster';
    $result = [];

    foreach ($beats as $i => $beat) {
        $sceneInput = $input !== '' ? "{$input}, {$beat['act']}: {$beat['desc']}" : "{$beat['act']}: {$beat['desc']}";

        $built = buildVideoPrompt($sceneInput, $videoTemplate, [
            'model'    => 'seedance_standard',
            'duration' => min(5, $duration),
            'quality'  => 'super',
            'mode'     => 'text',
        ]);

        $result[] = [
            'timestamp' => $beat['ts'],
            'act'       => $beat['act'],
            'desc'      => $beat['desc'],
            'prompt'    => $built['positive'],
            'index'     => $i,
        ];
    }

    return $result;
}

// ── Gesamt-Trailer-Prompt aufbauen ────────────────────────────────
function buildOverallTrailerPrompt(
    string $input,
    string $template,
    string $musicStyle,
    string $pacing,
    int    $duration
): array {
    $videoTemplate  = TRAILER_TEMPLATE_MAP[$template]   ?? 'blockbuster';
    $musicMod       = MUSIC_MODIFIERS[$musicStyle]      ?? MUSIC_MODIFIERS['epic'];
    $pacingDesc     = PACING_DESCRIPTORS[$pacing]       ?? PACING_DESCRIPTORS['trailer'];

    $options = [
        'model'    => 'seedance_standard',
        'duration' => $duration,
        'quality'  => 'super',
        'mode'     => 'text',
    ];

    $built    = buildVideoPrompt($input ?: 'cinematic trailer sequence', $videoTemplate, $options);
    $positive = $built['positive'];
    $negative = $built['negative'];

    // Trailer-spezifische Erweiterungen
    $trailerExtras = [
        "MUSIC: {$musicMod}",
        "PACING: {$pacingDesc}",
        "DURATION: {$duration}s total runtime, professional trailer timing",
        "GRADE: cinematic color grade, high contrast, deep blacks, vivid highlights",
    ];
    $positive .= ' | ' . implode(' | ', $trailerExtras);

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

        $timeline = buildTimeline($input, $template, $duration);
        $overall  = buildOverallTrailerPrompt($input, $template, $musicStyle, $pacing, $duration);

        echo json_encode([
            'success'  => true,
            'timeline' => $timeline,
            'positive' => $overall['positive'],
            'negative' => $overall['negative'],
            'meta'     => [
                'template'    => $template,
                'music_style' => $musicStyle,
                'pacing'      => $pacing,
                'duration'    => $duration,
            ],
        ], JSON_UNESCAPED_UNICODE);
        exit;

    case 'improve':
        if ($input === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Kein Prompt übergeben.']);
            exit;
        }
        $improved = improvePrompt($input);
        echo json_encode([
            'success'  => true,
            'positive' => $improved,
            'negative' => '',
            'timeline' => [],
            'meta'     => ['action' => 'improve'],
        ], JSON_UNESCAPED_UNICODE);
        exit;

    case 'cinematic':
        if ($input === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Kein Prompt übergeben.']);
            exit;
        }
        $upgraded = cinematicUpgradePrompt($input);
        echo json_encode([
            'success'  => true,
            'positive' => $upgraded,
            'negative' => '',
            'timeline' => [],
            'meta'     => ['action' => 'cinematic'],
        ], JSON_UNESCAPED_UNICODE);
        exit;

    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => "Unbekannte action: \"{$action}\"."]);
        exit;
}
