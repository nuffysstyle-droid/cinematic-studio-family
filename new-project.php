<?php
require_once 'includes/config.php';
$pageTitle = 'Neues Projekt';
require_once 'includes/header.php';
?>

<div class="np-page">

    <!-- ── Hero ─────────────────────────────────────────────────── -->
    <div class="np-hero">
        <div>
            <h2 class="np-hero__title" id="page-headline">Neues Projekt</h2>
            <p class="np-hero__sub text-muted">
                Lege ein neues Cinematic Projekt an oder bearbeite ein bestehendes Projekt.
            </p>
        </div>
        <a href="dashboard.php" class="btn btn-secondary btn-sm">← Zurück zum Dashboard</a>
    </div>

    <!-- ── Formular-Card ─────────────────────────────────────────── -->
    <div class="np-layout">
        <div class="card np-card">

            <!-- Loading-Overlay (Edit-Modus: Projekt wird geladen) -->
            <div id="form-loading" class="np-loading" hidden>
                <span class="np-loading__spinner"></span>
                <span class="text-muted text-sm">Projekt wird geladen …</span>
            </div>

            <!-- Formular -->
            <form id="project-form" novalidate>

                <!-- Titel -->
                <div class="form-group">
                    <label for="field-title">
                        Titel <span class="np-required">*</span>
                    </label>
                    <input
                        type="text"
                        id="field-title"
                        placeholder="z.B. Sommerurlaub 2026 – Cinematic Edit"
                        maxlength="120"
                        autocomplete="off"
                    >
                    <span id="title-error" class="np-field-error" hidden>Bitte einen Titel eingeben.</span>
                </div>

                <!-- Typ -->
                <div class="form-group">
                    <label for="field-type">
                        Projekt-Typ <span class="np-required">*</span>
                    </label>
                    <select id="field-type">
                        <option value="">— Typ wählen —</option>
                        <option value="image">🖼 Image Projekt</option>
                        <option value="video">🎬 Video Projekt</option>
                        <option value="tiktok">🎵 TikTok Projekt</option>
                        <option value="animation">✨ Animation Auftrag</option>
                        <option value="sticker">🎨 Sticker Auftrag</option>
                        <option value="ready_video">📹 Sofort fertiges Video</option>
                        <option value="trailer">🎞 Trailer Projekt</option>
                    </select>
                    <span id="type-error" class="np-field-error" hidden>Bitte einen Typ auswählen.</span>
                </div>

                <!-- Beschreibung -->
                <div class="form-group">
                    <label for="field-description">
                        Beschreibung <span class="text-muted" style="font-weight:400;">(optional)</span>
                    </label>
                    <textarea
                        id="field-description"
                        rows="4"
                        placeholder="Kurze Beschreibung des Projekts, Ziel, Stil, besondere Anforderungen …"
                        maxlength="600"
                    ></textarea>
                    <span id="desc-counter" class="text-sm text-muted" style="text-align:right; margin-top:4px;"></span>
                </div>

                <!-- Aktionen -->
                <div class="np-actions">
                    <button type="submit" id="btn-submit" class="btn btn-primary btn-lg">
                        <span id="btn-submit-text">✦ Projekt erstellen</span>
                    </button>
                    <a href="dashboard.php" class="btn btn-secondary">Abbrechen</a>
                </div>

            </form>

        </div><!-- .np-card -->

        <!-- ── Seiteninfo ──────────────────────────────────────── -->
        <div class="np-info">
            <div class="card np-info-card">
                <div class="card-header">
                    <span class="card-title">💡 Hinweise</span>
                </div>
                <div class="card-body">
                    <ul class="np-tips">
                        <li>Wähle den Typ, der am besten zum geplanten Inhalt passt.</li>
                        <li>Titel und Typ können später im Dashboard geändert werden.</li>
                        <li>Beschreibung hilft dir, den Überblick zu behalten.</li>
                        <li>Ein Projekt fasst alle Prompts, Elemente und Exports zusammen.</li>
                    </ul>
                </div>
            </div>

            <div class="card np-info-card np-type-guide">
                <div class="card-header">
                    <span class="card-title">📋 Typ-Übersicht</span>
                </div>
                <div class="card-body">
                    <div class="type-guide-list">
                        <div class="type-guide-item">
                            <span class="type-guide-icon">🖼</span>
                            <div>
                                <div class="type-guide-name">Image Projekt</div>
                                <div class="type-guide-desc text-muted text-sm">Charaktere, Produkte, Szenen</div>
                            </div>
                        </div>
                        <div class="type-guide-item">
                            <span class="type-guide-icon">🎬</span>
                            <div>
                                <div class="type-guide-name">Video Projekt</div>
                                <div class="type-guide-desc text-muted text-sm">Seedance-Clips, Cinematic Scenes</div>
                            </div>
                        </div>
                        <div class="type-guide-item">
                            <span class="type-guide-icon">🎵</span>
                            <div>
                                <div class="type-guide-name">TikTok Projekt</div>
                                <div class="type-guide-desc text-muted text-sm">Kurzvideos, Creator Content</div>
                            </div>
                        </div>
                        <div class="type-guide-item">
                            <span class="type-guide-icon">🎞</span>
                            <div>
                                <div class="type-guide-name">Trailer Projekt</div>
                                <div class="type-guide-desc text-muted text-sm">Film-Trailer, Story-Reels</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div><!-- .np-info -->

    </div><!-- .np-layout -->

