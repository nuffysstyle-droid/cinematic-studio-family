<?php
/**
 * merge-clips.php — Multi-Scene Clip-Merge
 * Cinematic Studio Family
 *
 * Mehrere Video-Clips hochladen und zu einem MP4 zusammenführen.
 * Verwendet api/upload.php (Upload) + api/merge-clips.php (Export).
 */

require_once 'includes/config.php';
$pageTitle = 'Multi-Scene Export';
$extraJs   = ['progress.js'];
require_once 'includes/header.php';
?>

<style>
/* ── Multi-Scene Export — Seiten-spezifische Styles ─────────────────────────*/

/* Hero */
.mc-hero {
    margin-bottom: 32px;
}
.mc-hero__eyebrow {
    font-size: 0.75rem;
    font-weight: 600;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: var(--accent-blue);
    margin-bottom: 8px;
}
.mc-hero__title {
    font-size: 2rem;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0 0 8px;
}
.mc-hero__sub {
    color: var(--text-muted);
    font-size: 0.95rem;
    max-width: 560px;
}

/* Haupt-Grid: Upload links, Einstellungen rechts */
.mc-grid {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 24px;
    align-items: start;
    margin-bottom: 32px;
}
@media (max-width: 900px) {
    .mc-grid { grid-template-columns: 1fr; }
}

/* ── Upload-Seite ─────────────────────────────────────────────────────────── */
.mc-upload-label {
    font-size: 0.8rem;
    font-weight: 600;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--text-muted);
    margin-bottom: 10px;
    display: block;
}

/* Dropzone — ergänzt .dropzone aus app.css */
.mc-dropzone {
    border: 2px dashed var(--border-color);
    border-radius: var(--radius);
    background: var(--bg-card);
    padding: 36px 24px;
    text-align: center;
    cursor: pointer;
    transition: border-color 0.2s, background 0.2s;
    position: relative;
}
.mc-dropzone:hover,
.mc-dropzone--active {
    border-color: var(--accent-blue);
    background: rgba(99, 179, 237, 0.05);
}
.mc-dropzone__icon {
    font-size: 2.2rem;
    margin-bottom: 10px;
    display: block;
}
.mc-dropzone__text {
    color: var(--text-muted);
    font-size: 0.9rem;
    margin-bottom: 14px;
}
.mc-dropzone__hint {
    font-size: 0.75rem;
    color: var(--text-muted);
    opacity: 0.7;
}

/* Clip-Liste */
.mc-clips-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 20px 0 10px;
}
.mc-clips-title {
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.08em;
}
.mc-clip-count-badge {
    background: var(--accent-blue-glow);
    color: var(--accent-blue);
    font-size: 0.75rem;
    font-weight: 700;
    border-radius: 20px;
    padding: 2px 10px;
    min-width: 24px;
    text-align: center;
}

.mc-clips-empty {
    text-align: center;
    padding: 24px;
    color: var(--text-muted);
    font-size: 0.85rem;
    border: 1px dashed var(--border-color);
    border-radius: var(--radius);
}

