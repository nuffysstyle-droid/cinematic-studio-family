# TODO.md — Cinematic Studio Family

## Legende
- 🔴 Blockiert | 🟡 In Arbeit | 🟢 Bereit | ✅ Fertig | ⬜ Ausstehend

---

## PHASE 0 — Planung & Setup

| # | Aufgabe                                      | Status | Priorität |
|---|----------------------------------------------|--------|-----------|
| 1 | Projektstruktur & Memory-Dateien anlegen     | ✅     | P0        |
| 2 | Tech-Stack entscheiden (Web-App + Node)      | ✅     | P0        |
| 3 | Plattform-Ziele festlegen (Win + Mac)        | ✅     | P0        |
| 4 | Video-Engine klären (FFmpeg nativ, server)   | ✅     | P0        |
| 5 | Repo initialisieren (Git + .gitignore)       | ✅     | P0        |
| 6 | Basis-Projektstruktur aufsetzen (PHP)        | ✅     | P0        |

---

## PHASE 1 — Fundament

| # | Aufgabe                                      | Status | Priorität |
|---|----------------------------------------------|--------|-----------|
| 7 | index.php + editor.php + export.php anlegen  | ⬜     | P1        |
| 8 | includes/ (header, footer, functions.php)    | ⬜     | P1        |
| 9 | assets/css/app.css Basis-Stylesheet          | ⬜     | P1        |
|10 | assets/js/ (app.js, editor.js, upload.js)    | ⬜     | P1        |
|11 | api/upload.php — Datei-Upload Endpunkt       | ⬜     | P1        |
|12 | api/projects.php — Projekt-CRUD              | ⬜     | P1        |
|13 | FFmpeg via exec() in functions.php           | ⬜     | P1        |

---

## PHASE 2 — Kern-Features

| # | Aufgabe                                      | Status | Priorität |
|---|----------------------------------------------|--------|-----------|
|14 | Media Import (Drag & Drop, Datei-Dialog)     | ⬜     | P1        |
|15 | Thumbnail-Generierung                        | ⬜     | P1        |
|16 | Media-Bibliothek / Asset-Browser             | ⬜     | P2        |
|17 | Timeline-Komponente (Basis)                  | ⬜     | P1        |
|18 | Clip Trim / Cut / Split                      | ⬜     | P2        |
|19 | Video-Preview / Playback                     | ⬜     | P1        |
|20 | Projekt speichern / laden                    | ⬜     | P1        |
|21 | Autosave                                     | ⬜     | P2        |

---

## PHASE 3 — Erweiterte Features

| # | Aufgabe                                      | Status | Priorität |
|---|----------------------------------------------|--------|-----------|
|22 | Template Engine (Jahresrückblick, Urlaub…)   | ⬜     | P2        |
|23 | Text-Overlays / Titelkarten                  | ⬜     | P2        |
|24 | Übergänge & Animationen                      | ⬜     | P2        |
|25 | Audio-Modul (Musik, Voice-Over)              | ⬜     | P2        |
|26 | Filter & Farbkorrektur                       | ⬜     | P3        |
|27 | Musik-Bibliothek (royalty-free)              | ⬜     | P3        |

---

## PHASE 4 — Export & Sharing

| # | Aufgabe                                      | Status | Priorität |
|---|----------------------------------------------|--------|-----------|
|28 | Export-Engine (MP4, WebM, GIF)               | ⬜     | P1        |
|29 | Fortschrittsanzeige beim Export              | ⬜     | P2        |
|30 | Export-Voreinstellungen (720p/1080p/4K)      | ⬜     | P2        |
|31 | Teilen-Funktion (WhatsApp, YouTube…)         | ⬜     | P3        |

---

## PHASE 5 — Qualität & Release

| # | Aufgabe                                      | Status | Priorität |
|---|----------------------------------------------|--------|-----------|
|32 | Unit Tests (Vitest)                          | ⬜     | P2        |
|33 | E2E Tests (Playwright)                       | ⬜     | P3        |
|34 | Performance-Optimierung                      | ⬜     | P2        |
|35 | App-Packaging (electron-builder)             | ⬜     | P2        |
|36 | Auto-Updater                                 | ⬜     | P3        |
|37 | Installer für Win/Mac/Linux                  | ⬜     | P3        |

---

## Notizen
- Prioritäten: P0 = Sofort, P1 = Phase-kritisch, P2 = Wichtig, P3 = Nice-to-have
- Reihenfolge kann sich nach Tech-Stack-Entscheidung ändern
