<?php
require_once 'includes/config.php';
$pageTitle = 'Academy';
require_once 'includes/header.php';

// ── Kategorien ─────────────────────────────────────────────────────
$categories = [
    'all'            => ['icon' => '📚', 'label' => 'Alle'],
    'api_credits'    => ['icon' => '🔑', 'label' => 'API & Credits'],
    'seedance'       => ['icon' => '🎬', 'label' => 'Seedance 2.0'],
    'image_studio'   => ['icon' => '🖼',  'label' => 'Image Studio'],
    'video_studio'   => ['icon' => '📽',  'label' => 'Video Studio'],
    'startframe'     => ['icon' => '▶',   'label' => 'Startframe / Endframe'],
    'elements'       => ['icon' => '🧩',  'label' => 'Element Library'],
    'character_sheet'=> ['icon' => '🧍',  'label' => 'Character Sheets'],
    'tiktok'         => ['icon' => '🎵',  'label' => 'TikTok Studio'],
    'sticker'        => ['icon' => '🏷',  'label' => 'Sticker'],
    'animation'      => ['icon' => '✨',  'label' => 'Animation Service'],
    'ready_videos'   => ['icon' => '📹',  'label' => 'Sofort fertige Videos'],
];

// ── Guide-Daten (statisch, kein CMS nötig) ────────────────────────
$guides = [
    [
        'id'          => 'g01',
        'title'       => 'API-Key erstellen und einfügen',
        'category'    => 'api_credits',
        'short'       => 'Hol dir deinen Kie.ai API-Key und verbinde ihn mit dem Studio in wenigen Schritten.',
        'read_time'   => '2 min',
        'difficulty'  => 'basic',
        'icon'        => '🔑',
        'steps'       => [
            'Gehe auf kie.ai und erstelle ein kostenloses Konto.',
            'Navigiere in den Account-Einstellungen zu „API Keys".',
            'Erstelle einen neuen API-Key und kopiere ihn.',
            'Öffne in Cinematic Studio → „API Key" in der Sidebar.',
            'Füge deinen Key ein und klicke auf „Verbindung testen".',
            'Bei Erfolg siehst du den grünen Badge „API verbunden".',
        ],
        'cta_label'   => '🔑 API verbinden',
        'cta_url'     => 'api-key.php',
    ],
    [
        'id'          => 'g02',
        'title'       => 'Credits über empfohlenen Link kaufen',
        'category'    => 'api_credits',
        'short'       => 'So kaufst du Seedance-Credits günstig und sicherst dir Bonus-Credits über den empfohlenen Link.',
        'read_time'   => '2 min',
        'difficulty'  => 'basic',
        'icon'        => '💳',
        'steps'       => [
            'Nutze den empfohlenen Referral-Link für Bonus-Credits beim ersten Kauf.',
            'Wähle ein Credits-Paket — beginne mit einem kleinen Paket zum Testen.',
            'Seedance Fast verbraucht weniger Credits als Seedance Standard.',
            'Standard-Qualität eignet sich für finale Produktionen.',
            'Behalte immer etwas Reserve — Video-Generierung verbraucht Credits je Sekunde.',
            'Credits werden sofort gutgeschrieben — kein Warten.',
        ],
        'cta_label'   => '🔑 API verbinden',
        'cta_url'     => 'api-key.php',
    ],
    [
        'id'          => 'g03',
        'title'       => 'Seedance Fast vs. Standard — was ist der Unterschied?',
        'category'    => 'seedance',
        'short'       => 'Fast ist günstiger und schneller. Standard ist hochwertiger. Wann nimmst du was?',
        'read_time'   => '3 min',
        'difficulty'  => 'creator',
        'icon'        => '⚡',
        'steps'       => [
            'Seedance Fast: schnell, günstig, gut für erste Entwürfe und Tests.',
            'Seedance Standard: höhere Qualität, bessere Bewegungsphysik, mehr Credits.',
            'Für Kundenprojekte oder finale Produktionen → immer Standard.',
            'Für Prompt-Tests und Iterationen → Fast spart Budget.',
            'Laufzeit beeinflusst Credits: 15s kostet mehr als 5s — plane vorher.',
            'Quality „Super" in Standard gibt nochmals bessere Details.',
            'Empfehlung: Test mit Fast → finales Video mit Standard.',
        ],
        'cta_label'   => '🎬 Video Studio öffnen',
        'cta_url'     => 'video-studio.php',
    ],
    [
        'id'          => 'g04',
        'title'       => 'Bessere Startframes erstellen',
        'category'    => 'startframe',
        'short'       => 'Der Startframe ist das erste Bild deines Videos. So machst du ihn stark.',
        'read_time'   => '4 min',
        'difficulty'  => 'creator',
        'icon'        => '▶',
        'steps'       => [
            'Wähle im Image Studio das Template „Startframe" aus.',
            'Beschreibe die exakte Startposition deiner Szene — wo ist die Kamera? Wo ist das Subjekt?',
            'Kein Bewegungsunschärfe im Startframe — das Bild soll statisch und scharf sein.',
            'Definiere Licht klar: Richtung, Stimmung, Intensität — das Video erbt diese Einstellung.',
            'Weiter Kamerawinkel für Establishing-Shots, nah für Emotionen.',
            'Nachdem du den Prompt generiert hast → In Seedance als Startframe-Bild hochladen.',
            'Im Video Studio Modus „Startframe" wählen und das Bild einfügen.',
        ],
        'cta_label'   => '🖼 Image Studio öffnen',
        'cta_url'     => 'image-studio.php',
    ],
    [
        'id'          => 'g05',
        'title'       => 'Endframes richtig verwenden',
        'category'    => 'startframe',
        'short'       => 'Endframes definieren das letzte Bild deines Clips. So bleiben Start und Ende konsistent.',
        'read_time'   => '3 min',
        'difficulty'  => 'creator',
        'icon'        => '⏹',
        'steps'       => [
            'Endframe = das gewünschte letzte Bild deines generierten Videos.',
            'Wichtig: Startframe und Endframe müssen die gleiche Lichtquelle und Farbstimmung haben.',
            'Generiere den Endframe mit dem Template „Endframe" im Image Studio.',
            'Schreibe im Prompt explizit: „post-action, resolved composition, settled atmosphere".',
            'Im Video Studio Modus „Start + End Frame" wählen — beide Bilder hochladen.',
            'Seedance interpoliert automatisch die Bewegung zwischen den Frames.',
            'Tipp: Teste erst ohne Endframe — manchmal reicht der Startframe alleine.',
        ],
        'cta_label'   => '🖼 Image Studio öffnen',
        'cta_url'     => 'image-studio.php',
    ],
    [
        'id'          => 'g06',
        'title'       => 'Character Sheet für stabile Gesichter',
        'category'    => 'character_sheet',
        'short'       => 'Character Sheets geben Seedance ein konsistentes Gesichtsreferenz-Bild für stabilere Ergebnisse.',
        'read_time'   => '5 min',
        'difficulty'  => 'pro',
        'icon'        => '🧍',
        'steps'       => [
            'Im Image Studio Template „Character Sheet" wählen.',
            'Beschreibe deinen Charakter: Alter, Aussehen, Kleidung, Stil, Haar.',
            'Das Sheet zeigt mehrere Ansichten (front, side, back) — ideal als Referenz.',
            'Gesicht-Qualität verbessern: Button „Gesicht korrigieren" nach erstem Prompt.',
            'Generiertes Sheet-Bild in der Element Library als „Charakter" speichern.',
            'Beim Videogenerieren: Character Sheet als Element-Referenz im Element-Modus hochladen.',
            'Tipp: Character Sheet für jeden wiederkehrenden Charakter erstellen und speichern.',
        ],
        'cta_label'   => '🖼 Image Studio öffnen',
        'cta_url'     => 'image-studio.php',
    ],
    [
        'id'          => 'g07',
        'title'       => 'Elemente richtig in der Library speichern',
        'category'    => 'elements',
        'short'       => 'Die Element Library ist dein persönliches Asset-System. So nutzt du sie optimal.',
        'read_time'   => '3 min',
        'difficulty'  => 'basic',
        'icon'        => '🧩',
        'steps'       => [
            'Elements → Typen: Charakter, Fahrzeug, Produkt, Kreatur, Umgebung, Logo, Objekt, Stil-Referenz.',
            'Rollen: Hauptcharakter, Hauptobjekt, Hintergrund, Stil-Referenz.',
            'Name vergeben — immer beschreibend: „Anna – rote Jacke, Stadtszene".',
            'Bild hochladen (optional): PNG/JPEG max. 10 MB — kie.ai akzeptiert als Referenz.',
            'Beschreibung schreiben — wird automatisch in Prompts eingebaut.',
            'Tipp: Wiederholende Charaktere, Fahrzeuge oder Produkte einmal anlegen und wiederverwenden.',
            'Gelöschte Elemente sind dauerhaft weg — sichere wichtige Bild-Dateien lokal.',
        ],
        'cta_label'   => '🧩 Element Library öffnen',
        'cta_url'     => 'elements.php',
    ],
    [
        'id'          => 'g08',
        'title'       => 'TikTok Hook in 2 Sekunden',
        'category'    => 'tiktok',
        'short'       => 'Der Hook entscheidet ob jemand weiterscrollt. So baust du einen unwiderstehlichen Opener.',
        'read_time'   => '4 min',
        'difficulty'  => 'creator',
        'icon'        => '⚡',
        'steps'       => [
            'Wähle im TikTok Studio das Template „Viral Hook" oder „TikTok Ad".',
            'Beschreibe nur die erste Szene — nicht den ganzen Clip.',
            'Hook-Regel: Bewegung + Überraschung + Frage. Kombiniere mindestens zwei davon.',
            'Vermeide langsame Establishing-Shots — fang mitten in der Szene an.',
            'Stil „Energy" oder „Dark" funktioniert stärker als „Cinematic" für Hooks.',
            'Der generierte Hook-Vorschlag ist ein Startpunkt — passe ihn auf deine Marke an.',
            'Teste verschiedene Hooks A/B — lade beide hoch und vergleiche Views in 24h.',
        ],
        'cta_label'   => '🎵 TikTok Studio öffnen',
        'cta_url'     => 'tiktok-studio.php',
    ],
    [
        'id'          => 'g09',
        'title'       => 'Sticker richtig anfragen',
        'category'    => 'sticker',
        'short'       => 'So formulierst du deine Sticker-Anfrage für das beste Ergebnis.',
        'read_time'   => '3 min',
        'difficulty'  => 'basic',
        'icon'        => '🏷',
        'steps'       => [
            'Wähle den richtigen Sticker-Typ: Emoji / Text / Logo / Reaction / Custom.',
            'Stil klar benennen: Neon, Glow, Gold, Fire, Minimal oder Cartoon.',
            'Format: Für TikTok LIVE → PNG transparent. Für Stories → 9:16.',
            'Beschreibung spezifisch halten: „Lila Neon-Herz mit Gold-Outline, animiert, pulsierend".',
            'Text-Sticker: maximal 3 kurze Wörter — lange Texte werden unleserlich.',
            'Logo-Sticker: PNG mit transparentem Hintergrund hochladen für beste Ergebnisse.',
            'Je mehr Details, desto besser das Ergebnis — Farbe, Effekt, Bewegung.',
        ],
        'cta_label'   => '🏷 Sticker Studio öffnen',
        'cta_url'     => 'tiktok-sticker.php',
    ],
    [
        'id'          => 'g10',
        'title'       => 'Animation Service richtig nutzen',
        'category'    => 'animation',
        'short'       => 'Booster, Multiplikator, Logo oder Custom — so wählst du die richtige Animation.',
        'read_time'   => '3 min',
        'difficulty'  => 'creator',
        'icon'        => '✨',
        'steps'       => [
            'Booster Animation: Für TikTok LIVE x5 Effekte — starke Lichtblitze, kurze Sequenz.',
            'Multiplikator Animation: x2/x3 Combo-Effekte für Rewards und Gift-Events.',
            'Logo Animation: Dein Marken-Logo als animiertes Intro/Overlay — einfach halten.',
            'Custom Animation: Für alles andere — beschreibe genau was du möchtest.',
            'Stil wählen: Energy und Neon wirken am besten für LIVE-Streams.',
            'Loop immer aktivieren für LIVE-Einsatz — das Video wiederholt sich nahtlos.',
            'Beschreibung so spezifisch wie möglich: Farbe, Effekte, Timing, Hintergrund.',
        ],
        'cta_label'   => '✨ Animation Studio öffnen',
        'cta_url'     => 'tiktok-animation.php',
    ],
    [
        'id'          => 'g11',
        'title'       => 'Sofort fertige Videos anfragen',
        'category'    => 'ready_videos',
        'short'       => 'Du möchtest kein Prompt schreiben? Hol dir ein fertiges Premium-Video direkt.',
        'read_time'   => '2 min',
        'difficulty'  => 'basic',
        'icon'        => '📹',
        'steps'       => [
            'Gehe zu „Sofort fertige Videos" in der Sidebar.',
            'Filtere nach Kategorie: TikTok Ads, Auto, Horror, Luxury, Anime etc.',
            'Klicke „Dieses Video anfragen" wenn du es genau so haben möchtest.',
            'Klicke „Ähnliches Video erstellen lassen" wenn du Anpassungen möchtest.',
            'Trage deine E-Mail oder Telegram und einen kurzen Wunsch ein.',
            'Kein Prompt nötig — wir kümmern uns um alles.',
            'Du erhältst das fertige Video als Download-Link.',
        ],
        'cta_label'   => '📹 Ready Videos ansehen',
        'cta_url'     => 'ready-videos.php',
    ],
    [
        'id'          => 'g12',
        'title'       => 'Video-Prompts mit Seedance optimieren',
        'category'    => 'video_studio',
        'short'       => 'So schreibst du Prompts die Seedance versteht — und bekommst weniger Jitter und bessere Bewegung.',
        'read_time'   => '5 min',
        'difficulty'  => 'pro',
        'icon'        => '🎬',
        'steps'       => [
            'Beschreibe immer: Subjekt + Aktion + Umgebung + Kamera in einem Satz.',
            'Vermeide: „sehr schön", „toll", „amazing" — sei konkret.',
            'Kamera-Keywords: „slow dolly push-in", „tracking shot", „crane shot".',
            'Negativer Prompt ist wichtig: „jitter, teleporting, bad anatomy" immer eintragen.',
            'Button „Bewegung verbessern" stabilisiert Jitter automatisch.',
            'Für Charaktere: Startframe + Character Sheet kombinieren → stabilstes Ergebnis.',
            'Kürzere Clips (5–8s) sind konsistenter als 15s — lieber mehrere kurze generieren.',
        ],
        'cta_label'   => '🎬 Video Studio öffnen',
        'cta_url'     => 'video-studio.php',
    ],
    [
        'id'          => 'g13',
        'title'       => 'Image Studio Templates verstehen',
        'category'    => 'image_studio',
        'short'       => 'Jedes Template hat eine andere DNA. So wählst du das richtige für dein Projekt.',
        'read_time'   => '3 min',
        'difficulty'  => 'basic',
        'icon'        => '🖼',
        'steps'       => [
            'Realistic Character: Menschen, Portraits, Charaktere — optimiert für Gesichter.',
            'Vehicle / Car: Automotive Fotografie — Reflexionen, Low-Angle, perfektes Bodywork.',
            'Product: E-Commerce und Print — sauberes Studio-Licht, Details scharf.',
            'Creature: Fantastische Wesen, Monster — filmische Qualität, reale Anatomie.',
            'Startframe: Für Video-Einstieg — kein Blur, klare Komposition.',
            'Endframe: Für Video-Ende — ruhige, abgeschlossene Bildkomposition.',
            'Character Sheet: Mehrere Ansichten desselben Charakters — für Konsistenz.',
        ],
        'cta_label'   => '🖼 Image Studio öffnen',
        'cta_url'     => 'image-studio.php',
    ],
];
?>