.mc-clip-list {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.mc-clip {
    display: grid;
    grid-template-columns: 28px 1fr auto auto 32px;
    align-items: center;
    gap: 10px;
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: var(--radius);
    padding: 10px 12px;
    transition: border-color 0.2s;
}
.mc-clip--ready  { border-left: 3px solid var(--accent-blue); }
.mc-clip--error  { border-left: 3px solid #e53e3e; opacity: 0.7; }
.mc-clip--uploading { border-left: 3px solid var(--accent-orange); }

.mc-clip__status {
    font-size: 1rem;
    text-align: center;
    flex-shrink: 0;
}
.mc-clip__name {
    font-size: 0.85rem;
    color: var(--text-primary);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.mc-clip__size {
    font-size: 0.75rem;
    color: var(--text-muted);
    white-space: nowrap;
}
.mc-clip__idx {
    font-size: 0.7rem;
    color: var(--text-muted);
    background: rgba(255,255,255,0.05);
    border-radius: 4px;
    padding: 1px 6px;
    white-space: nowrap;
}
.mc-clip__remove {
    background: none;
    border: none;
    color: var(--text-muted);
    font-size: 1rem;
    cursor: pointer;
    padding: 2px 6px;
    border-radius: 4px;
    line-height: 1;
    transition: color 0.15s, background 0.15s;
}
.mc-clip__remove:hover { color: #e53e3e; background: rgba(229,62,62,0.1); }

/* ── Einstellungen-Seite ──────────────────────────────────────────────────── */
.mc-settings {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: var(--radius);
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 20px;
}
.mc-settings__section-title {
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--text-muted);
    margin-bottom: 10px;
}

/* Preset-Auswahl */
.mc-preset-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}
.mc-preset-option input[type="radio"] { display: none; }
.mc-preset-btn {
    display: block;
    padding: 12px 10px;
    border: 1px solid var(--border-color);
    border-radius: var(--radius);
    background: var(--bg-secondary);
    cursor: pointer;
    text-align: center;
    transition: border-color 0.2s, background 0.2s;
}
.mc-preset-btn__label {
    display: block;
    font-weight: 700;
    font-size: 0.9rem;
    color: var(--text-primary);
}
.mc-preset-btn__sub {
    display: block;
    font-size: 0.7rem;
    color: var(--text-muted);
    margin-top: 2px;
}
.mc-preset-option input[type="radio"]:checked + .mc-preset-btn {
    border-color: var(--accent-blue);
    background: var(--accent-blue-glow);
}
.mc-preset-option input[type="radio"]:checked + .mc-preset-btn .mc-preset-btn__label {
    color: var(--accent-blue);
}

/* Output-Name */
.mc-output-name {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.mc-output-name label {
    font-size: 0.8rem;
    color: var(--text-muted);
}
.mc-output-name input {
    width: 100%;
    box-sizing: border-box;
}

/* Merge-Button */
.btn-merge {
    width: 100%;
    padding: 14px;
    font-size: 1rem;
    font-weight: 700;
    border-radius: var(--radius);
}

/* Info-Note */
.mc-note {
    background: rgba(99,179,237,0.06);
    border: 1px solid rgba(99,179,237,0.2);
    border-left: 3px solid var(--accent-blue);
    border-radius: var(--radius);
    padding: 10px 14px;
    font-size: 0.78rem;
    color: var(--text-muted);
    line-height: 1.55;
}
.mc-note strong {
    color: var(--text-secondary);
}

/* ── Ergebnis-Bereich ────────────────────────────────────────────────────── */
.mc-result {
    background: var(--bg-card);
    border: 1px solid rgba(72,199,116,0.35);
    border-left: 4px solid #48c774;
    border-radius: var(--radius);
    padding: 24px 28px;
    margin-bottom: 32px;
}
.mc-result__header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 16px;
}
.mc-result__icon { font-size: 1.5rem; }
.mc-result__title {
    font-size: 1.15rem;
    font-weight: 700;
    color: #48c774;
    margin: 0;
}
.mc-result__sub {
    font-size: 0.8rem;
    color: var(--text-muted);
    margin: 2px 0 0;
}
.mc-result__meta {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    margin-bottom: 20px;
}
@media (max-width: 640px) {
    .mc-result__meta { grid-template-columns: repeat(2, 1fr); }
}
.mc-result__meta-item {
    background: var(--bg-secondary);
    border-radius: var(--radius);
    padding: 10px 12px;
}
.mc-result__meta-label {
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--text-muted);
    margin-bottom: 4px;
}
.mc-result__meta-value {
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--text-primary);
}
.mc-result__actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

/* ── Fortschritt ─────────────────────────────────────────────────────────── */
.mc-progress {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: var(--radius);
    padding: 28px;
    text-align: center;
    margin-bottom: 32px;
}
.mc-progress__spinner {
    font-size: 2rem;
    animation: mc-spin 1.2s linear infinite;
    display: block;
    margin-bottom: 12px;
}
@keyframes mc-spin { to { transform: rotate(360deg); } }
.mc-progress__title {
    font-size: 1rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 6px;
}
.mc-progress__hint {
    font-size: 0.8rem;
    color: var(--text-muted);
}
</style>

<?php require_once 'includes/sidebar.php'; ?>

