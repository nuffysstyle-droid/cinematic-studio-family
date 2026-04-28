# PROJECT_STATUS.md — Cinematic Studio Family

## Aktueller Status
**Phase:** Phase 3 abgeschlossen — FREEZE 🧊  
**Stand:** 2026-04-28  
**Version:** 0.0.1  
**Git:** Working Tree clean — alle 26 TODOs committed

---

## Projektbeschreibung
Cinematic Studio Family ist eine Anwendung zur professionellen Erstellung und Verwaltung von Familienvideos, Foto-Diashows und cinematischen Erinnerungen. Zielgruppe sind Familien, die Urlaubsvideos, Geburtstagsfilme oder Jahresrückblicke in Kinoqualität produzieren möchten — ohne professionelle Vorkenntnisse.

---

## Status-Übersicht

| Bereich              | Status         | Notizen                                                                    |
|----------------------|----------------|----------------------------------------------------------------------------|
| Projektplanung       | ✅ Fertig       | Memory-Dateien angelegt                                                   |
| Architektur          | ✅ Fertig       | PHP 8 + Vanilla JS + FFmpeg serverseitig                                  |
| UI/UX Design         | ✅ Fertig       | Dark Cinematic Design System durchgängig umgesetzt                        |
| Backend-Setup        | ✅ Fertig       | Upload-API + Projects-CRUD + alle Service-Endpunkte                       |
| Frontend-Setup       | ✅ Fertig       | CSS + JS Fundament vollständig                                            |
| Phase 1 — Fundament  | ✅ Fertig       | 14 Seiten-Grundgerüste, includes/, api/, assets/                          |
| Phase 2 — Kern       | ✅ Fertig       | Prompt Engine, Studios, Elements, Guidance, Dashboard, Projektverwaltung  |
| Phase 3 — TikTok+    | ✅ Fertig 🧊    | TikTok Studio, Animation, Sticker, Trailer, Showroom, Academy             |
| Phase 4 — Export     | 🟡 In Arbeit    | FFmpeg-Service ✅, Clip-Merge ✅, Export-API ✅, Polling ✅, Thumbnails      |
| Phase 5 — Release    | 🔲 Ausstehend   | Settings, Security, Render Deployment, Tests                              |

---

## Phase 3 — Abschlussbericht (FREEZE)

### Gebaute Bereiche (TODO #21–26)

| TODO | Datei                  | Bereich                          | Typ              |
|------|------------------------|----------------------------------|------------------|
| #21  | tiktok-studio.php      | TikTok Prompt Generator          | Generator        |
|      | api/generate-tiktok.php| TikTok API (Hook + CTA + Prompt) | Backend          |
| #22  | tiktok-animation.php   | Animation Service (4 Typen)      | Service + Form   |
|      | api/animation-request.php | Anfragen speichern (JSON)     | Backend          |
| #23  | tiktok-sticker.php     | Sticker Showroom + Service       | Service + Form   |
|      | api/sticker-request.php| Anfragen speichern (JSON)        | Backend          |
| #24  | trailer-builder.php    | Cinematic Trailer Builder        | Generator        |
|      | api/generate-trailer.php| Trailer-Timeline + Prompts      | Backend          |
| #25  | ready-videos.php       | Premium Video Showroom           | Showroom         |
|      | data/ready-videos.json | 12 Demo-Einträge                 | Daten            |
| #26  | academy.php            | Wissens-Hub mit 13 Guides        | Content-Hub      |

### Qualitäts-Standards eingehalten
- ✅ Kein innerHTML für dynamische Daten — ausschließlich DOM API / textContent
- ✅ Alle API-Endpunkte mit Whitelist-Validierung (VALID_* Konstanten)
- ✅ JSON-Speicher mit LOCK_EX (atomares Schreiben)
- ✅ API-Key nur in `$_SESSION` (kein Logging, keine DB)
- ✅ Upload-Dateien mit finfo MIME-Prüfung + Zufallsnamen
- ✅ `<template>`-Cloning für alle Card-Renderings
- ✅ CSS-counter für Step-Nummerierung (kein JS)
- ✅ Loading-States + Toast-Feedback auf allen Aktionen

---

## Offene Punkte nach Phase 3

