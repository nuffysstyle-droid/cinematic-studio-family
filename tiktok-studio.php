<?php
require_once 'includes/config.php';
require_once 'includes/prompt-engine.php';
$pageTitle = 'TikTok Studio';
$extraJs   = ['editor.js', 'upload.js'];
require_once 'includes/header.php';
?>

<div class="studio-page">

    <!-- ── Hero ─────────────────────────────────────────────────── -->
    <div class="studio-hero">
        <div>
            <h2 class="studio-hero__title">TikTok Studio</h2>
            <p class="studio-hero__sub text-muted">
                Erstelle virale Hooks, TikTok Ads, kurze 9:16 Clips und Creator-Prompts.
            </p>
        </div>
        <?php if (empty($_SESSION['api_key'])): ?>
            <a href="api-key.php" class="btn btn-secondary btn-sm">⚠ API-Key verbinden</a>
        <?php else: ?>
            <span class="badge-connected">● API verbunden</span>
        <?php endif; ?>
    </div>

    <!-- ── Guidance Bar ──────────────────────────────────────────── -->
    <div class="guidance-bar" id="guidance-bar">
        <div class="guidance-tip">
            <strong>⚡ Hook zuerst:</strong>
            TikTok braucht in den ersten 1–2 Sekunden einen klaren, visuellen Hook — sonst scrollt man weiter.
        </div>
        <div class="guidance-tip">
            <strong>📐 Klar und kurz:</strong>
            Halte die Szene visuell stark und leicht verständlich. Weniger ist mehr.
        </div>
        <div class="guidance-tip">
            <strong>🛒 Für Ads:</strong>
            Produkt früh zeigen, Nutzen in 3 Sekunden erklären, CTA am Ende setzen.
        </div>
    </div>

    <!-- ── Quick Cards: Unterbereiche ────────────────────────────── -->
    <div class="tt-subnav">
        <a href="tiktok-animation.php" class="tt-subcard">
            <span class="tt-subcard__icon">✨</span>
            <div class="tt-subcard__label">Animation Service</div>
            <div class="tt-subcard__sub text-muted">KI-Animationen &amp; Motion</div>
        </a>
        <a href="tiktok-sticker.php" class="tt-subcard">
            <span class="tt-subcard__icon">🎨</span>
            <div class="tt-subcard__label">Sticker Showroom</div>
            <div class="tt-subcard__sub text-muted">Custom Sticker generieren</div>
        </a>
        <a href="ready-videos.php" class="tt-subcard">
            <span class="tt-subcard__icon">📹</span>
            <div class="tt-subcard__label">Sofort fertige Videos</div>
            <div class="tt-subcard__sub text-muted">Ready-to-Post Vorlagen</div>
        </a>
        <a href="trailer-builder.php" class="tt-subcard">
            <span class="tt-subcard__icon">🎞️</span>
            <div class="tt-subcard__label">Trailer Builder</div>
            <div class="tt-subcard__sub text-muted">Cinematic Reels &amp; Trailer</div>
        </a>
    </div>

    <!-- ── Studio Grid ────────────────────────────────────────────── -->
    <div class="studio-grid">

        <!-- ── Linke Spalte: Eingabe ──────────────────────────── -->
        <div class="studio-col-input">

            <!-- Beschreibung + Template -->
            <div class="card mb-4">
                <div class="card-header">
                    <span class="card-title">Idee &amp; Template</span>
                </div>
                <div class="card-body">

                    <div class="form-group">
                        <label for="prompt-input">Beschreibung / Idee</label>
                        <textarea
                            id="prompt-input"
                            placeholder="z.B. Junge Frau öffnet Paket, ist begeistert, hält Produkt in die Kamera …"
                            rows="4"
                        ></textarea>
                        <span id="prompt-counter" class="text-sm" style="text-align:right; margin-top:4px;"></span>
                    </div>

                    <div class="form-group">
                        <label for="template-select">TikTok Template</label>
                        <select id="template-select">
                            <option value="viral_hook">⚡ Viral Hook</option>
                            <option value="tiktok_ad">📢 TikTok Ad</option>
                            <option value="tiktok_shop">🛒 TikTok Shop</option>
                            <option value="creator_intro">🎤 Creator Intro</option>
                            <option value="product_demo">📦 Product Demo</option>
                            <option value="story_clip">🎬 Story Clip</option>
                        </select>
                    </div>

                    <div class="tt-selects">
                        <div class="form-group">
                            <label for="style-select">Musik- / Stilrichtung</label>
                            <select id="style-select">
                                <option value="cinematic">🎬 Cinematic</option>
                                <option value="energy">⚡ Energy</option>
                                <option value="dark">🌑 Dark</option>
                                <option value="luxury">💎 Luxury</option>
                                <option value="emotional">❤️ Emotional</option>
                                <option value="funny">😄 Funny</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="cta-select">Call to Action</label>
                            <select id="cta-select">
                                <option value="follow">👆 Folgen</option>
                                <option value="buy">🛒 Kaufen</option>
                                <option value="comment">💬 Kommentieren</option>
                                <option value="share">📤 Teilen</option>
                                <option value="none">— Kein CTA</option>
                            </select>
                        </div>
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
                        <button id="btn-build" class="btn btn-primary">
                            ⚡ TikTok Prompt erstellen
                        </button>
                        <button id="btn-improve" class="btn btn-secondary" disabled>
                            ↑ Make it Better
                        </button>
                        <button id="btn-cinematic" class="btn btn-secondary" disabled>
                            🎬 Cinematic Upgrade
                        </button>
                    </div>
                </div>
            </div>

        </div><!-- .studio-col-input -->

        <!-- ── Rechte Spalte: Ergebnis ────────────────────────── -->
        <div class="studio-col-result">
            <div class="card h-full">
                <div class="card-header">
                    <span class="card-title">Generierter Prompt</span>
                    <div class="tt-copy-actions" id="copy-actions" hidden>
                        <button id="btn-copy-positive" class="btn btn-secondary btn-sm">Prompt kopieren</button>
                        <button id="btn-copy-hook"     class="btn btn-secondary btn-sm">Hook kopieren</button>
                    </div>
                </div>
                <div class="card-body">

                    <!-- Leer-State -->
                    <div id="result-empty" class="result-empty">
                        <div class="result-empty__icon">⚡</div>
                        <p>Beschreibe deine TikTok-Idee und klicke auf<br><strong>„TikTok Prompt erstellen"</strong></p>
                    </div>

                    <!-- Ergebnis -->
                    <div id="result-output" hidden>

                        <!-- Hook-Vorschlag -->
                        <div class="tt-highlight-box mb-4">
                            <div class="tt-highlight-box__label">
                                <span class="tt-highlight-box__badge">⚡ Hook</span>
                                <span class="text-muted text-sm">Erster Satz / Opening</span>
                            </div>
                            <div id="result-hook" class="tt-highlight-box__text"></div>
                        </div>

                        <!-- Positiver Prompt -->
                        <div class="prompt-block mb-4">
                            <label class="prompt-block__label">Positiver Prompt</label>
                            <div id="positive-prompt" class="prompt-block__text"></div>
                        </div>

                        <!-- Negativer Prompt -->
                        <div class="prompt-block mb-4">
                            <label class="prompt-block__label prompt-block__label--neg">Negativer Prompt</label>
                            <div id="negative-prompt" class="prompt-block__text prompt-block__text--neg"></div>
                        </div>

                        <!-- CTA-Vorschlag -->
                        <div id="cta-block" class="tt-cta-block" hidden>
                            <div class="tt-cta-block__label">📣 CTA-Vorschlag</div>
                            <div id="result-cta" class="tt-cta-block__text"></div>
                        </div>

                        <!-- Preview-Platzhalter -->
                        <div class="preview-placeholder mt-6">
                            <div class="preview-placeholder__box preview-placeholder__box--vertical">
                                <span class="preview-placeholder__icon">📱</span>
                                <p class="text-sm text-muted">9:16 Vorschau — wird später über API angebunden</p>
                            </div>
                        </div>

                    </div><!-- #result-output -->

                </div>
            </div>
        </div><!-- .studio-col-result -->

    </div><!-- .studio-grid -->