<!-- ═══════════════════════════════════════════════════════════════════════════
     HERO
═══════════════════════════════════════════════════════════════════════════ -->
<section class="mc-hero">
    <p class="mc-hero__eyebrow">⛓️ Phase 4 — Export</p>
    <h2 class="mc-hero__title">Multi-Scene Export</h2>
    <p class="mc-hero__sub">
        Lade mehrere Video-Clips hoch und verbinde sie zu einem fertigen MP4.
        Einfacher Cut — keine Übergänge, keine KI, sofort bereit.
    </p>
</section>

<!-- Fortschritts-Banner (nur während Export sichtbar) -->
<div id="mc-progress" class="mc-progress" hidden>
    <span class="mc-progress__spinner">⚙️</span>
    <p class="mc-progress__title">Export läuft…</p>
    <p class="mc-progress__hint">FFmpeg verarbeitet deine Clips. Das kann 1–3 Minuten dauern.</p>

    <!-- Progress-Bar (Phase 4 — TODO #33) -->
    <div class="csf-progress" style="margin-top:16px;">
        <div class="csf-progress__head">
            <span class="csf-progress__label" id="mc-progress-status">Rendering…</span>
            <span class="csf-progress__percent" id="mc-progress-percent">—</span>
        </div>
        <div class="csf-progress__bar">
            <div class="csf-progress__fill" id="mc-progress-fill"></div>
        </div>
    </div>
</div>

<!-- Fehlerbox (Phase 4 — TODO #34) -->
<div id="mc-error-box" class="csf-error-box" hidden>
    <span class="csf-error-box__icon" aria-hidden="true">⚠</span>
    <div class="csf-error-box__body">
        <p class="csf-error-box__title" id="mc-error-title">Es ist ein Fehler aufgetreten.</p>
        <p class="csf-error-box__detail" id="mc-error-detail" hidden></p>
    </div>
    <button type="button" class="csf-error-box__close" id="mc-error-close" aria-label="Fehler schließen">✕</button>
</div>

<!-- Ergebnis-Banner (nach erfolgreichem Export) -->
<div id="mc-result" class="mc-result" hidden>
    <div class="mc-result__header">
        <span class="mc-result__icon">✅</span>
        <div>
            <p class="mc-result__title">Export erfolgreich!</p>
            <p class="mc-result__sub" id="result-filename-sub"></p>
        </div>
    </div>

    <div class="mc-result__meta">
        <div class="mc-result__meta-item">
            <p class="mc-result__meta-label">Clips</p>
            <p class="mc-result__meta-value" id="result-clips">—</p>
        </div>
        <div class="mc-result__meta-item">
            <p class="mc-result__meta-label">Preset</p>
            <p class="mc-result__meta-value" id="result-preset">—</p>
        </div>
        <div class="mc-result__meta-item">
            <p class="mc-result__meta-label">Dateigröße</p>
            <p class="mc-result__meta-value" id="result-size">—</p>
        </div>
        <div class="mc-result__meta-item">
            <p class="mc-result__meta-label">Format</p>
            <p class="mc-result__meta-value">MP4 / H.264</p>
        </div>
    </div>

    <div class="mc-result__actions">
        <a id="download-btn" href="#" download class="btn btn-primary">
            ⬇ MP4 herunterladen
        </a>
        <button id="btn-thumbnail" type="button" class="btn btn-secondary">
            🖼 Thumbnail generieren
        </button>
        <button id="btn-new-export" class="btn btn-secondary">
            ↺ Neuer Export
        </button>
    </div>

    <!-- Thumbnail-Vorschau (Phase 4 — TODO #31) -->
    <div id="mc-thumb-preview" class="csf-thumb-preview" hidden>
        <p class="csf-thumb-preview__label">Thumbnail (00:00:01)</p>
        <img id="mc-thumb-img" src="" alt="Thumbnail des exportierten Videos">
    </div>

    <!-- Re-Export mit anderem Preset (Phase 4 — TODO #32) -->
    <div class="csf-export-group" style="margin-top:20px;">
        <p class="csf-export-group__title">Erneut exportieren mit anderem Preset</p>
        <div class="csf-export-presets">
            <label>
                <input type="radio" name="re-preset" value="720p">
                <span>720p · HD</span>
            </label>
            <label>
                <input type="radio" name="re-preset" value="1080p" checked>
                <span>1080p · Full HD</span>
            </label>
        </div>
        <div class="csf-action-row">
            <button id="btn-reexport" type="button" class="btn btn-primary">
                ▶ Erneut exportieren
            </button>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════════
     HAUPT-GRID
═══════════════════════════════════════════════════════════════════════════ -->
<div class="mc-grid">

    <!-- ── Linke Spalte: Upload + Clip-Liste ─────────────────────────────── -->
    <div>
        <!-- Dropzone -->
        <span class="mc-upload-label">1. Clips hochladen</span>

        <div id="mc-dropzone" class="mc-dropzone" role="button" tabindex="0"
             aria-label="Klicken oder Dateien hierher ziehen">
            <span class="mc-dropzone__icon">🎬</span>
            <p class="mc-dropzone__text">
                Dateien hierher ziehen oder klicken zum Auswählen
            </p>
            <label class="btn btn-secondary" style="cursor:pointer; display:inline-block;">
                Clips auswählen
                <input id="file-input" type="file"
                       accept="video/mp4,video/webm,video/quicktime"
                       multiple hidden>
            </label>
            <p class="mc-dropzone__hint">MP4 · WEBM · MOV — max. 100 MB pro Clip</p>
        </div>

        <!-- Clip-Liste Header -->
        <div class="mc-clips-header">
            <span class="mc-clips-title">Hochgeladene Clips</span>
            <span class="mc-clip-count-badge" id="clip-count">0</span>
        </div>

        <!-- Leerzustand -->
        <p class="mc-clips-empty" id="clips-empty">
            Noch keine Clips — lade mindestens 2 Videos hoch.
        </p>

        <!-- Clip-Liste -->
        <ul class="mc-clip-list" id="clip-list"></ul>
    </div>

    <!-- ── Rechte Spalte: Einstellungen + Aktion ──────────────────────────── -->
    <div class="mc-settings">

        <!-- Preset -->
        <div>
            <p class="mc-settings__section-title">2. Export-Qualität</p>
            <div class="mc-preset-grid">
                <label class="mc-preset-option">
                    <input type="radio" name="preset" value="720p">
                    <span class="mc-preset-btn">
                        <span class="mc-preset-btn__label">720p</span>
                        <span class="mc-preset-btn__sub">HD · schnell</span>
                    </span>
                </label>
                <label class="mc-preset-option">
                    <input type="radio" name="preset" value="1080p" checked>
                    <span class="mc-preset-btn">
                        <span class="mc-preset-btn__label">1080p</span>
                        <span class="mc-preset-btn__sub">Full HD · empfohlen</span>
                    </span>
                </label>
            </div>
        </div>

        <!-- Ausgabename -->
        <div class="mc-output-name">
            <p class="mc-settings__section-title">3. Ausgabename <span style="color:var(--text-muted);font-weight:400;font-size:0.7rem;">(optional)</span></p>
            <input id="output-name" type="text"
                   placeholder="z. B. urlaub_2026"
                   maxlength="60"
                   pattern="[a-zA-Z0-9_\-]+"
                   title="Nur Buchstaben, Ziffern, _ und - erlaubt">
        </div>

        <!-- Merge-Button -->
        <div>
            <p class="mc-settings__section-title">4. Exportieren</p>
            <button id="btn-merge" class="btn btn-primary btn-merge" disabled
                    title="Mindestens 2 fertige Clips erforderlich">
                ▶ Clips zusammenfügen
            </button>
        </div>

        <!-- Hinweis -->
        <div class="mc-note">
            <strong>Hinweis:</strong> Alle Clips sollten dasselbe Format (MP4)
            und möglichst gleiche Auflösung haben. Bei gemischten Quellen
            wird das Seitenverhältnis via Letterbox angepasst.<br><br>
            <strong>V1:</strong> Kein Drag-Reorder — Reihenfolge = Upload-Reihenfolge.
        </div>

    </div>
</div>

<!-- Template: Clip-Listeneintrag -->
<template id="tpl-clip">
    <li class="mc-clip">
        <span class="mc-clip__status"></span>
        <span class="mc-clip__name"></span>
        <span class="mc-clip__size"></span>
        <span class="mc-clip__idx"></span>
        <button class="mc-clip__remove" type="button" title="Clip entfernen">✕</button>
    </li>
</template>

<script>
(function () {
    'use strict';

    // ── State ─────────────────────────────────────────────────────────────
    let clips   = [];   // { id, displayName, apiFilename, size, status }
    let merging = false;
    let _idSeq  = 0;

    // ── DOM-Referenzen ────────────────────────────────────────────────────
    const fileInput   = document.getElementById('file-input');
    const dropzone    = document.getElementById('mc-dropzone');
    const clipList    = document.getElementById('clip-list');
    const clipCount   = document.getElementById('clip-count');
    const emptyState  = document.getElementById('clips-empty');
    const btnMerge    = document.getElementById('btn-merge');
    const resultSec   = document.getElementById('mc-result');
    const progressSec = document.getElementById('mc-progress');
    const btnNewExp   = document.getElementById('btn-new-export');
    const outputName  = document.getElementById('output-name');
    const clipTpl     = document.getElementById('tpl-clip');

    // Phase 4 — TODO #31/#32/#33/#34
    const btnThumb       = document.getElementById('btn-thumbnail');
    const btnReexport    = document.getElementById('btn-reexport');
    const thumbPreview   = document.getElementById('mc-thumb-preview');
    const thumbImg       = document.getElementById('mc-thumb-img');
    const errorBox       = document.getElementById('mc-error-box');
    const errorTitle     = document.getElementById('mc-error-title');
    const errorDetail    = document.getElementById('mc-error-detail');
    const errorClose     = document.getElementById('mc-error-close');
    const progressFill   = document.getElementById('mc-progress-fill');
    const progressStatus = document.getElementById('mc-progress-status');
    const progressPct    = document.getElementById('mc-progress-percent');

    // State für letzten Merge-Output (für Thumbnail + Re-Export)
    let lastMergedFile = null;   // basename des Merge-Outputs (für action=convert/thumbnail)
    let indeterminate  = null;   // Handle der Bar-Animation

    // ── Upload ────────────────────────────────────────────────────────────

    /**
     * Lädt eine einzelne Datei via api/upload.php hoch.
     * Fügt Clip sofort als "uploading" in die Liste ein und aktualisiert den Status.
     */
    async function uploadFile(file) {
        const id   = 'clip_' + (++_idSeq);
        const clip = {
            id,
            displayName:  file.name,
            apiFilename:  null,
            size:         file.size,
            status:       'uploading',
        };
        clips.push(clip);
        renderClips();

        const fd = new FormData();
        fd.append('file', file);

        try {
            const resp = await fetch('api/upload.php', { method: 'POST', body: fd });
            const data = await resp.json().catch(() => ({ success: false, error: 'Ungültige Server-Antwort.' }));

            if (data.success && data.type === 'video') {
                clip.apiFilename = data.filename;
                clip.status      = 'ready';
            } else {
                clip.status = 'error';
                Toast.error(data.error || 'Upload fehlgeschlagen.');
            }
        } catch (_) {
            clip.status = 'error';
            Toast.error('Netzwerkfehler beim Upload.');
        }

        renderClips();
        updateMergeBtn();
    }

    /**
     * Mehrere Dateien nacheinander hochladen (kein Parallel-Flood der API).
     */
    async function uploadFiles(fileList) {
        for (const file of fileList) {
            if (!file.type.startsWith('video/')) {
                Toast.warning(file.name + ': kein Video — übersprungen.');
                continue;
            }
            if (file.size > 100 * 1024 * 1024) {
                Toast.warning(file.name + ': über 100 MB — übersprungen.');
                continue;
            }
            await uploadFile(file);
        }
    }

    // ── Render ────────────────────────────────────────────────────────────

    function renderClips() {
        const readyCount = clips.filter(c => c.status === 'ready').length;
        clipCount.textContent = String(readyCount);

        // Container leeren (DOM API, kein innerHTML)
        clipList.replaceChildren();

        if (clips.length === 0) {
            emptyState.hidden = false;
            return;
        }
        emptyState.hidden = true;

        let pos = 1;
        clips.forEach(function (clip) {
            const clone = clipTpl.content.cloneNode(true);
            const li    = clone.querySelector('.mc-clip');

            // Modifier-Klasse je Status
            if (clip.status === 'ready')     li.classList.add('mc-clip--ready');
            if (clip.status === 'error')     li.classList.add('mc-clip--error');
            if (clip.status === 'uploading') li.classList.add('mc-clip--uploading');

            // Status-Icon
            const icons = { uploading: '⏳', ready: '✓', error: '✗' };
            clone.querySelector('.mc-clip__status').textContent = icons[clip.status] || '?';

            // Dateiname + Größe + Position (alle via textContent)
            clone.querySelector('.mc-clip__name').textContent = clip.displayName;
            clone.querySelector('.mc-clip__size').textContent = fmtSize(clip.size);
            if (clip.status === 'ready') {
                clone.querySelector('.mc-clip__idx').textContent = '#' + pos;
                pos++;
            }

            // Entfernen-Button
            const removeId = clip.id;
            clone.querySelector('.mc-clip__remove').addEventListener('click', function () {
                clips = clips.filter(function (c) { return c.id !== removeId; });
                renderClips();
                updateMergeBtn();
            });

            clipList.appendChild(clone);
        });
    }

    function updateMergeBtn() {
        const readyClips = clips.filter(function (c) { return c.status === 'ready'; });
        btnMerge.disabled = readyClips.length < 2 || merging;
        btnMerge.title    = readyClips.length < 2
            ? 'Mindestens 2 fertig hochgeladene Clips erforderlich'
            : '';
    }

    // ── Merge / Export ────────────────────────────────────────────────────

    async function startMerge() {
        const readyClips = clips.filter(function (c) { return c.status === 'ready'; });
        if (readyClips.length < 2 || merging) return;

        merging = true;
        btnMerge.disabled     = true;
        btnMerge.textContent  = '⏳ Bitte warten…';
        resultSec.hidden      = true;
        progressSec.hidden    = false;
        hideError();

        // Indeterminate-Animation während des synchronen Requests
        // (Hinweis: api/merge-clips.php blockiert bis fertig — kein Polling möglich)
        progressPct.textContent = '';
        if (indeterminate) indeterminate.stop();
        indeterminate = csfIndeterminate(progressFill, progressStatus, 'Rendering…');

        const preset     = (document.querySelector('input[name="preset"]:checked') || {}).value || '1080p';
        const customName = outputName.value.trim();
        let httpStatus = 0;

        try {
            const resp = await fetch('api/merge-clips.php', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify({
                    clips:       readyClips.map(function (c) { return c.apiFilename; }),
                    preset:      preset,
                    output_name: customName,
                }),
            });
            httpStatus = resp.status;

            const data = await resp.json().catch(function () {
                return { success: false, error: 'Ungültige Server-Antwort.' };
            });

            // Indeterminate stoppen, finalen Wert setzen
            if (indeterminate) {
                indeterminate.stop(100, data.success ? 'Fertig.' : 'Fehlgeschlagen.');
                indeterminate = null;
            }
            progressPct.textContent = '100%';

            // Kurze Verzögerung damit der User die "Fertig"-Bar sieht
            setTimeout(function () { progressSec.hidden = true; }, 400);

            if (data.success) {
                showResult(data);
                Toast.success('Export erfolgreich!');
            } else {
                handleApiError(httpStatus, data.error);
            }
        } catch (_) {
            if (indeterminate) { indeterminate.stop(0, 'Fehlgeschlagen.'); indeterminate = null; }
            progressSec.hidden = true;
            handleApiError(0, 'Netzwerkfehler beim Export.');
        }

        merging               = false;
        btnMerge.textContent  = '▶ Clips zusammenfügen';
        updateMergeBtn();
    }

    function showResult(data) {
        resultSec.hidden = false;
        thumbPreview.hidden = true;   // alte Thumbnails von Vorgänger-Exports zurücksetzen
        thumbImg.src = '';

        // Metadaten (alle via textContent — kein innerHTML)
        document.getElementById('result-filename-sub').textContent = data.filename || '';
        document.getElementById('result-clips').textContent        = (data.clip_count || '?') + ' Clips';
        document.getElementById('result-preset').textContent       = (data.preset || '').toUpperCase();
        document.getElementById('result-size').textContent         = fmtSize(data.size_bytes || 0);

        // Download-Button
        const dlBtn  = document.getElementById('download-btn');
        dlBtn.href   = data.url   || '#';
        dlBtn.download = data.filename || 'export.mp4';

        // Für Thumbnail/Re-Export merken: Merge-Output liegt in storage/exports/.
        // api/export.php verlangt jedoch eine Upload-Datei aus storage/uploads/videos/.
        // Daher: Thumbnail/Re-Export nutzen den merged Filename — falls api/export.php
        // ihn nicht akzeptiert, kommt eine sprechende Fehlermeldung über die Error-Box.
        lastMergedFile = data.filename || null;

        // Re-Export-Preset auf Default setzen (1080p), Thumbnail-Button aktiv
        const reBtns = document.querySelectorAll('input[name="re-preset"]');
        reBtns.forEach(function (r) { r.checked = (r.value === '1080p'); });

        btnThumb.disabled    = !lastMergedFile;
        btnReexport.disabled = !lastMergedFile;

        resultSec.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    // ── Thumbnail (Phase 4 — TODO #31) ────────────────────────────────────

    async function startThumbnail() {
        if (!lastMergedFile) {
            Toast.warning('Kein Export vorhanden — bitte zuerst Clips zusammenfügen.');
            return;
        }
        hideError();
        btnThumb.disabled    = true;
        const orig           = btnThumb.textContent;
        btnThumb.textContent = '⏳ Thumbnail…';

        let httpStatus = 0;
        try {
            const resp = await fetch('api/export.php', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify({
                    action:   'thumbnail',
                    filename: lastMergedFile,
                    offset:   '00:00:01',
                }),
            });
            httpStatus = resp.status;
            const data = await resp.json().catch(function () {
                return { success: false, error: 'Ungültige Server-Antwort.' };
            });

            if (data.success && data.data && data.data.url) {
                // KEIN innerHTML — direkter Property-Set
                thumbImg.src = data.data.url;
                thumbPreview.hidden = false;
                Toast.success('Thumbnail erstellt.');
            } else {
                handleApiError(httpStatus, data.error);
            }
        } catch (_) {
            handleApiError(0, 'Netzwerkfehler beim Thumbnail-Erstellen.');
        }

        btnThumb.disabled    = false;
        btnThumb.textContent = orig;
    }

    // ── Re-Export (Phase 4 — TODO #32) ────────────────────────────────────

    async function startReexport() {
        if (!lastMergedFile) {
            Toast.warning('Kein Export vorhanden — bitte zuerst Clips zusammenfügen.');
            return;
        }
        const newPreset = (document.querySelector('input[name="re-preset"]:checked') || {}).value || '1080p';
        if (newPreset !== '720p' && newPreset !== '1080p') {
            Toast.error('Ungültiges Preset.');
            return;
        }

        hideError();
        btnReexport.disabled = true;
        const orig           = btnReexport.textContent;
        btnReexport.textContent = '⏳ Konvertiere…';

        progressSec.hidden = false;
        if (indeterminate) indeterminate.stop();
        indeterminate = csfIndeterminate(progressFill, progressStatus, 'Konvertiere ' + newPreset + '…');
        progressPct.textContent = '';

        let httpStatus = 0;
        try {
            const resp = await fetch('api/export.php', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify({
                    action:   'convert',
                    filename: lastMergedFile,
                    preset:   newPreset,
                }),
            });
            httpStatus = resp.status;
            const data = await resp.json().catch(function () {
                return { success: false, error: 'Ungültige Server-Antwort.' };
            });

            if (indeterminate) {
                indeterminate.stop(100, data.success ? 'Fertig.' : 'Fehlgeschlagen.');
                indeterminate = null;
            }
            progressPct.textContent = '100%';
            setTimeout(function () { progressSec.hidden = true; }, 400);

            if (data.success && data.data && data.data.url) {
                // Download-Button auf neue Datei updaten
                const dlBtn = document.getElementById('download-btn');
                dlBtn.href  = data.data.url;
                dlBtn.download = data.data.filename || 'export.mp4';
                document.getElementById('result-filename-sub').textContent = data.data.filename || '';
                document.getElementById('result-preset').textContent       = (data.data.preset || newPreset).toUpperCase();
                document.getElementById('result-size').textContent         = fmtSize(data.data.size_bytes || 0);
                lastMergedFile = data.data.filename || lastMergedFile;
                Toast.success('Erneuter Export erfolgreich.');
            } else {
                handleApiError(httpStatus, data.error);
            }
        } catch (_) {
            if (indeterminate) { indeterminate.stop(0, 'Fehlgeschlagen.'); indeterminate = null; }
            progressSec.hidden = true;
            handleApiError(0, 'Netzwerkfehler beim Re-Export.');
        }

        btnReexport.disabled = false;
        btnReexport.textContent = orig;
    }

    // ── Fehler-Anzeige (Phase 4 — TODO #34) ───────────────────────────────

    function handleApiError(httpStatus, rawError) {
        const mapped = (typeof csfMapError === 'function')
            ? csfMapError(httpStatus, rawError)
            : { message: rawError || 'Unbekannter Fehler.', details: '' };

        // Toast (kurze Bestätigung)
        Toast.error(mapped.message);

        // Persistente Fehlerbox (textContent — kein innerHTML)
        errorTitle.textContent = mapped.message;
        if (mapped.details && mapped.details !== mapped.message) {
            errorDetail.textContent = mapped.details;
            errorDetail.hidden = false;
        } else {
            errorDetail.textContent = '';
            errorDetail.hidden = true;
        }
        errorBox.hidden = false;
        errorBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function hideError() {
        errorBox.hidden = true;
        errorTitle.textContent = '';
        errorDetail.textContent = '';
        errorDetail.hidden = true;
    }

    // ── Hilfsfunktionen ───────────────────────────────────────────────────

    function fmtSize(bytes) {
        if (!bytes || bytes <= 0) return '—';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / 1024 / 1024).toFixed(1) + ' MB';
    }

    // ── Event-Listener ────────────────────────────────────────────────────

    // Datei-Input
    fileInput.addEventListener('change', function () {
        const files = Array.from(this.files || []);
        this.value  = ''; // Reset — gleiches File erneut wählbar
        if (files.length) uploadFiles(files);
    });

    // Dropzone — Klick öffnet File-Picker
    dropzone.addEventListener('click', function (e) {
        if (e.target.tagName === 'INPUT') return;
        fileInput.click();
    });
    dropzone.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); fileInput.click(); }
    });

    // Drag-and-Drop
    dropzone.addEventListener('dragover', function (e) {
        e.preventDefault();
        dropzone.classList.add('mc-dropzone--active');
    });
    dropzone.addEventListener('dragleave', function () {
        dropzone.classList.remove('mc-dropzone--active');
    });
    dropzone.addEventListener('drop', function (e) {
        e.preventDefault();
        dropzone.classList.remove('mc-dropzone--active');
        const files = Array.from(e.dataTransfer.files);
        if (!files.length) return;
        uploadFiles(files);
    });

    // Merge-Button
    btnMerge.addEventListener('click', startMerge);

    // Thumbnail / Re-Export / Error-Close
    btnThumb?.addEventListener('click', startThumbnail);
    btnReexport?.addEventListener('click', startReexport);
    errorClose?.addEventListener('click', hideError);

    // Neuer Export
    btnNewExp.addEventListener('click', function () {
        resultSec.hidden = true;
        thumbPreview.hidden = true;
        thumbImg.src = '';
        hideError();
        lastMergedFile = null;
        clips = [];
        renderClips();
        updateMergeBtn();
        outputName.value = '';
        Toast.info('Bereit für den nächsten Export.');
    });

    // ── Init ──────────────────────────────────────────────────────────────
    renderClips();
    updateMergeBtn();

})();
</script>

<?php require_once 'includes/footer.php'; ?>
