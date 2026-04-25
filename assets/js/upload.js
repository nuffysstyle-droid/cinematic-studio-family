/* ============================================================
   upload.js — Cinematic Studio Family
   Upload Preview, Dateiname, Validierung
   Geladen auf: image-studio, video-studio
   ============================================================ */

'use strict';

/* ----------------------------------------------------------
   KONFIGURATION
   ---------------------------------------------------------- */
const UPLOAD_CONFIG = {
    image: {
        accept:   ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
        maxBytes: 20 * 1024 * 1024,   // 20 MB
        label:    'Bild',
    },
    video: {
        accept:   ['video/mp4', 'video/quicktime', 'video/x-matroska', 'video/webm'],
        maxBytes: 500 * 1024 * 1024,  // 500 MB
        label:    'Video',
    },
};


/* ----------------------------------------------------------
   VALIDIERUNG
   ---------------------------------------------------------- */
const UploadValidator = (() => {

    function validate(file, type = 'image') {
        const cfg = UPLOAD_CONFIG[type];
        if (!cfg) return { ok: false, error: 'Unbekannter Upload-Typ.' };

        if (!cfg.accept.includes(file.type)) {
            return {
                ok: false,
                error: `Ungültiges Format. Erlaubt: ${cfg.accept.map(t => t.split('/')[1]).join(', ')}`
            };
        }

        if (file.size > cfg.maxBytes) {
            const mb = (cfg.maxBytes / 1024 / 1024).toFixed(0);
            return { ok: false, error: `Datei zu groß. Maximum: ${mb} MB` };
        }

        return { ok: true };
    }

    function formatBytes(bytes) {
        if (bytes < 1024)        return `${bytes} B`;
        if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
        return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
    }

    return { validate, formatBytes };
})();


/* ----------------------------------------------------------
   UPLOAD PREVIEW
   ---------------------------------------------------------- */
const UploadPreview = (() => {

    function init(inputSelector, previewSelector, type = 'image') {
        const input   = document.querySelector(inputSelector);
        const preview = document.querySelector(previewSelector);
        if (!input || !preview) return;

        const nameEl  = preview.querySelector('.upload-filename');
        const sizeEl  = preview.querySelector('.upload-filesize');
        const imgEl   = preview.querySelector('.upload-image-preview');
        const videoEl = preview.querySelector('.upload-video-preview');

        input.addEventListener('change', () => {
            const file = input.files?.[0];
            if (!file) return;

            const result = UploadValidator.validate(file, type);
            if (!result.ok) {
                Toast.error(result.error);
                input.value = '';
                reset(preview);
                return;
            }

            if (nameEl) nameEl.textContent = file.name;
            if (sizeEl) sizeEl.textContent = UploadValidator.formatBytes(file.size);

            const url = URL.createObjectURL(file);

            if (type === 'image' && imgEl) {
                imgEl.src = url;
                imgEl.hidden = false;
                if (videoEl) videoEl.hidden = true;
            }

            if (type === 'video' && videoEl) {
                videoEl.src = url;
                videoEl.hidden = false;
                if (imgEl) imgEl.hidden = true;
            }

            preview.hidden = false;
            console.log('[UploadPreview] Datei bereit:', file.name, UploadValidator.formatBytes(file.size));
        });
    }

    function reset(preview) {
        if (!preview) return;
        const imgEl   = preview.querySelector('.upload-image-preview');
        const videoEl = preview.querySelector('.upload-video-preview');
        const nameEl  = preview.querySelector('.upload-filename');
        const sizeEl  = preview.querySelector('.upload-filesize');

        if (imgEl)   { imgEl.src = '';   imgEl.hidden = true; }
        if (videoEl) { videoEl.src = ''; videoEl.hidden = true; }
        if (nameEl)  nameEl.textContent = '';
        if (sizeEl)  sizeEl.textContent = '';
        preview.hidden = true;
    }

    return { init, reset };
})();


/* ----------------------------------------------------------
   DRAG & DROP
   ---------------------------------------------------------- */
const DropZone = (() => {

    function init(zoneSelector, inputSelector, type = 'image') {
        const zone  = document.querySelector(zoneSelector);
        const input = document.querySelector(inputSelector);
        if (!zone || !input) return;

        ['dragenter', 'dragover'].forEach(ev => {
            zone.addEventListener(ev, e => {
                e.preventDefault();
                zone.classList.add('dropzone--active');
            });
        });

        ['dragleave', 'drop'].forEach(ev => {
            zone.addEventListener(ev, () => zone.classList.remove('dropzone--active'));
        });

        zone.addEventListener('drop', e => {
            e.preventDefault();
            const file = e.dataTransfer?.files?.[0];
            if (!file) return;

            const result = UploadValidator.validate(file, type);
            if (!result.ok) { Toast.error(result.error); return; }

            // DataTransfer auf Input setzen
            const dt = new DataTransfer();
            dt.items.add(file);
            input.files = dt.files;
            input.dispatchEvent(new Event('change'));
        });

        // Klick auf Drop-Zone öffnet Datei-Dialog
        zone.addEventListener('click', () => input.click());
    }

    return { init };
})();


/* ----------------------------------------------------------
   INIT
   ---------------------------------------------------------- */
document.addEventListener('DOMContentLoaded', () => {
    // Wird seitenspezifisch aufgerufen, sobald die entsprechenden
    // HTML-Elemente auf image-studio.php / video-studio.php existieren.
    // Beispiel (später in den Seiten):
    // UploadPreview.init('#image-input', '#image-preview', 'image');
    // DropZone.init('#image-dropzone', '#image-input', 'image');
    console.log('[upload.js] bereit');
});