</div><!-- .np-page -->


<style>
/* ── new-project.php spezifisch ────────────────────────────── */

/* Hero */
.np-hero {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 28px;
    flex-wrap: wrap;
}
.np-hero__title {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 6px;
}
.np-hero__sub {
    font-size: 0.9rem;
}

/* Layout: Formular + Seiteninfo */
.np-layout {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 24px;
    align-items: start;
}

/* Formular-Card */
.np-card {
    padding: 28px;
}

/* Required-Marker */
.np-required {
    color: var(--accent-orange);
    margin-left: 2px;
}

/* Fehler-Meldung unter Feld */
.np-field-error {
    font-size: 0.78rem;
    color: #f56565;
    margin-top: 4px;
}

/* Beschreibungs-Zähler */
#desc-counter {
    display: block;
    text-align: right;
}

/* Aktionsbuttons */
.np-actions {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-top: 8px;
}

/* Loading-Overlay */
.np-loading {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 0 24px;
}
.np-loading__spinner {
    width: 18px;
    height: 18px;
    border: 2px solid var(--border-color);
    border-top-color: var(--accent-blue);
    border-radius: 50%;
    animation: np-spin 0.7s linear infinite;
    flex-shrink: 0;
}
@keyframes np-spin { to { transform: rotate(360deg); } }

/* Seiteninfo */
.np-info {
    display: flex;
    flex-direction: column;
    gap: 16px;
}
.np-info-card {
    padding: 20px;
}
.np-tips {
    display: flex;
    flex-direction: column;
    gap: 8px;
    padding-left: 16px;
    list-style: disc;
    color: var(--text-secondary);
    font-size: 0.85rem;
    line-height: 1.55;
}

/* Typ-Übersicht */
.type-guide-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.type-guide-item {
    display: flex;
    align-items: center;
    gap: 12px;
}
.type-guide-icon {
    font-size: 1.25rem;
    width: 28px;
    text-align: center;
    flex-shrink: 0;
}
.type-guide-name {
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 2px;
}
.type-guide-desc {
    font-size: 0.75rem;
}

/* Responsive */
@media (max-width: 900px) {
    .np-layout {
        grid-template-columns: 1fr;
    }
    .np-info {
        order: -1; /* Info-Box oben auf Mobile */
        display: none; /* Auf kleinen Screens ausblenden — Platz sparen */
    }
}
@media (max-width: 600px) {
    .np-hero {
        flex-direction: column;
        align-items: flex-start;
    }
    .np-actions {
        flex-direction: column;
        align-items: stretch;
    }
    .np-actions .btn {
        justify-content: center;
    }
}
</style>


