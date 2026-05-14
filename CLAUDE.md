# CLAUDE.md — Cinematic Vision Studio
# Zentrale Projektdokumentation für AI-Agenten, Claude Code, LLM Council

> **Pflichtlektüre.** Jeder Agent (Claude Code, Sub-Agent, Council-Advisor) liest diese
> Datei VOR Arbeitsbeginn. Sie ist die Single Source of Truth über das Projekt.
> Detail-Kontext liegt in `memory/`. Deployment-Details in `memory/deployment.md`.

---

## Projekt-Identität

| Feld | Wert |
|---|---|
| **Produktname** | Cinematic Vision Studio |
| **Repo / Codebase** | cinematic-studio-family |
| **Version** | 0.1.0 (Free MVP, live seit 2026-05-13) |
| **Live-URL** | https://cinematic-studio-family.onrender.com |
| **GitHub** | nuffysstyle-droid/cinematic-studio-family |
| **Stand** | 2026-05-14 (Session 3) |

---

## Projektvision

Familien sollen Urlaubsvideos, Geburtstagsfilme und Jahresrückblicke in Kinoqualität
produzieren können — **ohne professionelle Vorkenntnisse, in unter 10 Minuten.**

Das Produkt kombiniert serverseitige Videoverarbeitung (FFmpeg) mit KI-Bildgenerierung
(Kie.ai) zu einem vollständigen "Upload → Edit → Render → Download"-Flow.

---

## Zielgruppe

- **Primär:** Familien, die Erinnerungen dokumentieren (Urlaub, Geburtstag, Jahresrückblick)
- **Sekundär:** Content Creator, die cinematic Short-Videos ohne teures Equipment produzieren
- **Skill-Level:** Keine Video-Vorkenntnisse nötig — guided UX, template-basiert
- **Plattform:** Browser-basiert (Desktop + Mobile), Windows + macOS

---

## Geschäftsmodell

| Stufe | Zugang | Preis |
|---|---|---|
| **Free** | 720p, max 15s, max 3 Slots, kein Ton, ephemeral Storage | kostenlos |
| **Starter+** | 1080p, Audio, Persistent Disk, mehr Slots | geplant ~$7–12/mo |
| **Pro** | KI-Generierungen, Premium Templates, API-Zugang | geplant ~$29/mo |

**Kristalle** — geplante interne Währung für KI-Generierungen (Kie.ai Credits).
Aktuell Demo-Dummy; echte Transaktion in V2.

---

## Tech-Stack (verbindlich — nicht ohne Diskussion ändern)

| Schicht | Technologie | Begründung |
|---|---|---|
| **Backend** | PHP 8.2 + Apache (mod_php) | Kein Build-Step, direkt deploybar, FFmpeg via apt |
| **Frontend** | Vanilla JS + HTML5 + CSS3 | Kein Framework-Overhead in V1 |
| **Video-Engine** | FFmpeg 7.1.3 (serverseitig) | Volle Codec-Kontrolle, kein WASM |
| **AI-Provider** | Kie.ai (Flux Kontext Pro/Max) | Async Task-API, JPEG-Output, 14-Tage CDN |
| **Deployment** | Render.com (Docker) | PHP + FFmpeg + Persistent Disk in einem |
| **Storage** | JSON-Dateien + LOCK_EX | Kein DB-Setup in V1 |
| **Fonts** | Liberation Sans (fonts-liberation) | FFmpeg drawtext, Linux-native |

### Harte Don'ts

- ❌ Kein `innerHTML` für User-Daten → `textContent` / DOM-API / `<template>`-Cloning
- ❌ Kein npm, kein Composer, kein Build-Tool in V1
- ❌ Kein externes JS-Framework (React, Vue, jQuery)
- ❌ Kein Login / Auth in V1
- ❌ Keine neuen CSS-Hex-Werte → bestehende CSS-Variablen verwenden
- ❌ Kein S3/R2 in V1 (ephemeral filesystem + Render Disk akzeptiert)
- ❌ Kein cURL (file_get_contents + stream_context reicht)

### Harte Do's

- ✅ `declare(strict_types=1)` in jeder PHP-Datei
- ✅ `escapeshellarg()` auf ALLEN Shell-Argumenten
- ✅ `csf_validate_path()` + `realpath()` + `CSF_STORAGE_ROOT`-Prefix vor jedem File-Access
- ✅ `LOCK_EX` bei jedem meta.json-Write
- ✅ Toast-Feedback + Error-Box bei jeder API-Aktion
- ✅ Mobile: 44px Touch-Targets, Stack-Layout unter 600px

---

## Aktueller Feature-Stand (V0.1.0)

