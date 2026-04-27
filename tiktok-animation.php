<?php
require_once 'includes/config.php';
require_once 'includes/prompt-engine.php';
$pageTitle = 'TikTok Animation Studio';
$extraJs   = ['editor.js', 'upload.js'];
require_once 'includes/header.php';
?>

<div class="studio-page">

    <!-- ── Hero ─────────────────────────────────────────────────── -->
    <div class="studio-hero">
        <div>
            <h2 class="studio-hero__title">TikTok Animation Studio</h2>
            <p class="studio-hero__sub text-muted">
                Individuelle Animationen für TikTok LIVE, Streams und Creator Content.
            </p>
        </div>
        <a href="tiktok-studio.php" class="btn btn-secondary btn-sm">← TikTok Studio</a>
    </div>

    <!-- ── Guidance Bar ──────────────────────────────────────────── -->
    <div class="guidance-bar" id="guidance-bar">
        <div class="guidance-tip">
            <strong>✨ Weniger ist mehr:</strong>
            Klare Animationen wirken professioneller als zu viele gleichzeitige Effekte.
        </div>
        <div class="guidance-tip">
            <strong>🎯 Logo Animationen:</strong>
            Einfache Bewegungen — Fade-in, Pulse, Slide — sind für Logos am wirkungsvollsten.
        </div>
        <div class="guidance-tip">
            <strong>⚡ TikTok Effekte:</strong>
            Kurze, starke Effekte (unter 3 Sekunden) performen besser als lange Sequenzen.
        </div>
    </div>

    <!-- ── Kategorie-Auswahl ──────────────────────────────────────── -->
    <section class="an-section">
        <h3 class="an-section__title">Animation Kategorie wählen</h3>
        <div class="an-categories">

            <button class="an-cat-card" data-type="booster" aria-pressed="false">
                <span class="an-cat-card__icon">🚀</span>
                <div class="an-cat-card__name">Booster Animation</div>
                <div class="an-cat-card__desc text-muted">x5 Effekt — maximaler Boost-Impact</div>
                <span class="an-cat-card__badge">LIVE &amp; Stream</span>
            </button>

            <button class="an-cat-card" data-type="multiplikator" aria-pressed="false">
                <span class="an-cat-card__icon">✖️</span>
                <div class="an-cat-card__name">Multiplikator Animation</div>
                <div class="an-cat-card__desc text-muted">x2 / x3 Effekt — Rewards &amp; Combos</div>
                <span class="an-cat-card__badge">LIVE &amp; Gifts</span>
            </button>

            <button class="an-cat-card" data-type="logo" aria-pressed="false">
                <span class="an-cat-card__icon">🎨</span>
                <div class="an-cat-card__name">Logo Animation</div>
                <div class="an-cat-card__desc text-muted">Dein Logo als animiertes Overlay</div>
                <span class="an-cat-card__badge">Branding</span>
            </button>

            <button class="an-cat-card" data-type="custom" aria-pressed="false">
                <span class="an-cat-card__icon">⚙️</span>
                <div class="an-cat-card__name">Custom Animation</div>
                <div class="an-cat-card__desc text-muted">Individuelle Lösung nach deinen Wünschen</div>
                <span class="an-cat-card__badge">On Request</span>
            </button>

        </div>
    </section>

    <!-- ── Studio Grid ───────────────────────────────────────────── -->
    <div class="studio-grid">

        <!-- ── Linke Spalte: Formular ────────────────────────────── -->
        <div class="studio-col-input">

            <!-- Basis-Felder -->
            <div class="card mb-4">
                <div class="card-header">
                    <span class="card-title">Anfrage Details</span>
                    <span id="selected-type-badge" class="an-type-badge" hidden></span>
                </div>
                <div class="card-body">

                    <div class="form-group">
                        <label for="field-type">
                            Animation Typ <span class="an-required">*</span>
                        </label>
                        <select id="field-type">
                            <option value="">— Kategorie oben wählen oder hier —</option>
                            <option value="booster">🚀 Booster Animation (x5)</option>
                            <option value="multiplikator">✖️ Multiplikator Animation (x2/x3)</option>
                            <option value="logo">🎨 Logo Animation</option>
                            <option value="custom">⚙️ Custom Animation</option>
                        </select>
                        <span id="type-error" class="an-field-error" hidden>Bitte einen Typ wählen.</span>
                    </div>

                    <div class="form-group">
                        <label for="field-description">
                            Beschreibung / Wunsch <span class="an-required">*</span>
                        </label>
                        <textarea
                            id="field-description"
                            rows="4"
                            placeholder="z.B. x5 Booster mit Neon-Lichtblitzen, orangefarben, Loop, für TikTok LIVE …"
                            maxlength="800"
                        ></textarea>
                        <span id="desc-counter" class="text-sm text-muted" style="text-align:right;margin-top:4px;display:block;"></span>
                        <span id="desc-error" class="an-field-error" hidden>Bitte eine Beschreibung eingeben.</span>
                    </div>

                    <!-- Logo Upload (nur bei Logo Animation) -->
                    <div id="logo-upload-section" hidden>
                        <div class="an-divider"></div>
                        <div class="form-group">
                            <label>Logo hochladen <span class="text-muted" style="font-weight:400;">(optional)</span></label>
                            <div id="logo-dropzone" class="dropzone">
                                <p class="dropzone__label">Logo hierher ziehen oder klicken</p>
                                <p class="text-muted text-sm">PNG mit Transparenz empfohlen · max. 10 MB</p>
                                <input type="file" id="logo-input" accept="image/jpeg,image/png,image/webp" hidden>
                            </div>
                            <div id="logo-preview" class="upload-preview" hidden>
                                <img class="upload-image-preview" src="" alt="Logo Vorschau">
                                <div class="upload-meta">
                                    <span class="upload-filename text-sm font-semibold"></span>
                                    <span class="upload-filesize text-sm text-muted"></span>
                                </div>
                                <button id="btn-remove-logo" class="btn btn-secondary btn-sm">Entfernen</button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Erweiterte Optionen (collapsible) -->
            <div class="card mb-4">
                <div class="card-header">
                    <span class="card-title">Erweiterte Optionen</span>
                    <button id="btn-toggle-options" class="btn btn-secondary btn-sm" aria-expanded="false">
                        + Optionen anzeigen
                    </button>
                </div>
                <div id="options-body" class="card-body" hidden>

                    <div class="an-options-grid">
                        <div class="form-group">
                            <label for="opt-style">Stil</label>
                            <select id="opt-style">
                                <option value="energy">⚡ Energy</option>
                                <option value="neon">💜 Neon</option>
                                <option value="glitch">🔧 Glitch</option>
                                <option value="fire">🔥 Fire</option>
                                <option value="luxury">💎 Luxury</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="opt-speed">Geschwindigkeit</label>
                            <select id="opt-speed">
                                <option value="normal">▶ Normal</option>
                                <option value="fast">⚡ Fast</option>
                                <option value="slow">🐌 Slow</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="opt-format">Format</label>
                            <select id="opt-format">
                                <option value="9:16">📱 9:16 (TikTok)</option>
                                <option value="1:1">⬜ 1:1 (Square)</option>
                                <option value="16:9">🖥 16:9 (Landscape)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Loop</label>
                            <div class="an-toggle-group">
                                <label class="an-toggle-option">
                                    <input type="radio" name="opt-loop" value="true" checked>
                                    <span class="an-toggle-btn">🔁 Ja</span>
                                </label>
                                <label class="an-toggle-option">
                                    <input type="radio" name="opt-loop" value="false">
                                    <span class="an-toggle-btn">⏹ Nein</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Start-/Endbeschreibung (für Transformationen) -->
                    <div class="an-divider"></div>
                    <p class="text-muted text-sm" style="margin-bottom: 12px;">
                        Für Transformations-Animationen: Start- und Endzustand beschreiben.
                    </p>
                    <div class="form-group">
                        <label for="opt-start">Startbeschreibung <span class="text-muted" style="font-weight:400;">(optional)</span></label>
                        <input type="text" id="opt-start" placeholder="z.B. Leerer dunkler Hintergrund …" maxlength="200">
                    </div>
                    <div class="form-group">
                        <label for="opt-end">Endbeschreibung <span class="text-muted" style="font-weight:400;">(optional)</span></label>
                        <input type="text" id="opt-end" placeholder="z.B. Logo erscheint mit Lichtblitzen …" maxlength="200">
                    </div>

                </div>
            </div>

            <!-- Aktionen -->
            <div class="card">
                <div class="card-header">
                    <span class="card-title">Aktionen</span>
                </div>
                <div class="card-body">
                    <div class="action-buttons">
                        <button id="btn-submit" class="btn btn-primary btn-lg">
                            🚀 Animation Anfrage erstellen
                        </button>
                        <button id="btn-gen-prompt" class="btn btn-secondary">
                            ✦ Prompt generieren
                        </button>
                        <button id="btn-improve" class="btn btn-secondary" disabled>
                            ↑ Make it Better
                        </button>
                    </div>
                </div>
            </div>

        </div><!-- .studio-col-input -->

        <!-- ── Rechte Spalte: Ergebnis ────────────────────────── -->
        <div class="studio-col-result">
            <div class="card h-full">
                <div class="card-header">
                    <span class="card-title">Ergebnis</span>
                    <button id="btn-copy-prompt" class="btn btn-secondary btn-sm" hidden>
                        Prompt kopieren
                    </button>
                </div>
                <div class="card-body">

                    <!-- Leer-State -->
                    <div id="result-empty" class="result-empty">
                        <div class="result-empty__icon">🎬</div>
                        <p>Wähle eine Kategorie, beschreibe deine Animation<br>und klicke auf <strong>„Anfrage erstellen"</strong></p>
                    </div>

                    <!-- Anfrage bestätigt -->
                    <div id="result-request" hidden>
                        <div class="an-success-banner">
                            <span class="an-success-banner__icon">✅</span>
                            <div>
                                <div class="an-success-banner__title">Anfrage gespeichert</div>
                                <div class="an-success-banner__sub text-muted text-sm" id="result-request-id"></div>
                            </div>
                        </div>

                        <!-- Animationsbeschreibung -->
                        <div class="an-result-block mb-4">
                            <div class="an-result-block__label">Animationsbeschreibung</div>
                            <div id="result-description" class="an-result-block__text"></div>
                        </div>

                        <!-- Technische Parameter -->
                        <div class="an-params-grid mb-4" id="result-params"></div>

                        <!-- Service-Hinweis -->
                        <div class="an-service-note">
                            <span class="an-service-note__icon">🎨</span>
                            <p class="text-sm">
                                Diese Animation wird <strong>individuell erstellt</strong> und nicht automatisch generiert.
                                Du erhältst eine fertige Animations-Datei.
                            </p>
                        </div>
                    </div><!-- #result-request -->

                    <!-- Optionaler Prompt -->
                    <div id="result-prompt-section" hidden>
                        <div class="an-divider" style="margin: 20px 0;"></div>
                        <div class="prompt-block">
                            <label class="prompt-block__label">Generierter Prompt</label>
                            <div id="result-prompt" class="prompt-block__text"></div>
                        </div>
                    </div>

                </div>
            </div>
        </div><!-- .studio-col-result -->

    </div><!-- .studio-grid -->

