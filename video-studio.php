<?php
require_once 'includes/config.php';
require_once 'includes/prompt-engine.php';
require_once 'includes/guidance.php';
$pageTitle       = 'Video Studio';
$defaultTemplate = 'cinematic_scene';
$defaultMode     = 'text';
$extraJs   = ['editor.js', 'upload.js', 'progress.js'];
require_once 'includes/header.php';
?>

<div class="studio-page">

    <!-- ── Hero ───────────────────────────────────────────────── -->
    <div class="studio-hero">
        <div>
            <h2 class="studio-hero__title">Video Studio</h2>
            <p class="studio-hero__sub text-muted">
                Erstelle Seedance-Videos mit Startframe, Endframe, Elementen
                und cinematic Prompt-Logik.
            </p>
        </div>
        <?php if (empty($_SESSION['api_key'])): ?>
            <a href="api-key.php" class="btn btn-secondary btn-sm">⚠ API-Key verbinden</a>
        <?php else: ?>
            <span class="badge-connected">● API verbunden</span>
        <?php endif; ?>
    </div>

    <!-- ── Smart Guidance (PHP-rendered, JS-updated on mode/template change) ── -->
    <?php renderGuidanceBar('video', $defaultTemplate, $defaultMode); ?>

    <div class="studio-grid">

        <!-- ── Linke Spalte: Eingabe ──────────────────────────── -->
        <div class="studio-col-input">

            <!-- Prompt + Template -->
            <div class="card mb-4">
                <div class="card-header">
                    <span class="card-title">Beschreibung &amp; Template</span>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="prompt-input">Szenenbeschreibung</label>
                        <textarea
                            id="prompt-input"
                            placeholder="z.B. Ein schwarzer Sportwagen fährt durch eine leere nächtliche Stadtstraße, Neonlichter spiegeln sich auf dem Asphalt …"
                            rows="4"
                        ></textarea>
                        <span id="prompt-counter" class="text-sm" style="text-align:right; margin-top:4px;"></span>
                    </div>
                    <div class="form-group">
                        <label for="template-select">Template</label>
                        <select id="template-select">
                            <option value="cinematic_scene">Cinematic Scene</option>
                            <option value="action_trailer">Action Trailer</option>
                            <option value="pov_car">POV Car</option>
                            <option value="product_ad">Product Ad</option>
                            <option value="horror_creature">Horror Creature</option>
                            <option value="transformation">Transformation</option>
                            <option value="blockbuster">Blockbuster Trailer</option>
                            <option value="tiktok_hook">TikTok Hook</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Seedance Optionen -->
            <div class="card mb-4">
                <div class="card-header">
                    <span class="card-title">Seedance Optionen</span>
                </div>
                <div class="card-body">
                    <div class="options-grid">

                        <div class="form-group">
                            <label for="opt-model">Modell</label>
                            <select id="opt-model">
                                <option value="seedance_fast">Seedance 2.0 Fast</option>
                                <option value="seedance_standard" selected>Seedance 2.0 Standard</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="opt-duration">Dauer</label>
                            <select id="opt-duration">
                                <option value="5">5 Sekunden</option>
                                <option value="8" selected>8 Sekunden</option>
                                <option value="10">10 Sekunden</option>
                                <option value="15">15 Sekunden</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="opt-quality">Qualität</label>
                            <select id="opt-quality">
                                <option value="normal" selected>Normal</option>
                                <option value="super">Super Quality</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="opt-mode">Modus</label>
                            <select id="opt-mode">
                                <option value="text" selected>Text only</option>
                                <option value="startframe">Startframe</option>
                                <option value="start+end">Startframe + Endframe</option>
                                <option value="element">Element-based</option>
                            </select>
                        </div>

                    </div><!-- .options-grid -->
                </div>
            </div>

            <!-- Upload: Startframe -->
            <div id="upload-startframe" class="card mb-4" hidden>
                <div class="card-header">
                    <span class="card-title">Startframe</span>
                </div>
                <div class="card-body">
                    <div id="start-dropzone" class="dropzone">
                        <p class="dropzone__label">Startframe hierher ziehen oder klicken</p>
                        <p class="text-muted text-sm">JPEG, PNG, WEBP · max. 10 MB</p>
                        <input type="file" id="start-input" accept="image/jpeg,image/png,image/webp" hidden>
                    </div>
                    <div id="start-preview" class="upload-preview" hidden>
                        <img class="upload-image-preview" src="" alt="Startframe">
                        <div class="upload-meta">
                            <span class="upload-filename text-sm font-semibold"></span>
                            <span class="upload-filesize text-sm text-muted"></span>
                        </div>
                        <button class="btn-remove-frame btn btn-secondary btn-sm" data-target="start">Entfernen</button>
                    </div>
                </div>
            </div>

            <!-- Upload: Endframe -->
            <div id="upload-endframe" class="card mb-4" hidden>
                <div class="card-header">
                    <span class="card-title">Endframe</span>
                </div>
                <div class="card-body">
                    <div id="end-dropzone" class="dropzone">
                        <p class="dropzone__label">Endframe hierher ziehen oder klicken</p>
                        <p class="text-muted text-sm">JPEG, PNG, WEBP · max. 10 MB</p>
                        <input type="file" id="end-input" accept="image/jpeg,image/png,image/webp" hidden>
                    </div>
                    <div id="end-preview" class="upload-preview" hidden>
                        <img class="upload-image-preview" src="" alt="Endframe">
                        <div class="upload-meta">
                            <span class="upload-filename text-sm font-semibold"></span>
                            <span class="upload-filesize text-sm text-muted"></span>
                        </div>
                        <button class="btn-remove-frame btn btn-secondary btn-sm" data-target="end">Entfernen</button>
                    </div>
                </div>
            </div>

            <!-- Aktionen -->
            <div class="card">
                <div class="card-header">
                    <span class="card-title">Prompt Aktionen</span>
                </div>
                <div class="card-body">
                    <div class="action-buttons">
                        <button id="btn-build-prompt" class="btn btn-primary">
                            ✦ Video-Prompt erstellen
                        </button>
                        <div class="action-modifiers">
                            <button id="btn-make-it-better"     class="btn btn-secondary btn-sm" disabled>↑ Make it Better</button>
                            <button id="btn-fix-faces"          class="btn btn-secondary btn-sm" disabled>👤 Fix Faces</button>
                            <button id="btn-better-motion"      class="btn btn-secondary btn-sm" disabled>〜 Better Motion</button>
                            <button id="btn-perfect-transition" class="btn btn-secondary btn-sm" disabled>⇄ Perfect Transition</button>
                            <button id="btn-cinematic-upgrade"  class="btn btn-secondary btn-sm" disabled>🎬 Cinematic Upgrade</button>
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- .studio-col-input -->

        <!-- ── Rechte Spalte: Ergebnis ────────────────────────── -->
        <div class="studio-col-result">
            <div class="card h-full">
                <div class="card-header">
                    <span class="card-title">Generierter Prompt</span>
                    <button id="btn-copy-positive" class="btn btn-secondary btn-sm" hidden>Kopieren</button>
                </div>
                <div class="card-body">

                    <!-- Leer-State -->
                    <div id="result-empty" class="result-empty">
                        <div class="result-empty__icon">🎬</div>
                        <p>Beschreibe deine Szene und klicke auf<br><strong>„Video-Prompt erstellen"</strong></p>
                    </div>

                    <!-- Ergebnis -->
                    <div id="result-output" hidden>

                        <div class="prompt-block mb-4">
                            <label class="prompt-block__label">Positiver Prompt</label>
                            <div id="positive-prompt" class="prompt-block__text"></div>
                        </div>

                        <div class="prompt-block mb-4">
                            <label class="prompt-block__label prompt-block__label--neg">Negativer Prompt</label>
                            <div id="negative-prompt" class="prompt-block__text prompt-block__text--neg"></div>
                        </div>

                        <!-- Meta -->
                        <div class="meta-block mb-4">
                            <label class="prompt-block__label">Meta</label>
                            <div class="meta-grid">
                                <div class="meta-item"><span class="meta-key">Modell</span><span id="meta-model" class="meta-val"></span></div>
                                <div class="meta-item"><span class="meta-key">Dauer</span><span id="meta-duration" class="meta-val"></span></div>
                                <div class="meta-item"><span class="meta-key">Qualität</span><span id="meta-quality" class="meta-val"></span></div>
                                <div class="meta-item"><span class="meta-key">Modus</span><span id="meta-mode" class="meta-val"></span></div>
                                <div class="meta-item"><span class="meta-key">Template</span><span id="meta-template" class="meta-val"></span></div>
                            </div>
                        </div>

                        <!-- Preview-Platzhalter -->
                        <div class="preview-placeholder">
                            <div class="preview-placeholder__box">
                                <span class="preview-placeholder__icon">🎥</span>
                                <p class="text-sm text-muted">
                                    Video-Generierung wird später über Seedance / Kie.ai API angebunden
                                </p>
                            </div>
                        </div>

                    </div><!-- #result-output -->

                </div>
            </div>
        </div><!-- .studio-col-result -->

    </div><!-- .studio-grid -->

    <!-- ═══════════════════════════════════════════════════════════════════════
         MP4-EXPORT-BEREICH (Phase 4 — TODO #32 + #33 + #34)
         Eigene Card unterhalb des Prompt-Builders: Video-Datei hochladen,
         Preset wählen, als MP4 exportieren. Optional Thumbnail erzeugen.
    ═══════════════════════════════════════════════════════════════════════ -->
    <div class="card mb-4" style="margin-top:24px;">
        <div class="card-header">
            <span class="card-title">MP4-Export · Konvertieren</span>
        </div>
        <div class="card-body">
            <p class="text-muted text-sm" style="margin-bottom:14px;">
                Lade eine bestehende Video-Datei hoch und exportiere sie als MP4 in 720p oder 1080p.
                Ideal um Aufnahmen zu vereinheitlichen, bevor sie in den Multi-Scene-Export gehen.
            </p>

            <!-- Upload -->
            <div id="vs-export-dropzone" class="dropzone" style="margin-bottom:14px;">
                <p class="dropzone__label">Video hierher ziehen oder klicken</p>
                <p class="text-muted text-sm">MP4 · WEBM · MOV — max. 500 MB</p>
                <input type="file" id="vs-export-input"
                       accept="video/mp4,video/webm,video/quicktime"
                       hidden>
            </div>

            <!-- Datei-Vorschau (nach Upload) -->
            <div id="vs-export-preview" class="upload-preview" hidden>
                <div class="upload-meta">
                    <span class="upload-filename text-sm font-semibold" id="vs-export-name"></span>
                    <span class="upload-filesize text-sm text-muted" id="vs-export-size"></span>
                </div>
                <button id="vs-export-remove" class="btn btn-secondary btn-sm" type="button">Entfernen</button>
            </div>

            <!-- Preset + Aktionen -->
            <div class="csf-export-group" id="vs-export-group" hidden>
                <p class="csf-export-group__title">Export-Qualität</p>
                <div class="csf-export-presets">
                    <label>
                        <input type="radio" name="vs-preset" value="720p">
                        <span>720p · HD</span>
                    </label>
                    <label>
                        <input type="radio" name="vs-preset" value="1080p" checked>
                        <span>1080p · Full HD</span>
                    </label>
                </div>
                <div class="csf-action-row">
                    <button id="vs-btn-convert"   class="btn btn-primary"   type="button" disabled>
                        ▶ Als MP4 exportieren
                    </button>
                    <button id="vs-btn-thumbnail" class="btn btn-secondary" type="button" disabled>
                        🖼 Thumbnail
                    </button>
                </div>
            </div>

            <!-- Progress-Bar (Phase 4 — TODO #33) -->
            <div id="vs-progress" class="csf-progress" hidden style="margin-top:16px;">
                <div class="csf-progress__head">
                    <span class="csf-progress__label" id="vs-progress-status">Rendering…</span>
                    <span class="csf-progress__percent" id="vs-progress-percent">—</span>
                </div>
                <div class="csf-progress__bar">
                    <div class="csf-progress__fill" id="vs-progress-fill"></div>
                </div>
            </div>

            <!-- Fehlerbox (Phase 4 — TODO #34) -->
            <div id="vs-error-box" class="csf-error-box" hidden style="margin-top:16px;">
                <span class="csf-error-box__icon" aria-hidden="true">⚠</span>
                <div class="csf-error-box__body">
                    <p class="csf-error-box__title" id="vs-error-title">Es ist ein Fehler aufgetreten.</p>
                    <p class="csf-error-box__detail" id="vs-error-detail" hidden></p>
                </div>
                <button type="button" class="csf-error-box__close" id="vs-error-close" aria-label="Fehler schließen">✕</button>
            </div>

            <!-- Result -->
            <div id="vs-result" hidden style="margin-top:16px; padding:16px; background:var(--bg-elevated); border-left:3px solid #48c774; border-radius:var(--radius);">
                <p class="text-sm" style="margin:0 0 8px; color:#48c774; font-weight:600;">✓ Export erfolgreich</p>
                <p class="text-sm text-muted" id="vs-result-meta" style="margin:0 0 12px;"></p>
                <a id="vs-download-btn" href="#" download class="btn btn-primary">⬇ MP4 herunterladen</a>
            </div>

            <!-- Thumbnail-Vorschau -->
            <div id="vs-thumb-preview" class="csf-thumb-preview" hidden>
                <p class="csf-thumb-preview__label">Thumbnail (00:00:01)</p>
                <img id="vs-thumb-img" src="" alt="Thumbnail des Videos">
            </div>
        </div>
    </div>

</div><!-- .studio-page -->


<style>
/* Erbt: .studio-page, .studio-hero, .studio-grid, .studio-col-input/.result,
         .guidance-bar, .guidance-tip, .badge-connected, .dropzone, .dropzone--active,
         .dropzone__label, .upload-preview, .upload-image-preview, .upload-meta,
         .action-buttons, .result-empty, .result-empty__icon,
         .prompt-block, .prompt-block__label, .prompt-block__text,
         .preview-placeholder, .preview-placeholder__box, .preview-placeholder__icon
   — alles bereits in image-studio.php definiert und wird im selben Request nicht neu deklariert.
   Hier nur Video-Studio spezifische Ergänzungen. */

.options-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0 16px;
}

.action-modifiers {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 6px;
    margin-top: 8px;
}

.meta-block { display: flex; flex-direction: column; gap: 6px; }
.meta-grid  { display: grid; grid-template-columns: 1fr 1fr; gap: 6px; }
.meta-item  {
    background: var(--bg-elevated);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-sm);
    padding: 8px 10px;
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.meta-key { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); }
.meta-val { font-size: 0.8rem; color: var(--text-primary); font-weight: 500; }

@media (max-width: 640px) {
    .options-grid   { grid-template-columns: 1fr; }
    .action-modifiers { grid-template-columns: 1fr; }
    .meta-grid      { grid-template-columns: 1fr; }
}
</style>

<script>
(function () {
    // ── Elemente ──────────────────────────────────────────────
    const promptInput    = document.getElementById('prompt-input');
    const templateSel    = document.getElementById('template-select');
    const optModel       = document.getElementById('opt-model');
    const optDuration    = document.getElementById('opt-duration');
    const optQuality     = document.getElementById('opt-quality');
    const optMode        = document.getElementById('opt-mode');

    const uploadStart    = document.getElementById('upload-startframe');
    const uploadEnd      = document.getElementById('upload-endframe');

    const btnBuild       = document.getElementById('btn-build-prompt');
    const btnBetter      = document.getElementById('btn-make-it-better');
    const btnFaces       = document.getElementById('btn-fix-faces');
    const btnMotion      = document.getElementById('btn-better-motion');
    const btnTransition  = document.getElementById('btn-perfect-transition');
    const btnCinematic   = document.getElementById('btn-cinematic-upgrade');
    const btnCopy        = document.getElementById('btn-copy-positive');

    const resultEmpty    = document.getElementById('result-empty');
    const resultOutput   = document.getElementById('result-output');
    const positiveEl     = document.getElementById('positive-prompt');
    const negativeEl     = document.getElementById('negative-prompt');

    let currentPositive  = '';
    let currentNegative  = '';

    // ── Upload-Bereiche initialisieren ────────────────────────
    UploadPreview.init('#start-input', '#start-preview', 'image');
    UploadPreview.init('#end-input',   '#end-preview',   'image');
    DropZone.init('#start-dropzone', '#start-input', 'image');
    DropZone.init('#end-dropzone',   '#end-input',   'image');

    document.querySelectorAll('.btn-remove-frame').forEach(btn => {
        btn.addEventListener('click', () => {
            const t = btn.dataset.target;
            UploadPreview.reset(document.getElementById(`${t}-preview`));
            document.getElementById(`${t}-input`).value = '';
        });
    });

    // ── Modus → Upload-Sektionen ein/ausblenden ───────────────
    function applyModeVisibility() {
        const mode = optMode.value;
        uploadStart.hidden = !['startframe', 'start+end'].includes(mode);
        uploadEnd.hidden   = mode !== 'start+end';
    }

    optMode.addEventListener('change', applyModeVisibility);
    applyModeVisibility(); // Initialzustand

    // ── Modifier-Buttons nach erstem Build freischalten ───────
    const modifierBtns = [btnBetter, btnFaces, btnMotion, btnTransition, btnCinematic];
    function enableModifiers() {
        modifierBtns.forEach(b => { if (b) b.disabled = false; });
    }

    // ── API-Call ──────────────────────────────────────────────
    async function callGenerateVideo(action = 'build') {
        const input    = promptInput?.value.trim() ?? '';
        const template = templateSel?.value ?? 'cinematic_scene';
        const options  = {
            model:    optModel?.value    ?? 'seedance_standard',
            duration: parseInt(optDuration?.value ?? '8'),
            quality:  optQuality?.value  ?? 'normal',
            mode:     optMode?.value     ?? 'text',
        };

        const body = { input, template, action, ...options };

        // Modifier arbeiten auf dem bestehenden positiven Prompt
        if (action !== 'build' && currentPositive) {
            body.input = currentPositive;
        }

        const actionBtnMap = {
            build:               btnBuild,
            improve:             btnBetter,
            fix_faces:           btnFaces,
            better_motion:       btnMotion,
            perfect_transition:  btnTransition,
            cinematic:           btnCinematic,
        };
        const btn = actionBtnMap[action] ?? btnBuild;
        const origText = btn.textContent;
        btn.disabled = true;
        btn.textContent = '…';

        try {
            const res  = await fetch('api/generate-video.php', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify(body),
            });
            const data = await res.json();

            if (!data.success) {
                Toast.error(data.error ?? 'Fehler beim Generieren.');
                return;
            }

            currentPositive = data.positive;
            currentNegative = data.negative;
            showResult(data);
            enableModifiers();

        } catch {
            Toast.error('Netzwerkfehler — bitte erneut versuchen.');
        } finally {
            btn.disabled   = false;
            btn.textContent = origText;
        }
    }

    function showResult(data) {
        positiveEl.textContent = data.positive;
        negativeEl.textContent = data.negative;

        // Meta befüllen
        const m = data.meta ?? {};
        document.getElementById('meta-model').textContent    = m.model    ?? '—';
        document.getElementById('meta-duration').textContent = m.duration ? m.duration + 's' : '—';
        document.getElementById('meta-quality').textContent  = m.quality  ?? '—';
        document.getElementById('meta-mode').textContent     = m.mode     ?? '—';
        document.getElementById('meta-template').textContent = m.template ?? '—';

        resultEmpty.hidden  = true;
        resultOutput.hidden = false;
        btnCopy.hidden      = false;
    }

    // ── Button Listeners ──────────────────────────────────────
    btnBuild?.addEventListener('click', () => callGenerateVideo('build'));

    const modifierMap = [
        [btnBetter,     'improve'],
        [btnFaces,      'fix_faces'],
        [btnMotion,     'better_motion'],
        [btnTransition, 'perfect_transition'],
        [btnCinematic,  'cinematic'],
    ];
    modifierMap.forEach(([btn, action]) => {
        btn?.addEventListener('click', () => {
            if (!currentPositive) { Toast.warning('Bitte zuerst einen Prompt erstellen.'); return; }
            callGenerateVideo(action);
        });
    });

    btnCopy?.addEventListener('click', () => {
        navigator.clipboard.writeText(currentPositive)
            .then(() => Toast.success('Prompt kopiert!'))
            .catch(() => Toast.error('Kopieren fehlgeschlagen.'));
    });

    // ── Guidance dynamisch updaten ────────────────────────────
    const guidanceTips = <?php echo json_encode(getAllGuidanceTips(), JSON_UNESCAPED_UNICODE); ?>;

    function updateGuidanceBar(template, mode) {
        const bar  = document.getElementById('guidance-bar');
        if (!bar) return;
        const bank = guidanceTips['video'] ?? {};
        const tips = [
            ...(bank[mode]     ?? []),
            ...(bank[template] ?? []),
            ...(bank['_default'] ?? []),
        ];
        const unique = [];
        tips.forEach(t => { if (!unique.find(u => u.text === t.text)) unique.push(t); });
        bar.innerHTML = unique.slice(0, 4).map(t =>
            `<div class="guidance-tip"><strong>${t.icon} ${t.title}:</strong> ${t.text}</div>`
        ).join('');
    }

    function updateWarnings(input, template, mode) {
        let existing = document.getElementById('guidance-warnings');
        const warnings = [];
        const len = input.trim().length;
        if (len > 0 && len < 20)  warnings.push('⚠ Deine Beschreibung ist sehr kurz. Ergänze Aktion, Umgebung und Kamera.');
        if (len > 1500)            warnings.push('⚠ Sehr lange Prompts können instabil werden. Kürze auf das Wesentliche.');
        if (mode === 'start+end')  warnings.push('🔍 Achte darauf, dass Startframe und Endframe visuell zusammenpassen.');
        if (['horror_creature', 'action_trailer', 'blockbuster'].includes(template))
            warnings.push('🎬 Komplexe Templates: halte eine klare Hauptbewegung.');

        if (!warnings.length) { if (existing) existing.remove(); return; }
        if (!existing) {
            existing = document.createElement('div');
            existing.id = 'guidance-warnings';
            existing.className = 'guidance-warnings';
            document.getElementById('guidance-bar')?.insertAdjacentElement('afterend', existing);
        }
        existing.innerHTML = warnings.map(w => `<div class="guidance-warning">${w}</div>`).join('');
    }

    templateSel?.addEventListener('change', () => {
        updateGuidanceBar(templateSel.value, optMode?.value ?? 'text');
        if (currentPositive) Toast.info('Template geändert — Prompt neu erstellen für beste Ergebnisse.');
    });

    optMode?.addEventListener('change', () => {
        updateGuidanceBar(templateSel?.value ?? 'cinematic_scene', optMode.value);
    });

    promptInput?.addEventListener('input', () => {
        updateWarnings(promptInput.value, templateSel?.value ?? '', optMode?.value ?? '');
    });

    // ════════════════════════════════════════════════════════════════════════
    //  Phase 4 — TODO #32/#33/#34: MP4-Export Card
    //
    //  Hinweis V1: api/upload.php + api/export.php laufen synchron.
    //  Echtes Polling über api/progress.php ist daher (noch) nicht aktiv —
    //  wir zeigen während des fetch() eine indeterminate-Bar via csfIndeterminate().
    //  Das Modul progress.js ist trotzdem komplett und auf Async-Worker
    //  vorbereitet (siehe csfTrackJob).
    // ════════════════════════════════════════════════════════════════════════

    const vsDropzone   = document.getElementById('vs-export-dropzone');
    const vsInput      = document.getElementById('vs-export-input');
    const vsPreview    = document.getElementById('vs-export-preview');
    const vsName       = document.getElementById('vs-export-name');
    const vsSize       = document.getElementById('vs-export-size');
    const vsRemove     = document.getElementById('vs-export-remove');
    const vsGroup      = document.getElementById('vs-export-group');
    const vsBtnConvert = document.getElementById('vs-btn-convert');
    const vsBtnThumb   = document.getElementById('vs-btn-thumbnail');
    const vsProgress   = document.getElementById('vs-progress');
    const vsProgFill   = document.getElementById('vs-progress-fill');
    const vsProgStatus = document.getElementById('vs-progress-status');
    const vsProgPct    = document.getElementById('vs-progress-percent');
    const vsErrorBox   = document.getElementById('vs-error-box');
    const vsErrorTitle = document.getElementById('vs-error-title');
    const vsErrorDet   = document.getElementById('vs-error-detail');
    const vsErrorClose = document.getElementById('vs-error-close');
    const vsResult     = document.getElementById('vs-result');
    const vsResultMeta = document.getElementById('vs-result-meta');
    const vsDownload   = document.getElementById('vs-download-btn');
    const vsThumbPrev  = document.getElementById('vs-thumb-preview');
    const vsThumbImg   = document.getElementById('vs-thumb-img');

    let vsUploadedFile = null;   // server-seitiger Filename (32 hex + ext)
    let vsBusy         = false;
    let vsIndet        = null;

    function vsHideError() {
        vsErrorBox.hidden = true;
        vsErrorTitle.textContent = '';
        vsErrorDet.textContent = '';
        vsErrorDet.hidden = true;
    }
    function vsShowError(httpStatus, rawError) {
        const mapped = (typeof csfMapError === 'function')
            ? csfMapError(httpStatus, rawError)
            : { message: rawError || 'Unbekannter Fehler.', details: '' };
        Toast.error(mapped.message);
        vsErrorTitle.textContent = mapped.message;
        if (mapped.details && mapped.details !== mapped.message) {
            vsErrorDet.textContent = mapped.details;
            vsErrorDet.hidden = false;
        } else {
            vsErrorDet.textContent = '';
            vsErrorDet.hidden = true;
        }
        vsErrorBox.hidden = false;
    }

    function vsFmtSize(bytes) {
        if (!bytes || bytes <= 0) return '—';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / 1024 / 1024).toFixed(1) + ' MB';
    }

    function vsResetState() {
        vsUploadedFile = null;
        vsPreview.hidden = true;
        vsGroup.hidden   = true;
        vsResult.hidden  = true;
        vsThumbPrev.hidden = true;
        vsThumbImg.src = '';
        vsBtnConvert.disabled = true;
        vsBtnThumb.disabled   = true;
        vsName.textContent = '';
        vsSize.textContent = '';
        if (vsInput) vsInput.value = '';
    }

    async function vsUpload(file) {
        if (vsBusy) return;
        if (!file || !file.type || !file.type.startsWith('video/')) {
            Toast.warning('Bitte eine Video-Datei wählen.');
            return;
        }
        // Frontend-Limit (Backend prüft erneut)
        if (file.size > 500 * 1024 * 1024) {
            Toast.warning('Datei größer als 500 MB — bitte vorher verkleinern.');
            return;
        }

        vsHideError();
        vsBusy = true;
        vsBtnConvert.disabled = true;
        vsBtnThumb.disabled   = true;
        vsName.textContent = file.name;
        vsSize.textContent = '⏳ Upload läuft…';
        vsPreview.hidden = false;
        vsGroup.hidden   = true;

        const fd = new FormData();
        fd.append('file', file);

        try {
            const resp = await fetch('api/upload.php', { method: 'POST', body: fd });
            const data = await resp.json().catch(() => ({ success: false, error: 'Ungültige Server-Antwort.' }));

            if (data.success && data.type === 'video' && data.filename) {
                vsUploadedFile = data.filename;
                vsSize.textContent = vsFmtSize(file.size);
                vsGroup.hidden   = false;
                vsBtnConvert.disabled = false;
                vsBtnThumb.disabled   = false;
                Toast.success('Video bereit für den Export.');
            } else {
                vsResetState();
                vsShowError(resp.status, data.error || 'Upload fehlgeschlagen.');
            }
        } catch (_) {
            vsResetState();
            vsShowError(0, 'Netzwerkfehler beim Upload.');
        }
        vsBusy = false;
    }

    async function vsConvert() {
        if (!vsUploadedFile || vsBusy) return;
        const preset = (document.querySelector('input[name="vs-preset"]:checked') || {}).value || '1080p';
        if (preset !== '720p' && preset !== '1080p') {
            Toast.error('Ungültiges Preset.');
            return;
        }

        vsHideError();
        vsBusy = true;
        vsBtnConvert.disabled = true;
        vsBtnThumb.disabled   = true;
        const orig = vsBtnConvert.textContent;
        vsBtnConvert.textContent = '⏳ Konvertiere…';

        vsResult.hidden = true;
        vsProgress.hidden = false;
        if (vsIndet) vsIndet.stop();
        vsIndet = csfIndeterminate(vsProgFill, vsProgStatus, 'Konvertiere ' + preset + '…');
        vsProgPct.textContent = '';

        let httpStatus = 0;
        try {
            const resp = await fetch('api/export.php', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify({
                    action:   'convert',
                    filename: vsUploadedFile,
                    preset:   preset,
                }),
            });
            httpStatus = resp.status;
            const data = await resp.json().catch(() => ({ success: false, error: 'Ungültige Server-Antwort.' }));

            if (vsIndet) {
                vsIndet.stop(100, data.success ? 'Fertig.' : 'Fehlgeschlagen.');
                vsIndet = null;
            }
            vsProgPct.textContent = '100%';
            setTimeout(() => { vsProgress.hidden = true; }, 400);

            if (data.success && data.data && data.data.url) {
                vsDownload.href     = data.data.url;
                vsDownload.download = data.data.filename || 'export.mp4';
                vsResultMeta.textContent =
                    (data.data.filename || '') + ' · ' +
                    (data.data.preset || preset).toUpperCase() + ' · ' +
                    vsFmtSize(data.data.size_bytes || 0);
                vsResult.hidden = false;
                Toast.success('Export erfolgreich!');
            } else {
                vsShowError(httpStatus, data.error);
            }
        } catch (_) {
            if (vsIndet) { vsIndet.stop(0, 'Fehlgeschlagen.'); vsIndet = null; }
            vsProgress.hidden = true;
            vsShowError(0, 'Netzwerkfehler beim Export.');
        }

        vsBtnConvert.textContent = orig;
        vsBtnConvert.disabled = false;
        vsBtnThumb.disabled   = false;
        vsBusy = false;
    }

    async function vsThumbnail() {
        if (!vsUploadedFile || vsBusy) return;

        vsHideError();
        vsBusy = true;
        vsBtnThumb.disabled = true;
        vsBtnConvert.disabled = true;
        const orig = vsBtnThumb.textContent;
        vsBtnThumb.textContent = '⏳ Thumbnail…';

        let httpStatus = 0;
        try {
            const resp = await fetch('api/export.php', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify({
                    action:   'thumbnail',
                    filename: vsUploadedFile,
                    offset:   '00:00:01',
                }),
            });
            httpStatus = resp.status;
            const data = await resp.json().catch(() => ({ success: false, error: 'Ungültige Server-Antwort.' }));

            if (data.success && data.data && data.data.url) {
                vsThumbImg.src = data.data.url;
                vsThumbPrev.hidden = false;
                Toast.success('Thumbnail erstellt.');
            } else {
                vsShowError(httpStatus, data.error);
            }
        } catch (_) {
            vsShowError(0, 'Netzwerkfehler beim Thumbnail.');
        }

        vsBtnThumb.textContent = orig;
        vsBtnThumb.disabled = false;
        vsBtnConvert.disabled = false;
        vsBusy = false;
    }

    // Event-Listener
    vsDropzone?.addEventListener('click', (e) => {
        if (e.target === vsInput) return;
        vsInput?.click();
    });
    ['dragenter', 'dragover'].forEach(ev => {
        vsDropzone?.addEventListener(ev, (e) => {
            e.preventDefault();
            vsDropzone.classList.add('dropzone--active');
        });
    });
    ['dragleave', 'drop'].forEach(ev => {
        vsDropzone?.addEventListener(ev, () => vsDropzone.classList.remove('dropzone--active'));
    });
    vsDropzone?.addEventListener('drop', (e) => {
        e.preventDefault();
        const f = e.dataTransfer?.files?.[0];
        if (f) vsUpload(f);
    });
    vsInput?.addEventListener('change', () => {
        const f = vsInput.files?.[0];
        if (f) vsUpload(f);
    });
    vsRemove?.addEventListener('click', () => {
        vsResetState();
        vsHideError();
    });
    vsBtnConvert?.addEventListener('click', vsConvert);
    vsBtnThumb?.addEventListener('click', vsThumbnail);
    vsErrorClose?.addEventListener('click', vsHideError);

})();
</script>

<?php require_once 'includes/footer.php'; ?>