### Vollständig implementiert ✅
| Feature | Dateien |
|---|---|
| Video-Upload (≤50 MB, ≤15s) | `api/upload.php`, `assets/js/upload.js` |
| Slot-Analyse via FFmpeg | `api/analyze.php` → `meta.json` + Thumbnails |
| Slot-Replacement: Bild | `api/replace-slot.php` → `meta.json` |
| Slot-Replacement: Video | `api/replace-slot.php` → `meta.json` |
| Slot-Replacement: Text-Titelkarte | `api/replace-slot.php` + `api/render-final.php` |
| Finaler Render → MP4 | `api/render-final.php` (FFmpeg concat) |
| Text-Overlay via FFmpeg drawtext | `api/render-final.php` → `csf_drawtext_escape()` |
| Export-Polling / Progress-Bar | `api/progress.php`, `assets/js/progress.js` |
| AI-Generierung (Kie.ai Flux Kontext) | `api/generate-ai.php` + `api/ai-status.php` |
| Demo-Studio-Interface | `studio-demo.php` |
| TikTok Prompt Generator | `tiktok-studio.php` + `api/generate-tiktok.php` |
| Trailer Builder | `trailer-builder.php` + `api/generate-trailer.php` |
| Academy (13 Guides) | `academy.php` |
| Health-Check-Endpoint | `api/health.php` |
| Element Library (Grundgerüst) | `elements.php` + `api/elements.php` |

### Neu implementiert in Session 2 (2026-05-14) ✅
| Feature | Dateien | Details |
|---|---|---|
| **Audio: stille AAC-Spur** | `api/render-final.php` | `-an` entfernt, alle 4 Slot-Typen bekommen `anullsrc` AAC 96k stereo. Concat `-c copy` funktioniert weiterhin. Original-Audio-Erhalt V3. |
| **Job-Level Lock** | `api/render-final.php` | `flock(LOCK_EX\|LOCK_NB)` auf `render.lock` → 409 bei parallelem Render |
| **KI-Bild Button** | `studio-demo.php` | Jede Slot-Card hat Prompt-Textarea + `✨ KI-Bild generieren` Button (lila/blau). `generateAiImage()` + `pollAiStatus()` mit 5s-Intervall, 3 Min. max, Thumbnail-Update on success. Auto-resume bei Page-Restore wenn `pending`. |
| **Nav verschlankt** | `studio-demo.php`, `scene-editor-test.html` | 5 Links: Home · Studio · Academy · Shop Beta · Kristalle |
| **Wallet Pill** | `studio-demo.php`, `scene-editor-test.html` | `💎 Free` (war `💎 500`) |
| **index.php Redirect** | `index.php` | HTTP 302 → `scene-editor-test.html` (war Placeholder) |
| **Korrekte Stats** | `scene-editor-test.html` | `720p` statt `4K`, Badge `Demo` für Kontakt/Verfügbarkeit |

### Neu implementiert in Session 3 (2026-05-14) ✅
| Feature | Dateien | Details |
|---|---|---|
| **Disk-Cleanup** | `includes/functions.php`, `api/cleanup.php`, `api/render-final.php` | `csf_cleanup_old_jobs()` — löscht Jobs/Exports/Temp >48h und Thumbnails ohne Job. Probabilistisch 1/50 nach Render. Manuell via `/api/cleanup.php?key=CLEANUP_SECRET`. |
| **Health: Storage-Stats** | `api/health.php` | `active_jobs`, `export_files`, `export_mb` in Response. `?debug=1&cleanup=1` triggert manuelles Cleanup. |
| **Elements Edit** | `elements.php`, `api/elements.php` | Edit-Button enabled. `update` Action implementiert (Name/Typ/Rolle/Beschreibung). Edit-Modal mit Live-DOM-Update auf Save. |
| **Logo-Upload verbunden** | `tiktok-animation.php`, `tiktok-sticker.php` | `uploadLogoIfNeeded()` — lädt Logo zu `api/upload.php` bevor Anfrage gesendet wird. `logo_url` im Request-Payload. |
| **Nav-Cleanup Sekundärseiten** | `availability.php`, `contact.php`, `crystals.php`, `ki-videos.php`, `portfolio.php`, `prompt-generator.php`, `shop.php` | Wallet `💎 Free`, Footer `© 2026`, Nav auf 5 Links vereinheitlicht. |
| **config.php Fixes** | `includes/config.php` | `APP_NAME` korrekt, `MAX_UPLOAD_BYTES` 50 MB (war 500 MB), `video/webm` hinzugefügt, `API_PROVIDER_LINK` → kie.ai |
| **save-request.php** | `api/save-request.php` (NEW) | POST-Endpoint: Contact-Anfragen aus ready-videos.php in `storage/requests.json` speichern. `flock(LOCK_EX)`, SHA-256 IP-Hash. |
| **SSRF-Schutz** | `api/ai-status.php` | DNS-IP-Validierung: `gethostbyname()` + `FILTER_FLAG_NO_PRIV_RANGE` |

