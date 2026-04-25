<?php
/**
 * includes/prompt-engine.php — Cinematic Studio Family
 *
 * Zentrale Prompt Engine.
 * Wandelt einfache User-Eingaben in strukturierte, cinematische Prompts um.
 * Keine externe API — nur String-Builder-Logik.
 */


// ================================================================
// IMAGE TEMPLATES
// Jedes Template definiert die "DNA" für eine Bildkategorie.
// ================================================================

const IMAGE_TEMPLATES = [

    'character' => [
        'subject_suffix' => 'full body portrait, realistic human proportions, detailed face, expressive eyes',
        'action'         => 'standing naturally, slight pose, weight distribution realistic',
        'environment'    => 'neutral studio background, subtle environmental context',
        'lighting'       => 'three-point lighting, soft key light, rim light for depth, subtle fill',
        'camera'         => '85mm portrait lens, f/1.8 aperture, shallow depth of field, eye-level shot',
        'style'          => 'photorealistic, cinematic color grading, film grain, no CGI look',
        'materials'      => 'realistic skin texture, fabric detail, natural hair strands, micro surface detail',
        'final_look'     => 'editorial photography, high-end commercial portrait, crisp detail',
        'negative'       => 'anime, cartoon, illustration, CGI, plastic skin, deformed face, extra limbs, bad anatomy, watermark',
    ],

    'car' => [
        'subject_suffix' => 'automotive photography, perfect bodywork, clean reflections, studio or dramatic location',
        'action'         => 'parked or low-speed dynamic shot, wheels straight, subtle motion blur if moving',
        'environment'    => 'dramatic location or clean studio, wet asphalt for reflections, moody sky',
        'lighting'       => 'dramatic side lighting, specular highlights on bodywork, controlled reflections',
        'camera'         => '35mm wide lens, low angle, three-quarter front view, tack-sharp focus',
        'style'          => 'automotive commercial photography, photorealistic, no CGI, high contrast',
        'materials'      => 'metallic paint, glass reflections, rubber tires, chrome details, brushed metal accents',
        'final_look'     => 'magazine cover quality, cinematic teal-orange grade, maximum detail',
        'negative'       => 'cartoon, deformed body, floating, unrealistic reflections, watermark, bad proportions',
    ],

    'product' => [
        'subject_suffix' => 'commercial product photography, perfect condition, no dust, no scratches',
        'action'         => 'static display, optimal angle showing key features',
        'environment'    => 'clean minimal background, subtle surface reflection, minimal props for context',
        'lighting'       => 'soft box lighting, bright even exposure, controlled specular highlights',
        'camera'         => 'macro or medium shot, 90mm lens, eye-level or slight top angle, sharp focus throughout',
        'style'          => 'commercial photography, clean and professional, photorealistic',
        'materials'      => 'surface texture visible, brand elements sharp, packaging detail crisp',
        'final_look'     => 'e-commerce or print advertising quality, clean white or gradient background',
        'negative'       => 'dirty, damaged, blurry, distorted, cartoon, CGI, watermark',
    ],

    'creature' => [
        'subject_suffix' => 'creature design, realistic anatomy, believable scale, natural weight and mass',
        'action'         => 'powerful stance or mid-action pose, weight grounded',
        'environment'    => 'dramatic natural environment, atmospheric depth, environmental storytelling',
        'lighting'       => 'dramatic moody lighting, strong rim light, deep shadows, volumetric atmosphere',
        'camera'         => 'wide angle 28mm, low angle for scale, tack-sharp creature, soft background',
        'style'          => 'photorealistic, film VFX quality, no cartoonish look, Jurassic Park realism standard',
        'materials'      => 'realistic skin, scales, or fur texture, wet surfaces, subsurface scattering on skin',
        'final_look'     => 'Hollywood creature feature, Nolan-esque realism, maximum menace',
        'negative'       => 'anime, cartoon, cute, deformed, floating, extra limbs, bad anatomy, watermark',
    ],

    'startframe' => [
        'subject_suffix' => 'opening frame for video sequence, establishing composition, clear visual hierarchy',
        'action'         => 'subject at rest or beginning of movement, pre-action tension',
        'environment'    => 'fully established environment, complete scene setting, atmospheric depth',
        'lighting'       => 'cinematic lighting fully established, consistent light direction for sequence',
        'camera'         => 'establishing shot, wide to medium, stable frame for clean video start',
        'style'          => 'photorealistic, cinematic, film look, sequence-ready',
        'materials'      => 'full detail visible, no motion blur, crisp starting frame',
        'final_look'     => 'perfect video start frame, clean composition for animation',
        'negative'       => 'motion blur, partial composition, incomplete scene, watermark',
    ],

    'endframe' => [
        'subject_suffix' => 'ending frame for video sequence, resolved composition, satisfying conclusion',
        'action'         => 'subject at rest or end of movement, post-action resolution',
        'environment'    => 'environment fully revealed, settled atmosphere, no dynamic elements mid-motion',
        'lighting'       => 'lighting fully settled, no dramatic flicker, consistent with sequence',
        'camera'         => 'final frame composition, medium to close, stable end frame',
        'style'          => 'photorealistic, cinematic, clean ending frame',
        'materials'      => 'full detail, sharp final frame, no motion artifacts',
        'final_look'     => 'perfect video end frame, complete and resolved visual',
        'negative'       => 'motion blur, incomplete action, abrupt cut feeling, watermark',
    ],

    'character_sheet' => [
        'subject_suffix' => 'character reference sheet, multiple views: front, side, back, three-quarter, consistent character across all angles',
        'action'         => 'neutral T-pose or natural standing pose, consistent across all views',
        'environment'    => 'clean white or neutral background, no distractions',
        'lighting'       => 'flat even lighting, no dramatic shadows, fully visible details',
        'camera'         => 'orthographic-style multiple views, consistent scale across panels',
        'style'          => 'character design reference quality, photorealistic, turnaround sheet',
        'materials'      => 'all costume and skin details clearly visible, consistent textures',
        'final_look'     => 'professional character bible quality, game or film production standard',
        'negative'       => 'dramatic lighting, shadows hiding details, inconsistent character, watermark',
    ],
];


