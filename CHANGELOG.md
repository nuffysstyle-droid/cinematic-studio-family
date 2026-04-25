# CHANGELOG.md — Cinematic Studio Family

Alle bedeutenden Änderungen werden hier dokumentiert.  
Format basiert auf [Keep a Changelog](https://keepachangelog.com/de/1.0.0/).

---

## [Unreleased]

### Hinzugefügt
- TODO #14: api-key.php + api/test-key.php — API-Key Session-Handling
  - includes/config.php: API_PROVIDER_LINK, API_PROVIDER_CREDITS_LINK, API_KEY_MIN_LENGTH
  - api-key.php: Headline, Schritt-für-Schritt Anleitung, Status-Banner (connected/checking/error/empty)
    Eingabefeld mit show/hide Toggle, Buttons (API holen, Credits kaufen, Verbindung testen)
    Anleitung-Modal, Sicherheitshinweis, JS fetch → api/test-key.php
  - api/test-key.php: POST-only, trim + Mindestlänge + Regex-Validierung, $_SESSION['api_key']
    Kein Logging des Keys, kein DB-Speicher
- TODO #13: includes/prompt-engine.php — Prompt Engine Core
  - buildImagePrompt($input, $template) → positive + negative prompt
    Templates: character, car, product, creature, startframe, endframe, character_sheet
    Felder: SUBJECT, ACTION, ENVIRONMENT, LIGHTING, CAMERA, STYLE, MATERIALS, FINAL LOOK
  - buildVideoPrompt($input, $template, $options) → positive + negative + meta
    Templates: cinematic_scene, action_trailer, pov_car, product_ad, horror_creature,
               transformation, blockbuster, tiktok_hook
    Options: model, duration (5/8/10/15), quality, mode
    Auto-Zeitstruktur (0s → midpoint → endPoint)
  - improvePrompt(), fixFacesPrompt(), betterMotionPrompt(),
    perfectTransitionPrompt(), cinematicUpgradePrompt()
  - getAvailableTemplates(), sanitizePromptInput()
  - Duplikat-Check beim Anhängen von Modifikatoren
- TODO.md komplett neu strukturiert:
  - FFmpeg-Service verschoben von Phase 1 → Phase 4 (Multi-Scene + Export)
  - Phase 2 V1-Fokus: Prompt Engine, API-Key, Image/Video Studio, Elements, Guidance
  - Phase 3: TikTok + Trailer Studio
  - Phase 4: FFmpeg + Export-Pipeline
  - 40 Tasks gesamt, klare Reihenfolge
- TODO #12: api/projects.php — Projekt-CRUD auf JSON-Basis
  - Aktionen: list, create, get, update, delete
  - Speicher: data/projects/projects.json (LOCK_EX, atomares Schreiben)
  - IDs: bin2hex(random_bytes(8))
  - JSON-Body + POST + GET als Input unterstützt
  - Validierung: title + type required
  - data/projects/projects.json als leeres Init-Array committed
- TODO #11: api/upload.php — sicherer Upload-Endpunkt
  - POST-only, JSON-Response
  - MIME-Type via finfo (nicht Browser-Header)
  - Erlaubt: JPEG/PNG/WEBP (≤10 MB), MP4/WEBM/MOV (≤100 MB)
  - Dateinamen: bin2hex(random_bytes(16)) — kein Original-Name
  - Ziel: storage/uploads/images/ oder storage/uploads/videos/
  - Ordner werden automatisch angelegt
  - .gitignore um Upload-Unterordner erweitert
- Mini-Fix: Toast-CSS in app.css ergänzt
  - .toast-container (fixed, rechts oben, z-index 9999)
  - .toast--info/success/error/warning (Dark + farbige Border-Left + Glow)
  - .toast--out + @keyframes toast-in/out (slide-in von rechts)
  - Mobile: toast-container full-width unter 768px
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
