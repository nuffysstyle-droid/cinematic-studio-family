<?php
require_once 'includes/config.php';

$pageTitle = 'Sofort fertige Videos';
require_once 'includes/header.php';

// ── Demo-Videos aus JSON laden ─────────────────────────────────────
$videosFile = DATA_PATH . 'ready-videos.json';
$videos     = [];
if (file_exists($videosFile)) {
    $decoded = json_decode(file_get_contents($videosFile), true);
    $videos  = is_array($decoded) ? $decoded : [];
}

// ── Kategorien definieren ──────────────────────────────────────────
$categories = [
    'all'        => ['label' => 'Alle',              'icon' => '🎬'],
    'tiktok_ads' => ['label' => 'TikTok Ads',        'icon' => '📱'],
    'auto'       => ['label' => 'Auto Videos',       'icon' => '🚗'],
    'horror'     => ['label' => 'Horror Trailer',    'icon' => '👁'],
    'luxury'     => ['label' => 'Luxury / Lifestyle','icon' => '💎'],
    'anime'      => ['label' => 'Anime Live Action', 'icon' => '⚔️'],
    'cinematic'  => ['label' => 'Cinematic',         'icon' => '🏔'],
    'branding'   => ['label' => 'Branding Videos',   'icon' => '✨'],
];
?>

<div class="rv-page">

    <!-- ── Hero ─────────────────────────────────────────────────── -->
    <div class="rv-hero">
        <div class="rv-hero__text">
            <div class="rv-hero__eyebrow">Premium Showroom</div>
            <h2 class="rv-hero__title">Sofort fertige Videos</h2>
            <p class="rv-hero__sub">
                Kein Prompt. Kein Stress.<br>
                Einfach downloaden oder ein ähnliches Video anfragen.
            </p>
            <div class="rv-hero__ctas">
                <a href="#rv-gallery" class="btn btn-primary btn-lg">🎬 Videos ansehen</a>
                <button id="btn-request-similar" class="btn btn-secondary btn-lg">📩 Ähnliches Video anfragen</button>
            </div>
        </div>
        <div class="rv-hero__badge-area">
            <div class="rv-stat">
                <div class="rv-stat__num"><?= count($videos) ?></div>
                <div class="rv-stat__label">Premium Videos</div>
            </div>
            <div class="rv-stat">
                <div class="rv-stat__num">8</div>
                <div class="rv-stat__label">Kategorien</div>
            </div>
            <div class="rv-stat">
                <div class="rv-stat__num">5–15s</div>
                <div class="rv-stat__label">Sofort einsatzbereit</div>
            </div>
        </div>
    </div>

    <!-- ── Filter-Leiste ─────────────────────────────────────────── -->
    <div class="rv-filters" id="rv-gallery">
        <?php foreach ($categories as $key => $cat): ?>
        <button
            class="rv-filter-btn <?= $key === 'all' ? 'rv-filter-btn--active' : '' ?>"
            data-filter="<?= $key ?>"
        >
            <?= $cat['icon'] ?> <?= htmlspecialchars($cat['label']) ?>
        </button>
        <?php endforeach; ?>
    </div>

    <!-- ── Ergebnis-Info ─────────────────────────────────────────── -->
    <div class="rv-results-info">
        <span id="rv-count" class="text-muted text-sm"></span>
    </div>

    <!-- ── Galerie-Grid ──────────────────────────────────────────── -->
    <div id="rv-grid" class="rv-grid"></div>

    <!-- ── Leerzustand ───────────────────────────────────────────── -->
    <div id="rv-empty" class="rv-empty" hidden>
        <div class="rv-empty__icon">🎬</div>
        <p>Keine Videos in dieser Kategorie.</p>
        <button class="btn btn-secondary" onclick="document.querySelector('[data-filter=all]').click()">
            Alle anzeigen
        </button>
    </div>

    <!-- ── USP-Leiste ────────────────────────────────────────────── -->
    <div class="rv-usp-bar">
        <div class="rv-usp-item">
            <span class="rv-usp-item__icon">⚡</span>
            <div>
                <div class="rv-usp-item__title">Sofort einsatzbereit</div>
                <div class="rv-usp-item__sub text-muted">Kein Warten, kein Generieren</div>
            </div>
        </div>
        <div class="rv-usp-item">
            <span class="rv-usp-item__icon">🎨</span>
            <div>
                <div class="rv-usp-item__title">Premium Qualität</div>
                <div class="rv-usp-item__sub text-muted">Seedance Standard &amp; Super</div>
            </div>
        </div>
        <div class="rv-usp-item">
            <span class="rv-usp-item__icon">🔧</span>
            <div>
                <div class="rv-usp-item__title">Anpassbar</div>
                <div class="rv-usp-item__sub text-muted">Ähnliches Video auf Anfrage</div>
            </div>
        </div>
        <div class="rv-usp-item">
            <span class="rv-usp-item__icon">📱</span>
            <div>
                <div class="rv-usp-item__title">Alle Formate</div>
                <div class="rv-usp-item__sub text-muted">TikTok · Reels · Stories · YouTube</div>
            </div>
        </div>
    </div>