<div class="ac-page">

    <!-- ── Hero ─────────────────────────────────────────────────── -->
    <div class="ac-hero">
        <div class="ac-hero__text">
            <div class="ac-hero__eyebrow">📚 Cinematic Academy</div>
            <h2 class="ac-hero__title">Academy</h2>
            <p class="ac-hero__sub text-muted">
                Lerne, wie du bessere Bilder, Videos, Startframes,<br>
                Sticker und TikTok-Inhalte erstellst.
            </p>
        </div>
        <div class="ac-hero__stats">
            <div class="ac-stat">
                <div class="ac-stat__num"><?= count($guides) ?></div>
                <div class="ac-stat__label">Guides</div>
            </div>
            <div class="ac-stat">
                <div class="ac-stat__num"><?= count($categories) - 1 ?></div>
                <div class="ac-stat__label">Themen</div>
            </div>
            <div class="ac-stat">
                <div class="ac-stat__num">Free</div>
                <div class="ac-stat__label">Kein Abo nötig</div>
            </div>
        </div>
    </div>

    <!-- ── Filter ────────────────────────────────────────────────── -->
    <div class="ac-filters">
        <?php foreach ($categories as $key => $cat): ?>
        <button
            class="ac-filter-btn <?= $key === 'all' ? 'ac-filter-btn--active' : '' ?>"
            data-filter="<?= htmlspecialchars($key) ?>"
        >
            <?= $cat['icon'] ?> <?= htmlspecialchars($cat['label']) ?>
        </button>
        <?php endforeach; ?>
    </div>

    <!-- ── Ergebnis-Info ─────────────────────────────────────────── -->
    <div class="ac-results-bar">
        <span id="ac-count" class="text-muted text-sm"></span>
        <span id="ac-filter-label" class="text-sm" style="color: var(--accent-blue);"></span>
    </div>

    <!-- ── Guide-Grid ────────────────────────────────────────────── -->
    <div id="ac-grid" class="ac-grid"></div>

    <!-- ── Leerzustand ───────────────────────────────────────────── -->
    <div id="ac-empty" class="ac-empty" hidden>
        <div class="ac-empty__icon">📚</div>
        <p>Keine Guides in dieser Kategorie.</p>
        <button class="btn btn-secondary" id="ac-show-all">Alle Guides anzeigen</button>
    </div>

