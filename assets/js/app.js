/* ============================================================
   app.js — Cinematic Studio Family
   Global UI: Sidebar, Navigation, Modal, Toast
   ============================================================ */

'use strict';

/* ----------------------------------------------------------
   SIDEBAR TOGGLE
   ---------------------------------------------------------- */
const Sidebar = (() => {
    const STORAGE_KEY = 'sidebar_collapsed';

    function init() {
        const layout  = document.querySelector('.app-layout');
        const toggle  = document.getElementById('sidebar-toggle');
        if (!layout) return;

        // Restore state from localStorage
        if (localStorage.getItem(STORAGE_KEY) === '1') {
            layout.classList.add('sidebar-collapsed');
        }

        if (toggle) {
            toggle.addEventListener('click', () => {
                const collapsed = layout.classList.toggle('sidebar-collapsed');
                localStorage.setItem(STORAGE_KEY, collapsed ? '1' : '0');
            });
        }
    }

    return { init };
})();


/* ----------------------------------------------------------
   ACTIVE NAVIGATION HIGHLIGHT
   ---------------------------------------------------------- */
const Nav = (() => {
    function init() {
        const currentFile = window.location.pathname.split('/').pop() || 'index.php';
        document.querySelectorAll('.nav-item').forEach(link => {
            const href = link.getAttribute('href') || '';
            if (href.endsWith(currentFile)) {
                link.classList.add('active');
            }
        });
    }

    return { init };
})();


/* ----------------------------------------------------------
   MODAL
   ---------------------------------------------------------- */
const Modal = (() => {
    let activeModal = null;

    function open(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.add('modal--open');
        document.body.style.overflow = 'hidden';
        activeModal = modal;
    }

    function close(id) {
        const modal = id ? document.getElementById(id) : activeModal;
        if (!modal) return;
        modal.classList.remove('modal--open');
        document.body.style.overflow = '';
        activeModal = null;
    }

    function init() {
        // Close on backdrop click
        document.addEventListener('click', e => {
            if (e.target.classList.contains('modal__backdrop')) {
                close();
            }
        });

        // Close on Escape
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape' && activeModal) close();
        });

        // Wire data-modal-open / data-modal-close attributes
        document.querySelectorAll('[data-modal-open]').forEach(btn => {
            btn.addEventListener('click', () => open(btn.dataset.modalOpen));
        });
        document.querySelectorAll('[data-modal-close]').forEach(btn => {
            btn.addEventListener('click', () => close(btn.dataset.modalClose));
        });
    }

    return { open, close, init };
})();


/* ----------------------------------------------------------
   TOAST / STATUS MESSAGES
   ---------------------------------------------------------- */
const Toast = (() => {
    let container = null;

    function getContainer() {
        if (!container) {
            container = document.createElement('div');
            container.className = 'toast-container';
            container.setAttribute('aria-live', 'polite');
            document.body.appendChild(container);
        }
        return container;
    }

    /**
     * @param {string} message
     * @param {'info'|'success'|'error'|'warning'} type
     * @param {number} duration  ms, 0 = persistent
     */
    function show(message, type = 'info', duration = 3500) {
        const toast = document.createElement('div');
        toast.className = `toast toast--${type}`;
        toast.textContent = message;

        const dismiss = () => {
            toast.classList.add('toast--out');
            toast.addEventListener('animationend', () => toast.remove(), { once: true });
        };

        toast.addEventListener('click', dismiss);
        getContainer().appendChild(toast);

        if (duration > 0) setTimeout(dismiss, duration);
        return { dismiss };
    }

    return {
        show,
        info:    (msg, ms) => show(msg, 'info',    ms),
        success: (msg, ms) => show(msg, 'success', ms),
        error:   (msg, ms) => show(msg, 'error',   ms),
        warning: (msg, ms) => show(msg, 'warning', ms),
    };
})();


/* ----------------------------------------------------------
   INIT
   ---------------------------------------------------------- */
document.addEventListener('DOMContentLoaded', () => {
    Sidebar.init();
    Nav.init();
    Modal.init();
});