// ================================================================
// VIDEO TEMPLATES
// ================================================================

const VIDEO_TEMPLATES = [

    'cinematic_scene' => [
        'style_header'   => 'cinematic blockbuster scene, photorealistic, film camera quality',
        'camera_motion'  => 'slow dolly push-in, subtle camera shake, anamorphic lens flare',
        'pacing'         => 'slow and deliberate, atmospheric tension buildup',
        'environment'    => 'rich environmental detail, atmospheric depth, volumetric light',
        'ending'         => 'hold on final frame, subtle camera rest',
        'negative'       => 'jump cuts, fast editing, cartoon, CGI look, teleporting subjects, jitter',
    ],

    'action_trailer' => [
        'style_header'   => 'Hollywood action trailer, fast paced, high energy, photorealistic',
        'camera_motion'  => 'dynamic handheld, quick pans, tracking shots, dramatic angles',
        'pacing'         => 'high energy, punchy cuts, momentum building',
        'environment'    => 'dramatic location, practical explosions or effects, dust and debris',
        'ending'         => 'freeze frame or dramatic slow-motion finish',
        'negative'       => 'slow pacing, static camera, cartoon, CGI look, flat lighting',
    ],

    'pov_car' => [
        'style_header'   => 'cinematic automotive POV shot, realistic driving perspective, photorealistic',
        'camera_motion'  => 'mounted hood or front bumper camera, smooth road motion, realistic vibration',
        'pacing'         => 'smooth acceleration, realistic speed physics, no teleporting',
        'environment'    => 'road environment, passing scenery, realistic motion blur at speed',
        'ending'         => 'smooth deceleration or clean exit from frame',
        'negative'       => 'cartoon, unrealistic speed, teleporting, floating car, CGI road',
    ],

    'product_ad' => [
        'style_header'   => 'premium commercial product video, clean and polished, photorealistic',
        'camera_motion'  => 'smooth orbit around product, subtle zoom, controlled lighting sweep',
        'pacing'         => 'elegant and deliberate, luxury pacing, no rushed cuts',
        'environment'    => 'clean studio or minimal elegant setting, controlled reflections',
        'ending'         => 'product centered, logo placement area clean',
        'negative'       => 'dirty environment, fast motion, cartoon, amateur look, watermark',
    ],

    'horror_creature' => [
        'style_header'   => 'cinematic horror, photorealistic creature, maximum tension, film quality',
        'camera_motion'  => 'slow creeping push-in, handheld tension, dramatic reveal pan',
        'pacing'         => 'slow and terrifying, silence before movement, sudden burst of action',
        'environment'    => 'dark atmospheric environment, minimal lighting, fog or mist',
        'ending'         => 'creature dominates frame or disappears into darkness',
        'negative'       => 'cute, cartoon, anime, CGI look, bright cheerful lighting, jitter',
    ],

    'transformation' => [
        'style_header'   => 'seamless cinematic transformation, photorealistic morphing, film quality',
        'camera_motion'  => 'locked or very slow push-in during transformation, no cutting away',
        'pacing'         => 'smooth and continuous, realistic physics throughout change',
        'environment'    => 'consistent environment throughout transformation, stable lighting',
        'ending'         => 'final form fully revealed, clean hold',
        'negative'       => 'hard cuts, jump transitions, cartoon morph, CGI look, teleporting',
    ],

    'blockbuster' => [
        'style_header'   => 'summer blockbuster opening sequence, IMAX quality, photorealistic',
        'camera_motion'  => 'epic sweeping crane or drone shot, IMAX wide establishing, then tighten',
        'pacing'         => 'epic scale buildup, massive scope, deliberate reveal',
        'environment'    => 'massive scale environment, epic landscapes or cityscapes, golden hour',
        'ending'         => 'title card moment, hero or key element centered',
        'negative'       => 'small scale, static camera, cartoon, CGI look, flat lighting',
    ],

    'tiktok_hook' => [
        'style_header'   => 'vertical 9:16 TikTok video, high-energy hook, photorealistic, social media quality',
        'camera_motion'  => 'quick dynamic opening, immediate action, no slow reveal',
        'pacing'         => 'fast hook within first 2 seconds, high energy throughout, punchy',
        'environment'    => 'clear and vibrant environment, bold colors, no cluttered background',
        'ending'         => 'clear call-to-action moment or loop-friendly ending',
        'negative'       => 'slow boring intro, static shot, CGI look, watermark, cluttered frame',
    ],
];


