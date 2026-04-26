# TODO.md — Cinematic Studio Family

## Legende
- 🔴 Blockiert | 🟡 In Arbeit | 🟢 Bereit | ✅ Fertig | ⬜ Ausstehend

---

## PHASE 0 — Planung & Setup

| # | Aufgabe                                      | Status | Priorität |
|---|----------------------------------------------|--------|-----------|
| 1 | Projektstruktur & Memory-Dateien anlegen     | ✅     | P0        |
| 2 | Tech-Stack entscheiden (Web-App + PHP)       | ✅     | P0        |
| 3 | Plattform-Ziele festlegen (Win + Mac)        | ✅     | P0        |
| 4 | Video-Engine klären (FFmpeg nativ, server)   | ✅     | P0        |
| 5 | Repo initialisieren (Git + .gitignore)       | ✅     | P0        |
| 6 | Basis-Projektstruktur aufsetzen (PHP)        | ✅     | P0        |

---

## PHASE 1 — Fundament

| # | Aufgabe                                      | Status | Priorität |
|---|----------------------------------------------|--------|-----------|
| 7 | 14 PHP-Seiten Grundgerüst anlegen            | ✅     | P1        |
| 8 | includes/ (config, header, sidebar, footer,  | ✅     | P1        |
|   | prompt-engine, guidance)                     |        |           |
| 9 | assets/css/app.css Basis-Stylesheet + Toast  | ✅     | P1        |
|10 | assets/js/ (app.js, editor.js, upload.js)    | ✅     | P1        |
|11 | api/upload.php — Datei-Upload Endpunkt       | ✅     | P1        |
|12 | api/projects.php — Projekt-CRUD              | ✅     | P1        |

---

## PHASE 2 — Kern-Features V1

| # | Aufgabe                                      | Status | Priorität |
|---|----------------------------------------------|--------|-----------|
|13 | includes/prompt-engine.php — Grundfunktionen | ✅     | P1        |
|14 | api-key.php — API-Key Eingabe & Session      | ✅     | P1        |
|15 | image-studio.php — UI + Prompt + Upload      | ✅     | P1        |
|16 | video-studio.php — UI + Prompt + Upload      | ✅     | P1        |
|17 | elements.php — Element Library Grundgerüst   | ⬜     | P2        |
|18 | includes/guidance.php — Smart Guidance       | ⬜     | P2        |
|19 | dashboard.php — Projektübersicht + CRUD      | ⬜     | P2        |
|20 | new-project.php — Projekt-Erstellformular    | ⬜     | P2        |

---

## PHASE 3 — TikTok & Trailer Studio

| # | Aufgabe                                      | Status | Priorität |
|---|----------------------------------------------|--------|-----------|
|21 | tiktok-studio.php — UI + Prompt              | ⬜     | P2        |
|22 | tiktok-animation.php — Animation-Editor      | ⬜     | P2        |
|23 | tiktok-sticker.php — Sticker-Werkzeug        | ⬜     | P2        |
|24 | trailer-builder.php — Trailer-Editor         | ⬜     | P2        |
|25 | ready-videos.php — Vorlagen-Galerie          | ⬜     | P3        |
|26 | academy.php — Tutorials & Guides             | ⬜     | P3        |

---

## PHASE 4 — Multi-Scene + Export (FFmpeg)

| # | Aufgabe                                      | Status | Priorität |
|---|----------------------------------------------|--------|-----------|
|27 | includes/functions.php — FFmpeg-Service      | ⬜     | P2        |
|28 | Multi-Scene Clip-Merge                       | ⬜     | P2        |
|29 | api/export.php — Export-Endpunkt             | ⬜     | P2        |
|30 | api/progress.php — Export-Fortschritt (SSE)  | ⬜     | P2        |
|31 | api/thumbnail.php — Thumbnail-Generierung    | ⬜     | P2        |
|32 | Export-Voreinstellungen (720p/1080p/4K)      | ⬜     | P2        |
|33 | Fortschrittsanzeige im UI                    | ⬜     | P2        |
|34 | Teilen-Funktion (Download, WhatsApp, YouTube)| ⬜     | P3        |

---

## PHASE 5 — Qualität & Release

| # | Aufgabe                                      | Status | Priorität |
|---|----------------------------------------------|--------|-----------|
|35 | settings.php — App-Einstellungen UI          | ⬜     | P2        |
|36 | Performance-Optimierung                      | ⬜     | P2        |
|37 | storage/ + data/ gegen Web-Zugriff absichern | ⬜     | P1        |
|38 | Render Deployment konfigurieren              | ⬜     | P1        |
|39 | E2E Tests (Playwright)                       | ⬜     | P3        |
|40 | Installer / Release Notes                    | ⬜     | P3        |

---

## Notizen
- Prioritäten: P0 = Sofort, P1 = Phase-kritisch, P2 = Wichtig, P3 = Nice-to-have
- FFmpeg erst in Phase 4 — wird gebraucht wenn Multi-Scene + Clip-Merge gebaut wird
- V1 Fokus: Prompt Engine → API-Key → Image Studio → Video Studio → Element Library → Guidance
