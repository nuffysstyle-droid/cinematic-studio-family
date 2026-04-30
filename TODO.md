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
|17 | elements.php — Element Library Grundgerüst   | ✅     | P2        |
|18 | includes/guidance.php — Smart Guidance       | ✅     | P2        |
|19 | dashboard.php — Projektübersicht + CRUD      | ✅     | P2        |
|20 | new-project.php — Projekt-Erstellformular    | ✅     | P2        |

---

## PHASE 3 — TikTok & Trailer Studio ✅ ABGESCHLOSSEN 🧊 (2026-04-28)

| # | Aufgabe                                      | Status | Priorität |
|---|----------------------------------------------|--------|-----------|
|21 | tiktok-studio.php — UI + Prompt              | ✅     | P2        |
|22 | tiktok-animation.php — Animation-Editor      | ✅     | P2        |
|23 | tiktok-sticker.php — Sticker-Werkzeug        | ✅     | P2        |
|24 | trailer-builder.php — Trailer-Editor         | ✅     | P2        |
|25 | ready-videos.php — Vorlagen-Galerie          | ✅     | P3        |
|26 | academy.php — Tutorials & Guides             | ✅     | P3        |

---

## PHASE 4 — Multi-Scene + Export (FFmpeg) ✅ ABGESCHLOSSEN (2026-04-29)

| # | Aufgabe                                      | Status | Priorität |
|---|----------------------------------------------|--------|-----------|
|27 | includes/functions.php — FFmpeg-Service      | ✅     | P2        |
|28 | Multi-Scene Clip-Merge                       | ✅     | P2        |
|29 | api/export.php — Export-Endpunkt             | ✅     | P2        |
|30 | api/progress.php — Export-Polling-Endpunkt   | ✅     | P2        |
|31 | Thumbnail-UI in merge-clips.php (Button + Vorschau) | ✅     | P2        |
|   | ~~api/thumbnail.php (separat)~~ → in api/export.php?action=thumbnail integriert | | |
|32 | Export-Button mit Preset (720p/1080p) — video-studio.php + merge-clips.php Re-Export | ✅     | P2        |
|33 | Fortschrittsanzeige im UI (assets/js/progress.js + Progress-Bar) | ✅     | P2        |
|34 | ~~Teilen-Funktion (Download, WhatsApp, YouTube)~~ → **Error Handling sichtbar machen (Fehlerbox + Mapping)** | ✅     | P2        |

---

## PHASE 5 — Qualität & Release

| # | Aufgabe                                      | Status | Priorität |
|---|----------------------------------------------|--------|-----------|
|35 | settings.php — App-Einstellungen UI          | ⬜     | P2        |
|36 | Performance-Optimierung                      | ⬜     | P2        |
|37 | storage/ + data/ gegen Web-Zugriff absichern | ✅     | P1        |
|38 | Render Deployment konfigurieren              | 🟡     | P1        |
|39 | E2E Tests (Playwright)                       | ⬜     | P3        |
|40 | Installer / Release Notes                    | ⬜     | P3        |

---

## SCENE REPLACEMENT EDITOR — User-Phase 2 (parallel zur V1-Roadmap)

| #   | Aufgabe                                          | Status | Priorität |
|-----|--------------------------------------------------|--------|-----------|
| S1  | api/analyze.php — Slots + Thumbnails             | ✅     | P1        |
| S2  | api/replace-slot.php — Slot-Ersatz speichern     | ✅     | P1        |
| S3  | api/get-job.php — Job-Status lesen               | ✅     | P1        |
| S4  | meta.json Schema + Backwards-Compat              | ✅     | P1        |
| S5  | scene-editor-test.html Replace-UI + DOM-Refactor | ✅     | P1        |
| S6  | scene-editor-test.html → IONOS pushen            | 🟡     | P1 (User) |
| S7  | Live-Test: Slot ersetzen + meta.json verifizieren | ⬜     | P1        |
| S8  | get-job.php Frontend-Restore (nach Reload)        | ⬜     | P2        |
| S9  | Phase 3: Slot-Cuts → finales Video rendern        | ⬜     | später    |

---

## Offene Technische Schulden (vor Phase 5 beheben)

| Punkt                                                        | Priorität |
|--------------------------------------------------------------|-----------|
| Logo-Upload in Animation + Sticker nicht mit api/upload.php  | P2        |
| Anfrage-Modal in ready-videos.php sendet nicht wirklich      | P2        |
| elements.php "Bearbeiten"-Button deaktiviert (API 501)       | P2        |
| API_PROVIDER_LINK in config.php ist Platzhalter              | P3        |

---

## Notizen
- Prioritäten: P0 = Sofort, P1 = Phase-kritisch, P2 = Wichtig, P3 = Nice-to-have
- FFmpeg erst in Phase 4 — wird gebraucht wenn Multi-Scene + Clip-Merge gebaut wird
- V1 Fokus: Prompt Engine → API-Key → Image Studio → Video Studio → Element Library → Guidance
- Phase 3 Freeze: 2026-04-28 — alle 26 TODOs committed, Working Tree clean
- TODO #38 Setup-Teil ✅ (render.yaml + Dockerfile + entrypoint.sh + apache.conf
  + api/health.php + README_DEPLOY.md). Status 🟡 weil der eigentliche Klick
  „Deploy on Render" nur durch den User mit eigenem Render-Account erfolgen kann.
  Sobald Live-Test (siehe README_DEPLOY.md §4) grün ist → ✅.
