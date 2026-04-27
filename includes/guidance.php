<?php
/**
 * includes/guidance.php — Smart Guidance System
 *
 * Kontextbasierte Tipps, Warnungen und Quick-Fix-Vorschläge.
 * Keine externe API — nur PHP-Arrays + String-Logik.
 */


// ================================================================
// SEITEN-DESCRIPTIONS (legacy — für sidebar.php / andere Seiten)
// ================================================================

const PAGE_DESCRIPTIONS = [
    'image-studio'     => 'Erstelle und bearbeite Bilder für dein Familienprojekt.',
    'video-studio'     => 'Schneide und kombiniere Videos mit Musik und Effekten.',
    'tiktok-studio'    => 'Erstelle kurze, vertikale Videos für TikTok & Reels.',
    'tiktok-animation' => 'Füge Animationen und Bewegtbild-Elemente hinzu.',
    'tiktok-sticker'   => 'Gestalte individuelle Sticker für deine Videos.',
    'elements'         => 'Verwalte wiederverwendbare Design-Elemente.',
    'ready-videos'     => 'Fertige Vorlagen-Videos, direkt verwendbar.',
    'trailer-builder'  => 'Baue cinematische Trailer aus deinen Clips.',
    'academy'          => 'Lernbereich: Tipps, Tutorials und Best Practices.',
];

function getGuidance(string $page): string {
    return PAGE_DESCRIPTIONS[$page] ?? '';
}


// ================================================================
// TIPS — Kontextbasiert nach Context + Template + Mode
// ================================================================

