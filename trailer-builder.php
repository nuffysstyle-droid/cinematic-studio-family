<?php
require_once 'includes/config.php';
require_once 'includes/prompt-engine.php';
$pageTitle = 'Trailer Builder';
$extraJs   = ['editor.js'];
require_once 'includes/header.php';
?>

<div class="studio-page">

    <!-- ── Hero ─────────────────────────────────────────────────── -->
    <div class="studio-hero">
        <div>
            <h2 class="studio-hero__title">Cinematic Trailer Builder</h2>
            <p class="studio-hero__sub text-muted">
                Baue aus einer Idee eine Trailer-Struktur mit Hook, Eskalation, Finale und Cut.
            </p>
        </div>
        <?php if (empty($_SESSION['api_key'])): ?>
            <a href="api-key.php" class="btn btn-secondary btn-sm">⚠ API-Key verbinden</a>
        <?php else: ?>
            <span class="badge-connected">● API verbunden</span>
        <?php endif; ?>
    </div>

    <!-- ── Guidance Bar ──────────────────────────────────────────── -->
    <div class="guidance-bar">
        <div class="guidance-tip">
            <strong>🎬 Hook zuerst:</strong>
            Ein Trailer braucht sofort einen starken Hook — die ersten 2 Sekunden entscheiden alles.
        </div>
        <div class="guidance-tip">
            <strong>⚖️ Weniger ist mehr:</strong>
            Nicht zu viele Ereignisse in 15 Sekunden packen — Qualität vor Quantität.
        </div>
        <div class="guidance-tip">
            <strong>💥 Starkes Finale:</strong>
            Das Finale sollte visuell klar und stark enden — ein unvergesslicher letzter Frame.
        </div>
    </div>

    <!-- ── Layout ────────────────────────────────────────────────── -->
    <div class="tb-layout">

        <!-- ── Linke Spalte: Formular ────────────────────────────── -->
        <div class="tb-col-input">

            <!-- Idee + Einstellungen -->
            <div class="card tb-mb">
                <div class="card-header">
                    <span class="card-title">Trailer Idee &amp; Einstellungen</span>
                </div>
                <div class="card-body">

                    <div class="form-group">
                        <label for="prompt-input">Trailer Idee / Beschreibung <span class="tb-required">*</span></label>
                        <textarea
                            id="prompt-input"
                            rows="4"
                            placeholder="z.B. Ein einsamer Krieger kehrt in seine zerstörte Heimatstadt zurück und muss sich seiner Vergangenheit stellen …"
                            maxlength="1200"
                        ></textarea>
                        <span id="prompt-counter" class="text-sm text-muted" style="text-align:right;margin-top:4px;display:block;"></span>
                        <span id="input-error" class="tb-field-error" hidden>Bitte eine Idee eingeben.</span>
                    </div>

                    <!-- Template -->
                    <div class="form-group">
                        <label>Trailer Template</label>
                        <div class="tb-template-grid">
                            <?php foreach ([
                                'blockbuster'    => ['🏆', 'Blockbuster'],
                                'action'         => ['💥', 'Action'],
                                'horror'         => ['👁', 'Horror'],
                                'drama'          => ['🎭', 'Drama'],
                                'documentary'    => ['📽', 'Documentary'],
                                'tiktok_trailer' => ['📱', 'TikTok Trailer'],
                            ] as $val => [$icon, $label]): ?>
                            <label class="tb-tmpl-option">
                                <input type="radio" name="template" value="<?= $val ?>" <?= $val === 'blockbuster' ? 'checked' : '' ?>>
                                <span class="tb-tmpl-btn">
                                    <span class="tb-tmpl-btn__icon"><?= $icon ?></span>
                                    <span class="tb-tmpl-btn__label"><?= $label ?></span>
                                </span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Musik + Pacing + Dauer in einer Reihe -->
                    <div class="tb-options-row">
                        <div class="form-group">
                            <label for="music-select">Musik-Stil</label>
                            <select id="music-select">
                                <option value="epic">🎺 Epic</option>
                                <option value="dark">🌑 Dark</option>
                                <option value="emotional">❤️ Emotional</option>
                                <option value="hybrid">⚡ Hybrid</option>
                                <option value="fast_cuts">🥁 Fast Cuts</option>
                                <option value="slow_build">🎻 Slow Build</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="pacing-select">Schnitt-Rhythmus</label>
                            <select id="pacing-select">
                                <option value="trailer">🎬 Trailer-Pacing</option>
                                <option value="slow">🐌 Ruhig</option>
                                <option value="medium">▶ Mittel</option>
                                <option value="fast">⚡ Schnell</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="duration-select">Dauer</label>
                            <select id="duration-select">
                                <option value="15" selected>15s</option>
                                <option value="10">10s</option>
                                <option value="30">30s</option>
                            </select>
                        </div>
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
                        <button id="btn-build" class="btn btn-primary btn-lg">
                            🎬 Trailer Prompt erstellen
                        </button>
                        <button id="btn-improve"   class="btn btn-secondary" disabled>↑ Make it Better</button>
                        <button id="btn-cinematic" class="btn btn-secondary" disabled>✨ Cinematic Upgrade</button>
                        <button id="btn-copy"      class="btn btn-secondary" hidden>📋 Prompt kopieren</button>
                    </div>
                </div>
            </div>

        </div><!-- .tb-col-input -->

        <!-- ── Rechte Spalte: Ergebnis ────────────────────────── -->
        <div class="tb-col-result">

            <!-- Leer-State -->
            <div id="result-empty" class="result-empty">
                <div class="result-empty__icon">🎬</div>
                <p>Beschreibe deine Trailer-Idee<br>und klicke auf <strong>„Trailer Prompt erstellen"</strong></p>
            </div>

            <!-- Ergebnis (hidden bis Build) -->
            <div id="result-output" hidden>

                <!-- ── Timeline ─────────────────────────────── -->
                <div class="card tb-mb">
                    <div class="card-header">
                        <span class="card-title">📽 Trailer Timeline</span>
                        <span id="timeline-meta" class="text-muted text-sm"></span>
                    </div>
                    <div class="card-body">
                        <div id="timeline-container" class="tb-timeline"></div>
                    </div>
                </div>

                <!-- ── Gesamt-Prompt ─────────────────────────── -->
                <div class="card tb-mb">
                    <div class="card-header">
                        <span class="card-title">Gesamt-Trailer-Prompt</span>
                    </div>
                    <div class="card-body">
                        <div class="prompt-block tb-mb-sm">
                            <label class="prompt-block__label">Positiver Prompt</label>
                            <div id="positive-prompt" class="prompt-block__text"></div>
                        </div>
                        <div class="prompt-block">
                            <label class="prompt-block__label prompt-block__label--neg">Negativer Prompt</label>
                            <div id="negative-prompt" class="prompt-block__text prompt-block__text--neg"></div>
                        </div>
                    </div>
                </div>

                <!-- ── Technische Parameter ──────────────────── -->
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Technische Parameter</span>
                    </div>
                    <div class="card-body">
                        <div id="meta-grid" class="tb-meta-grid"></div>
                    </div>
                </div>

            </div><!-- #result-output -->

        </div><!-- .tb-col-result -->

    </div><!-- .tb-layout -->

