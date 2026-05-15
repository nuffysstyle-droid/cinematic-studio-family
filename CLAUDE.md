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
| **Version** | 0.3.0 (Auth/Login live, Render deployed) |
| **Live-URL** | https://cinematic-studio-family.onrender.com |
| **GitHub** | nuffysstyle-droid/cinematic-studio-family |
| **Stand** | 2026-05-16 (Session 6) |

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

### Neu implementiert in Session 4 (2026-05-14) ✅
| Feature | Dateien | Details |
|---|---|---|
| **V3 Audio: Original-Ton-Erhalt** | `api/render-final.php` | `csf_ffprobe_run()` prüft vor dem Slot-Loop ob Original-Video einen Audio-Stream hat. Wenn ja → `-map 0:a` + `-ar 44100 -ac 2 -c:a aac`. Wenn nein → anullsrc. Auch Video-Replacements mit Audio-Track werden korrekt behandelt. |
| **Cron-Job: täglicher Cleanup** | `render.yaml`, `bin/cleanup-cron.php` | Render Cron-Service (`type: cron`, 03:00 UTC täglich). `bin/cleanup-cron.php` CLI-Script — ruft `csf_cleanup_old_jobs()` auf, gibt Stats aus. Ergänzt probabilistisches 1/50 Cleanup. |
| **Apache: storage/jobs Freigabe** | `docker/apache.conf` | `<Directory /var/www/html/storage/jobs>` mit `Require all granted` hinzugefügt. `meta.json` und PHP-Dateien bleiben blockiert per `<FilesMatch>`. Behebt 403-Fehler für KI-generierte Replacement-Bilder. |
| **PassEnv CLEANUP_SECRET** | `docker/apache.conf` | `PassEnv CLEANUP_SECRET` eingetragen — PHP-`getenv()` kann `CLEANUP_SECRET` jetzt lesen. |
| **Header-Einrückung** | `docker/apache.conf` | CORS-Header-Zeilen korrekt eingerückt (waren nicht eingerückt, Apache-Fehler möglich). |

### Neu implementiert in Session 6 (2026-05-16) ✅
| Feature | Dateien | Details |
|---|---|---|
| **IP Rate Limiting** | `includes/rate_limit.php`, `api/generate-ai.php`, `api/render-final.php`, `api/upload.php` | File-based, SHA-256 IP-Hash, slot-based windows. Limits: 10 KI/h, 15 renders/h, 30 uploads/h. `.htaccess` Deny in rate_limits/. |
| **SQLite Auth DB** | `includes/db.php` | PDO Singleton, WAL+NORMAL+FK+busy_timeout=5000. Schema V1: users, crystal_transactions, login_attempts, remember_tokens. Render Persistent Disk Path. |
| **Auth System** | `includes/auth.php` | ARGON2ID register, brute-force-guarded login (5/15min), session-fixation-schutz, remember-me rolling 30d HMAC token, plan enforcement (free/starter/pro), atomic crystal spend/add. |
| **Auth API Endpoints** | `api/auth/login.php`, `api/auth/register.php`, `api/auth/logout.php`, `api/auth/me.php` | Rate-limited Login (20/h), Register (10/h), redirect-aware Logout, GET /me. |
| **Login UI** | `login.php` | Tab-basiert Login + Register, dark theme, Passwort-Stärke-Anzeige, Redirect-Support, remember-me Checkbox. |
| **Studio Auth-Integration** | `studio-demo.php` | PHP-Header, zeigt Login/Logout dynamisch je nach Session. Wallet zeigt echten Kristall-Stand. |

### Dummy / Placeholder (V2+)
| Feature | Status |
|---|---|
| Kristalle / Payment / Stripe | Demo-Dummy (DB-Tabellen bereit) |
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

## Aktuelle Probleme (Stand 2026-05-16, Session 6)

| Problem | Priorität | Status |
|---|---|---|
| `KIE_AI_API_KEY` in Render eingetragen? | 🔴 P0 | **User-Aktion nötig** wenn noch nicht gesetzt: Render Dashboard → Environment → `KIE_AI_API_KEY` → Redeploy |
| `APP_SECRET` für Cookie-Signing nicht gesetzt | 🔴 P1 | Render Dashboard → `APP_SECRET` (min. 32 Zeichen random) → Redeploy — sonst Fallback in config.php |
| Kein echter AI-E2E-Test abgeschlossen | 🟡 P2 | Erst nach KIE_AI_API_KEY Eintrag möglich |
| `CLEANUP_SECRET` nicht in Render eingetragen | 🟢 P3 | Optional: Render Dashboard → `CLEANUP_SECRET` (min. 20 Zeichen) |

→ Vollständige Liste: `memory/current-problems.md`

---

## Roadmap (Kurzform)

