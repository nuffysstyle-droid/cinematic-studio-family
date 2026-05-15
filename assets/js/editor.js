/* ============================================================
   editor.js — Cinematic Studio Family
   Studio Editor: Prompt Helper + AI Action Buttons
   Geladen auf: image-studio, video-studio, tiktok-studio,
                tiktok-animation, trailer-builder
   ============================================================ */

'use strict';

/* ----------------------------------------------------------
   PROMPT FIELD HELPER
   ---------------------------------------------------------- */
const PromptField = (() => {
    const FIELD_SELECTOR = '#prompt-input';
    const MAX_CHARS = 1000;

    function getField() {
        return document.querySelector(FIELD_SELECTOR);
    }

    function getValue() {
        return (getField()?.value ?? '').trim();
    }

    function setValue(text) {
        const field = getField();
        if (field) field.value = text;
    }

    function append(text) {
        const field = getField();
        if (!field) return;
        field.value = field.value ? field.value + ' ' + text : text;
    }

    function clear() {
        setValue('');
    }

    function initCounter() {
        const field = getField();
        const counter = document.querySelector('#prompt-counter');
        if (!field || !counter) return;

        const update = () => {
            const len = field.value.length;
            counter.textContent = `${len} / ${MAX_CHARS}`;
            counter.style.color = len > MAX_CHARS * 0.9
                ? 'var(--accent-orange)'
                : 'var(--text-muted)';
        };

        field.addEventListener('input', update);
        update();
    }

    return { getValue, setValue, append, clear, initCounter };
})();


/* ----------------------------------------------------------
   AI ACTION BUTTONS
   Wird von Seiten genutzt, die KEINE eigene Inline-Implementierung
   der Modifier-Buttons haben. Seiten mit eigenem API-Binding
   (video-studio.php, image-studio.php, …) rufen EditorActions.init()
   NICHT auf — sie registrieren ihre eigenen Click-Handler.
   ---------------------------------------------------------- */
const EditorActions = (() => {

    /**
     * Bindet einen Button an einen asynchronen API-Call.
     * @param {string}   btnId    Element-ID (ohne führendes #)
     * @param {Function} apiFn   async () => void — wird bei Klick ausgeführt
     */
    function wire(btnId, apiFn) {
        const btn = document.getElementById(btnId);
        if (!btn) return;
        btn.addEventListener('click', async () => {
            const orig = btn.textContent;
            btn.disabled = true;
            btn.textContent = '…';
            try {
                await apiFn();
            } finally {
                btn.disabled = false;
                btn.textContent = orig;
            }
        });
    }

    /**
     * Fallback-Init für Seiten OHNE eigene Prompt-Modifier-Implementierung.
     * Bindet jeden bekannten Modifier-Button an einen generischen API-Endpoint.
     *
     * @param {string} endpoint   PHP-Endpoint (z.B. 'api/generate-video.php')
     * @param {Function} getBody  () => object — Request-Body Builder
     * @param {Function} onResult (data) => void — wird mit dem JSON-Response aufgerufen
     */
    function initFallback(endpoint, getBody, onResult) {
        const modifiers = ['make-it-better', 'fix-faces', 'better-motion', 'perfect-transition', 'cinematic-upgrade'];

        const actionMap = {
            'make-it-better':      'improve',
            'fix-faces':           'fix_faces',
            'better-motion':       'better_motion',
            'perfect-transition':  'perfect_transition',
            'cinematic-upgrade':   'cinematic',
        };

        modifiers.forEach(id => {
            wire(`btn-${id}`, async () => {
                const action = actionMap[id];
                const body   = { ...getBody(), action };
                try {
                    const res  = await fetch(endpoint, {
                        method:  'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body:    JSON.stringify(body),
                    });
                    const data = await res.json();
                    if (!data.success) {
                        Toast.error(data.error ?? 'Fehler beim Verarbeiten.');
                        return;
                    }
                    onResult(data);
                } catch {
                    Toast.error('Netzwerkfehler — bitte erneut versuchen.');
                }
            });
        });
    }

    return { wire, initFallback };
})();


/* ----------------------------------------------------------
   INIT
   Nur PromptField.initCounter() — EditorActions wird von den
   jeweiligen Seiten bei Bedarf manuell initialisiert.
   ---------------------------------------------------------- */
document.addEventListener('DOMContentLoaded', () => {
    PromptField.initCounter();
});