</div><!-- .studio-page -->


<style>
/* ── Trailer Builder spezifisch ────────────────────────────── */
.tb-mb    { margin-bottom: 16px; }
.tb-mb-sm { margin-bottom: 12px; }

/* Layout */
.tb-layout {
    display: grid;
    grid-template-columns: 420px 1fr;
    gap: 24px;
    align-items: start;
}
.tb-col-input  { display: flex; flex-direction: column; gap: 16px; }
.tb-col-result { display: flex; flex-direction: column; gap: 16px; }

/* Required */
.tb-required { color: var(--accent-orange); margin-left: 2px; }
.tb-field-error { font-size: 0.78rem; color: #f56565; margin-top: 4px; display: block; }

/* Template Grid */
.tb-template-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
    margin-bottom: 4px;
}
.tb-tmpl-option { display: flex; cursor: pointer; }
.tb-tmpl-option input[type="radio"] { display: none; }
.tb-tmpl-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    padding: 10px 6px;
    width: 100%;
    background: var(--bg-elevated);
    border: 2px solid var(--border-color);
    border-radius: var(--radius-sm);
    transition: background var(--transition), border-color var(--transition);
    text-align: center;
}
.tb-tmpl-btn:hover { background: var(--bg-hover); border-color: var(--accent-blue); }
.tb-tmpl-option input[type="radio"]:checked + .tb-tmpl-btn {
    background: var(--accent-blue-glow);
    border-color: var(--accent-blue);
}
.tb-tmpl-btn__icon  { font-size: 1.3rem; }
.tb-tmpl-btn__label { font-size: 0.72rem; font-weight: 600; color: var(--text-secondary); }
.tb-tmpl-option input[type="radio"]:checked + .tb-tmpl-btn .tb-tmpl-btn__label {
    color: var(--accent-blue);
}

