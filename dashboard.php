<?php
require_once 'includes/config.php';
$pageTitle = 'Dashboard';
require_once 'includes/header.php';
?>

<div class="dashboard-page">

    <!-- ── Hero ─────────────────────────────────────────────────── -->
    <div class="dashboard-hero">
        <div class="dashboard-hero__text">
            <h2 class="dashboard-hero__title">Cinematic Studio Family</h2>
            <p class="dashboard-hero__sub text-muted">
                Dein AI Film Studio für Bilder, Seedance-Videos, Elemente und Creator-Content.
            </p>
        </div>
        <?php if (empty($_SESSION['api_key'])): ?>
            <a href="api-key.php" class="btn btn-secondary btn-sm">⚠ API-Key verbinden</a>
        <?php else: ?>
            <span class="badge-connected">● API verbunden</span>
        <?php endif; ?>
    </div>

    <!-- ── Quick Actions ─────────────────────────────────────────── -->
    <section class="dashboard-section">
        <h3 class="dashboard-section__title">Schnellzugriff</h3>
        <div class="quick-actions">

            <a href="new-project.php" class="quick-action-card quick-action-card--primary">
                <span class="quick-action-card__icon">➕</span>
                <div>
                    <div class="quick-action-card__label">Neues Projekt</div>
                    <div class="quick-action-card__sub">Projekt erstellen</div>
                </div>
            </a>

            <a href="image-studio.php" class="quick-action-card">
                <span class="quick-action-card__icon">🖼️</span>
                <div>
                    <div class="quick-action-card__label">Image Studio</div>
                    <div class="quick-action-card__sub">Bild-Prompts generieren</div>
                </div>
            </a>

            <a href="video-studio.php" class="quick-action-card">
                <span class="quick-action-card__icon">🎬</span>
                <div>
                    <div class="quick-action-card__label">Video Studio</div>
                    <div class="quick-action-card__sub">Seedance-Prompts erstellen</div>
                </div>
            </a>

            <a href="elements.php" class="quick-action-card">
                <span class="quick-action-card__icon">🧩</span>
                <div>
                    <div class="quick-action-card__label">Element Library</div>
                    <div class="quick-action-card__sub">Charaktere &amp; Assets</div>
                </div>
            </a>

            <a href="api-key.php" class="quick-action-card">
                <span class="quick-action-card__icon">🔑</span>
                <div>
                    <div class="quick-action-card__label">API verbinden</div>
                    <div class="quick-action-card__sub">Kie.ai / Seedance Key</div>
                </div>
            </a>

        </div>
    </section>

    <!-- ── Projektübersicht ──────────────────────────────────────── -->
    <section class="dashboard-section">
        <div class="dashboard-section__header">
            <h3 class="dashboard-section__title">Meine Projekte</h3>
            <a href="new-project.php" class="btn btn-primary btn-sm">+ Neues Projekt</a>
        </div>

        <!-- Loading State -->
        <div id="projects-loading" class="projects-loading">
            <span class="projects-loading__spinner"></span>
            <span class="text-muted text-sm">Projekte werden geladen …</span>
        </div>

        <!-- Leerzustand -->
        <div id="projects-empty" class="projects-empty" hidden>
            <div class="projects-empty__icon">🎬</div>
            <p class="projects-empty__text">Du hast noch keine Projekte.</p>
            <p class="text-muted text-sm">Starte dein erstes Cinematic Projekt.</p>
            <a href="new-project.php" class="btn btn-primary" style="margin-top: 16px;">
                ➕ Neues Projekt erstellen
            </a>
        </div>

        <!-- Projekt-Grid -->
        <div id="projects-grid" class="projects-grid" hidden></div>

    </section>

</div><!-- .dashboard-page -->


<!-- ── Projekt-Card Template (kein innerHTML, sichere DOM-Erstellung) ── -->
<template id="tpl-project-card">
    <div class="project-card">
        <div class="project-card__header">
            <div class="project-card__type-badge"></div>
            <span class="project-card__date"></span>
        </div>
        <div class="project-card__title"></div>
        <div class="project-card__description text-muted text-sm"></div>
        <div class="project-card__meta">
            <span class="project-card__updated text-muted text-sm"></span>
        </div>
        <div class="project-card__actions">
            <button class="btn btn-secondary btn-sm js-open">📂 Öffnen</button>
            <button class="btn btn-danger btn-sm js-delete">🗑 Löschen</button>
        </div>
    </div>
</template>


<style>
/* ── Dashboard-spezifische Styles ──────────────────────────── */