const GUIDANCE_TIPS = [

    'image' => [
        // Template-spezifisch
        'character' => [
            ['icon' => '👤', 'title' => 'Konsistente Figuren', 'text' => 'Für konsistente Figuren nutze später Character Sheets mit klarer Front-/Side-Ansicht.'],
            ['icon' => '💡', 'title' => 'Beleuchtung', 'text' => 'Drei-Punkt-Beleuchtung (Key, Fill, Rim) sorgt für plastisches, cinematisches Aussehen.'],
        ],
        'car' => [
            ['icon' => '🚗', 'title' => 'Auto-Ansicht', 'text' => 'Autos funktionieren besser mit klarer Seiten-/Frontansicht und sauberem Licht.'],
            ['icon' => '🌧', 'title' => 'Reflexionen', 'text' => 'Nasse Fahrbahn oder Studio-Boden verstärken die Tiefe und machen Reflexionen sichtbar.'],
        ],
        'product' => [
            ['icon' => '📦', 'title' => 'Sauberer Hintergrund', 'text' => 'Produktfotos brauchen klare Kanten, sauberen Hintergrund und realistische Reflexionen.'],
            ['icon' => '🔦', 'title' => 'Soft-Box Licht', 'text' => 'Weiches Licht ohne harte Schatten ist für Produkte professioneller als dramatisches Licht.'],
        ],
        'creature' => [
            ['icon' => '🦎', 'title' => 'Realistische Anatomie', 'text' => 'Beschreibe Größe und Gewicht — dann wirkt die Kreatur glaubwürdiger im Bild.'],
            ['icon' => '🌑', 'title' => 'Dramatisches Licht', 'text' => 'Starkes Gegenlicht (Rim Light) lässt Kreaturen imposanter und volumiger wirken.'],
        ],
        'startframe' => [
            ['icon' => '🎬', 'title' => 'Klare Komposition', 'text' => 'Startframes sollten eine klare Komposition haben, wenig Chaos und eindeutige Hauptfigur.'],
            ['icon' => '📐', 'title' => 'Drittel-Regel', 'text' => 'Platziere das Hauptmotiv an einem Drittel-Schnittpunkt für eine dynamischere Komposition.'],
        ],
        'endframe' => [
            ['icon' => '🔚', 'title' => 'Logischer Abschluss', 'text' => 'Endframes müssen logisch zur Bewegung passen, sonst wirkt der Übergang hart.'],
            ['icon' => '🕯', 'title' => 'Lichtkontinuität', 'text' => 'Halte Lichtrichtung und Farbtemperatur konsistent mit dem Startframe.'],
        ],
        'character_sheet' => [
            ['icon' => '📋', 'title' => 'Multi-Ansicht', 'text' => 'Character Sheets brauchen Front, Side, Back und 3/4-Ansicht für stabiles Charakter-Design.'],
            ['icon' => '💡', 'title' => 'Flaches Licht', 'text' => 'Nutze gleichmäßiges Licht ohne Schatten — Details müssen in allen Ansichten sichtbar sein.'],
        ],
        '_default' => [
            ['icon' => '✦', 'title' => 'Photorealistisch', 'text' => 'Vermeide Anime- oder Cartoon-Formulierungen — nutze "photorealistic, cinematic" für beste Ergebnisse.'],
            ['icon' => '📷', 'title' => 'Kamera-Perspektive', 'text' => 'Definiere immer Kamera-Winkel und Brennweite für präzisere Bildgenerierung.'],
        ],
    ],

    'video' => [
        // Mode-spezifisch
        'text' => [
            ['icon' => '🔗', 'title' => 'Bewegungskette', 'text' => 'Beschreibe eine klare Bewegungskette: Start → Aktion → Reaktion → Ende.'],
            ['icon' => '🎥', 'title' => 'Kamerabewegung', 'text' => 'Nenne explizit die Kamerabewegung: "slow dolly push-in", "handheld", "crane shot".'],
        ],
        'startframe' => [
            ['icon' => '🖼', 'title' => 'Stabiler Start', 'text' => 'Das Video startet stabiler, wenn dein Startframe klare Kamera, Licht und Hauptmotiv zeigt.'],
            ['icon' => '⚖', 'title' => 'Komposition', 'text' => 'Einfache, ruhige Startframe-Kompositionen erzeugen bessere Videoübergänge.'],
        ],
        'start+end' => [
            ['icon' => '🔄', 'title' => 'Lichtkontinuität', 'text' => 'Start- und Endframe sollten dieselbe Lichtstimmung und ähnliche Kamera-Perspektive haben.'],
            ['icon' => '📏', 'title' => 'Visueller Match', 'text' => 'Achte darauf, dass Start- und Endframe visuell zusammenpassen — der KI-Transition fällt leichter.'],
        ],
        'element' => [
            ['icon' => '🧩', 'title' => 'Konsistenz', 'text' => 'Elemente helfen, Charaktere, Autos oder Produkte über mehrere Videos konsistenter zu halten.'],
            ['icon' => '📚', 'title' => 'Element Library', 'text' => 'Je mehr Details im Element gespeichert sind, desto stabiler bleibt das Motiv über Videos hinweg.'],
        ],
        // Template-spezifische Ergänzungen
        'action_trailer' => [
            ['icon' => '⚡', 'title' => 'Eine Hauptbewegung', 'text' => 'Bei Action nicht zu viele Dinge gleichzeitig. Eine starke Bewegung ist besser als fünf unklare.'],
        ],
        'horror_creature' => [
            ['icon' => '🎭', 'title' => 'Langsame Enthüllung', 'text' => 'Horror wirkt besser mit langsamer Enthüllung statt sofortiger Vollansicht der Kreatur.'],
        ],
        'blockbuster' => [
            ['icon' => '🌅', 'title' => 'Epische Skala', 'text' => 'Blockbuster-Shots brauchen enormen Maßstab — Menschen vs. Gebäude, Auto vs. Landschaft.'],
        ],
        'tiktok_hook' => [
            ['icon' => '⏱', 'title' => 'Erste 2 Sekunden', 'text' => 'TikTok-Hooks müssen in den ersten 2 Sekunden Aufmerksamkeit erzeugen — sofort zur Sache.'],
        ],
        '_default' => [
            ['icon' => '👤', 'title' => 'Gesichter', 'text' => 'Für Gesichter: keine extremen Close-ups, nutze Character Sheet Logik und klare Lichtführung.'],
            ['icon' => '🔬', 'title' => 'Physik', 'text' => 'Beschreibe realistische Physik — Masse, Trägheit, Geschwindigkeit — für glaubwürdige Videos.'],
        ],
    ],

    'element' => [
        '_default' => [
            ['icon' => '💡', 'title' => 'Elemente ≠ Startframes', 'text' => 'Elemente sind wiederverwendbare Referenzen, keine Startframes. Sie dienen als konsistente Basis.'],
            ['icon' => '📸', 'title' => 'Referenzbild', 'text' => 'Ein sauberes Referenzbild (klarer Hintergrund, gutes Licht) verbessert die Erkennbarkeit.'],
        ],
    ],

    'tiktok_animation' => [
        '_default' => [
            ['icon' => '✨', 'title' => 'Kurze Loops', 'text' => 'TikTok Animationen funktionieren am besten als kurze, wiederholbare Loops (2–4 Sekunden).'],
            ['icon' => '🎯', 'title' => 'Fokus', 'text' => 'Eine Animation pro Clip — nicht zu viele Elemente gleichzeitig bewegen.'],
        ],
    ],

    'sticker' => [
        '_default' => [
            ['icon' => '🎨', 'title' => 'Einfache Silhouette', 'text' => 'Sticker funktionieren besser mit klarer, einfacher Silhouette und starkem Kontrast.'],
            ['icon' => '⬜', 'title' => 'Transparenter Hintergrund', 'text' => 'Plane für transparenten Hintergrund — kein Gradienten-Hintergrund in der Beschreibung.'],
        ],
    ],

    'ready_videos' => [
        '_default' => [
            ['icon' => '📹', 'title' => 'Vorlage anpassen', 'text' => 'Passe Ready Videos an indem du Text-Overlays und Musik wechselst — nicht den Kernschnitt.'],
        ],
    ],
];