</div><!-- .ac-page -->


<!-- ── Guide-Card Template ────────────────────────────────────── -->
<template id="tpl-ac-card">
    <div class="ac-card" data-category="" data-id="">
        <div class="ac-card__top">
            <span class="ac-card__icon"></span>
            <div class="ac-card__badges">
                <span class="ac-card__cat-badge"></span>
                <span class="ac-card__diff-badge"></span>
            </div>
        </div>
        <div class="ac-card__title"></div>
        <div class="ac-card__short text-muted text-sm"></div>
        <div class="ac-card__footer">
            <span class="ac-card__read-time text-muted text-sm"></span>
            <button class="btn btn-primary btn-sm ac-card__open-btn">Guide öffnen →</button>
        </div>
    </div>
</template>


<!-- ── Guide-Modal ────────────────────────────────────────────── -->
<div id="ac-modal" class="modal" role="dialog" aria-modal="true" aria-labelledby="ac-modal-title" hidden>
    <div class="modal-backdrop" id="ac-modal-backdrop"></div>
    <div class="modal-box ac-modal-box">
        <div class="modal-header">
            <div class="ac-modal-header-inner">
                <span id="ac-modal-icon" class="ac-modal-icon"></span>
                <div>
                    <div class="ac-modal-meta">
                        <span id="ac-modal-cat"  class="ac-card__cat-badge"></span>
                        <span id="ac-modal-diff" class="ac-card__diff-badge"></span>
                        <span id="ac-modal-time" class="text-muted text-sm"></span>
                    </div>
                    <h3 class="modal-title" id="ac-modal-title"></h3>
                </div>
            </div>
            <button class="modal-close" id="ac-modal-close" aria-label="Schließen">✕</button>
        </div>
        <div class="modal-body">
            <div class="ac-steps-container">
                <ol id="ac-modal-steps" class="ac-steps"></ol>
            </div>
        </div>
        <div class="modal-footer">
            <a id="ac-modal-cta" class="btn btn-primary" href="#">→</a>
            <button class="btn btn-secondary" id="ac-modal-close-2">Schließen</button>
        </div>
    </div>