</div><!-- .studio-page -->


<style>
/* ── TikTok Animation Studio spezifisch ────────────────────── */
.mb-4 { margin-bottom: 16px; }

/* Section Header */
.an-section { margin-bottom: 24px; }
.an-section__title {
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--text-muted);
    letter-spacing: 0.05em;
    text-transform: uppercase;
    margin-bottom: 12px;
}

/* Kategorie-Cards */
.an-categories {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 12px;
}
.an-cat-card {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 4px;
    padding: 18px 16px 14px;
    background: var(--bg-panel);
    border: 2px solid var(--border-color);
    border-radius: var(--radius);
    text-align: left;
    cursor: pointer;
    color: var(--text-primary);
    transition: background var(--transition), border-color var(--transition), transform var(--transition);
}
.an-cat-card:hover {
    background: var(--bg-elevated);
    border-color: var(--accent-blue);
    transform: translateY(-2px);
}
.an-cat-card[aria-pressed="true"] {
    border-color: var(--accent-orange);
    background: rgba(245, 131, 61, 0.07);
}
.an-cat-card[aria-pressed="true"]::after {
    content: '✓';
    position: absolute;
    top: 10px;
    right: 12px;
    font-size: 0.8rem;
    font-weight: 700;
    color: var(--accent-orange);
}
.an-cat-card__icon {
    font-size: 1.6rem;
    margin-bottom: 6px;
}
.an-cat-card__name {
    font-size: 0.875rem;
    font-weight: 600;
}
.an-cat-card__desc {
    font-size: 0.75rem;
    line-height: 1.4;
}
.an-cat-card__badge {
    margin-top: 8px;
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.07em;
    text-transform: uppercase;
    background: var(--bg-elevated);
    border: 1px solid var(--border-color);
    color: var(--text-muted);
    border-radius: var(--radius-sm);
    padding: 2px 7px;
}