// ================================================================
// WARNINGS — Kontextbasiert + Input-Analyse
// ================================================================

const GUIDANCE_WARNINGS = [
    'short_input' => [
        'condition' => 'input_length < 20',
        'icon'      => '⚠',
        'text'      => 'Deine Beschreibung ist sehr kurz. Ergänze Aktion, Umgebung und Kamera für bessere Ergebnisse.',
    ],
    'long_input' => [
        'condition' => 'input_length > 1500',
        'icon'      => '⚠',
        'text'      => 'Sehr lange Prompts können instabil werden. Kürze auf das Wesentliche.',
    ],
    'start_end_match' => [
        'condition' => 'mode == start+end',
        'icon'      => '🔍',
        'text'      => 'Achte darauf, dass Startframe und Endframe visuell zusammenpassen.',
    ],
    'complex_action' => [
        'condition' => 'template in [horror_creature, action_trailer, blockbuster]',
        'icon'      => '🎬',
        'text'      => 'Zu viele schnelle Aktionen können zu Chaos führen. Halte eine klare Hauptbewegung.',
    ],
];


// ================================================================
// QUICK FIX SUGGESTIONS
// ================================================================

const QUICK_FIX_SUGGESTIONS = [
    'image' => [
        ['action' => 'improve',   'label' => '↑ Make it Better',      'desc' => 'Mehr Details zu Licht, Kamera, Realismus'],
        ['action' => 'fix_faces', 'label' => '👤 Fix Faces',           'desc' => 'Gesichtsanatomie verbessern'],
        ['action' => 'cinematic', 'label' => '🎬 Cinematic Upgrade',   'desc' => 'Film-Look und Farbgebung verstärken'],
    ],
    'video' => [
        ['action' => 'improve',            'label' => '↑ Make it Better',       'desc' => 'Mehr Details zu Licht, Kamera, Realismus'],
        ['action' => 'fix_faces',          'label' => '👤 Fix Faces',            'desc' => 'Gesichtsanatomie verbessern'],
        ['action' => 'better_motion',      'label' => '〜 Better Motion',        'desc' => 'Flüssige Bewegungen, realistische Physik'],
        ['action' => 'perfect_transition', 'label' => '⇄ Perfect Transition',   'desc' => 'Nahtlose Übergänge sicherstellen'],
        ['action' => 'cinematic',          'label' => '🎬 Cinematic Upgrade',    'desc' => 'Film-Look und Farbgebung verstärken'],
    ],
    '_default' => [
        ['action' => 'improve',   'label' => '↑ Verbessern', 'desc' => 'Prompt mit mehr Details anreichern'],
        ['action' => 'cinematic', 'label' => '🎬 Cinematic',  'desc' => 'Cinematischen Film-Look hinzufügen'],
    ],
];


