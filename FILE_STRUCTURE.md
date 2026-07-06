# FILE_STRUCTURE.md — Cinematic Vision Studio
# Dateistruktur: annotierter Ist-Zustand & verbindlicher Ziel-Baum

| Feld | Wert |
|---|---|
| Version | 1.0 (Architecture Lock, 2026-07-05) — D-013 |
| Legende | ✅ produktiv · 🧊 Legacy (Code-Freeze, Sunset D-004) · 🗑 Artefakt (Cleanup Phase 1) · 📚 Bibel |

---

## 1. Ist-Zustand (Root, annotiert — Audit 2026-07-05)

```
cinematic-studio-family/
├── 📚 Bibel (15): CLAUDE, MASTER_BLUEPRINT, PRODUCT_ROADMAP, DESIGN_SYSTEM,
│      COMPONENT_LIBRARY, AI_ENGINE, DATABASE_ARCHITECTURE, API_ARCHITECTURE,
│      SECURITY, ADMIN_SYSTEM, CRYSTAL_SYSTEM, DEPLOYMENT, QA_MASTERPLAN,
│      FILE_STRUCTURE, DECISION_LOG (.md)
├── ✅ IONOS-Seiten (15 .html): scene-editor-test (Home), immobilienvideos (Design-Referenz!),
│      portfolio, shop, crystals, academy, prompt-generator, kontakt, calendar,
│      impressum, datenschutz, agb, widerruf, cookies, index (Redirect)
├── ✅ App-Seiten (.php): studio-demo, login, dashboard, profile, forgot-password,
│      index, crystals  + Redirect-Stubs (academy, shop, portfolio, prompt-generator,
│      contact, availability, ki-videos)
├── 🧊 Legacy-Suite (.php): video-studio, image-studio, tiktok-studio/-animation/-sticker,
│      merge-clips, elements, ready-videos, trailer-builder, new-project, settings, api-key
├── ✅ api/            Kern (upload, analyze, replace-slot, render-final, generate-ai,
│   │                  ai-status, health, cleanup, contact-submit, settings/quality, auth/×6)
│   └── 🧊             Legacy-API ×16 (export, job-status, generate-*, projects, elements, …)
├── ✅ includes/       config, functions, db, auth, rate_limit, mailer, job_service,
│   │                  prompt-engine (🧊→Wiederverwendung AI_ENGINE §5), guidance,
│   └──                header/sidebar/footer (🧊 Legacy-Layout)
├── ✅ assets/         css/ (cvs-core ✅, app 🧊) · js/ · fonts/ · icon/ (3×1,7 MB → WebP!)
│   │                  portfolio/ + showcase/ (⚠️ Duplikat-Bäume → §2)
│   └──                cvs-logo*.png/jpg (3 Varianten, unoptimiert) · cvs-hero-loop.mp4 (7,6 MB)
├── ✅ storage/ data/  (.htaccess-geschützt — TABU) · ✅ bin/ docker/ render.yaml Dockerfile
├── ✅ archive/        qa-screenshots/, ionos-process/, tools/, upload-testing/
├── ⚪ Alt-Doku (superseded, D-013): ARCHITECTURE, README_DEPLOY, PROJECT_STATUS,
│      CHANGELOG (bleibt!), TODO, MEMORY, NEXT_SESSION_HANDOFF, WORKSPACE_CLEANUP_*,
│      AGENT, CLAUDE_INSTRUCTIONS, PROMPT_TEMPLATES (.md) · memory/ ×12 · docs/
├── 🗑 Null-Byte-Müll ×9: 'E-Mail · .cvs-nav-img · 1) · 70% · s.style.outline ·
│      window.scrollTo(0 · { · {,+ · {})
├── 🗑 QA-Screenshots im Root ×33 (~29 MB): Screenshot 2026-*, kontakt-*, crystals-wow-*, …
│      (klären: CVS Header.png, luxmation-brief-ref.png → evtl. Referenz, O-Frage an Björn)
├── 🗑 php-server.log · videos/Immobilienvideo.mp4 (Roh-Asset, 8 MB → assets/media oder Archiv)
└── 🗑 Deploy-Skripte: ionos-upload*.mjs, deploy-*.mjs (sterben mit D-001)
    + .claude/worktrees ×8 (67 MB, prunen) · .playwright-mcp/ · .swarm/ · .claude-flow/
```

