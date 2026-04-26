<?php
require_once 'includes/config.php';
require_once 'includes/prompt-engine.php';
$pageTitle = 'Image Studio';
$extraJs   = ['editor.js', 'upload.js'];
require_once 'includes/header.php';
?>

<div class="studio-page">

    <!-- ── Hero ───────────────────────────────────────────────── -->
    <div class="studio-hero">
        <div>
            <h2 class="studio-hero__title">Image Studio</h2>
            <p class="studio-hero__sub text-muted">
                Erstelle Charaktere, Fahrzeuge, Produkte, Kreaturen, Startframes
                und Endframes für deine Videos.
            </p>
        </div>
        <?php if (empty($_SESSION['api_key'])): ?>
            <a href="api-key.php" class="btn btn-secondary btn-sm">⚠ API-Key verbinden</a>
        <?php else: ?>
            <span class="badge-connected">● API verbunden</span>
        <?php endif; ?>
    </div>

    <!-- ── Smart Guidance ─────────────────────────────────────── -->
    <div class="guidance-bar">
        <div class="guidance-tip">
            💡 <strong>Character Sheet:</strong>
            Für stabile Charaktere nutze später den Character Sheet Workflow — er sichert konsistentes Aussehen über mehrere Bilder.
        </div>
        <div class="guidance-tip">
            🎬 <strong>Startframes:</strong>
            Startframes funktionieren besser mit klarer Komposition und eindeutigem Bildmittelpunkt.
        </div>
    </div>

    <div class="studio-grid">

        <!-- ── Linke Spalte: Eingabe ──────────────────────────── -->
        <div class="studio-col-input">

            <!-- Formular: Prompt + Template -->
            <div class="card mb-4">
                <div class="card-header">
                    <span class="card-title">Beschreibung &amp; Template</span>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="prompt-input">Beschreibung</label>
                        <textarea
                            id="prompt-input"
                            placeholder="z.B. junger Mann in schwarzer Lederjacke, futuristische Stadt im Hintergrund …"
                            rows="4"
                        ></textarea>
                        <span id="prompt-counter" class="text-sm" style="text-align:right; margin-top:4px;"></span>
                    </div>

                    <div class="form-group">
                        <label for="template-select">Template</label>
                        <select id="template-select">
                            <option value="character">Realistic Character</option>
                            <option value="car">Vehicle / Car</option>
                            <option value="product">Product</option>
                            <option value="creature">Creature</option>
                            <option value="startframe">Startframe</option>
                            <option value="endframe">Endframe</option>
                            <option value="character_sheet">Character Sheet</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Upload: optionales Referenzbild -->
            <div class="card mb-4">
                <div class="card-header">
                    <span class="card-title">Referenzbild <span class="text-muted text-sm">(optional)</span></span>
                </div>
                <div class="card-body">
                    <div id="image-dropzone" class="dropzone">
                        <p class="dropzone__label">Bild hierher ziehen oder klicken</p>
                        <p class="text-muted text-sm">JPEG, PNG, WEBP · max. 10 MB</p>
                        <input type="file" id="image-input" accept="image/jpeg,image/png,image/webp" hidden>
                    </div>

                    <div id="image-preview" class="upload-preview" hidden>
                        <img class="upload-image-preview" src="" alt="Vorschau">
                        <div class="upload-meta">
                            <span class="upload-filename text-sm font-semibold"></span>
                            <span class="upload-filesize text-sm text-muted"></span>
                        </div>
                        <button id="btn-remove-upload" class="btn btn-secondary btn-sm">Entfernen</button>
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
                            ✦ Bild-Prompt erstellen
                        </button>
                        <button id="btn-improve" class="btn btn-secondary" id="btn-make-it-better">
                            ↑ Prompt verbessern
                        </button>
                        <button id="btn-cinematic" class="btn btn-secondary" id="btn-cinematic-upgrade">
                            🎬 Cinematic Upgrade
                        </button>
                        <button id="btn-save-element" class="btn btn-secondary" disabled title="Kommt in Phase 2">
                            💾 Als Element speichern
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
                    <button id="btn-copy-positive" class="btn btn-secondary btn-sm" hidden>
                        Kopieren
                    </button>
                </div>
                <div class="card-body">

                    <!-- Leer-State -->
                    <div id="result-empty" class="result-empty">
                        <div class="result-empty__icon">✦</div>
                        <p>Beschreibe dein Bild und klicke auf<br><strong>„Bild-Prompt erstellen"</strong></p>
                    </div>

                    <!-- Ergebnis -->
                    <div id="result-output" hidden>

                        <div class="prompt-block mb-4">
                            <label class="prompt-block__label">Positiver Prompt</label>
                            <div id="positive-prompt" class="prompt-block__text"></div>
                        </div>

                        <div class="prompt-block">
                            <label class="prompt-block__label prompt-block__label--neg">Negativer Prompt</label>
                            <div id="negative-prompt" class="prompt-block__text prompt-block__text--neg"></div>
                        </div>

                        <!-- Preview-Platzhalter -->
                        <div class="preview-placeholder mt-6">
                            <div class="preview-placeholder__box">
                                <span class="preview-placeholder__icon">🖼</span>
                                <p class="text-sm text-muted">Bildgenerierung wird später über API angebunden</p>
                            </div>
                        </div>

                    </div><!-- #result-output -->

                </div>
            </div>

        </div><!-- .studio-col-result -->

    </div><!-- .studio-grid -->