<script>
(function () {

    // ── Elemente ──────────────────────────────────────────────────
    const headline    = document.getElementById('page-headline');
    const formLoading = document.getElementById('form-loading');
    const form        = document.getElementById('project-form');
    const fieldTitle  = document.getElementById('field-title');
    const fieldType   = document.getElementById('field-type');
    const fieldDesc   = document.getElementById('field-description');
    const titleError  = document.getElementById('title-error');
    const typeError   = document.getElementById('type-error');
    const descCounter = document.getElementById('desc-counter');
    const btnSubmit   = document.getElementById('btn-submit');
    const btnText     = document.getElementById('btn-submit-text');

    // ── Modus erkennen ────────────────────────────────────────────
    const params  = new URLSearchParams(window.location.search);
    const editId  = params.get('id') ?? '';
    const isEdit  = editId !== '';

    // ── UI an Modus anpassen ──────────────────────────────────────
    if (isEdit) {
        headline.textContent  = 'Projekt bearbeiten';
        btnText.textContent   = '💾 Projekt speichern';
    }

    // ── Beschreibungs-Zeichenzähler ───────────────────────────────
    function updateDescCounter() {
        const len = fieldDesc.value.length;
        const max = parseInt(fieldDesc.getAttribute('maxlength') ?? '600', 10);
        descCounter.textContent = len + ' / ' + max;
        descCounter.style.color = len > max * 0.9 ? 'var(--accent-orange)' : '';
    }
    fieldDesc.addEventListener('input', updateDescCounter);
    updateDescCounter();

    // ── Validierung ───────────────────────────────────────────────
    function validate() {
        let ok = true;

        if (fieldTitle.value.trim() === '') {
            titleError.hidden = false;
            fieldTitle.style.borderColor = '#f56565';
            ok = false;
        } else {
            titleError.hidden = true;
            fieldTitle.style.borderColor = '';
        }

        if (fieldType.value === '') {
            typeError.hidden = false;
            fieldType.style.borderColor = '#f56565';
            ok = false;
        } else {
            typeError.hidden = true;
            fieldType.style.borderColor = '';
        }

        return ok;
    }

    // Fehler zurücksetzen beim Tippen
    fieldTitle.addEventListener('input',  () => { titleError.hidden = true; fieldTitle.style.borderColor = ''; });
    fieldType.addEventListener('change',  () => { typeError.hidden  = true; fieldType.style.borderColor  = ''; });

    // ── Formular absenden ─────────────────────────────────────────
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        if (!validate()) return;

        const action = isEdit ? 'update' : 'create';
        const body   = {
            action,
            title:       fieldTitle.value.trim(),
            type:        fieldType.value,
            description: fieldDesc.value.trim(),
        };
        if (isEdit) body.id = editId;

        // Loading-State
        btnSubmit.disabled = true;
        btnText.textContent = '…';

        try {
            const res  = await fetch('api/projects.php', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify(body),
            });
            const data = await res.json();

            if (!data.success) {
                Toast.error(data.error ?? 'Speichern fehlgeschlagen.');
                return;
            }

            Toast.success(isEdit ? 'Projekt gespeichert!' : 'Projekt erstellt!');

            // Kurz warten, Toast anzeigen lassen, dann Redirect
            setTimeout(() => {
                window.location.href = 'dashboard.php';
            }, 900);

        } catch {
            Toast.error('Netzwerkfehler — bitte erneut versuchen.');
        } finally {
            btnSubmit.disabled = false;
            btnText.textContent = isEdit ? '💾 Projekt speichern' : '✦ Projekt erstellen';
        }
    });

    // ── Edit-Modus: Projekt laden & Formular befüllen ─────────────
    async function loadProjectForEdit(id) {
        formLoading.hidden = false;
        form.hidden        = true;

        try {
            const res  = await fetch('api/projects.php?action=get&id=' + encodeURIComponent(id));
            const data = await res.json();

            if (!data.success) {
                Toast.error(data.error ?? 'Projekt konnte nicht geladen werden.');
                formLoading.hidden = true;
                // Trotzdem leeres Formular zeigen (Fallback)
                form.hidden = false;
                return;
            }

            const p = data.data;

            // Felder befüllen (DOM-safe, kein innerHTML)
            fieldTitle.value = p.title       ?? '';
            fieldType.value  = p.type        ?? '';
            fieldDesc.value  = p.description ?? '';
            updateDescCounter();

            formLoading.hidden = true;
            form.hidden        = false;

        } catch {
            Toast.error('Netzwerkfehler — Projekt konnte nicht geladen werden.');
            formLoading.hidden = true;
            form.hidden        = false;
        }
    }

    // ── Init ──────────────────────────────────────────────────────
    if (isEdit) {
        loadProjectForEdit(editId);
    }

})();
</script>

<?php require_once 'includes/footer.php'; ?>