// ================================================================
// IMAGE PROMPT BUILDER
// ================================================================

/**
 * Baut einen strukturierten Bild-Prompt aus User-Eingabe + Template.
 *
 * @param string $input    Einfache User-Beschreibung
 * @param string $template Einer der IMAGE_TEMPLATES Keys
 * @return array           ['positive' => string, 'negative' => string]
 */
function buildImagePrompt(string $input, string $template = 'character'): array {
    $input    = trim($input);
    $template = strtolower(trim($template));
    $tpl      = IMAGE_TEMPLATES[$template] ?? IMAGE_TEMPLATES['character'];

    $subject = $input
        ? "{$input}, {$tpl['subject_suffix']}"
        : $tpl['subject_suffix'];

    $positive = implode(', ', array_filter([
        "SUBJECT: {$subject}",
        "ACTION: {$tpl['action']}",
        "ENVIRONMENT: {$tpl['environment']}",
        "LIGHTING: {$tpl['lighting']}",
        "CAMERA: {$tpl['camera']}",
        "STYLE: {$tpl['style']}",
        "MATERIALS: {$tpl['materials']}",
        "FINAL LOOK: {$tpl['final_look']}",
    ]));

    return [
        'positive' => $positive,
        'negative' => $tpl['negative'],
    ];
}


// ================================================================
// VIDEO PROMPT BUILDER
// ================================================================

/**
 * Baut einen strukturierten Video-Prompt.
 *
 * @param string $input    User-Beschreibung der Szene
 * @param string $template Einer der VIDEO_TEMPLATES Keys
 * @param array  $options  [
 *   'model'    => 'seedance_fast' | 'seedance_standard',
 *   'duration' => 5 | 8 | 10 | 15,
 *   'quality'  => 'normal' | 'super',
 *   'mode'     => 'text' | 'startframe' | 'start+end' | 'element',
 * ]
 * @return array ['positive' => string, 'negative' => string, 'meta' => array]
 */
