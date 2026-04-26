<?php
require_once 'includes/config.php';
$pageTitle = 'Element Library';
$extraJs   = ['upload.js'];
require_once 'includes/header.php';
?>

<div class="studio-page">

    <!-- ── Hero ───────────────────────────────────────────────── -->
    <div class="studio-hero">
        <div>
            <h2 class="studio-hero__title">Element Library</h2>
            <p class="studio-hero__sub text-muted">
                Speichere Charaktere, Autos, Produkte, Logos und Style-Referenzen
                für wiederkehrende Videos.
            </p>
        </div>
        <button id="btn-toggle-form" class="btn btn-primary btn-sm">+ Element hinzufügen</button>
    </div>

    <!-- ── Hinweisbox ─────────────────────────────────────────── -->
    <div class="guidance-bar">
        <div class="guidance-tip">
            💡 <strong>Elemente ≠ Startframes:</strong>
            Elemente sind wiederverwendbare Referenzen für Charaktere, Objekte oder Styles —
            keine Startframes. Sie können später im Video Studio als Basis ausgewählt werden.
        </div>
    </div>

    <!-- ── Formular (eingeklappt) ─────────────────────────────── -->
    <div id="element-form-wrapper" class="card mb-6" hidden>
        <div class="card-header">
            <span class="card-title">Neues Element erstellen</span>
            <button id="btn-close-form" class="btn btn-secondary btn-sm">Schließen</button>
        </div>
        <div class="card-body">
            <div class="element-form-grid">

                <!-- Linke Seite: Felder -->
                <div>
                    <div class="form-group">
                        <label for="el-name">Name <span class="text-muted">(erforderlich)</span></label>
                        <input type="text" id="el-name" placeholder="z.B. Max, Porsche 911, Neon City …">
                    </div>

                    <div class="form-group">
                        <label for="el-type">Typ <span class="text-muted">(erforderlich)</span></label>
                        <select id="el-type">
                            <option value="">— Typ wählen —</option>
                            <option value="character">Charakter</option>
                            <option value="car">Auto</option>
                            <option value="product">Produkt</option>
                            <option value="creature">Kreatur</option>
                            <option value="environment">Umgebung</option>
                            <option value="logo">Logo</option>
                            <option value="object">Objekt</option>
                            <option value="style_reference">Style Reference</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="el-role">Rolle</label>
                        <select id="el-role">
                            <option value="">— Rolle wählen —</option>
                            <option value="main_character">Hauptcharakter</option>
                            <option value="main_object">Hauptobjekt</option>
                            <option value="background">Hintergrund</option>
                            <option value="style_reference">Style Reference</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="el-description">Beschreibung</label>
                        <textarea id="el-description" rows="3" placeholder="Kurze Beschreibung des Elements …"></textarea>
                    </div>
                </div>

                <!-- Rechte Seite: Upload -->
                <div>
                    <label class="form-label-static">Referenzbild <span class="text-muted">(optional)</span></label>
                    <div id="el-dropzone" class="dropzone mt-2">
                        <p class="dropzone__label">Bild hierher ziehen oder klicken</p>
                        <p class="text-muted text-sm">JPEG, PNG, WEBP · max. 10 MB</p>
                        <input type="file" id="el-image-input" accept="image/jpeg,image/png,image/webp" hidden>
                    </div>
                    <div id="el-preview" class="upload-preview" hidden>
                        <img class="upload-image-preview" src="" alt="Vorschau">
                        <div class="upload-meta">
                            <span class="upload-filename text-sm font-semibold"></span>
                            <span class="upload-filesize text-sm text-muted"></span>
                        </div>
                        <button id="btn-remove-el-img" class="btn btn-secondary btn-sm">Entfernen</button>
                    </div>
                    <p id="el-upload-status" class="text-sm text-muted mt-2" hidden></p>
                </div>

            </div><!-- .element-form-grid -->

            <div class="flex justify-between items-center mt-6">
                <p id="el-form-error" class="text-sm" style="color:#f56565;" hidden></p>
                <button id="btn-save-element" class="btn btn-primary">Element speichern</button>
            </div>
        </div>
    </div>

    <!-- ── Element Cards ──────────────────────────────────────── -->
    <div id="elements-loading" class="result-empty">
        <div class="result-empty__icon">⏳</div>
        <p>Elemente werden geladen …</p>
    </div>

    <div id="elements-empty" class="result-empty" hidden>
        <div class="result-empty__icon">🧩</div>
        <p>Noch keine Elemente gespeichert.<br>
        Klicke auf <strong>„+ Element hinzufügen"</strong> um zu starten.</p>
    </div>

    <div id="elements-grid" class="elements-grid" hidden></div>

</div><!-- .studio-page -->