/* Type Badge im Card-Header */
.an-type-badge {
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    background: rgba(245, 131, 61, 0.15);
    color: var(--accent-orange);
    border: 1px solid rgba(245, 131, 61, 0.3);
    border-radius: var(--radius-sm);
    padding: 3px 8px;
}

/* Required marker */
.an-required { color: var(--accent-orange); margin-left: 2px; }

/* Field error */
.an-field-error {
    font-size: 0.78rem;
    color: #f56565;
    margin-top: 4px;
    display: block;
}

/* Divider */
.an-divider {
    height: 1px;
    background: var(--border-color);
    margin: 16px 0;
}

/* Options-Grid (2×2) */
.an-options-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

/* Loop Toggle */
.an-toggle-group {
    display: flex;
    gap: 8px;
}
.an-toggle-option {
    display: flex;
    align-items: center;
    cursor: pointer;
}
.an-toggle-option input[type="radio"] { display: none; }
.an-toggle-btn {
    display: inline-block;
    padding: 7px 14px;
    background: var(--bg-elevated);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-sm);
    font-size: 0.82rem;
    color: var(--text-secondary);
    cursor: pointer;
    transition: background var(--transition), border-color var(--transition), color var(--transition);
    white-space: nowrap;
}
.an-toggle-option input[type="radio"]:checked + .an-toggle-btn {
    background: var(--accent-blue-glow);
    border-color: var(--accent-blue);
    color: var(--accent-blue);
    font-weight: 600;
}