/* Hero */
.dashboard-hero {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 32px;
    flex-wrap: wrap;
}
.dashboard-hero__title {
    font-size: 1.6rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 6px;
}
.dashboard-hero__sub {
    font-size: 0.925rem;
    max-width: 560px;
}

/* Sections */
.dashboard-section {
    margin-bottom: 40px;
}
.dashboard-section__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 16px;
}
.dashboard-section__title {
    font-size: 1rem;
    font-weight: 600;
    color: var(--text-secondary);
    letter-spacing: 0.04em;
    text-transform: uppercase;
    font-size: 0.8rem;
    margin-bottom: 16px;
}
.dashboard-section__header .dashboard-section__title {
    margin-bottom: 0;
}

/* Quick Actions */
.quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
    gap: 12px;
}
.quick-action-card {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 16px 18px;
    background: var(--bg-panel);
    border: 1px solid var(--border-color);
    border-radius: var(--radius);
    text-decoration: none;
    color: var(--text-primary);
    transition: background var(--transition), border-color var(--transition), transform var(--transition);
}
.quick-action-card:hover {
    background: var(--bg-elevated);
    border-color: var(--accent-blue);
    transform: translateY(-2px);
}
.quick-action-card--primary {
    border-color: rgba(61, 142, 245, 0.4);
    background: rgba(61, 142, 245, 0.06);
}
.quick-action-card--primary:hover {
    background: rgba(61, 142, 245, 0.12);
}
.quick-action-card__icon {
    font-size: 1.5rem;
    flex-shrink: 0;
}
.quick-action-card__label {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 2px;
}
.quick-action-card__sub {
    font-size: 0.75rem;
    color: var(--text-muted);
}

/* Loading */
.projects-loading {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 32px 0;
}
.projects-loading__spinner {
    width: 18px;
    height: 18px;
    border: 2px solid var(--border-color);
    border-top-color: var(--accent-blue);
    border-radius: 50%;
    animation: spin 0.7s linear infinite;
    flex-shrink: 0;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* Leerzustand */
.projects-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 60px 24px;
    text-align: center;
    background: var(--bg-panel);
    border: 1px solid var(--border-color);
    border-radius: var(--radius);
}
.projects-empty__icon {
    font-size: 3rem;
    margin-bottom: 16px;
    opacity: 0.5;
}
.projects-empty__text {
    font-size: 1rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 6px;
}

/* Projekt-Grid */
.projects-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 16px;
}

/* Projekt-Card */
.project-card {
    background: var(--bg-panel);
    border: 1px solid var(--border-color);
    border-radius: var(--radius);
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    transition: border-color var(--transition), transform var(--transition);
}
.project-card:hover {
    border-color: var(--accent-blue);
    transform: translateY(-2px);
}
.project-card__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}
.project-card__type-badge {
    font-size: 0.7rem;
    font-weight: 600;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    background: var(--accent-blue-glow);
    color: var(--accent-blue);
    border-radius: var(--radius-sm);
    padding: 3px 8px;
}
.project-card__date {
    font-size: 0.72rem;
    color: var(--text-muted);
    flex-shrink: 0;
}
.project-card__title {
    font-size: 0.975rem;
    font-weight: 600;
    color: var(--text-primary);
    line-height: 1.4;
}
.project-card__description {
    line-height: 1.5;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    min-height: 1.5em;
}
.project-card__meta {
    margin-top: 2px;
}
.project-card__actions {
    display: flex;
    gap: 8px;
    margin-top: 4px;
}
.project-card__actions .btn {
    flex: 1;
    justify-content: center;
}

/* Responsive */
@media (max-width: 768px) {
    .dashboard-hero {
        flex-direction: column;
        align-items: flex-start;
    }
    .quick-actions {
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    }
    .projects-grid {
        grid-template-columns: 1fr;
    }
}
</style>