</div>


<style>
/* ── Academy ────────────────────────────────────────────────── */

/* Hero */
.ac-hero {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 24px;
    margin-bottom: 32px;
    flex-wrap: wrap;
}
.ac-hero__eyebrow {
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--accent-blue);
    margin-bottom: 8px;
}
.ac-hero__title {
    font-size: 2rem;
    font-weight: 800;
    color: var(--text-primary);
    margin-bottom: 10px;
}
.ac-hero__sub { font-size: 0.95rem; line-height: 1.7; }

.ac-hero__stats {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    align-items: flex-start;
    padding-top: 4px;
}
.ac-stat {
    background: var(--bg-panel);
    border: 1px solid var(--border-color);
    border-radius: var(--radius);
    padding: 14px 20px;
    text-align: center;
    min-width: 90px;
}
.ac-stat__num   { font-size: 1.5rem; font-weight: 800; color: var(--accent-blue); line-height: 1; margin-bottom: 4px; }
.ac-stat__label { font-size: 0.7rem; color: var(--text-muted); font-weight: 500; }

/* Filter */
.ac-filters {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 12px;
}
.ac-filter-btn {
    padding: 6px 13px;
    background: var(--bg-panel);
    border: 1px solid var(--border-color);
    border-radius: 999px;
    font-size: 0.8rem;
    font-weight: 500;
    color: var(--text-secondary);
    cursor: pointer;
    white-space: nowrap;
    transition: background var(--transition), border-color var(--transition), color var(--transition);
}
.ac-filter-btn:hover { background: var(--bg-elevated); border-color: var(--accent-blue); color: var(--text-primary); }
.ac-filter-btn--active { background: var(--accent-blue-glow); border-color: var(--accent-blue); color: var(--accent-blue); font-weight: 600; }