/* Erfolgs-Banner */
.an-success-banner {
    display: flex;
    align-items: center;
    gap: 14px;
    background: rgba(72, 199, 116, 0.08);
    border: 1px solid rgba(72, 199, 116, 0.3);
    border-left: 3px solid #48c774;
    border-radius: var(--radius);
    padding: 14px 16px;
    margin-bottom: 20px;
}
.an-success-banner__icon { font-size: 1.4rem; }
.an-success-banner__title {
    font-size: 0.9rem;
    font-weight: 600;
    color: #48c774;
    margin-bottom: 2px;
}

/* Result Block */
.an-result-block {
    background: var(--bg-elevated);
    border: 1px solid var(--border-color);
    border-radius: var(--radius);
    padding: 14px 16px;
}
.an-result-block__label {
    font-size: 0.72rem;
    font-weight: 600;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: var(--text-muted);
    margin-bottom: 8px;
}
.an-result-block__text {
    font-size: 0.9rem;
    color: var(--text-primary);
    line-height: 1.6;
}

/* Parameter-Grid */
.an-params-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 8px;
}
.an-param-item {
    background: var(--bg-elevated);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-sm);
    padding: 10px 12px;
}
.an-param-item__key {
    font-size: 0.7rem;
    font-weight: 600;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: var(--text-muted);
    margin-bottom: 4px;
}
.an-param-item__val {
    font-size: 0.875rem;
    color: var(--text-primary);
    font-weight: 500;
}

