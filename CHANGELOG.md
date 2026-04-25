# CHANGELOG.md — Cinematic Studio Family

Alle bedeutenden Änderungen werden hier dokumentiert.  
Format basiert auf [Keep a Changelog](https://keepachangelog.com/de/1.0.0/).

---

## [Unreleased]

### Hinzugefügt
- TODO #10: assets/js/ — Basis-JS Grundgerüst
  - app.js: Sidebar Toggle, Active Nav Highlight, Modal, Toast
  - editor.js: PromptField Helper, 5 AI Action Buttons (Platzhalter)
  - upload.js: UploadPreview, DropZone, Validierung (Bild/Video)
  - footer.php: $extraJs auf Array-Support erweitert
  - image-studio.php + video-studio.php: laden jetzt editor.js + upload.js
- TODO #9: assets/css/app.css — Dark Cinematic Basis-Stylesheet
  - CSS Reset, :root Variablen (bg, text, accent-blue/orange, border, shadow)
  - Layout: .app-layout, .sidebar (fixed 260px, collapsed-Vorbereitung), .main-content
  - Sidebar: nav-items, hover/active states
  - Topbar, Cards/Panels, Buttons (primary/secondary/danger/sm/lg)
  - Form-Elemente im Dark-Stil mit sichtbarem Fokus-State
  - Utility-Klassen (flex, grid, gap, mt, mb, text)
  - Responsive Breakpoints 1024px + 768px vorbereitet
- Projekt-Memory-Dateien angelegt (PROJECT_STATUS.md, ARCHITECTURE.md, TODO.md, CHANGELOG.md, CLAUDE_INSTRUCTIONS.md, PROMPT_TEMPLATES.md)
- TODO #1 abgeschlossen

### Entschieden
- App-Typ: Web-App (React + Node.js)
- Plattformen: Windows + macOS
- Video-Verarbeitung: FFmpeg nativ, serverseitig
- Deployment: Cloud-gehostet (öffentliche URL, kein lokaler Install)
- Auth: Kein Login in v1, keine Registrierung
- API-Key: nur Session-temporär im Browser (sessionStorage), nie serverseitig
- ARCHITECTURE.md vollständig überarbeitet inkl. Sicherheits-Regeln für API-Keys
- TODO #2, #3, #4 abgeschlossen
- Cloud-Provider: Render (Node.js + FFmpeg)
- Datei-Speicher: Server-Disk temporär für v1, Upgrade-Pfad zu R2/S3 dokumentiert
- TODO #5: Git-Repo initialisiert, .gitignore erstellt, initialer Commit
- Stack-Änderung: Node.js/React → PHP + Vanilla JS (einfacherer MVP)
- ARCHITECTURE.md komplett auf PHP umgeschrieben
- TODO Phase 1 auf PHP-Tasks angepasst
- TODO #6: Ordnerstruktur angelegt (includes/, api/, assets/, storage/, data/)
- TODO #7+8: 14 PHP-Seiten Grundgerüst + 6 Includes angelegt
  - Seiten: index, dashboard, new-project, api-key, image-studio, video-studio,
    elements, tiktok-studio, tiktok-animation, tiktok-sticker,
    ready-videos, trailer-builder, academy, settings
  - Includes: config, header, sidebar, footer, prompt-engine, guidance

---

## [0.0.1] — 2026-04-25

### Hinzugefügt
- Projektordner `cinematic-studio-family/` erstellt
- Initiale Planungs-Dokumentation

---

<!-- VORLAGE FÜR NEUE EINTRÄGE:

## [X.Y.Z] — YYYY-MM-DD

### Hinzugefügt
- Neue Features

### Geändert
- Änderungen an bestehenden Features

### Behoben
- Bug-Fixes

### Entfernt
- Entfernte Features

### Sicherheit
- Sicherheits-Fixes

-->