### Dummy / Placeholder (V2+)
| Feature | Status |
|---|---|
| Login / User-Accounts | Demo-Dummy |
| Kristalle / Payment / Stripe | Demo-Dummy |
| Audio-Preservation (Original-Ton) | V3 Backlog (aktuell: stille AAC-Spur) |
| KI-Video-Generierung | Architecture only (Kie.ai video endpoints geplant) |

---

## Deployment

| Feld | Wert |
|---|---|
| **Platform** | Render.com |
| **Plan** | Free → Starter ($7/mo) |
| **Runtime** | Docker (php:8.2-apache) |
| **Port** | `$PORT` (Render: 10000) → dynamisch via Entrypoint |
| **Storage** | Render Persistent Disk 1 GB → `/var/www/html/render-data` |
| **Auto-Deploy** | Bei Push auf `main` |
| **Health-Check** | `/index.php` (Render intern) + `/api/health.php` (manuell) |

**Kritisch:** `KIE_AI_API_KEY` muss als Render-Environment-Variable gesetzt sein.
Apache braucht `PassEnv KIE_AI_API_KEY` in `docker/apache.conf` damit `getenv()` greift.
→ Details: `memory/deployment.md`

---

## Aktuelle Probleme (Stand 2026-05-14, Session 3)

| Problem | Priorität | Status |
|---|---|---|
| `KIE_AI_API_KEY` nicht in Render eingetragen | 🔴 P0 | **User-Aktion nötig:** Render Dashboard → Environment → `KIE_AI_API_KEY` eintragen → Redeploy |
| Kein echter AI-E2E-Test abgeschlossen | 🔴 P0 | Warte auf gültigen Key in Render |
| `CLEANUP_SECRET` nicht in Render eingetragen | 🟡 P2 | Optional: Render Dashboard → `CLEANUP_SECRET` (min. 20 Zeichen) → Redeploy |
| Audio (Original-Ton): stille Spur statt Originalton | 🟡 P2 | V3 Backlog — aktuell anullsrc AAC, Original-Audio-Erhalt folgt |
| Disk-Cleanup noch kein Cron | 🟢 P3 | Aktuell: 1/50 probabilistisch nach Render. Scheduled Task via Render noch nicht konfiguriert |

→ Vollständige Liste: `memory/current-problems.md`

---

## Roadmap (Kurzform)

| Milestone | Inhalt | Status |
|---|---|---|
| **V0.1.0** | Free MVP: Upload → Analyse → Replace → Render → Download | ✅ Live |
| **V0.2.0** | KI-Bild Button live (UI fertig), Audio-Spur vorhanden (silent), Job-Lock | 🟡 UI fertig — KIE_AI_API_KEY ausstehend |
| **V0.3.0** | Original-Audio-Erhalt, Starter+ Plan, 1080p | ⬜ Geplant |
| **V1.0.0** | Login, Kristalle, Payment (Stripe) | ⬜ Geplant |
| **V2.0.0** | Multi-User, S3/R2, KI-Video, Templates | ⬜ Vision |

→ Details: `memory/roadmap.md`

---

## Dateistruktur (Schlüsseldateien)

```
cinematic-studio-family/
├── CLAUDE.md                    ← Diese Datei (AI-Kontext)
├── memory/                      ← Strukturierter AI-Kontext
│   ├── business.md
│   ├── architecture.md
│   ├── deployment.md
│   ├── ffmpeg.md
│   ├── byok-system.md
│   ├── video-pipeline.md
│   ├── roadmap.md
│   └── current-problems.md
├── docs/
│   └── project-overview.md      ← Menschenlesbare Übersicht
│
├── studio-demo.php              ← Haupt-UI (MVP)
├── api/
│   ├── analyze.php              ← Video → Slots + meta.json
│   ├── replace-slot.php         ← Slot-Ersatz speichern
│   ├── render-final.php         ← FFmpeg Render-Pipeline
│   ├── generate-ai.php          ← Kie.ai Task starten
│   ├── ai-status.php            ← Kie.ai Task pollen + Bild speichern
│   ├── health.php               ← Server-Status
│   └── ...
├── includes/
│   ├── config.php               ← Konstanten, Session-Start
│   ├── functions.php            ← FFmpeg-Service-Library
│   └── ...
├── docker/
│   ├── apache.conf              ← PassEnv KIE_AI_API_KEY hier!
│   └── entrypoint.sh
├── Dockerfile                   ← PHP 8.2 + Apache + FFmpeg + fonts-liberation
└── render.yaml                  ← Render-Deployment-Config
```