// ================================================================
// PUBLIC API
// ================================================================

/**
 * Gibt passende Tipps zurück.
 *
 * @param string $context   'image' | 'video' | 'element' | 'tiktok_animation' | 'sticker' | 'ready_videos'
 * @param string|null $template  Template-Key (z.B. 'character', 'cinematic_scene')
 * @param string|null $mode      Mode-Key (z.B. 'text', 'startframe', 'start+end')
 * @param string $input          User-Eingabe (für input-basierte Tipps, optional)
 * @return array<array{icon:string, title:string, text:string}>
 */
function getGuidanceTips(string $context, ?string $template = null, ?string $mode = null, string $input = ''): array {
    $bank = GUIDANCE_TIPS[$context] ?? [];
    $tips = [];

    // Mode-spezifisch (Video Studio)
    if ($mode && isset($bank[$mode])) {
        $tips = array_merge($tips, $bank[$mode]);
    }

    // Template-spezifisch
    if ($template && isset($bank[$template])) {
        foreach ($bank[$template] as $tip) {
            // Kein Duplikat (selber Text)
            if (!_tipExists($tips, $tip['text'])) {
                $tips[] = $tip;
            }
        }
    }

    // Defaults wenn noch nicht genug Tipps
    if (count($tips) < 2 && isset($bank['_default'])) {
        foreach ($bank['_default'] as $tip) {
            if (!_tipExists($tips, $tip['text'])) {
                $tips[] = $tip;
            }
        }
    }

    return array_slice($tips, 0, 4); // max. 4 Tipps
}

/**
 * Gibt aktive Warnungen zurück, basierend auf Kontext + Input.
 *
 * @return array<array{icon:string, text:string}>
 */
function getGuidanceWarnings(string $context, ?string $template = null, ?string $mode = null, string $input = ''): array {
    $warnings = [];
    $len      = mb_strlen(trim($input));

    if ($len > 0 && $len < 20) {
        $warnings[] = GUIDANCE_WARNINGS['short_input'];
    }
    if ($len > 1500) {
        $warnings[] = GUIDANCE_WARNINGS['long_input'];
    }
    if ($mode === 'start+end') {
        $warnings[] = GUIDANCE_WARNINGS['start_end_match'];
    }
    if (in_array($template, ['horror_creature', 'action_trailer', 'blockbuster'], true)) {
        $warnings[] = GUIDANCE_WARNINGS['complex_action'];
    }

    return $warnings;
}

/**
 * Gibt Quick-Fix-Vorschläge für den Kontext zurück.
 *
 * @return array<array{action:string, label:string, desc:string}>
 */
function getQuickFixSuggestions(string $context, ?string $template = null, ?string $mode = null): array {
    return QUICK_FIX_SUGGESTIONS[$context] ?? QUICK_FIX_SUGGESTIONS['_default'];
}

/**
 * Rendert eine Guidance-Bar direkt als HTML.
 * Nutzung in PHP-Seiten: <?php renderGuidanceBar('image', 'character'); ?>
 */
function renderGuidanceBar(string $context, ?string $template = null, ?string $mode = null, string $input = ''): void {
    $tips = getGuidanceTips($context, $template, $mode, $input);
    if (empty($tips)) return;
    echo '<div class="guidance-bar" id="guidance-bar">';
    foreach ($tips as $tip) {
        $icon  = htmlspecialchars($tip['icon']  ?? '💡');
        $title = htmlspecialchars($tip['title'] ?? '');
        $text  = htmlspecialchars($tip['text']  ?? '');
        echo "<div class=\"guidance-tip\"><strong>{$icon} {$title}:</strong> {$text}</div>";
    }
    echo '</div>';
}

/**
 * Gibt alle Tips als JSON-fähiges Array für JS zurück.
 * Nutzung: json_encode(getAllGuidanceTips())
 */
function getAllGuidanceTips(): array {
    return GUIDANCE_TIPS;
}


// ================================================================
// INTERNAL HELPERS
// ================================================================

function _tipExists(array $tips, string $text): bool {
    foreach ($tips as $t) {
        if (($t['text'] ?? '') === $text) return true;
    }
    return false;
}