</div><!-- .rv-page -->


<!-- ── Video-Card Template ────────────────────────────────────── -->
<template id="tpl-rv-card">
    <div class="rv-card" data-category="">

        <!-- Thumbnail -->
        <div class="rv-card__thumb">
            <span class="rv-card__thumb-icon"></span>
            <div class="rv-card__overlay">
                <button class="rv-card__play-btn">▶</button>
            </div>
            <div class="rv-card__duration-badge"></div>
            <div class="rv-card__price-badge"></div>
        </div>

        <!-- Info -->
        <div class="rv-card__body">
            <div class="rv-card__meta">
                <span class="rv-card__category-badge"></span>
                <span class="rv-card__style text-muted text-sm"></span>
            </div>
            <div class="rv-card__title"></div>
            <div class="rv-card__desc text-muted text-sm"></div>

            <!-- Aktionen -->
            <div class="rv-card__actions">
                <button class="btn btn-primary btn-sm js-request-this">📩 Dieses Video anfragen</button>
                <button class="btn btn-secondary btn-sm js-request-similar">✦ Ähnliches erstellen</button>
            </div>
            <button class="rv-card__prompt-toggle js-prompt-toggle" hidden>▸ Prompt ansehen</button>
            <div class="rv-card__prompt-box js-prompt-box" hidden></div>
        </div>

    </div>
</template>


<!-- ── Anfrage-Modal ──────────────────────────────────────────── -->
<div id="modal-request" class="modal" role="dialog" aria-modal="true" aria-labelledby="modal-request-title" hidden>
    <div class="modal-backdrop" data-modal-close="modal-request"></div>
    <div class="modal-box">
        <div class="modal-header">
            <h3 class="modal-title" id="modal-request-title">Video anfragen</h3>
            <button class="modal-close" data-modal-close="modal-request" aria-label="Schließen">✕</button>
        </div>
        <div class="modal-body">
            <p id="modal-request-context" class="text-muted text-sm" style="margin-bottom: 16px;"></p>
            <div class="form-group">
                <label for="modal-email">Deine E-Mail oder Telegram</label>
                <input type="text" id="modal-email" placeholder="name@example.com oder @username">
            </div>
            <div class="form-group">
                <label for="modal-message">Beschreibung / Anpassungswünsche</label>
                <textarea id="modal-message" rows="3" placeholder="z.B. gleicher Stil, aber mit meinem Logo und roter Farbe …"></textarea>
            </div>
            <p class="text-muted text-sm">💡 Nach dem Absenden melden wir uns bei dir. Keine Zahlung nötig.</p>
        </div>
        <div class="modal-footer">
            <button id="modal-submit" class="btn btn-primary">📩 Anfrage senden</button>
            <button class="btn btn-secondary" data-modal-close="modal-request">Abbrechen</button>
        </div>
    </div>
</div>


<style>
/* ── Ready Videos Showroom ──────────────────────────────────── */

/* Hero */
.rv-hero {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 32px;
    margin-bottom: 36px;
    padding: 32px;
    background: linear-gradient(135deg, rgba(61,142,245,0.06) 0%, rgba(245,131,61,0.04) 100%);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    flex-wrap: wrap;
}
.rv-hero__eyebrow {
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: var(--accent-orange);
    margin-bottom: 8px;
}
.rv-hero__title {
    font-size: 2rem;
    font-weight: 800;
    color: var(--text-primary);
    line-height: 1.2;
    margin-bottom: 12px;
}
.rv-hero__sub {
    font-size: 1rem;
    color: var(--text-secondary);
    line-height: 1.6;
    margin-bottom: 20px;
}
.rv-hero__ctas { display: flex; gap: 12px; flex-wrap: wrap; }

