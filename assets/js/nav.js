/**
 * CVS Navigation Core — Phase 1
 * Shared behaviour for all pages: cursor, scroll-state, mobile-nav, active-link.
 * Usage: <script src="assets/js/nav.js" defer></script>
 */
(function () {
  'use strict';

  // Guard against double-init (SPA/Hot-Reload safe)
  if (window.__CVS_NAV_INIT__) return;
  window.__CVS_NAV_INIT__ = true;

  // ── 1. CUSTOM CURSOR (desktop only) ──
  const isDesktop = window.matchMedia('(pointer: fine)').matches;
  if (isDesktop) {
    const cursor = document.getElementById('cursor');
    const ring   = document.getElementById('cursor-ring');
    if (cursor && ring) {
      let mx = 0, my = 0, rx = 0, ry = 0, active = false;
      let rafId = null;

      document.addEventListener('mousemove', e => {
        mx = e.clientX; my = e.clientY;
        cursor.style.left = mx + 'px';
        cursor.style.top  = my + 'px';
        if (!active) {
          active = true;
          document.body.classList.add('cursor-active');
        }
      });

      (function loop() {
        rx += (mx - rx) * 0.14;
        ry += (my - ry) * 0.14;
        ring.style.left = rx + 'px';
        ring.style.top  = ry + 'px';
        rafId = requestAnimationFrame(loop);
      })();

      const interactives = 'a, button, [role="button"], .portfolio-item, .service-world, .crystal-card, .slot, .why-card, .pf-card, .ac-card';
      document.querySelectorAll(interactives).forEach(el => {
        el.addEventListener('mouseenter', () => {
          cursor.style.width  = '20px'; cursor.style.height  = '20px';
          ring.style.width    = '52px'; ring.style.height    = '52px';
        });
        el.addEventListener('mouseleave', () => {
          cursor.style.width  = '8px';  cursor.style.height  = '8px';
          ring.style.width    = '32px'; ring.style.height    = '32px';
        });
      });
    }
  }

  // ── 2. NAVBAR SCROLL STATE ──
  const nav = document.getElementById('main-nav');
  if (nav) {
    const onScroll = () => {
      const scrolled = window.scrollY > 60;
      nav.classList.toggle('scrolled', scrolled);
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }

  // ── 3. MOBILE NAV TOGGLE (supports both master + drop-in patterns) ──
  function bindMobileNav(burgerSel, menuSel, navSel) {
    const burger = document.querySelector(burgerSel);
    const menu   = document.querySelector(menuSel);
    const nav    = document.querySelector(navSel);
    if (!burger || !menu) return;

    // Position dropdown below nav
    function positionMenu() {
      if (nav && menu.classList.contains('open')) {
        const navH = nav.offsetHeight;
        menu.style.top = navH + 'px';
      }
    }

    function setOpen(isOpen) {
      menu.classList.toggle('open', isOpen);
      burger.classList.toggle('open', isOpen);
      burger.setAttribute('aria-expanded', String(isOpen));
      if (isOpen) positionMenu();
      // Body scroll lock
      document.body.style.overflow = isOpen ? 'hidden' : '';
    }

    burger.addEventListener('click', () => {
      setOpen(!menu.classList.contains('open'));
    });

    // Close on link click
    menu.querySelectorAll('a').forEach(a => {
      a.addEventListener('click', () => setOpen(false));
    });

    // Close on ESC
    document.addEventListener('keydown', e => {
      if (e.key === 'Escape' && menu.classList.contains('open')) setOpen(false);
    });

    // Close on outside click
    document.addEventListener('click', e => {
      if (menu.classList.contains('open') && !menu.contains(e.target) && !burger.contains(e.target)) {
        setOpen(false);
      }
    });
  }

  // Master pattern: #burger-btn + #mobile-nav (fullscreen overlay)
  bindMobileNav('#burger-btn', '#mobile-nav', 'nav');
  // Drop-In pattern: #mobBurger + #mobMenu (dropdown)
  bindMobileNav('#mobBurger', '#mobMenu', '.cvs-nav-simple');

  // ── 4. ACTIVE LINK HIGHLIGHTING ──
  (function markActive() {
    const links = document.querySelectorAll('.nav-links a[href], .mobile-nav a[href], .mob-menu a[href]');
    if (!links.length) return;
    const here = window.location.pathname;
    const file = here.split('/').pop() || 'index.html';
    links.forEach(a => {
      const href = (a.getAttribute('href') || '').replace('./', '');
      const match = href === here || href === file ||
                    (file === '' && (href === 'index.html' || href === ''));
      if (match) a.classList.add('active');
    });
  })();
})();
