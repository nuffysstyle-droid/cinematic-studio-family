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
   Platzhalter — noch keine echte KI-Anbindung
   ---------------------------------------------------------- */
const EditorActions = (() => {

    const actions = {
        'make-it-better': {
            label: 'Make it Better',
            // TODO: Prompt-Enhancement via AI API
            handler() {
                const current = PromptField.getValue();
                if (!current) { Toast.warning('Bitte zuerst einen Prompt eingeben.'); return; }
                Toast.info('Make it Better … (Platzhalter)');
                console.log('[EditorActions] make-it-better →', current);
            }
        },
        'fix-faces': {
            label: 'Fix Faces',
            // TODO: Face-Correction via AI API
            handler() {
                Toast.info('Fix Faces … (Platzhalter)');
                console.log('[EditorActions] fix-faces');
            }
        },
        'better-motion': {
            label: 'Better Motion',
            // TODO: Motion-Enhancement via AI API
            handler() {
                Toast.info('Better Motion … (Platzhalter)');
                console.log('[EditorActions] better-motion');
            }
        },
        'perfect-transition': {
            label: 'Perfect Transition',
            // TODO: Transition-Optimierung via AI API
            handler() {
                Toast.info('Perfect Transition … (Platzhalter)');
                console.log('[EditorActions] perfect-transition');
            }
        },
        'cinematic-upgrade': {
            label: 'Cinematic Upgrade',
            // TODO: Cinematic LUT + Grading via AI API
            handler() {
                Toast.info('Cinematic Upgrade … (Platzhalter)');
                console.log('[EditorActions] cinematic-upgrade');
            }
        },
    };

    function init() {
        Object.entries(actions).forEach(([id, action]) => {
            const btn = document.getElementById(`btn-${id}`);
            if (!btn) return;
            btn.addEventListener('click', () => {
                setLoading(btn, true);
                // Simuliertes Async — wird durch echten API-Call ersetzt
                setTimeout(() => {
                    action.handler();
                    setLoading(btn, false);
                }, 600);
            });
        });
    }

    function setLoading(btn, loading) {
        btn.disabled = loading;
        btn.dataset.originalText = btn.dataset.originalText ?? btn.textContent;
        btn.textContent = loading ? '…' : btn.dataset.originalText;
    }

    return { init };
})();


/* ----------------------------------------------------------
   INIT
   ---------------------------------------------------------- */
document.addEventListener('DOMContentLoaded', () => {
    PromptField.initCounter();
    EditorActions.init();
});