---

## Working Environment

| Feld | Wert |
|---|---|
| **Dev-OS** | Windows |
| **Shell** | Git Bash + PowerShell 5.1 |
| **Pfade** | POSIX-Style in Bash (`/c/Users/...`), Backslash in PowerShell |
| **Chaining** | `; if ($?) { }` in PS — kein `&&`/`||` |
| **File-Upload** | PowerShell `System.Net.Http.HttpClient` (curl hat Pfad-Probleme auf Windows) |
| **Encoding** | UTF-8 |

---

## Agent-Regeln (für Claude Code + Sub-Agents)

1. **Diese Datei + memory/ VOR jeder Arbeit lesen.**
2. **Nie ohne Freigabe committen.** git diff zeigen → auf OK warten.
3. **Keine Frameworks einführen** — Flat PHP bleibt Flat PHP.
4. **Keine Dateien löschen** ohne explizite Anweisung.
5. **Nach jedem Feature:** `PROJECT_STATUS.md` + `CHANGELOG.md` + `CLAUDE.md` updaten.
6. **Council-Trigger:** Bei Entscheidungen mit mehreren validen Optionen → "council this:" vorschlagen.

---

## Was wurde in der letzten Session gebaut (Session 3 — 2026-05-14)

> Dieser Block ist für jeden neuen Agenten / Account. Lesen, dann loslegen.

### Geänderte Dateien
| Datei | Was geändert |
|---|---|
| `api/render-final.php` | Job-Lock (LOCK_NB) + Audio: alle 4 Slot-Typen haben jetzt anullsrc AAC-Spur statt -an + 1/50 Cleanup-Trigger |
| `studio-demo.php` | Nav verschlankt (5 Links), Wallet "💎 Free", Cold-Start-Text humanisiert, KI-Bild Button + generateAiImage() + pollAiStatus() |
| `scene-editor-test.html` | Nav verschlankt, Wallet "💎 Free", Stat 720p statt 4K, Academy-CTA, Badge-Fixes |
| `index.php` | HTTP 302 Redirect → scene-editor-test.html |
| `includes/functions.php` | `csf_cleanup_old_jobs()` hinzugefügt |
| `includes/config.php` | APP_NAME, MAX_UPLOAD_BYTES (50MB), video/webm, API-Links |
| `api/cleanup.php` (NEU) | Cleanup-Endpoint mit CLEANUP_SECRET |
| `api/health.php` | Storage-Stats, ?debug=1&cleanup=1 |
| `api/elements.php` | `update` Action implementiert |
| `elements.php` | Edit-Button + Edit-Modal |
| `api/save-request.php` (NEU) | Contact-Anfragen aus ready-videos.php speichern |
| `api/ai-status.php` | SSRF-Schutz: DNS-IP-Validierung |
| `tiktok-animation.php` | Logo-Upload zu api/upload.php verbunden |
| `tiktok-sticker.php` | Logo-Upload zu api/upload.php verbunden |
| `availability.php`, `contact.php`, `crystals.php`, `ki-videos.php`, `portfolio.php`, `prompt-generator.php`, `shop.php` | Nav 5 Links, Wallet 💎 Free, Footer 2026 |

### Was NICHT geändert werden soll (Don't Touch)
- `api/generate-ai.php` — Backend ist fertig, wartet nur auf KIE_AI_API_KEY
- `api/analyze.php`, `api/replace-slot.php` — unverändert, funktionieren
- `docker/apache.conf` — PassEnv bereits deployed
- `data/ready-videos.json` — 12 Demo-Einträge, gut so

### Nächste offene Aufgaben (in dieser Reihenfolge)
1. **[User-Aktion]** `KIE_AI_API_KEY` in Render-Dashboard eintragen → Redeploy
2. **[User-Aktion]** `CLEANUP_SECRET` in Render-Dashboard eintragen (min. 20 Zeichen, beliebig)
3. **[Agent]** E2E-Test: Upload Video → KI-Bild generieren → Render → Download (nach Key-Eintrag)
4. **[Agent]** Original-Audio-Erhalt in render-final.php (V3) — aktuell stille AAC
5. **[Agent]** Cron-Job für Cleanup auf Render konfigurieren (`render.yaml` cron section)
6. **[Entscheidung]** Domain-Strategie: cinematic-studio-family.com vs cinematic-vision-studio.com