/* Optionen Zeile */
.tb-options-row {
    display: grid;
    grid-template-columns: 1fr 1fr 90px;
    gap: 12px;
    margin-top: 4px;
}

/* ── Timeline ────────────────────────────────────────────────── */
.tb-timeline {
    display: flex;
    flex-direction: column;
    gap: 0;
    position: relative;
}
.tb-timeline::before {
    content: '';
    position: absolute;
    left: 40px;
    top: 24px;
    bottom: 24px;
    width: 2px;
    background: var(--border-color);
}

.tb-beat {
    display: grid;
    grid-template-columns: 80px 1fr;
    gap: 0 16px;
    align-items: start;
    padding: 12px 0;
    position: relative;
}
.tb-beat:not(:last-child) {
    border-bottom: 1px solid var(--border-subtle);
}

/* Timestamp + Dot */
.tb-beat__ts {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    padding-top: 2px;
    position: relative;
}
.tb-beat__dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: var(--accent-blue);
    border: 2px solid var(--bg-panel);
    box-shadow: 0 0 0 2px var(--accent-blue);
    flex-shrink: 0;
    z-index: 1;
}
.tb-beat--cut .tb-beat__dot { background: var(--accent-orange); box-shadow: 0 0 0 2px var(--accent-orange); }
.tb-beat--finale .tb-beat__dot { background: #f5d03d; box-shadow: 0 0 0 2px #f5d03d; }

.tb-beat__time {
    font-size: 0.72rem;
    font-weight: 700;
    color: var(--text-muted);
    letter-spacing: 0.04em;
    font-family: 'SF Mono', 'Fira Code', monospace;
}

/* Beat Content */
.tb-beat__content { min-width: 0; }
.tb-beat__header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 4px;
    flex-wrap: wrap;
}
.tb-beat__act {
    font-size: 0.8rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: var(--text-primary);
}
.tb-beat__act-badge {
    font-size: 0.65rem;
    font-weight: 600;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    background: var(--accent-blue-glow);
    color: var(--accent-blue);
    border-radius: var(--radius-sm);
    padding: 2px 6px;
}
.tb-beat--cut .tb-beat__act-badge {
    background: rgba(245,131,61,0.15);
    color: var(--accent-orange);
}
.tb-beat--finale .tb-beat__act-badge {
    background: rgba(245,208,61,0.15);
    color: #f5d03d;
}
.tb-beat__desc {
    font-size: 0.82rem;
    color: var(--text-secondary);
    line-height: 1.5;
    margin-bottom: 8px;
}
.tb-beat__prompt-toggle {
    font-size: 0.75rem;
    color: var(--accent-blue);
    background: none;
    border: none;
    cursor: pointer;
    padding: 0;
    display: flex;
    align-items: center;
    gap: 4px;
}
.tb-beat__prompt-toggle:hover { text-decoration: underline; }
.tb-beat__prompt-box {
    margin-top: 8px;
    background: var(--bg-elevated);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-sm);
    padding: 10px 12px;
    font-size: 0.78rem;
    color: var(--text-secondary);
    line-height: 1.5;
}

/* Meta-Grid */
.tb-meta-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
}
.tb-meta-item {
    background: var(--bg-elevated);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-sm);
    padding: 10px 12px;
}
.tb-meta-item__key {
    font-size: 0.7rem; font-weight: 600; letter-spacing: 0.06em;
    text-transform: uppercase; color: var(--text-muted); margin-bottom: 4px;
}
.tb-meta-item__val {
    font-size: 0.875rem; color: var(--text-primary); font-weight: 500;
}

/* Responsive */
@media (max-width: 1100px) {
    .tb-layout { grid-template-columns: 380px 1fr; }
    .tb-meta-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 900px) {
    .tb-layout { grid-template-columns: 1fr; }
    .tb-options-row { grid-template-columns: 1fr 1fr; }
    .tb-options-row .form-group:last-child { grid-column: 1 / -1; }
}
@media (max-width: 600px) {
    .tb-template-grid { grid-template-columns: repeat(2, 1fr); }
    .tb-options-row { grid-template-columns: 1fr; }
    .tb-meta-grid { grid-template-columns: 1fr 1fr; }
}
</style>