/* Results Bar */
.ac-results-bar {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 16px;
    min-height: 20px;
}

/* Grid */
.ac-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 16px;
}

/* Leerzustand */
.ac-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    padding: 60px 24px;
    text-align: center;
    color: var(--text-muted);
}
.ac-empty__icon { font-size: 3rem; opacity: 0.4; }

/* ── Guide-Card ──────────────────────────────────────────────── */
.ac-card {
    background: var(--bg-panel);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    transition: border-color var(--transition), transform var(--transition), box-shadow var(--transition);
    cursor: default;
}
.ac-card:hover {
    border-color: rgba(61,142,245,0.4);
    transform: translateY(-2px);
    box-shadow: 0 6px 24px rgba(0,0,0,0.35);
}

.ac-card__top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 8px;
}
.ac-card__icon { font-size: 1.8rem; line-height: 1; }

.ac-card__badges { display: flex; gap: 6px; flex-wrap: wrap; justify-content: flex-end; }

/* Kategorie-Badge */
.ac-card__cat-badge {
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    background: var(--bg-elevated);
    border: 1px solid var(--border-color);
    color: var(--text-muted);
    border-radius: var(--radius-sm);
    padding: 2px 7px;
}

/* Schwierigkeits-Badge */
.ac-card__diff-badge {
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    border-radius: var(--radius-sm);
    padding: 2px 7px;
}
.ac-card__diff-badge[data-diff="basic"]   { background: rgba(72,199,116,0.15); color: #48c774; border: 1px solid rgba(72,199,116,0.3); }
.ac-card__diff-badge[data-diff="creator"] { background: var(--accent-blue-glow); color: var(--accent-blue); border: 1px solid rgba(61,142,245,0.3); }
.ac-card__diff-badge[data-diff="pro"]     { background: rgba(245,131,61,0.15); color: var(--accent-orange); border: 1px solid rgba(245,131,61,0.3); }

.ac-card__title {
    font-size: 0.9rem;
    font-weight: 700;
    color: var(--text-primary);
    line-height: 1.4;
}
.ac-card__short {
    line-height: 1.55;
    flex: 1;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.ac-card__footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    margin-top: 4px;
}
.ac-card__read-time::before { content: '⏱ '; }

/* ── Modal ───────────────────────────────────────────────────── */
.modal {
    position: fixed;
    inset: 0;
    z-index: 200;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}
.modal[hidden] { display: none; }
.modal-backdrop {
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0.75);
    backdrop-filter: blur(4px);
    cursor: pointer;
}
.modal-box {
    position: relative;
    background: var(--bg-panel);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    width: 100%;
    max-width: 560px;
    max-height: 85vh;
    overflow-y: auto;
    box-shadow: var(--shadow);
    z-index: 1;
}
.ac-modal-box { max-width: 580px; }