**Kernprobleme:** Root vermischt Produkt, Doku, Legacy und Müll; zwei Asset-Bäume;
Assets mit Leerzeichen/Umlauten (`Anime und Comic und Zeichentrick.png`) — funktioniert
URL-encodiert, bleibt aber fehleranfällig → Ziel-Konvention §3.

---

## 2. Ziel-Baum (nach Phase 1–3; Umbenennungen NUR mit Redirect-Map DEPLOYMENT §5)

```
cinematic-studio-family/
├── *.md                      # die 15 Bibel-Dokumente + CHANGELOG.md (bleibt Root)
├── pages/                    # dünne PHP-Templates (eine Datei je Route)
│   ├── home.php · portfolio.php · immobilienvideos.php · academy.php · shop.php
│   ├── kristalle.php · prompt-master.php · kontakt.php · buchung.php · legal/×5
│   └── studio.php · dashboard.php · profil.php · login.php · admin/×n (Phase 3)
├── includes/
│   ├── partials/             # 🆕 head.php · nav.php · footer.php  (D-005 — EINE Nav)
│   ├── services/             # functions(ffmpeg) · auth · db · rate_limit · mailer ·
│   │                         # pricing 🆕 · billing 🆕 · ai (kie-Helfer) · audit 🆕
│   └── config.php
├── api/v1/                   # API_ARCHITECTURE §3 (Alt-Pfade als Rewrite-Aliase)
├── assets/
│   ├── css/cvs-core.css      # EIN Stylesheet-System (app.css stirbt mit Legacy)
│   ├── js/  (nav, ui 🆕 toast/modal, api 🆕 fetch+CSRF, studio, reveal)
│   ├── fonts/
│   └── media/                # 🆕 EIN Medienbaum: brand/ · portfolio/ · shop/ · icons/ · hero/
│                             # Namenskonvention: kebab-case, ascii, keine Leerzeichen
├── storage/ · data/(nur solange Legacy lebt) · bin/ (cron, backup 🆕, tests/ 🆕)
├── docker/ · Dockerfile · render.yaml · robots.txt 🆕 · sitemap.xml 🆕 · .htaccess (Rewrites) 🆕
└── archive/                  # docs-legacy/ 🆕 · legacy-app/ 🆕 (Phase 3) · qa-screenshots/
```

Apache-Rewrites mappen Clean-URLs auf `pages/*.php`; Root bleibt frei von Seiten-Dateien
(Ausnahme: Einstiegs-`index.php`).

---

## 3. Dauerhafte Ordnungsregeln

1. **Root ist heilig:** dort leben nur Bibel, CHANGELOG, Build-/Deploy-Manifeste und `index.php`.
   Screenshots → `archive/qa-screenshots/`, Skripte → `bin/`, Medien → `assets/media/`.
2. **Namenskonvention neu:** kebab-case, ASCII, keine Leerzeichen/Umlaute in Dateinamen.
   Bestand wird NUR im Zuge der Medien-Konsolidierung umbenannt (mit Referenz-Update + Test).
3. **Eine Sache, ein Ort:** kein zweiter Asset-/Doku-/CSS-Baum. Duplikat entdeckt → 
   DECISION_LOG-Eintrag + Konsolidierung eingeplant.
4. **Löschen nie direkt:** erst `archive/`, eine Phase Karenz, dann Löschung mit Björn-Freigabe.
5. Migrationszuordnung: Müll/Screenshots → Phase 1 · Partials/pages/ → Phase 1 (Shell) ·
   media-Konsolidierung → Phase 1–2 · legacy-app-Archivierung → Phase 3.