</div><!-- .studio-page -->


<style>
/* Studio-Page Layout */
.studio-page          { max-width: 1200px; }

.studio-hero {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 20px;
}
.studio-hero__title { font-size: 1.5rem; font-weight: 700; margin-bottom: 4px; }
.studio-hero__sub   { font-size: 0.9rem; max-width: 520px; }

.badge-connected {
    font-size: 0.75rem;
    color: #4ade80;
    background: rgba(74,222,128,0.1);
    border: 1px solid rgba(74,222,128,0.3);
    border-radius: 20px;
    padding: 4px 10px;
    white-space: nowrap;
    flex-shrink: 0;
}

/* Guidance Bar */
.guidance-bar {
    display: flex;
    gap: 12px;
    margin-bottom: 24px;
    flex-wrap: wrap;
}
.guidance-tip {
    flex: 1;
    min-width: 220px;
    background: var(--bg-elevated);
    border: 1px solid var(--border-color);
    border-left: 3px solid var(--accent-blue);
    border-radius: var(--radius-sm);
    padding: 10px 14px;
    font-size: 0.8rem;
    color: var(--text-secondary);
    line-height: 1.5;
}
.guidance-tip strong { color: var(--text-primary); }

/* Studio Grid */
.studio-grid {
    display: grid;
    grid-template-columns: 420px 1fr;
    gap: 24px;
    align-items: start;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

/* Dropzone */
.dropzone {
    border: 2px dashed var(--border-color);
    border-radius: var(--radius);
    padding: 28px 20px;
    text-align: center;
    cursor: pointer;
    transition: border-color var(--transition), background var(--transition);
}
.dropzone:hover,
.dropzone--active {
    border-color: var(--accent-blue);
    background: var(--accent-blue-glow);
}
.dropzone__label { color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 4px; }

/* Upload Preview */
.upload-preview {
    margin-top: 12px;
    display: flex;
    align-items: center;
    gap: 12px;
    background: var(--bg-elevated);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-sm);
    padding: 10px 12px;
}
.upload-image-preview {
    width: 56px; height: 56px;
    object-fit: cover;
    border-radius: var(--radius-sm);
    flex-shrink: 0;
}
.upload-meta { flex: 1; display: flex; flex-direction: column; gap: 2px; }

/* Prompt Result */
.result-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 12px;
    padding: 60px 20px;
    color: var(--text-muted);
    text-align: center;
    line-height: 1.6;
}
.result-empty__icon { font-size: 2rem; opacity: 0.3; }
.result-empty strong { color: var(--text-secondary); }

.prompt-block { display: flex; flex-direction: column; gap: 6px; }
.prompt-block__label {
    font-size: 0.75rem;
    font-weight: 600;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: var(--accent-blue);
}
.prompt-block__label--neg { color: #f56565; }
.prompt-block__text {
    background: var(--bg-elevated);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-sm);
    padding: 12px;
    font-size: 0.8rem;
    color: var(--text-secondary);
    line-height: 1.6;
    white-space: pre-wrap;
    word-break: break-word;
    max-height: 180px;
    overflow-y: auto;
}
.prompt-block__text--neg { border-color: rgba(245,101,101,0.2); }