### Technische Schulden (vor Phase 5 beheben)
| Punkt | Datei | Priorität |
|-------|-------|-----------|
| Logo-Upload nicht mit api/upload.php verbunden | tiktok-animation.php, tiktok-sticker.php | P2 |
| Anfrage-Modal sendet nicht wirklich (nur Toast) | ready-videos.php | P2 |
| elements.php "Bearbeiten"-Button disabled | elements.php + api/elements.php (501) | P2 |
| API_PROVIDER_LINK ist Platzhalter | includes/config.php | P3 |
| storage/ + data/ nicht gegen Web-Zugriff geschützt | .htaccess fehlt | P1 |

---

## Technische Risiken — Phase 4

| Risiko | Beschreibung | Schwere | Maßnahme |
|--------|--------------|---------|----------|
| **FFmpeg auf Render** | Render.com bietet FFmpeg nicht out-of-the-box; muss als Dependency im Build-Step installiert werden | 🔴 Hoch | Render Dockerfile / apt-get ffmpeg vor Phase 5 testen |
| **Upload-Persistenz** | Dateien auf Server-Disk gehen bei Render-Restart verloren (ephemeral filesystem) | 🔴 Hoch | Vor Go-Live: R2/S3 Upgrade zwingend |
| **Ready-Videos ohne Zahlung** | Kein Payment-System — "Anfragen" ist nur Kontaktformular | 🟡 Mittel | V1 akzeptabel; V2 Stripe/Payment einplanen |
| **API-Anbindung Platzhalter** | Seedance/AI-API noch nicht real angebunden — alles sind Prompt-Generatoren | 🟡 Mittel | Phase 4 real anbinden oder V1 explizit als "Prompt Tool" definieren |
| **Große Rohdateien** | Video-Upload bis 100 MB — bei Multi-Scene-Merge steigt Speicherbedarf stark | 🟡 Mittel | Temporäre Cleanup-Routine in Export-API |
| **Keine Authentifizierung** | Alle Daten in `data/` öffentlich erreichbar wenn kein .htaccess | 🟠 Mittel-Hoch | TODO #37 zwingend vor Deployment |
| **Concurrent JSON-Writes** | LOCK_EX schützt vor Race-Conditions, aber bei hoher Last trotzdem riskant | 🟢 Gering | V1 akzeptabel; bei Wachstum auf SQLite/DB migrieren |

---

## Offene Entscheidungen (benötigen User-Input vor Phase 4)

- [ ] **FFmpeg auf Render:** Eigenes Dockerfile oder native Render-Buildpacks?
- [ ] **Export-Formate:** Nur MP4 H.264, oder auch WebM / MOV?
- [ ] **SSE vs. Polling:** Export-Fortschritt via Server-Sent Events (TODO #30) oder simpler Polling-Loop?
- [x] **App-Typ:** PHP + Vanilla JS (kein Framework)
- [x] **Video-Verarbeitung:** FFmpeg nativ, serverseitig
- [x] **Deployment-Ziel:** Render ✅
- [x] **Auth:** Kein Login in v1
- [x] **API-Key:** Session-temporär, nie serverseitig gespeichert
- [x] **Datei-Speicher:** Server-Disk temporär (v1), R2/S3 als Upgrade-Pfad

---

## Nächste Schritte — Phase 4

1. ~~**TODO #27** — `includes/functions.php` — FFmpeg-Service~~ ✅
2. ~~**TODO #28** — Multi-Scene Clip-Merge~~ ✅
3. **TODO #29** — `api/export.php` — Export-Endpunkt
4. **TODO #30** — `api/progress.php` — Export-Fortschritt (SSE)
5. **TODO #31** — `api/thumbnail.php` — Thumbnail-Generierung
6. **TODO #32** — Export-Voreinstellungen (720p / 1080p / 4K)
7. **TODO #33** — Fortschrittsanzeige im UI
8. **TODO #34** — Teilen-Funktion (Download, WhatsApp, YouTube)

---

## Commit-Historie Phase 3

```
db53a4f  feat: #26 academy.php — Academy Wissens-Hub mit 13 Guides + Guide-Modal
9cda083  feat: #25 ready-videos.php — Premium Video Showroom + 12 Demo-Einträge
e014f2d  feat: #24 trailer-builder.php + api/generate-trailer.php — Cinematic Trailer Builder
49a6f27  feat: #23 tiktok-sticker.php — Sticker Studio Service-Bereich
ec85523  feat: #22 tiktok-animation.php — Animation Studio Service-Bereich
065feb9  feat: #21 tiktok-studio.php + api/generate-tiktok.php — TikTok Studio V1
```