function buildVideoPrompt(string $input, string $template = 'cinematic_scene', array $options = []): array {
    $input    = trim($input);
    $template = strtolower(trim($template));
    $tpl      = VIDEO_TEMPLATES[$template] ?? VIDEO_TEMPLATES['cinematic_scene'];

    $duration = (int) ($options['duration'] ?? 8);
    $model    = $options['model']   ?? 'seedance_standard';
    $quality  = $options['quality'] ?? 'normal';
    $mode     = $options['mode']    ?? 'text';

    // Zeitstruktur aufbauen
    $midPoint = (int) round($duration * 0.4);
    $endPoint = $duration;

    $timeStructure = "0s: scene establishes — {$input}. "
        . "{$midPoint}s: action develops, {$tpl['pacing']}. "
        . "{$endPoint}s: {$tpl['ending']}.";

    $positive = implode(' | ', array_filter([
        "STYLE: {$tpl['style_header']}",
        "SCENE: {$input}",
        "TIME: {$timeStructure}",
        "CAMERA: {$tpl['camera_motion']}",
        "ENVIRONMENT: {$tpl['environment']}",
        "ENDING: {$tpl['ending']}",
    ]));

    return [
        'positive' => $positive,
        'negative' => $tpl['negative'],
        'meta'     => [
            'model'    => $model,
            'duration' => $duration,
            'quality'  => $quality,
            'mode'     => $mode,
            'template' => $template,
        ],
    ];
}


// ================================================================
// PROMPT MODIFIER FUNCTIONS
// Diese Funktionen erweitern einen bestehenden Prompt-String.
// ================================================================

/**
 * Verbessert einen bestehenden Prompt mit mehr Details.
 */
function improvePrompt(string $prompt): string {
    $additions = [
        'ultra detailed',
        'cinematic volumetric lighting',
        'realistic atmospheric depth',
        'sharp focus on subject',
        'natural ambient occlusion',
        'physically accurate shadows',
        'high dynamic range',
        'professional color grading',
    ];
    return _appendToPrompt($prompt, $additions);
}

/**
 * Ergänzt Gesichts-Korrekturen.
 */
function fixFacesPrompt(string $prompt): string {
    $additions = [
        'realistic face',
        'correct facial anatomy',
        'natural proportions',
        'no distortion',
        'symmetrical features',
        'detailed eyes with catchlights',
        'natural skin texture',
        'no uncanny valley',
    ];
    return _appendToPrompt($prompt, $additions);
}

/**
 * Verbessert Bewegungsqualität.
 */
function betterMotionPrompt(string $prompt): string {
    $additions = [
        'smooth fluid motion',
        'realistic physics',
        'natural momentum',
        'no jitter',
        'no teleporting',
        'consistent velocity',
        'believable inertia',
        'motion blur proportional to speed',
    ];
    return _appendToPrompt($prompt, $additions);
}

/**
 * Verbessert Übergänge.
 */
function perfectTransitionPrompt(string $prompt): string {
    $additions = [
        'seamless transition',
        'continuous movement flow',
        'no hard cuts',
        'consistent lighting across transition',
        'smooth camera handoff',
        'maintained subject continuity',
    ];
    return _appendToPrompt($prompt, $additions);
}

/**
 * Cinematic Upgrade — maximale Filmqualität.
 */
function cinematicUpgradePrompt(string $prompt): string {
    $additions = [
        'cinematic film look',
        'anamorphic lens characteristics',
        'film grain',
        'high dynamic range',
        'realistic shadow detail',
        'cinematic color palette',
        'bokeh depth of field',
        'photochemical grading',
        'IMAX quality',
    ];
    return _appendToPrompt($prompt, $additions);
}


// ================================================================
// INTERNAL HELPERS
// ================================================================

/**
 * Hängt Additions an einen Prompt-String an ohne Duplikate.
 *
 * @param string   $prompt
 * @param string[] $additions
 * @return string
 */
function _appendToPrompt(string $prompt, array $additions): string {
    $prompt = rtrim($prompt, ' ,');
    foreach ($additions as $addition) {
        // Nur hinzufügen wenn noch nicht enthalten (case-insensitive)
        if (stripos($prompt, $addition) === false) {
            $prompt .= ', ' . $addition;
        }
    }
    return $prompt;
}

/**
 * Gibt alle verfügbaren Template-Keys zurück.
 *
 * @param string $type 'image' | 'video'
 * @return string[]
 */
function getAvailableTemplates(string $type = 'image'): array {
    return $type === 'video'
        ? array_keys(VIDEO_TEMPLATES)
        : array_keys(IMAGE_TEMPLATES);
}

/**
 * Sanitiert User-Input bevor er in Prompts fließt.
 * Entfernt Sonderzeichen die Prompts brechen könnten.
 */
function sanitizePromptInput(string $input): string {
    // HTML-Entities entfernen, Steuerzeichen, mehrfache Leerzeichen
    $input = strip_tags($input);
    $input = preg_replace('/[\x00-\x1F\x7F]/', '', $input);
    $input = preg_replace('/\s+/', ' ', $input);
    return trim($input);
}
