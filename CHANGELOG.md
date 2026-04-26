# CHANGELOG.md — Cinematic Studio Family

Alle bedeutenden Änderungen werden hier dokumentiert.  
Format basiert auf [Keep a Changelog](https://keepachangelog.com/de/1.0.0/).

---

## [Unreleased]

### Hinzugefügt
- TODO #17: elements.php — Element Library V1
  - Typen: character, car, product, creature, environment, logo, object, style_reference
  - Rollen: main_character, main_object, background, style_reference
  - Formular: Name, Typ, Rolle, Beschreibung, Bild-Upload (api/upload.php)
  - Upload → api/upload.php → URL in save-element gespeichert
  - api/save-element.php: POST, Validierung, bin2hex ID, LOCK_EX JSON
  - api/elements.php: list (neueste zuerst), delete, update vorbereitet (501)
  - data/elements.json initialisiert, storage/elements/ angelegt
  - Element Cards: Bild/Platzhalter, Type-Badge, Role-Badge, Name, Beschreibung, Löschen
  - JS: loadElements(), prependCard(), deleteElement() mit confirm()
  - .gitignore: storage/elements/*, data/elements.json
- TODO #16: video-studio.php + api/generate-video.php + api/job-status.php
  - Hero, 4 Smart Guidance Tips, 2-Spalten-Layout
  - Seedance Optionen: Modell, Dauer (5/8/10/15s), Qualität, Modus
  - Upload: Startframe + Endframe — sichtbar je nach Modus (JS-gesteuert)
  - 6 Aktions-Buttons (build + 5 Modifier), alle mit Loading-State
  - Ergebnis: Positiver/Negativer Prompt + Meta-Grid (5 Felder) + Preview-Platzhalter
  - api/generate-video.php: alle 5 Modifier-Actions, Duration-Validierung
  - api/job-status.php: Platzhalter für Phase 4
  - app.css: geteilte Studio-Styles zentralisiert (dropzone, upload-preview,
    guidance-bar, studio-grid, prompt-block, result-empty …)
  - image-studio.php: inline <style> auf Seiten-spezifisches reduziert
- TODO #15: image-studio.php + api/generate-image.php — Image Studio V1
  - Hero, Smart Guidance Bar (2 Tips), 2-Spalten-Layout
  - Formular: Prompt Textarea (mit Zeichenzähler), Template-Auswahl (7 Templates)
  - Upload: Dropzone + Preview + Dateiname/Größe (nutzt upload.js)
  - Aktionen: Bild-Prompt erstellen, Prompt verbessern, Cinematic Upgrade, Element speichern (disabled)
  - Ergebnis: Positiver/Negativer Prompt, Kopieren-Button, Preview-Platzhalter
  - api/generate-image.php: POST, sanitizePromptInput, buildImagePrompt + Modifier-Actions
  - Template-Wechsel-Hinweis via Toast
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