.modal-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    padding: 20px 24px 16px;
    border-bottom: 1px solid var(--border-color);
    position: sticky;
    top: 0;
    background: var(--bg-panel);
    z-index: 1;
}
.ac-modal-header-inner {
    display: flex;
    align-items: flex-start;
    gap: 14px;
}
.ac-modal-icon { font-size: 2rem; line-height: 1; flex-shrink: 0; padding-top: 2px; }
.ac-modal-meta { display: flex; align-items: center; gap: 8px; margin-bottom: 6px; flex-wrap: wrap; }
.modal-title   { font-size: 1rem; font-weight: 700; color: var(--text-primary); line-height: 1.4; }

.modal-close {
    font-size: 1rem; color: var(--text-muted); background: none; border: none;
    cursor: pointer; padding: 4px 8px; border-radius: var(--radius-sm);
    line-height: 1; flex-shrink: 0;
    transition: color var(--transition), background var(--transition);
}
.modal-close:hover { color: var(--text-primary); background: var(--bg-elevated); }

.modal-body   { padding: 20px 24px; }
.modal-footer { padding: 16px 24px 20px; display: flex; gap: 12px; border-top: 1px solid var(--border-color); }

/* Steps */
.ac-steps {
    display: flex;
    flex-direction: column;
    gap: 12px;
    padding: 0;
    list-style: none;
    counter-reset: step-counter;
}
.ac-step {
    display: grid;
    grid-template-columns: 32px 1fr;
    gap: 12px;
    align-items: start;
    counter-increment: step-counter;
}
.ac-step__num {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: var(--accent-blue-glow);
    border: 1px solid rgba(61,142,245,0.3);
    color: var(--accent-blue);
    font-size: 0.78rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.ac-step__num::before { content: counter(step-counter); }
.ac-step__text {
    font-size: 0.875rem;
    color: var(--text-secondary);
    line-height: 1.6;
    padding-top: 6px;
}

/* Responsive */
@media (max-width: 768px) {
    .ac-hero { flex-direction: column; }
    .ac-hero__title { font-size: 1.5rem; }
    .ac-grid { grid-template-columns: 1fr; }
    .ac-modal-box { max-width: 100%; }
}
@media (max-width: 480px) {
    .ac-hero__stats { width: 100%; }
    .ac-stat { flex: 1; min-width: 80px; }
    .modal-footer { flex-direction: column; }
}
</style>


<script>
(function () {

    // ── Guide-Daten aus PHP ───────────────────────────────────────
    const GUIDES = <?php echo json_encode($guides, JSON_UNESCAPED_UNICODE); ?>;

    // ── Kategorie-Labels ─────────────────────────────────────────
    const CAT_LABELS = <?php echo json_encode(array_map(fn($c) => $c['label'], $categories), JSON_UNESCAPED_UNICODE); ?>;

    const DIFF_LABELS = { basic: 'Basic', creator: 'Creator', pro: 'Pro' };

    // ── DOM ───────────────────────────────────────────────────────
    const grid      = document.getElementById('ac-grid');
    const emptyEl   = document.getElementById('ac-empty');
    const countEl   = document.getElementById('ac-count');
    const filterEl  = document.getElementById('ac-filter-label');
    const filterBtns = document.querySelectorAll('.ac-filter-btn');
    const tpl       = document.getElementById('tpl-ac-card');
    const showAllBtn = document.getElementById('ac-show-all');

    // Modal-Elemente
    const modal      = document.getElementById('ac-modal');
    const mBackdrop  = document.getElementById('ac-modal-backdrop');
    const mClose     = document.getElementById('ac-modal-close');
    const mClose2    = document.getElementById('ac-modal-close-2');
    const mIcon      = document.getElementById('ac-modal-icon');
    const mTitle     = document.getElementById('ac-modal-title');
    const mCat       = document.getElementById('ac-modal-cat');
    const mDiff      = document.getElementById('ac-modal-diff');
    const mTime      = document.getElementById('ac-modal-time');
    const mSteps     = document.getElementById('ac-modal-steps');
    const mCta       = document.getElementById('ac-modal-cta');

    // ── Card rendern ──────────────────────────────────────────────
    function renderCard(guide) {
        const clone = tpl.content.cloneNode(true);
        const card  = clone.querySelector('.ac-card');

        card.dataset.category = guide.category ?? '';
        card.dataset.id       = guide.id       ?? '';

        // Icon
        clone.querySelector('.ac-card__icon').textContent = guide.icon ?? '📚';

        // Badges
        const catBadge  = clone.querySelector('.ac-card__cat-badge');
        const diffBadge = clone.querySelector('.ac-card__diff-badge');

        catBadge.textContent  = CAT_LABELS[guide.category] ?? guide.category ?? '—';
        diffBadge.textContent = DIFF_LABELS[guide.difficulty] ?? guide.difficulty ?? '—';
        diffBadge.dataset.diff = guide.difficulty ?? 'basic';

        // Text
        clone.querySelector('.ac-card__title').textContent = guide.title ?? '';
        clone.querySelector('.ac-card__short').textContent = guide.short ?? '';
        clone.querySelector('.ac-card__read-time').textContent = guide.read_time ?? '';

        // Öffnen-Button
        clone.querySelector('.ac-card__open-btn').addEventListener('click', () => openGuide(guide));

        grid.appendChild(clone);
    }

    // ── Guide-Modal öffnen ────────────────────────────────────────
    function openGuide(guide) {
        // Header
        mIcon.textContent  = guide.icon      ?? '📚';
        mTitle.textContent = guide.title     ?? '';
        mTime.textContent  = guide.read_time ?? '';

        mCat.textContent   = CAT_LABELS[guide.category] ?? guide.category ?? '—';
        mDiff.textContent  = DIFF_LABELS[guide.difficulty] ?? guide.difficulty ?? '—';
        mDiff.dataset.diff = guide.difficulty ?? 'basic';

        // Steps aufbauen (DOM-API, kein innerHTML für Step-Texte)
        mSteps.innerHTML = '';
        (guide.steps ?? []).forEach(stepText => {
            const li  = document.createElement('li');
            li.className = 'ac-step';

            const num  = document.createElement('span');
            num.className = 'ac-step__num';
            // Zahl wird via CSS counter-increment gesetzt — kein JS nötig

            const text = document.createElement('span');
            text.className  = 'ac-step__text';
            text.textContent = stepText;

            li.appendChild(num);
            li.appendChild(text);
            mSteps.appendChild(li);
        });

        // CTA
        mCta.textContent = guide.cta_label ?? 'Öffnen';
        mCta.href        = guide.cta_url   ?? '#';

        // Modal anzeigen
        modal.hidden = false;
        modal.scrollTop = 0;
        mClose.focus();
    }

    function closeGuide() { modal.hidden = true; }

    mBackdrop.addEventListener('click', closeGuide);
    mClose.addEventListener('click',   closeGuide);
    mClose2.addEventListener('click',  closeGuide);
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && !modal.hidden) closeGuide();
    });

    // ── Filter ────────────────────────────────────────────────────
    let activeFilter = 'all';

    function applyFilter(filter) {
        activeFilter = filter;
        grid.innerHTML = '';

        const filtered = filter === 'all'
            ? GUIDES
            : GUIDES.filter(g => g.category === filter);

        if (filtered.length === 0) {
            emptyEl.hidden = false;
            countEl.textContent = '';
            filterEl.textContent = '';
        } else {
            emptyEl.hidden = true;
            filtered.forEach(renderCard);
            countEl.textContent = filtered.length + ' Guide' + (filtered.length !== 1 ? 's' : '');
            filterEl.textContent = filter !== 'all' ? '— ' + (CAT_LABELS[filter] ?? filter) : '';
        }

        filterBtns.forEach(btn => {
            btn.classList.toggle('ac-filter-btn--active', btn.dataset.filter === filter);
        });
    }

    filterBtns.forEach(btn => btn.addEventListener('click', () => applyFilter(btn.dataset.filter)));
    showAllBtn?.addEventListener('click', () => applyFilter('all'));

    // ── Init ──────────────────────────────────────────────────────
    applyFilter('all');

})();
</script>

<?php require_once 'includes/footer.php'; ?>