/* Stat-Badges */
.rv-hero__badge-area {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
    align-items: flex-start;
    padding-top: 4px;
}
.rv-stat {
    background: var(--bg-elevated);
    border: 1px solid var(--border-color);
    border-radius: var(--radius);
    padding: 14px 20px;
    text-align: center;
    min-width: 100px;
}
.rv-stat__num {
    font-size: 1.6rem;
    font-weight: 800;
    color: var(--accent-blue);
    line-height: 1;
    margin-bottom: 4px;
}
.rv-stat__label { font-size: 0.72rem; color: var(--text-muted); font-weight: 500; }

/* Filter */
.rv-filters {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-bottom: 12px;
    scroll-margin-top: 20px;
}
.rv-filter-btn {
    padding: 7px 14px;
    background: var(--bg-panel);
    border: 1px solid var(--border-color);
    border-radius: 999px;
    font-size: 0.82rem;
    font-weight: 500;
    color: var(--text-secondary);
    cursor: pointer;
    transition: background var(--transition), border-color var(--transition), color var(--transition);
    white-space: nowrap;
}
.rv-filter-btn:hover {
    background: var(--bg-elevated);
    border-color: var(--accent-blue);
    color: var(--text-primary);
}
.rv-filter-btn--active {
    background: var(--accent-blue-glow);
    border-color: var(--accent-blue);
    color: var(--accent-blue);
    font-weight: 600;
}

/* Results info */
.rv-results-info { margin-bottom: 16px; min-height: 20px; }

/* Grid */
.rv-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

/* Leerzustand */
.rv-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    padding: 60px 24px;
    text-align: center;
    color: var(--text-muted);
    margin-bottom: 40px;
}
.rv-empty__icon { font-size: 3rem; opacity: 0.4; }

/* ── Video Card ─────────────────────────────────────────────── */
.rv-card {
    background: var(--bg-panel);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transition: border-color var(--transition), transform var(--transition), box-shadow var(--transition);
}
.rv-card:hover {
    border-color: rgba(61,142,245,0.4);
    transform: translateY(-3px);
    box-shadow: 0 8px 32px rgba(0,0,0,0.4);
}

/* Thumbnail */
.rv-card__thumb {
    position: relative;
    height: 160px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    flex-shrink: 0;
}
.rv-card__thumb-icon {
    font-size: 3.5rem;
    opacity: 0.6;
    pointer-events: none;
    transition: opacity var(--transition), transform var(--transition);
}
.rv-card:hover .rv-card__thumb-icon {
    opacity: 0.3;
    transform: scale(1.1);
}

/* Play-Overlay */
.rv-card__overlay {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(0,0,0,0.0);
    transition: background var(--transition);
}
.rv-card:hover .rv-card__overlay { background: rgba(0,0,0,0.35); }

.rv-card__play-btn {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: rgba(255,255,255,0.15);
    border: 2px solid rgba(255,255,255,0.4);
    color: #fff;
    font-size: 1.1rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transform: scale(0.8);
    transition: opacity var(--transition), transform var(--transition), background var(--transition);
    padding-left: 3px;
}
.rv-card:hover .rv-card__play-btn {
    opacity: 1;
    transform: scale(1);
}
.rv-card__play-btn:hover { background: rgba(255,255,255,0.25); }

/* Badges auf Thumbnail */
.rv-card__duration-badge, .rv-card__price-badge {
    position: absolute;
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 0.05em;
    border-radius: var(--radius-sm);
    padding: 3px 7px;
}
.rv-card__duration-badge {
    bottom: 8px;
    left: 8px;
    background: rgba(0,0,0,0.7);
    color: #fff;
}
.rv-card__price-badge {
    top: 8px;
    right: 8px;
    background: var(--accent-orange);
    color: #fff;
}
.rv-card__price-badge--standard {
    background: var(--accent-blue);
}