/* Service-Hinweis */
.an-service-note {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    background: rgba(61, 142, 245, 0.06);
    border: 1px solid rgba(61, 142, 245, 0.2);
    border-radius: var(--radius);
    padding: 12px 14px;
}
.an-service-note__icon { font-size: 1.2rem; flex-shrink: 0; }
.an-service-note p { color: var(--text-secondary); line-height: 1.5; }
.an-service-note strong { color: var(--text-primary); }

/* Responsive */
@media (max-width: 768px) {
    .an-categories { grid-template-columns: 1fr 1fr; }
    .an-options-grid { grid-template-columns: 1fr; }
    .an-params-grid { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 480px) {
    .an-categories { grid-template-columns: 1fr; }
    .an-params-grid { grid-template-columns: 1fr; }
}
</style>


<script>
(function () {

    // ── DOM-Elemente ──────────────────────────────────────────────
    const catCards      = document.querySelectorAll('.an-cat-card');
    const fieldType     = document.getElementById('field-type');
    const fieldDesc     = document.getElementById('field-description');
    const descCounter   = document.getElementById('desc-counter');
    const typeError     = document.getElementById('type-error');
    const descError     = document.getElementById('desc-error');
    const selectedBadge = document.getElementById('selected-type-badge');

    const logoSection   = document.getElementById('logo-upload-section');
    const logoInput     = document.getElementById('logo-input');
    const logoPreview   = document.getElementById('logo-preview');
    const btnRemoveLogo = document.getElementById('btn-remove-logo');

    const optionsBody   = document.getElementById('options-body');
    const btnToggleOpts = document.getElementById('btn-toggle-options');

    const btnSubmit     = document.getElementById('btn-submit');
    const btnGenPrompt  = document.getElementById('btn-gen-prompt');
    const btnImprove    = document.getElementById('btn-improve');
    const btnCopyPrompt = document.getElementById('btn-copy-prompt');

    const resultEmpty         = document.getElementById('result-empty');
    const resultRequest       = document.getElementById('result-request');
    const resultRequestId     = document.getElementById('result-request-id');
    const resultDescription   = document.getElementById('result-description');
    const resultParams        = document.getElementById('result-params');
    const resultPromptSection = document.getElementById('result-prompt-section');
    const resultPromptEl      = document.getElementById('result-prompt');

    // ── State ─────────────────────────────────────────────────────
    let currentPrompt = '';

    // ── Kategorie-Cards: Auswahl-Logik ────────────────────────────
    const TYPE_LABELS = {
        'booster':       '🚀 Booster',
        'multiplikator': '✖️ Multiplikator',
        'logo':          '🎨 Logo',
        'custom':        '⚙️ Custom',
    };

    function selectCategory(type) {
        // Cards aktualisieren
        catCards.forEach(card => {
            const active = card.dataset.type === type;
            card.setAttribute('aria-pressed', active ? 'true' : 'false');
        });

        // Select synchronisieren
        fieldType.value = type;
        typeError.hidden = true;
        fieldType.style.borderColor = '';

        // Badge im Card-Header zeigen
        selectedBadge.textContent = TYPE_LABELS[type] ?? type;
        selectedBadge.hidden      = false;

        // Logo-Upload nur bei Logo-Typ
        logoSection.hidden = (type !== 'logo');
    }

    catCards.forEach(card => {
        card.addEventListener('click', () => selectCategory(card.dataset.type));
    });

    // Select → Karte synchronisieren
    fieldType.addEventListener('change', () => {
        const val = fieldType.value;
        if (val) selectCategory(val);
        else {
            catCards.forEach(c => c.setAttribute('aria-pressed', 'false'));
            selectedBadge.hidden = true;
            logoSection.hidden   = true;
        }
    });

    // ── Beschreibungs-Zähler ──────────────────────────────────────
    fieldDesc.addEventListener('input', () => {
        const len = fieldDesc.value.length;
        const max = parseInt(fieldDesc.getAttribute('maxlength') || '800', 10);
        descCounter.textContent = len + ' / ' + max;
        descCounter.style.color = len > max * 0.9 ? 'var(--accent-orange)' : '';
    });

    // ── Erweiterte Optionen Toggle ────────────────────────────────
    btnToggleOpts.addEventListener('click', () => {
        const open = optionsBody.hidden;
        optionsBody.hidden = !open;
        btnToggleOpts.setAttribute('aria-expanded', open ? 'true' : 'false');
        btnToggleOpts.textContent = open ? '− Optionen ausblenden' : '+ Optionen anzeigen';
    });

    // ── Logo Upload ───────────────────────────────────────────────
    UploadPreview.init('#logo-input', '#logo-preview', 'image');
    DropZone.init('#logo-dropzone', '#logo-input', 'image');

    btnRemoveLogo?.addEventListener('click', () => {
        UploadPreview.reset(logoPreview);
        logoInput.value = '';
    });

    // ── Validierung ───────────────────────────────────────────────
    function validate() {
        let ok = true;

        if (!fieldType.value) {
            typeError.hidden = false;
            fieldType.style.borderColor = '#f56565';
            ok = false;
        } else {
            typeError.hidden = true;
            fieldType.style.borderColor = '';
        }

        if (!fieldDesc.value.trim()) {
            descError.hidden = false;
            fieldDesc.style.borderColor = '#f56565';
            ok = false;
        } else {
            descError.hidden = true;
            fieldDesc.style.borderColor = '';
        }

        return ok;
    }

    fieldType.addEventListener('change', () => { typeError.hidden = true; fieldType.style.borderColor = ''; });
    fieldDesc.addEventListener('input',  () => { descError.hidden = true; fieldDesc.style.borderColor = ''; });

    // ── Optionen sammeln ──────────────────────────────────────────
    function getOptions() {
        const loopVal = document.querySelector('input[name="opt-loop"]:checked')?.value ?? 'true';
        return {
            type:              fieldType.value,
            description:       fieldDesc.value.trim(),
            style:             document.getElementById('opt-style').value,
            speed:             document.getElementById('opt-speed').value,
            loop:              loopVal === 'true',
            format:            document.getElementById('opt-format').value,
            start_description: document.getElementById('opt-start').value.trim(),
            end_description:   document.getElementById('opt-end').value.trim(),
        };
    }

    // ── Anfrage erstellen ─────────────────────────────────────────
    btnSubmit.addEventListener('click', async () => {
        if (!validate()) return;

        const origText     = btnSubmit.textContent;
        btnSubmit.disabled = true;
        btnSubmit.textContent = '…';

        try {
            const res  = await fetch('api/animation-request.php', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify(getOptions()),
            });
            const data = await res.json();

            if (!data.success) {
                Toast.error(data.error ?? 'Anfrage konnte nicht gespeichert werden.');
                return;
            }

            Toast.success('Animations-Anfrage erstellt!');
            showRequestResult(data);

        } catch {
            Toast.error('Netzwerkfehler — bitte erneut versuchen.');
        } finally {
            btnSubmit.disabled    = false;
            btnSubmit.textContent = origText;
        }
    });

    // ── Prompt generieren ─────────────────────────────────────────
    btnGenPrompt.addEventListener('click', async () => {
        const desc = fieldDesc.value.trim();
        if (!desc) {
            Toast.warning('Bitte zuerst eine Beschreibung eingeben.');
            return;
        }

        const origText       = btnGenPrompt.textContent;
        btnGenPrompt.disabled = true;
        btnGenPrompt.textContent = '…';

        try {
            const opts = getOptions();
            const res  = await fetch('api/generate-tiktok.php', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify({
                    action:   'build',
                    input:    desc,
                    template: 'viral_hook',
                    style:    opts.style === 'neon' || opts.style === 'energy' ? opts.style : 'cinematic',
                    cta:      'none',
                }),
            });
            const data = await res.json();

            if (!data.success) {
                Toast.error(data.error ?? 'Fehler beim Generieren.');
                return;
            }

            currentPrompt = data.positive;
            showPromptResult(data.positive);
            btnImprove.disabled = false;
            btnCopyPrompt.hidden = false;

        } catch {
            Toast.error('Netzwerkfehler — bitte erneut versuchen.');
        } finally {
            btnGenPrompt.disabled    = false;
            btnGenPrompt.textContent = origText;
        }
    });

    // ── Make it Better ────────────────────────────────────────────
    btnImprove.addEventListener('click', async () => {
        if (!currentPrompt) {
            Toast.warning('Bitte zuerst einen Prompt generieren.');
            return;
        }

        const origText     = btnImprove.textContent;
        btnImprove.disabled = true;
        btnImprove.textContent = '…';

        try {
            const res  = await fetch('api/generate-tiktok.php', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify({
                    action:   'improve',
                    input:    currentPrompt,
                    template: 'viral_hook',
                    style:    'cinematic',
                    cta:      'none',
                }),
            });
            const data = await res.json();

            if (!data.success) {
                Toast.error(data.error ?? 'Fehler beim Verbessern.');
                return;
            }

            currentPrompt = data.positive;
            showPromptResult(data.positive);
            Toast.success('Prompt verbessert!');

        } catch {
            Toast.error('Netzwerkfehler — bitte erneut versuchen.');
        } finally {
            btnImprove.disabled    = false;
            btnImprove.textContent = origText;
        }
    });

    // ── Copy Prompt ───────────────────────────────────────────────
    btnCopyPrompt.addEventListener('click', () => {
        navigator.clipboard.writeText(currentPrompt)
            .then(() => Toast.success('Prompt kopiert!'))
            .catch(() => Toast.error('Kopieren fehlgeschlagen.'));
    });

    // ── Ergebnis anzeigen: Anfrage ────────────────────────────────
    function showRequestResult(data) {
        const req = data.request;

        // ID / Referenz
        resultRequestId.textContent = 'Referenz-ID: ' + (data.id ?? '—');

        // Beschreibung (DOM-safe)
        resultDescription.textContent = req.description ?? '';

        // Parameter-Grid aufbauen
        resultParams.innerHTML = '';
        const params = [
            { key: 'Typ',           val: TYPE_LABELS[req.type] ?? req.type },
            { key: 'Stil',          val: req.style  ?? '—' },
            { key: 'Geschwindigkeit', val: req.speed ?? '—' },
            { key: 'Loop',          val: req.loop ? '🔁 Ja' : '⏹ Nein' },
            { key: 'Format',        val: req.format ?? '—' },
            { key: 'Status',        val: '⏳ Pending' },
        ];

        params.forEach(p => {
            const item = document.createElement('div');
            item.className = 'an-param-item';

            const key = document.createElement('div');
            key.className = 'an-param-item__key';
            key.textContent = p.key;

            const val = document.createElement('div');
            val.className = 'an-param-item__val';
            val.textContent = p.val;

            item.appendChild(key);
            item.appendChild(val);
            resultParams.appendChild(item);
        });

        // UI
        resultEmpty.hidden   = true;
        resultRequest.hidden = false;
    }

    // ── Ergebnis anzeigen: Prompt ─────────────────────────────────
    function showPromptResult(prompt) {
        resultPromptEl.textContent      = prompt;
        resultPromptSection.hidden      = false;

        // Falls noch kein Request-Ergebnis: leeren State ausblenden
        if (!resultRequest.hidden === false) {
            resultEmpty.hidden = true;
        }
    }

})();
</script>

<?php require_once 'includes/footer.php'; ?>