<!-- ── Element Card Template (JS clont diesen) ────────────────── -->
<template id="element-card-template">
    <div class="element-card" data-id="">
        <div class="element-card__img-wrap">
            <img class="element-card__img" src="" alt="" hidden>
            <div class="element-card__img-placeholder">🧩</div>
        </div>
        <div class="element-card__body">
            <div class="element-card__meta">
                <span class="element-card__type"></span>
                <span class="element-card__role"></span>
            </div>
            <h3 class="element-card__name"></h3>
            <p class="element-card__desc text-muted text-sm"></p>
        </div>
        <div class="element-card__actions">
            <button class="btn btn-secondary btn-sm btn-edit" disabled title="Bearbeiten — kommt in Phase 3">Bearbeiten</button>
            <button class="btn btn-danger btn-sm btn-delete">Löschen</button>
        </div>
    </div>
</template>


<style>
.mb-6 { margin-bottom: 24px; }
.mt-2 { margin-top: 8px; }
.mt-6 { margin-top: 24px; }
.form-label-static { font-size: 0.8rem; font-weight: 500; color: var(--text-secondary); }

.element-form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
}

/* Element Cards Grid */
.elements-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 16px;
}

.element-card {
    background: var(--bg-panel);
    border: 1px solid var(--border-color);
    border-radius: var(--radius);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transition: border-color var(--transition);
}
.element-card:hover { border-color: var(--accent-blue); }

.element-card__img-wrap {
    width: 100%;
    aspect-ratio: 4/3;
    background: var(--bg-elevated);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}
.element-card__img {
    width: 100%; height: 100%;
    object-fit: cover;
}
.element-card__img-placeholder {
    font-size: 2.5rem;
    opacity: 0.25;
}