.preview-placeholder__box {
    border: 2px dashed var(--border-color);
    border-radius: var(--radius);
    padding: 40px;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}
.preview-placeholder__icon { font-size: 2rem; opacity: 0.3; }

.h-full { height: 100%; }
.mb-4   { margin-bottom: 16px; }
.mt-6   { margin-top: 24px; }

@media (max-width: 900px) {
    .studio-grid { grid-template-columns: 1fr; }
}
</style>

<script>
(function () {
    const promptInput   = document.getElementById('prompt-input');
    const templateSel   = document.getElementById('template-select');
    const btnBuild      = document.getElementById('btn-build-prompt');
    const btnImprove    = document.getElementById('btn-improve');
    const btnCinematic  = document.getElementById('btn-cinematic');
    const btnCopyPos    = document.getElementById('btn-copy-positive');
    const btnRemoveUp   = document.getElementById('btn-remove-upload');

    const resultEmpty   = document.getElementById('result-empty');
    const resultOutput  = document.getElementById('result-output');
    const positiveEl    = document.getElementById('positive-prompt');
    const negativeEl    = document.getElementById('negative-prompt');

    // Aktueller Prompt-State (für Modifier-Buttons)
    let currentPositive = '';
    let currentNegative = '';

    // ── Upload & Dropzone initialisieren ──────────────────────
    UploadPreview.init('#image-input', '#image-preview', 'image');
    DropZone.init('#image-dropzone', '#image-input', 'image');

    btnRemoveUp?.addEventListener('click', () => {
        UploadPreview.reset(document.getElementById('image-preview'));
        document.getElementById('image-input').value = '';
    });

    // ── Prompt API-Call ───────────────────────────────────────
    async function callGenerateImage(action = 'build') {
        const input    = promptInput?.value.trim() ?? '';
        const template = templateSel?.value ?? 'character';

        // Für Modifier: bestehenden positiven Prompt mitschicken
        const body = { input, template, action };
        if (action !== 'build' && currentPositive) {
            body.input = currentPositive; // Modifier arbeiten auf bestehendem Prompt
        }

        const btn = action === 'build' ? btnBuild
                  : action === 'improve' ? btnImprove
                  : btnCinematic;

        const origText = btn.textContent;
        btn.disabled = true;
        btn.textContent = '…';

        try {
            const res  = await fetch('api/generate-image.php', {
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
            showResult(data.positive, data.negative);

        } catch {
            Toast.error('Netzwerkfehler — bitte erneut versuchen.');
        } finally {
            btn.disabled = false;
            btn.textContent = origText;
        }
    }

    function showResult(positive, negative) {
        positiveEl.textContent = positive;
        negativeEl.textContent = negative;
        resultEmpty.hidden  = true;
        resultOutput.hidden = false;
        btnCopyPos.hidden   = false;
        btnImprove.disabled    = false;
        btnCinematic.disabled  = false;
    }

    // ── Event Listeners ───────────────────────────────────────
    btnBuild?.addEventListener('click',     () => callGenerateImage('build'));
    btnImprove?.addEventListener('click',   () => {
        if (!currentPositive) { Toast.warning('Bitte zuerst einen Prompt erstellen.'); return; }
        callGenerateImage('improve');
    });
    btnCinematic?.addEventListener('click', () => {
        if (!currentPositive) { Toast.warning('Bitte zuerst einen Prompt erstellen.'); return; }
        callGenerateImage('cinematic');
    });

    // Prompt kopieren
    btnCopyPos?.addEventListener('click', () => {
        navigator.clipboard.writeText(currentPositive)
            .then(() => Toast.success('Prompt kopiert!'))
            .catch(() => Toast.error('Kopieren fehlgeschlagen.'));
    });

    // Template-Wechsel → Hinweis
    templateSel?.addEventListener('change', () => {
        if (currentPositive) {
            Toast.info('Template geändert — Prompt neu erstellen für beste Ergebnisse.');
        }
    });

})();
</script>

<?php require_once 'includes/footer.php'; ?>