/* Card Body */
.rv-card__body {
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    flex: 1;
}
.rv-card__meta { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
.rv-card__category-badge {
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    background: var(--bg-elevated);
    border: 1px solid var(--border-color);
    color: var(--text-muted);
    border-radius: var(--radius-sm);
    padding: 2px 7px;
}
.rv-card__title {
    font-size: 0.9rem;
    font-weight: 700;
    color: var(--text-primary);
    line-height: 1.4;
}
.rv-card__desc {
    line-height: 1.5;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    flex: 1;
}
.rv-card__actions { display: flex; gap: 8px; margin-top: 4px; }
.rv-card__actions .btn { flex: 1; justify-content: center; font-size: 0.75rem; padding: 6px 8px; }

/* Prompt Toggle */
.rv-card__prompt-toggle {
    font-size: 0.72rem;
    color: var(--accent-blue);
    background: none;
    border: none;
    cursor: pointer;
    padding: 0;
    text-align: left;
    display: flex;
    align-items: center;
    gap: 4px;
}
.rv-card__prompt-toggle:hover { text-decoration: underline; }
.rv-card__prompt-box {
    background: var(--bg-elevated);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-sm);
    padding: 10px;
    font-size: 0.72rem;
    color: var(--text-secondary);
    line-height: 1.5;
}

/* ── USP Bar ─────────────────────────────────────────────────── */
.rv-usp-bar {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-top: 16px;
    padding: 24px;
    background: var(--bg-panel);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
}
.rv-usp-item {
    display: flex;
    align-items: center;
    gap: 12px;
}
.rv-usp-item__icon { font-size: 1.6rem; flex-shrink: 0; }
.rv-usp-item__title { font-size: 0.875rem; font-weight: 600; color: var(--text-primary); margin-bottom: 2px; }
.rv-usp-item__sub { font-size: 0.75rem; }

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
    background: rgba(0,0,0,0.7);
    backdrop-filter: blur(4px);
    cursor: pointer;
}
.modal-box {
    position: relative;
    background: var(--bg-panel);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    width: 100%;
    max-width: 480px;
    box-shadow: var(--shadow);
    z-index: 1;
}
.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 24px 16px;
    border-bottom: 1px solid var(--border-color);
}
.modal-title { font-size: 1rem; font-weight: 700; color: var(--text-primary); }
.modal-close {
    font-size: 1rem;
    color: var(--text-muted);
    background: none;
    border: none;
    cursor: pointer;
    padding: 4px 8px;
    border-radius: var(--radius-sm);
    line-height: 1;
    transition: color var(--transition), background var(--transition);
}
.modal-close:hover { color: var(--text-primary); background: var(--bg-elevated); }
.modal-body   { padding: 20px 24px; }
.modal-footer { padding: 16px 24px 20px; display: flex; gap: 12px; border-top: 1px solid var(--border-color); }

/* ── Responsive ─────────────────────────────────────────────── */
@media (max-width: 1100px) {
    .rv-usp-bar { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 768px) {
    .rv-hero { flex-direction: column; padding: 20px; }
    .rv-hero__title { font-size: 1.5rem; }
    .rv-hero__badge-area { width: 100%; }
    .rv-stat { flex: 1; min-width: 80px; }
    .rv-grid { grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); }
    .rv-usp-bar { grid-template-columns: 1fr 1fr; gap: 12px; padding: 16px; }
}
@media (max-width: 480px) {
    .rv-hero__ctas { flex-direction: column; }
    .rv-grid { grid-template-columns: 1fr; }
    .rv-usp-bar { grid-template-columns: 1fr; }
    .rv-card__actions { flex-direction: column; }
}
</style>