.element-card__body {
    padding: 12px;
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.element-card__meta {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
    margin-bottom: 4px;
}
.element-card__type,
.element-card__role {
    font-size: 0.7rem;
    font-weight: 600;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    padding: 2px 7px;
    border-radius: 20px;
    background: var(--bg-elevated);
    color: var(--text-muted);
    border: 1px solid var(--border-color);
}
.element-card__type { color: var(--accent-blue); border-color: var(--accent-blue-glow); }
.element-card__name {
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--text-primary);
}
.element-card__desc {
    font-size: 0.78rem;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.element-card__actions {
    padding: 10px 12px;
    display: flex;
    gap: 8px;
    border-top: 1px solid var(--border-color);
}
.element-card__actions .btn { flex: 1; }

@media (max-width: 640px) {
    .element-form-grid { grid-template-columns: 1fr; }
}
</style>

<script>
(function () {
    // ── DOM Refs ──────────────────────────────────────────────
    const btnToggleForm  = document.getElementById('btn-toggle-form');
    const btnCloseForm   = document.getElementById('btn-close-form');
    const formWrapper    = document.getElementById('element-form-wrapper');
    const btnSave        = document.getElementById('btn-save-element');
    const btnRemoveImg   = document.getElementById('btn-remove-el-img');
    const formError      = document.getElementById('el-form-error');
    const uploadStatus   = document.getElementById('el-upload-status');

    const elName         = document.getElementById('el-name');
    const elType         = document.getElementById('el-type');
    const elRole         = document.getElementById('el-role');
    const elDesc         = document.getElementById('el-description');
    const elInput        = document.getElementById('el-image-input');

    const loadingEl      = document.getElementById('elements-loading');
    const emptyEl        = document.getElementById('elements-empty');
    const gridEl         = document.getElementById('elements-grid');
    const cardTemplate   = document.getElementById('element-card-template');

    // Tracking der hochgeladenen Bild-URL
    let uploadedImageUrl = '';

    // ── Formular ein/ausklappen ───────────────────────────────
    btnToggleForm?.addEventListener('click', () => {
        formWrapper.hidden = false;
        formWrapper.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
    btnCloseForm?.addEventListener('click', () => { formWrapper.hidden = true; });

    // ── Upload initialisieren ─────────────────────────────────
    UploadPreview.init('#el-image-input', '#el-preview', 'image');
    DropZone.init('#el-dropzone', '#el-image-input', 'image');

    btnRemoveImg?.addEventListener('click', () => {
        UploadPreview.reset(document.getElementById('el-preview'));
        elInput.value    = '';
        uploadedImageUrl = '';
    });

    // Upload → api/upload.php → URL merken
    elInput?.addEventListener('change', async () => {
        const file = elInput.files?.[0];
        if (!file) return;

        if (uploadStatus) { uploadStatus.textContent = 'Bild wird hochgeladen …'; uploadStatus.hidden = false; }

        const fd = new FormData();
        fd.append('file', file);

        try {
            const res  = await fetch('api/upload.php', { method: 'POST', body: fd });
            const data = await res.json();
            if (data.success) {
                uploadedImageUrl = data.url;
                if (uploadStatus) uploadStatus.textContent = '✓ Bild hochgeladen.';
            } else {
                if (uploadStatus) uploadStatus.textContent = 'Upload fehlgeschlagen: ' + data.error;
                Toast.error(data.error);
            }
        } catch {
            if (uploadStatus) uploadStatus.textContent = 'Netzwerkfehler beim Upload.';
        }
    });

    // ── Element speichern ─────────────────────────────────────
    btnSave?.addEventListener('click', async () => {
        formError.hidden = true;
        const name = elName?.value.trim() ?? '';
        const type = elType?.value ?? '';

        if (!name) { showFormError('Name ist erforderlich.'); return; }
        if (!type) { showFormError('Bitte einen Typ wählen.'); return; }

        const orig = btnSave.textContent;
        btnSave.disabled = true;
        btnSave.textContent = 'Speichern …';

        try {
            const res  = await fetch('api/save-element.php', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify({
                    name,
                    type,
                    role:        elRole?.value ?? '',
                    description: elDesc?.value.trim() ?? '',
                    image_url:   uploadedImageUrl,
                }),
            });
            const data = await res.json();

            if (data.success) {
                Toast.success('Element gespeichert.');
                resetForm();
                formWrapper.hidden = true;
                prependCard(data.data);
                gridEl.hidden  = false;
                emptyEl.hidden = true;
            } else {
                showFormError(data.error ?? 'Speichern fehlgeschlagen.');
            }
        } catch {
            showFormError('Netzwerkfehler — bitte erneut versuchen.');
        } finally {
            btnSave.disabled = false;
            btnSave.textContent = orig;
        }
    });

    function showFormError(msg) {
        formError.textContent = msg;
        formError.hidden = false;
    }

    function resetForm() {
        if (elName)  elName.value  = '';
        if (elType)  elType.value  = '';
        if (elRole)  elRole.value  = '';
        if (elDesc)  elDesc.value  = '';
        elInput.value = '';
        uploadedImageUrl = '';
        UploadPreview.reset(document.getElementById('el-preview'));
        if (uploadStatus) { uploadStatus.textContent = ''; uploadStatus.hidden = true; }
    }

    // ── Elemente laden ────────────────────────────────────────
    async function loadElements() {
        try {
            const res  = await fetch('api/elements.php?action=list');
            const data = await res.json();

            loadingEl.hidden = true;

            if (!data.success || !data.data.length) {
                emptyEl.hidden = false;
                return;
            }

            data.data.forEach(prependCard);
            gridEl.hidden = false;
        } catch {
            loadingEl.hidden = true;
            emptyEl.hidden   = false;
        }
    }

    // ── Card rendern ──────────────────────────────────────────
    const TYPE_LABELS = {
        character: 'Charakter', car: 'Auto', product: 'Produkt',
        creature: 'Kreatur', environment: 'Umgebung', logo: 'Logo',
        object: 'Objekt', style_reference: 'Style Ref',
    };
    const ROLE_LABELS = {
        main_character: 'Hauptcharakter', main_object: 'Hauptobjekt',
        background: 'Hintergrund', style_reference: 'Style Reference',
    };

    function prependCard(el) {
        const clone = cardTemplate.content.cloneNode(true);
        const card  = clone.querySelector('.element-card');
        card.dataset.id = el.id;

        // Bild
        if (el.image_url) {
            const img = card.querySelector('.element-card__img');
            const ph  = card.querySelector('.element-card__img-placeholder');
            img.src    = el.image_url;
            img.alt    = el.name;
            img.hidden = false;
            ph.hidden  = true;
        }

        card.querySelector('.element-card__type').textContent = TYPE_LABELS[el.type] ?? el.type;
        card.querySelector('.element-card__name').textContent = el.name;
        card.querySelector('.element-card__desc').textContent = el.description || '';

        const roleEl = card.querySelector('.element-card__role');
        if (el.role && ROLE_LABELS[el.role]) {
            roleEl.textContent = ROLE_LABELS[el.role];
        } else {
            roleEl.remove();
        }

        // Löschen
        card.querySelector('.btn-delete').addEventListener('click', () => deleteElement(el.id, card));

        gridEl.prepend(clone);
    }

    // ── Element löschen ───────────────────────────────────────
    async function deleteElement(id, cardEl) {
        if (!confirm('Element wirklich löschen?')) return;

        try {
            const res  = await fetch('api/elements.php', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify({ action: 'delete', id }),
            });
            const data = await res.json();

            if (data.success) {
                cardEl.remove();
                Toast.success('Element gelöscht.');
                if (!gridEl.querySelector('.element-card')) {
                    gridEl.hidden  = true;
                    emptyEl.hidden = false;
                }
            } else {
                Toast.error(data.error ?? 'Löschen fehlgeschlagen.');
            }
        } catch {
            Toast.error('Netzwerkfehler.');
        }
    }

    // ── Init ──────────────────────────────────────────────────
    loadElements();

})();
</script>

<?php require_once 'includes/footer.php'; ?>
