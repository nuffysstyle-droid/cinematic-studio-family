<?php
require_once 'includes/config.php';
require_once 'includes/prompt-engine.php';
require_once 'includes/guidance.php';
$pageTitle       = 'Image Studio';
$defaultTemplate = 'character';
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

    <!-- ── Smart Guidance (PHP-rendered, JS-updated on template change) ── -->
    <?php renderGuidanceBar('image', $defaultTemplate); ?>

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
/* image-studio.php spezifisch — geteilte Studio-Styles in app.css */
.mb-4 { margin-bottom: 16px; }
.mt-6 { margin-top: 24px; }
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

    // ── Template-Wechsel: Guidance + Toast ───────────────────
    const guidanceTips = <?php echo json_encode(getAllGuidanceTips(), JSON_UNESCAPED_UNICODE); ?>;

    function updateGuidanceBar(template) {
        const bar      = document.getElementById('guidance-bar');
        if (!bar) return;
        const bank     = guidanceTips['image'] ?? {};
        const tips     = [...(bank[template] ?? []), ...(bank['_default'] ?? [])];
        const unique   = [];
        tips.forEach(t => { if (!unique.find(u => u.text === t.text)) unique.push(t); });
        const shown = unique.slice(0, 4);
        bar.innerHTML  = shown.map(t =>
            `<div class="guidance-tip"><strong>${t.icon} ${t.title}:</strong> ${t.text}</div>`
        ).join('');
    }

    function updateWarnings(input, template) {
        let existing = document.getElementById('guidance-warnings');
        const warnings = [];
        const len = input.trim().length;
        if (len > 0 && len < 20)  warnings.push('⚠ Deine Beschreibung ist sehr kurz. Ergänze Aktion, Umgebung und Kamera.');
        if (len > 1500)            warnings.push('⚠ Sehr lange Prompts können instabil werden. Kürze auf das Wesentliche.');
        if (['horror_creature', 'action_trailer', 'blockbuster'].includes(template))
            warnings.push('🎬 Komplexe Templates: halte eine klare Hauptaktion.');

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
        updateGuidanceBar(templateSel.value);
        if (currentPositive) Toast.info('Template geändert — Prompt neu erstellen für beste Ergebnisse.');
    });

    promptInput?.addEventListener('input', () => {
        updateWarnings(promptInput.value, templateSel?.value ?? '');
    });

})();
</script>

<?php require_once 'includes/footer.php'; ?>