</div><!-- .studio-page -->


<style>
/* ── TikTok Studio spezifisch ──────────────────────────────── */
.mb-4 { margin-bottom: 16px; }
.mt-6 { margin-top: 24px; }

/* Sub-Navigation Cards */
.tt-subnav {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 12px;
    margin-bottom: 24px;
}
.tt-subcard {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 4px;
    padding: 14px 16px;
    background: var(--bg-panel);
    border: 1px solid var(--border-color);
    border-radius: var(--radius);
    text-decoration: none;
    color: var(--text-primary);
    transition: background var(--transition), border-color var(--transition), transform var(--transition);
}
.tt-subcard:hover {
    background: var(--bg-elevated);
    border-color: var(--accent-blue);
    transform: translateY(-2px);
}
.tt-subcard__icon {
    font-size: 1.4rem;
    margin-bottom: 4px;
}
.tt-subcard__label {
    font-size: 0.85rem;
    font-weight: 600;
}
.tt-subcard__sub {
    font-size: 0.75rem;
}

/* Stil + CTA nebeneinander */
.tt-selects {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

/* Copy-Buttons Header */
.tt-copy-actions {
    display: flex;
    gap: 8px;
}

/* Hook Highlight Box */
.tt-highlight-box {
    background: rgba(245, 131, 61, 0.07);
    border: 1px solid rgba(245, 131, 61, 0.25);
    border-left: 3px solid var(--accent-orange);
    border-radius: var(--radius);
    padding: 14px 16px;
}
.tt-highlight-box__label {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
}
.tt-highlight-box__badge {
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    background: var(--accent-orange);
    color: #fff;
    border-radius: var(--radius-sm);
    padding: 2px 7px;
}
.tt-highlight-box__text {
    font-size: 0.9rem;
    color: var(--text-primary);
    font-style: italic;
    line-height: 1.6;
}

/* CTA Block */
.tt-cta-block {
    background: var(--bg-elevated);
    border: 1px solid var(--border-color);
    border-radius: var(--radius);
    padding: 12px 16px;
}
.tt-cta-block__label {
    font-size: 0.75rem;
    font-weight: 600;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    color: var(--text-muted);
    margin-bottom: 6px;
}
.tt-cta-block__text {
    font-size: 0.9rem;
    color: var(--text-primary);
    font-weight: 500;
}

/* Preview: vertikales 9:16 Format */
.preview-placeholder__box--vertical {
    aspect-ratio: 9 / 16;
    max-width: 160px;
    margin: 0 auto;
}

/* Responsive */
@media (max-width: 768px) {
    .tt-selects {
        grid-template-columns: 1fr;
    }
    .tt-subnav {
        grid-template-columns: repeat(2, 1fr);
    }
    .tt-copy-actions {
        flex-direction: column;
    }
}
@media (max-width: 480px) {
    .tt-subnav {
        grid-template-columns: 1fr;
    }
}
</style>


<script>
(function () {

    // ── Elemente ──────────────────────────────────────────────────
    const promptInput   = document.getElementById('prompt-input');
    const templateSel   = document.getElementById('template-select');
    const styleSel      = document.getElementById('style-select');
    const ctaSel        = document.getElementById('cta-select');

    const btnBuild      = document.getElementById('btn-build');
    const btnImprove    = document.getElementById('btn-improve');
    const btnCinematic  = document.getElementById('btn-cinematic');

    const resultEmpty   = document.getElementById('result-empty');
    const resultOutput  = document.getElementById('result-output');
    const resultHook    = document.getElementById('result-hook');
    const positiveEl    = document.getElementById('positive-prompt');
    const negativeEl    = document.getElementById('negative-prompt');
    const ctaBlock      = document.getElementById('cta-block');
    const resultCta     = document.getElementById('result-cta');
    const copyActions   = document.getElementById('copy-actions');
    const btnCopyPos    = document.getElementById('btn-copy-positive');
    const btnCopyHook   = document.getElementById('btn-copy-hook');

    // ── State ─────────────────────────────────────────────────────
    let currentPositive = '';
    let currentHook     = '';

    // ── Zeichenzähler ─────────────────────────────────────────────
    const counter = document.getElementById('prompt-counter');
    promptInput?.addEventListener('input', () => {
        const len = promptInput.value.length;
        if (counter) counter.textContent = len > 0 ? len + ' Zeichen' : '';
    });

    // ── API-Call ──────────────────────────────────────────────────
    async function callGenerate(action = 'build') {
        const input    = promptInput?.value.trim() ?? '';
        const template = templateSel?.value ?? 'viral_hook';
        const style    = styleSel?.value    ?? 'cinematic';
        const cta      = ctaSel?.value      ?? 'none';

        const body = { template, style, cta, action };

        if (action === 'build') {
            if (!input) {
                Toast.warning('Bitte eine Idee eingeben.');
                return;
            }
            body.input = input;
        } else {
            // Modifier arbeiten auf dem bestehenden Prompt
            if (!currentPositive) {
                Toast.warning('Bitte zuerst einen Prompt erstellen.');
                return;
            }
            body.input = currentPositive;
        }

        const btn      = action === 'build' ? btnBuild : action === 'improve' ? btnImprove : btnCinematic;
        const origText = btn.textContent;
        btn.disabled   = true;
        btn.textContent = '…';

        try {
            const res  = await fetch('api/generate-tiktok.php', {
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
            currentHook     = data.hook ?? '';
            showResult(data);

        } catch {
            Toast.error('Netzwerkfehler — bitte erneut versuchen.');
        } finally {
            btn.disabled    = false;
            btn.textContent = origText;
        }
    }

    // ── Ergebnis anzeigen ─────────────────────────────────────────
    function showResult(data) {
        // Hook
        resultHook.textContent = data.hook ?? '—';

        // Prompts
        positiveEl.textContent = data.positive ?? '';
        negativeEl.textContent = data.negative ?? '';

        // CTA
        if (data.cta && data.cta.trim() !== '') {
            resultCta.textContent = data.cta;
            ctaBlock.hidden       = false;
        } else {
            ctaBlock.hidden = true;
        }

        // UI-Sichtbarkeit
        resultEmpty.hidden  = true;
        resultOutput.hidden = false;
        copyActions.hidden  = false;

        // Modifier-Buttons freischalten
        btnImprove.disabled   = false;
        btnCinematic.disabled = false;
    }

    // ── Event Listener ────────────────────────────────────────────
    btnBuild?.addEventListener('click',    () => callGenerate('build'));
    btnImprove?.addEventListener('click',  () => callGenerate('improve'));
    btnCinematic?.addEventListener('click',() => callGenerate('cinematic'));

    // Kopieren
    btnCopyPos?.addEventListener('click', () => {
        navigator.clipboard.writeText(currentPositive)
            .then(() => Toast.success('Prompt kopiert!'))
            .catch(() => Toast.error('Kopieren fehlgeschlagen.'));
    });

    btnCopyHook?.addEventListener('click', () => {
        navigator.clipboard.writeText(currentHook)
            .then(() => Toast.success('Hook kopiert!'))
            .catch(() => Toast.error('Kopieren fehlgeschlagen.'));
    });

    // Template-Wechsel
    templateSel?.addEventListener('change', () => {
        if (currentPositive) {
            Toast.info('Template geändert — Prompt neu erstellen für beste Ergebnisse.');
        }
    });

})();
</script>

<?php require_once 'includes/footer.php'; ?>