| Milestone | Inhalt | Status |
|---|---|---|
| **V0.1.0** | Free MVP: Upload → Analyse → Replace → Render → Download | ✅ Live |
| **V0.2.0** | KI-Bild Button, Audio-Spur, Job-Lock | ✅ Live |
| **V0.3.0** | Original-Audio-Erhalt, Cron-Cleanup, Rate Limiting, Auth/Login | ✅ Deployed |
| **V0.4.0** | Starter+ Plan (1080p), Email-Verifizierung, Stripe-Integration | ⬜ Geplant |
| **V1.0.0** | Kristalle live, echte KI-Abrechnung, Dashboard | ⬜ Geplant |
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
├── studio-demo.php              ← Haupt-UI (MVP, auth-aware)
├── login.php                    ← Login + Register UI (tab-basiert)
├── api/
│   ├── analyze.php              ← Video → Slots + meta.json
│   ├── replace-slot.php         ← Slot-Ersatz speichern
│   ├── render-final.php         ← FFmpeg Render-Pipeline (V3 Audio)
│   ├── generate-ai.php          ← Kie.ai Task starten
│   ├── ai-status.php            ← Kie.ai Task pollen + Bild speichern
│   ├── health.php               ← Server-Status
│   ├── auth/
│   │   ├── login.php            ← POST /api/auth/login
│   │   ├── register.php         ← POST /api/auth/register
│   │   ├── logout.php           ← POST /api/auth/logout
│   │   └── me.php               ← GET  /api/auth/me
│   └── ...
├── includes/
│   ├── config.php               ← Konstanten, Session-Start
│   ├── functions.php            ← FFmpeg-Service-Library
│   ├── db.php                   ← SQLite PDO Singleton + Schema-Init
│   ├── auth.php                 ← Auth: register/login/logout/user/crystals
│   ├── rate_limit.php           ← IP-basiertes Rate Limiting (file-based)
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

## Was wurde in der letzten Session gebaut (Session 6 — 2026-05-16)

> Dieser Block ist für jeden neuen Agenten / Account. Lesen, dann loslegen.

### Session 6 — Neue / geänderte Dateien
| Datei | Was geändert |
|---|---|
| `includes/rate_limit.php` (NEU) | File-based IP Rate Limiter. `csf_rate_limit_check()`, `csf_rate_limit_cleanup()`. `.htaccess` Deny. SHA-256 IP-Hash. |
| `includes/db.php` (NEU) | SQLite PDO Singleton. WAL, FK, busy_timeout. Schema V1 (users, crystal_transactions, login_attempts, remember_tokens). `csf_db_transaction()`. |
| `includes/auth.php` (NEU) | ARGON2ID register. Brute-force login (5/15min). Session-Fixation-Schutz. Remember-Me 30d Rolling. `csf_auth_require()`, `csf_auth_require_plan()`, `csf_auth_spend_crystals()`, `csf_auth_add_crystals()`. |
| `api/auth/register.php` (NEU) | POST /api/auth/register — Rate-limited 10/h |
| `api/auth/login.php` (NEU) | POST /api/auth/login — Rate-limited 20/h, remaining_tries |
| `api/auth/logout.php` (NEU) | POST /api/auth/logout — redirect-aware (HTML vs JSON) |
| `api/auth/me.php` (NEU) | GET /api/auth/me — 401 wenn nicht eingeloggt |
| `login.php` (NEU) | Tab-basiertes Login/Register UI. Dark theme. Passwort-Stärke. Remember-me. |
| `studio-demo.php` | PHP-Header + `csf_auth_user()`. Nav zeigt Login/Logout/Kristalle dynamisch. |
| `api/generate-ai.php` | `require auth.php`, `$authUser` tracking, `ai_user_id` in meta.json |
| `api/render-final.php` | `require auth.php`, `$authUser` für künftige Tracking |
| `api/upload.php` | `require auth.php`, `$authUser` für künftige Tracking |

### Was NICHT geändert werden soll (Don't Touch)
- `api/analyze.php`, `api/replace-slot.php` — Funktionieren korrekt
- `api/cleanup.php` — Korrekt, wartet auf CLEANUP_SECRET in Render
- `includes/functions.php` — `csf_cleanup_old_jobs()` ist final
- `data/ready-videos.json` — 12 Demo-Einträge, gut so
- `docker/apache.conf`, `Dockerfile`, `render.yaml` — Korrekt konfiguriert

### Nächste offene Aufgaben (in dieser Reihenfolge)
1. **[User-Aktion]** `APP_SECRET` in Render-Dashboard → min. 32 Zeichen random → Redeploy
2. **[User-Aktion]** `KIE_AI_API_KEY` in Render-Dashboard → Environment Variables → Redeploy
3. **[User-Aktion]** `CLEANUP_SECRET` in Render-Dashboard → min. 20 Zeichen → Redeploy
4. **[Agent]** E2E Login testen: `/login.php` → Register → Login → Studio → Logout
5. **[Agent]** Email-Verifizierung (Mailgun/SendGrid oder SMTP via PHP mail())
6. **[Agent]** Starter+ Plan: 1080p toggle in render-final.php über User-Plan
7. **[Agent]** Dashboard-Seite: `dashboard.php` — Eigene Jobs, Kristall-Verlauf, Plan-Info