<script>
(function () {

    // ── Projekt-Typ Labels ─────────────────────────────────────────
    const TYPE_LABELS = {
        'image':           '🖼 Bild',
        'video':           '🎬 Video',
        'tiktok':          '🎵 TikTok',
        'trailer':         '🎞 Trailer',
        'cinematic_scene': '🎬 Cinematic',
        'action_trailer':  '💥 Action',
        'product_ad':      '📦 Produkt',
        'character':       '🧍 Charakter',
        'mixed':           '✨ Mixed',
    };

    function typeLabel(type) {
        return TYPE_LABELS[type] ?? type ?? '—';
    }

    // ── Datum formatieren ─────────────────────────────────────────
    function fmtDate(iso) {
        if (!iso) return '—';
        try {
            return new Date(iso).toLocaleDateString('de-DE', {
                day: '2-digit', month: '2-digit', year: 'numeric'
            });
        } catch { return iso; }
    }

    // ── Elemente ──────────────────────────────────────────────────
    const elLoading = document.getElementById('projects-loading');
    const elEmpty   = document.getElementById('projects-empty');
    const elGrid    = document.getElementById('projects-grid');
    const tpl       = document.getElementById('tpl-project-card');

    // ── Projekt-Card rendern (kein innerHTML — nur textContent/DOM) ─
    function renderProjectCard(project) {
        const clone = tpl.content.cloneNode(true);
        const card  = clone.querySelector('.project-card');

        // Typ-Badge
        clone.querySelector('.project-card__type-badge').textContent = typeLabel(project.type);

        // Erstellungsdatum
        clone.querySelector('.project-card__date').textContent = fmtDate(project.created_at);

        // Titel
        clone.querySelector('.project-card__title').textContent = project.title ?? '(kein Titel)';

        // Beschreibung
        const descEl = clone.querySelector('.project-card__description');
        if (project.description && project.description.trim() !== '') {
            descEl.textContent = project.description;
        } else {
            descEl.textContent = 'Keine Beschreibung';
            descEl.style.fontStyle = 'italic';
        }

        // Zuletzt aktualisiert
        clone.querySelector('.project-card__updated').textContent =
            'Geändert: ' + fmtDate(project.updated_at);

        // Button: Öffnen → new-project.php?id=…  (Phase 2 Platzhalter)
        clone.querySelector('.js-open').addEventListener('click', () => {
            window.location.href = 'new-project.php?id=' + encodeURIComponent(project.id);
        });

        // Button: Löschen
        clone.querySelector('.js-delete').addEventListener('click', () => {
            deleteProject(project.id, card);
        });

        elGrid.appendChild(clone);
    }

    // ── Projekte laden ────────────────────────────────────────────
    async function loadProjects() {
        elLoading.hidden = false;
        elEmpty.hidden   = true;
        elGrid.hidden    = true;
        elGrid.innerHTML = '';

        try {
            const res  = await fetch('api/projects.php?action=list');
            const data = await res.json();

            if (!data.success) {
                Toast.error(data.error ?? 'Projekte konnten nicht geladen werden.');
                elLoading.hidden = true;
                return;
            }

            elLoading.hidden = true;

            if (!data.data || data.data.length === 0) {
                elEmpty.hidden = false;
                return;
            }

            elGrid.hidden = false;
            data.data.forEach(renderProjectCard);

        } catch {
            elLoading.hidden = true;
            Toast.error('Netzwerkfehler — Projekte konnten nicht geladen werden.');
        }
    }

    // ── Projekt löschen ───────────────────────────────────────────
    async function deleteProject(id, cardEl) {
        if (!confirm('Projekt wirklich löschen? Diese Aktion kann nicht rückgängig gemacht werden.')) {
            return;
        }

        // Karte sofort visuell deaktivieren
        if (cardEl) {
            cardEl.style.opacity = '0.4';
            cardEl.style.pointerEvents = 'none';
        }

        try {
            const res  = await fetch('api/projects.php', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify({ action: 'delete', id }),
            });
            const data = await res.json();

            if (!data.success) {
                Toast.error(data.error ?? 'Löschen fehlgeschlagen.');
                // Karte wieder aktivieren
                if (cardEl) {
                    cardEl.style.opacity = '';
                    cardEl.style.pointerEvents = '';
                }
                return;
            }

            // Karte mit Animation entfernen
            if (cardEl) {
                cardEl.style.transition = 'opacity 0.3s, transform 0.3s';
                cardEl.style.opacity    = '0';
                cardEl.style.transform  = 'scale(0.95)';
                setTimeout(() => {
                    cardEl.remove();
                    // Leerzustand prüfen
                    if (elGrid.children.length === 0) {
                        elGrid.hidden  = true;
                        elEmpty.hidden = false;
                    }
                }, 320);
            }

            Toast.success('Projekt gelöscht.');

        } catch {
            Toast.error('Netzwerkfehler — Projekt konnte nicht gelöscht werden.');
            if (cardEl) {
                cardEl.style.opacity = '';
                cardEl.style.pointerEvents = '';
            }
        }
    }

    // ── Init ──────────────────────────────────────────────────────
    loadProjects();

})();
</script>

<?php require_once 'includes/footer.php'; ?>