<script>
(function () {

    // ── Video-Daten aus PHP ins JS ─────────────────────────────────
    const VIDEOS = <?php echo json_encode($videos, JSON_UNESCAPED_UNICODE); ?>;

    // ── Kategorie-Labels ─────────────────────────────────────────
    const CAT_LABELS = {
        'tiktok_ads': 'TikTok Ads',
        'auto':       'Auto',
        'horror':     'Horror',
        'luxury':     'Luxury',
        'anime':      'Anime',
        'cinematic':  'Cinematic',
        'branding':   'Branding',
    };

    // ── DOM ───────────────────────────────────────────────────────
    const grid      = document.getElementById('rv-grid');
    const emptyEl   = document.getElementById('rv-empty');
    const countEl   = document.getElementById('rv-count');
    const filterBtns = document.querySelectorAll('.rv-filter-btn');
    const tpl        = document.getElementById('tpl-rv-card');

    const modal         = document.getElementById('modal-request');
    const modalContext  = document.getElementById('modal-request-context');
    const modalEmail    = document.getElementById('modal-email');
    const modalMessage  = document.getElementById('modal-message');
    const modalSubmit   = document.getElementById('modal-submit');

    // ── Anfrage-Button (Hero) ─────────────────────────────────────
    document.getElementById('btn-request-similar')?.addEventListener('click', () => {
        openModal('Allgemeine Video-Anfrage — beschreibe deine Idee.');
    });

    // ── Modal öffnen / schließen ──────────────────────────────────
    function openModal(context) {
        modalContext.textContent = context;
        modalEmail.value    = '';
        modalMessage.value  = '';
        modal.hidden        = false;
        modalEmail.focus();
    }

    function closeModal() { modal.hidden = true; }

    document.querySelectorAll('[data-modal-close="modal-request"]').forEach(btn => {
        btn.addEventListener('click', closeModal);
    });

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && !modal.hidden) closeModal();
    });

    modalSubmit.addEventListener('click', () => {
        const contact = modalEmail.value.trim();
        if (!contact) {
            Toast.warning('Bitte E-Mail oder Telegram angeben.');
            modalEmail.focus();
            return;
        }
        // Kein echter Versand — Toast + Schließen
        Toast.success('Anfrage gespeichert! Wir melden uns bald.');
        closeModal();
    });

    // ── Card rendern ──────────────────────────────────────────────
    function renderCard(video) {
        const clone = tpl.content.cloneNode(true);
        const card  = clone.querySelector('.rv-card');

        card.dataset.category = video.category ?? '';

        // Thumbnail Hintergrund
        const thumb = clone.querySelector('.rv-card__thumb');
        thumb.style.backgroundColor = video.thumbnail_color ?? '#0d1017';
        clone.querySelector('.rv-card__thumb-icon').textContent = video.thumbnail_icon ?? '🎬';

        // Play-Button (Info-Toast — kein echter Player)
        clone.querySelector('.rv-card__play-btn').addEventListener('click', () => {
            Toast.info('Preview-Player wird in Phase 4 angebunden.');
        });

        // Dauer + Preis-Badge
        clone.querySelector('.rv-card__duration-badge').textContent = video.duration ?? '';

        const priceBadge = clone.querySelector('.rv-card__price-badge');
        priceBadge.textContent = video.price_label ?? '';
        if ((video.price_label ?? '').toLowerCase() === 'standard') {
            priceBadge.classList.add('rv-card__price-badge--standard');
        }

        // Kategorie + Stil
        clone.querySelector('.rv-card__category-badge').textContent = CAT_LABELS[video.category] ?? video.category ?? '—';
        clone.querySelector('.rv-card__style').textContent          = video.style ?? '';

        // Titel + Beschreibung (textContent — kein HTML-Injection)
        clone.querySelector('.rv-card__title').textContent = video.title ?? '';
        clone.querySelector('.rv-card__desc').textContent  = video.description ?? '';

        // Aktionen
        clone.querySelector('.js-request-this').addEventListener('click', () => {
            openModal('Du fragst nach: „' + (video.title ?? '') + '"');
        });
        clone.querySelector('.js-request-similar').addEventListener('click', () => {
            openModal('Ähnliches Video wie: „' + (video.title ?? '') + '" — beschreibe deine Anpassungen.');
        });

        // Prompt (optional)
        const promptToggle = clone.querySelector('.js-prompt-toggle');
        const promptBox    = clone.querySelector('.js-prompt-box');
        if (video.prompt) {
            promptToggle.hidden = false;
            promptBox.textContent = video.prompt;
            promptToggle.addEventListener('click', () => {
                const open = promptBox.hidden;
                promptBox.hidden       = !open;
                promptToggle.textContent = open ? '▾ Prompt ausblenden' : '▸ Prompt ansehen';
            });
        }

        grid.appendChild(clone);
    }

    // ── Filter + Render ───────────────────────────────────────────
    let activeFilter = 'all';

    function applyFilter(filter) {
        activeFilter = filter;
        grid.innerHTML = '';

        const filtered = filter === 'all'
            ? VIDEOS
            : VIDEOS.filter(v => v.category === filter);

        if (filtered.length === 0) {
            emptyEl.hidden = false;
            countEl.textContent = '';
        } else {
            emptyEl.hidden = true;
            filtered.forEach(renderCard);
            countEl.textContent = filtered.length + ' Video' + (filtered.length !== 1 ? 's' : '') + ' gefunden';
        }

        // Filter-Buttons aktualisieren
        filterBtns.forEach(btn => {
            btn.classList.toggle('rv-filter-btn--active', btn.dataset.filter === filter);
        });
    }

    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => applyFilter(btn.dataset.filter));
    });

    // ── Init ──────────────────────────────────────────────────────
    applyFilter('all');

})();
</script>

<?php require_once 'includes/footer.php'; ?>