<script>
(function () {

    // ── Elemente ──────────────────────────────────────────────────
    const promptInput   = document.getElementById('prompt-input');
    const promptCounter = document.getElementById('prompt-counter');
    const inputError    = document.getElementById('input-error');
    const musicSel      = document.getElementById('music-select');
    const pacingSel     = document.getElementById('pacing-select');
    const durationSel   = document.getElementById('duration-select');

    const btnBuild      = document.getElementById('btn-build');
    const btnImprove    = document.getElementById('btn-improve');
    const btnCinematic  = document.getElementById('btn-cinematic');
    const btnCopy       = document.getElementById('btn-copy');

    const resultEmpty   = document.getElementById('result-empty');
    const resultOutput  = document.getElementById('result-output');
    const timelineEl    = document.getElementById('timeline-container');
    const timelineMeta  = document.getElementById('timeline-meta');
    const positiveEl    = document.getElementById('positive-prompt');
    const negativeEl    = document.getElementById('negative-prompt');
    const metaGrid      = document.getElementById('meta-grid');

    // ── State ─────────────────────────────────────────────────────
    let currentPositive = '';

    // ── Zeichenzähler ─────────────────────────────────────────────
    promptInput.addEventListener('input', () => {
        const len = promptInput.value.length;
        const max = parseInt(promptInput.getAttribute('maxlength') || '1200', 10);
        promptCounter.textContent = len + ' / ' + max;
        promptCounter.style.color = len > max * 0.85 ? 'var(--accent-orange)' : '';
    });

    // ── Template lesen ────────────────────────────────────────────
    function getTemplate() {
        return document.querySelector('input[name="template"]:checked')?.value ?? 'blockbuster';
    }

    // ── API-Call ──────────────────────────────────────────────────
    async function callGenerate(action = 'build') {
        const input = promptInput.value.trim();

        if (action === 'build') {
            if (!input) {
                inputError.hidden = false;
                promptInput.style.borderColor = '#f56565';
                return;
            }
        } else if (!currentPositive) {
            Toast.warning('Bitte zuerst einen Trailer-Prompt erstellen.');
            return;
        }

        const body = {
            action,
            input:       action === 'build' ? input : currentPositive,
            template:    getTemplate(),
            music_style: musicSel.value,
            pacing:      pacingSel.value,
            duration:    parseInt(durationSel.value, 10),
        };

        const btn      = action === 'build' ? btnBuild : action === 'improve' ? btnImprove : btnCinematic;
        const origText = btn.textContent;
        btn.disabled   = true;
        btn.textContent = '…';

        try {
            const res  = await fetch('api/generate-trailer.php', {
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

            if (action === 'build') {
                showFullResult(data);
            } else {
                // Modifier: nur Prompt aktualisieren
                positiveEl.textContent = data.positive;
                if (data.negative) negativeEl.textContent = data.negative;
                Toast.success(action === 'improve' ? 'Prompt verbessert!' : 'Cinematic Upgrade angewendet!');
            }

        } catch {
            Toast.error('Netzwerkfehler — bitte erneut versuchen.');
        } finally {
            btn.disabled    = false;
            btn.textContent = origText;
        }
    }

    // ── Vollständiges Ergebnis rendern ────────────────────────────
    function showFullResult(data) {
        // Timeline
        renderTimeline(data.timeline ?? [], data.meta?.duration ?? 15);

        // Prompts (DOM-safe)
        positiveEl.textContent = data.positive ?? '';
        negativeEl.textContent = data.negative ?? '';

        // Meta-Grid
        renderMetaGrid(data.meta ?? {});

        // UI
        resultEmpty.hidden  = true;
        resultOutput.hidden = false;
        btnImprove.disabled   = false;
        btnCinematic.disabled = false;
        btnCopy.hidden        = false;
    }

    // ── Timeline rendern ──────────────────────────────────────────
    function renderTimeline(beats, duration) {
        timelineEl.innerHTML = '';
        timelineMeta.textContent = beats.length + ' Szenen · ' + duration + 's';

        beats.forEach((beat, i) => {
            const isLast    = i === beats.length - 1;
            const isCut     = beat.act.toLowerCase().includes('cut') || beat.act.toLowerCase().includes('title');
            const isFinale  = beat.act.toLowerCase().includes('finale') || beat.act.toLowerCase().includes('klimax');
            const promptId  = 'beat-prompt-' + i;

            const row = document.createElement('div');
            row.className = 'tb-beat'
                + (isCut    ? ' tb-beat--cut'    : '')
                + (isFinale ? ' tb-beat--finale' : '');

            // Timestamp-Spalte
            const tsCol = document.createElement('div');
            tsCol.className = 'tb-beat__ts';

            const dot = document.createElement('span');
            dot.className = 'tb-beat__dot';

            const timeLabel = document.createElement('span');
            timeLabel.className = 'tb-beat__time';
            timeLabel.textContent = beat.timestamp ?? '';

            tsCol.appendChild(dot);
            tsCol.appendChild(timeLabel);

            // Content-Spalte
            const content = document.createElement('div');
            content.className = 'tb-beat__content';

            const header = document.createElement('div');
            header.className = 'tb-beat__header';

            const actLabel = document.createElement('span');
            actLabel.className = 'tb-beat__act';
            actLabel.textContent = beat.act ?? '';

            const badge = document.createElement('span');
            badge.className = 'tb-beat__act-badge';
            badge.textContent = isCut ? 'CUT' : isFinale ? 'FINALE' : 'AKT';

            header.appendChild(actLabel);
            header.appendChild(badge);

            const desc = document.createElement('div');
            desc.className = 'tb-beat__desc';
            desc.textContent = beat.desc ?? '';

            // Szenen-Prompt Toggle
            const toggle = document.createElement('button');
            toggle.className = 'tb-beat__prompt-toggle';
            toggle.textContent = '▸ Szenen-Prompt anzeigen';
            toggle.setAttribute('aria-expanded', 'false');
            toggle.setAttribute('aria-controls', promptId);

            const promptBox = document.createElement('div');
            promptBox.className = 'tb-beat__prompt-box';
            promptBox.id = promptId;
            promptBox.textContent = beat.prompt ?? '—';
            promptBox.hidden = true;

            toggle.addEventListener('click', () => {
                const open = promptBox.hidden;
                promptBox.hidden = !open;
                toggle.textContent = open ? '▾ Szenen-Prompt ausblenden' : '▸ Szenen-Prompt anzeigen';
                toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            });

            content.appendChild(header);
            content.appendChild(desc);
            if (beat.prompt) {
                content.appendChild(toggle);
                content.appendChild(promptBox);
            }

            row.appendChild(tsCol);
            row.appendChild(content);
            timelineEl.appendChild(row);
        });
    }

    // ── Meta-Grid rendern ─────────────────────────────────────────
    const TEMPLATE_LABELS   = { blockbuster:'🏆 Blockbuster', action:'💥 Action', horror:'👁 Horror', drama:'🎭 Drama', documentary:'📽 Documentary', tiktok_trailer:'📱 TikTok' };
    const MUSIC_LABELS      = { epic:'🎺 Epic', dark:'🌑 Dark', emotional:'❤️ Emotional', hybrid:'⚡ Hybrid', fast_cuts:'🥁 Fast Cuts', slow_build:'🎻 Slow Build' };
    const PACING_LABELS     = { slow:'🐌 Ruhig', medium:'▶ Mittel', fast:'⚡ Schnell', trailer:'🎬 Trailer' };

    function renderMetaGrid(meta) {
        metaGrid.innerHTML = '';
        const items = [
            { key: 'Template',   val: TEMPLATE_LABELS[meta.template]     ?? meta.template   ?? '—' },
            { key: 'Musik-Stil', val: MUSIC_LABELS[meta.music_style]     ?? meta.music_style ?? '—' },
            { key: 'Schnitt',    val: PACING_LABELS[meta.pacing]         ?? meta.pacing      ?? '—' },
            { key: 'Dauer',      val: meta.duration ? meta.duration + 's' : '—' },
        ];
        items.forEach(p => {
            const item = document.createElement('div');
            item.className = 'tb-meta-item';
            const k = document.createElement('div');
            k.className = 'tb-meta-item__key';
            k.textContent = p.key;
            const v = document.createElement('div');
            v.className = 'tb-meta-item__val';
            v.textContent = p.val;
            item.appendChild(k);
            item.appendChild(v);
            metaGrid.appendChild(item);
        });
    }

    // ── Events ────────────────────────────────────────────────────
    btnBuild.addEventListener('click',     () => callGenerate('build'));
    btnImprove.addEventListener('click',   () => callGenerate('improve'));
    btnCinematic.addEventListener('click', () => callGenerate('cinematic'));

    btnCopy.addEventListener('click', () => {
        navigator.clipboard.writeText(currentPositive)
            .then(() => Toast.success('Prompt kopiert!'))
            .catch(() => Toast.error('Kopieren fehlgeschlagen.'));
    });

    // Fehler zurücksetzen
    promptInput.addEventListener('input', () => {
        inputError.hidden = true;
        promptInput.style.borderColor = '';
    });

})();
</script>

<?php require_once 'includes/footer.php'; ?>
