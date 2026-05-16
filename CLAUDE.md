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
| **Version** | 0.3.1 (Auth live, E2E getestet, KI-Flow verifiziert) |
| **Live-URL** | https://cinematic-studio-family.onrender.com |
| **IONOS-URL** | https://cinematic-vision-studio.de/scene-editor-test.html |
| **GitHub** | nuffysstyle-droid/cinematic-studio-family |
| **Stand** | 2026-05-16 (Session 7) |

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
| **Free** | 720p, max 15s, max 3 Slots, 50 Welcome-Kristalle | kostenlos |
| **Starter+** | 1080p, Audio, Persistent Disk, mehr Slots | geplant ~$7–12/mo |
| **Pro** | KI-Generierungen, Premium Templates, API-Zugang | geplant ~$29/mo |

**Kristalle** — interne Währung für KI-Generierungen (Kie.ai Credits).
Neue User bekommen automatisch 50 Welcome-Kristalle. Echte Transaktion in V2.

---

## Tech-Stack (verbindlich — nicht ohne Diskussion ändern)

| Schicht | Technologie | Begründung |
|---|---|---|
| **Backend** | PHP 8.2 + Apache (mod_php) | Kein Build-Step, direkt deploybar, FFmpeg via apt |
| **Frontend** | Vanilla JS + HTML5 + CSS3 | Kein Framework-Overhead in V1 |
| **Video-Engine** | FFmpeg 7.1.3 (serverseitig) | Volle Codec-Kontrolle, kein WASM |
| **AI-Provider** | Kie.ai (Flux Kontext Pro/Max) | Async Task-API, JPEG-Output, 14-Tage CDN |
| **Auth-DB** | SQLite 3 via PDO | WAL-Modus, keine DB-Server nötig, Render Persistent Disk |
| **Deployment** | Render.com (Docker, Starter-Plan) | PHP + FFmpeg + Persistent Disk in einem |
| **Static Hosting** | IONOS (cinematic-vision-studio.de) | Landing Page, Shop, Academy, Portfolio |
| **Storage** | JSON-Dateien + LOCK_EX + SQLite | Kein DB-Setup nötig |
| **Fonts** | Liberation Sans (fonts-liberation) | FFmpeg drawtext, Linux-native |

### Harte Don'ts

- ❌ Kein `innerHTML` für User-Daten → `textContent` / DOM-API / `<template>`-Cloning
- ❌ Kein npm, kein Composer, kein Build-Tool in V1
- ❌ Kein externes JS-Framework (React, Vue, jQuery)
- ❌ Keine neuen CSS-Hex-Werte → bestehende CSS-Variablen verwenden
- ❌ Kein S3/R2 in V1 (ephemeral filesystem + Render Disk akzeptiert)
- ❌ Kein cURL (file_get_contents + stream_context reicht)

### Harte Do's

- ✅ `declare(strict_types=1)` in jeder PHP-Datei
- ✅ `escapeshellarg()` auf ALLEN Shell-Argumenten
- ✅ `csf_validate_path()` + `realpath()` + `CSF_STORAGE_ROOT`-Prefix vor jedem File-Access
- ✅ `LOCK_EX` bei jedem meta.json-Write
- ✅ `htmlspecialchars((string)$var)` — immer (string)-Cast vor htmlspecialchars!
- ✅ Toast-Feedback + Error-Box bei jeder API-Aktion
- ✅ Mobile: 44px Touch-Targets, Stack-Layout unter 600px
- ✅ Playwright für alle Browser-Tests (kein Claude_in_Chrome)
- ✅ PowerShell für alle lokalen Projektbefehle

---

## Aktueller Feature-Stand (V0.3.1)

### Vollständig implementiert + Live getestet ✅

| Feature | Dateien | Details |
|---|---|---|
| **Video-Upload** | `api/upload.php` | ≤50 MB, ≤15s, MIME-Check, is_uploaded_file() |
| **Slot-Analyse** | `api/analyze.php` | FFmpeg → meta.json + Thumbnails |
| **Slot-Replacement** | `api/replace-slot.php` | Bild / Video / Text-Titelkarte |
| **Finaler Render → MP4** | `api/render-final.php` | FFmpeg concat, V3 Audio, Job-Lock, Plan-Enforcement |
| **V3 Original-Audio** | `api/render-final.php` | ffprobe prüft Audio-Track → -map 0:a wenn vorhanden |
| **KI-Generierung** | `api/generate-ai.php`, `api/ai-status.php` | Kie.ai Flux Kontext, SSRF-Schutz |
| **Export-Qualität** | `api/settings/quality.php` | 720p / 1080p Session-Setting, Plan-Enforcement |
| **Auth: Register** | `api/auth/register.php`, `includes/auth.php` | ARGON2ID, 50 Welcome-Kristalle, Rate-limited 10/h |
| **Auth: Login** | `api/auth/login.php` | Brute-force-Schutz 5/15min, Remember-me 30d |
| **Auth: Logout** | `api/auth/logout.php` | Redirect-aware (HTML vs JSON) |
| **Auth: Me** | `api/auth/me.php` | GET → user-Objekt oder 401 |
| **Auth: Passwort ändern** | `api/auth/change-password.php` | Rate-limited 10/h, ARGON2ID rehash, invalidiert tokens |
| **Auth: Passwort vergessen** | `api/auth/forgot-password.php` | User-enum-safe, Token in DB, V0.5 Email |
| **Login UI** | `login.php` | Tab: Login + Register, dark theme, Stärke-Balken |
| **Dashboard** | `dashboard.php` | Kristalle, Plan, Jobs, Transaktionen, Upgrade-CTA |
| **Profil** | `profile.php` | Account-Info, Passwort ändern, Danger Zone |
| **Passwort vergessen UI** | `forgot-password.php` | Token-Modus + E-Mail-Info-Seite |
| **Studio Auth-Integration** | `studio-demo.php` | Login/Logout dynamisch, Kristall-Balance, 1080p-Toggle |
| **Dashboard → Studio Link** | `studio-demo.php` | `?job_id=` URL-Param → auto-restore Job |
| **IP Rate Limiting** | `includes/rate_limit.php` | File-based, SHA-256 IP-Hash, 10 KI/h, 15 renders/h |
| **Disk-Cleanup** | `includes/functions.php`, `api/cleanup.php` | csf_cleanup_old_jobs(), 1/50 probabilistisch |
| **Cron-Cleanup** | `render.yaml`, `bin/cleanup-cron.php` | Täglich 03:00 UTC, CLI-Script |
| **Health-Check** | `api/health.php` | ok/php/ffmpeg/storage/ai mit Stats |
| **IONOS Static Pages** | scene-editor-test.html, shop.html, academy.html, … | Alle ✅ auf cinematic-vision-studio.de |
| **IONOS Root-Redirect** | `index.html` (IONOS), `index.php` (Render) | → scene-editor-test.html |

### E2E-Test bestätigt ✅ (Session 7, 2026-05-16)

```
Upload test_video.mp4 (5s, 640x360)
→ Analyse: 2 Slots erkannt
→ KI-Bild (Kie.ai): "cinematic golden sunset over mountains" → generiert in ~40s
→ Render: 0.4 MB MP4, 5s, 720p
→ Download: MP4 herunterladbar + Video-Player inline
→ Auth: Login/Register/Dashboard/Profil alle ✅ ohne Fehler
→ Kristalle: 50 Welcome-Bonus korrekt, welcome_bonus Transaktion sichtbar
```

### Dummy / Placeholder (V2+)
| Feature | Status |
|---|---|
| Kristalle / Payment / Stripe | Demo-Dummy (DB-Tabellen bereit) |
| Email-Verifizierung | Geplant V0.5 (Mailgun/SMTP) |
| KI-Video-Generierung | Architecture only (Kie.ai video endpoints geplant) |

---

## Deployment

| Feld | Wert |
|---|---|
| **Platform** | Render.com |
| **Plan** | Starter ($7/mo) — 512 MB RAM, kein Sleep, Disk erlaubt |
| **Runtime** | Docker (php:8.2-apache) |
| **Port** | `$PORT` (Render: 10000) → dynamisch via Entrypoint |
| **Storage** | Render Persistent Disk 1 GB → `/var/www/html/render-data` |
| **DB** | SQLite → `/var/www/html/render-data/cinematic.db` |
| **Auto-Deploy** | Bei Push auf `main` |
| **Health-Check** | `/index.php` (Render intern) + `/api/health.php` (manuell) |
| **Cron** | `csf-cleanup-cron` Service in render.yaml → täglich 03:00 UTC |

**Gesetzte Render Env-Vars:**
- ✅ `KIE_AI_API_KEY` — gesetzt, verifiziert via health.php (`kie_key_set: true`)
- ⬜ `CLEANUP_SECRET` — optional, für manuelles `/api/cleanup.php?key=...`

---

## Aktuelle Probleme (Stand 2026-05-16, Session 7)

| Problem | Priorität | Status |
|---|---|---|
| `CLEANUP_SECRET` nicht in Render | 🟢 P3 | Optional — Render Dashboard → `CLEANUP_SECRET` (20+ Zeichen) |
| IONOS index.html noch nicht hochgeladen | 🟡 P2 | `index.html` lokal erstellt → via IONOS FTP hochladen (Root 403 fix) |
| Email-Verifizierung fehlt | 🟡 P2 | V0.5 Backlog — Mailgun oder PHP mail() |
| Stripe/Payment nicht integriert | 🟡 P2 | V1.0 Backlog |
| Cron-Service aktiv auf Render? | 🟡 P2 | render.yaml hat `type: cron` — prüfen ob Render ihn deployed hat |

---

## Roadmap (Kurzform)

| Milestone | Inhalt | Status |
|---|---|---|
| **V0.1.0** | Free MVP: Upload → Analyse → Replace → Render → Download | ✅ Live |
| **V0.2.0** | KI-Bild Button, Audio-Spur, Job-Lock | ✅ Live |
| **V0.3.0** | Original-Audio-Erhalt, Cron-Cleanup, Rate Limiting, Auth/Login | ✅ Live |
| **V0.3.1** | Dashboard, Profil, Passwort-Reset, E2E-Test bestätigt, Bug-Fixes | ✅ Live |
| **V0.4.0** | Starter+ Plan live, Stripe Payment, Email-Verifizierung | ⬜ Geplant |
| **V0.5.0** | Email-System (Willkommen, Reset, Upgrade) | ⬜ Geplant |
| **V1.0.0** | Kristalle live, echte KI-Abrechnung, Multi-Plan | ⬜ Geplant |
| **V2.0.0** | Multi-User, S3/R2, KI-Video, Templates | ⬜ Vision |

---

## Dateistruktur (Schlüsseldateien)

```
cinematic-studio-family/
├── CLAUDE.md                        ← Diese Datei (AI-Kontext, immer aktuell halten!)
├── memory/                          ← Strukturierter AI-Kontext
│   ├── architecture.md
│   ├── deployment.md
│   ├── ffmpeg.md
│   ├── roadmap.md
│   └── current-problems.md
│
├── index.html                       ← IONOS: Root-Redirect → scene-editor-test.html
├── scene-editor-test.html           ← IONOS: Landing Page / Homepage
├── shop.html                        ← IONOS: Shop Beta
├── academy.html                     ← IONOS: Academy
├── crystals.html                    ← IONOS: Kristalle & Pakete
├── portfolio.html                   ← IONOS: Portfolio
├── availability.html                ← IONOS: Verfügbarkeit
│
├── index.php                        ← Render: 301 → scene-editor-test.html
├── studio-demo.php                  ← Render: Haupt-UI (auth-aware, KI-Bild, 1080p-Toggle)
├── login.php                        ← Render: Login + Register (tab-basiert)
├── dashboard.php                    ← Render: User-Dashboard (Kristalle, Jobs, Plan)
├── profile.php                      ← Render: Profil (Account-Info, Passwort ändern)
├── forgot-password.php              ← Render: Passwort-Reset UI
│
├── api/
│   ├── analyze.php                  ← Video → Slots + meta.json
│   ├── replace-slot.php             ← Slot-Ersatz speichern
│   ├── render-final.php             ← FFmpeg Render-Pipeline (V3 Audio, Plan-Enforcement)
│   ├── generate-ai.php              ← Kie.ai Task starten
│   ├── ai-status.php                ← Kie.ai Task pollen + SSRF-Schutz
│   ├── health.php                   ← Server-Status (FFmpeg, KIE-Key, Storage)
│   ├── cleanup.php                  ← Manueller Cleanup (CLEANUP_SECRET)
│   ├── settings/
│   │   └── quality.php              ← POST: 720p/1080p Session-Setting
│   └── auth/
│       ├── login.php                ← POST /api/auth/login
│       ├── register.php             ← POST /api/auth/register
│       ├── logout.php               ← POST /api/auth/logout
│       ├── me.php                   ← GET  /api/auth/me
│       ├── change-password.php      ← POST /api/auth/change-password
│       └── forgot-password.php      ← POST /api/auth/forgot-password
│
├── includes/
│   ├── config.php                   ← Konstanten, Session-Start
│   ├── functions.php                ← FFmpeg-Service-Library + csf_cleanup_old_jobs()
│   ├── db.php                       ← SQLite PDO Singleton + Schema-Init
│   ├── auth.php                     ← Auth: register/login/logout/user/crystals
│   └── rate_limit.php               ← IP-basiertes Rate Limiting (file-based)
│
├── bin/
│   └── cleanup-cron.php             ← CLI-Script für Render Cron-Service
│
├── docker/
│   ├── apache.conf                  ← PassEnv KIE_AI_API_KEY, storage/jobs Freigabe
│   └── entrypoint.sh
├── Dockerfile                       ← PHP 8.2 + Apache + FFmpeg + fonts-liberation
└── render.yaml                      ← Web-Service + Cron-Service (03:00 UTC)
```

---

## Working Environment

| Feld | Wert |
|---|---|
| **Dev-OS** | Windows |
| **Shell** | Git Bash + PowerShell 5.1 |
| **Browser-Tests** | Playwright (kein Claude_in_Chrome!) |
| **Pfade** | POSIX-Style in Bash (`/c/Users/...`), Backslash in PowerShell |
| **Chaining** | `; if ($?) { }` in PS — kein `&&`/`||` |
| **Encoding** | UTF-8 |

---

## Agent-Regeln (für Claude Code + Sub-Agents)

1. **Diese Datei VOR jeder Arbeit lesen.**
2. **Nie ohne Freigabe committen** — bei expliziter Erlaubnis direkt committen + pushen.
3. **Keine Frameworks einführen** — Flat PHP bleibt Flat PHP.
4. **Keine Dateien löschen** ohne explizite Anweisung.
5. **Nach jedem Feature: CLAUDE.md aktualisieren** (Version, Features, Probleme, Session-Block).
6. **htmlspecialchars immer mit (string)-Cast:** `htmlspecialchars((string)$var)`.
7. **Playwright für Browser-Tests** — kein Claude_in_Chrome, kein zweites Fenster.
8. **Council-Trigger:** Bei Entscheidungen mit mehreren validen Optionen → vorschlagen.

---

## Was wurde in der letzten Session gebaut (Session 7 — 2026-05-16)

> Dieser Block ist für jeden neuen Agenten. Lesen, dann loslegen.

### Session 7 — Neue / geänderte Dateien

| Datei | Was geändert |
|---|---|
| `studio-demo.php` | **Bug-Fix:** `htmlspecialchars((string)$crystals)` — int-Cast fehlte → Fatal Error. Root-Links → scene-editor-test.html (war / → 403) |
| `index.php` | Redirect → scene-editor-test.html (war / → 403) |
| `index.html` (NEU, IONOS) | Meta-Refresh + JS-Redirect → scene-editor-test.html (IONOS Root-Fix) |
| `bin/cleanup-cron.php` (NEU) | CLI-Script für Render Cron-Service. Ruft csf_cleanup_old_jobs() auf, gibt Stats aus. |
| `dashboard.php` | Neu gebaut: Kristalle, Plan, Jobs (Server-Projekte), Transaktionen, Upgrade-CTA |
| `profile.php` | Neu gebaut: Account-Info, Passwort ändern (via API), Danger Zone |
| `forgot-password.php` | Neu gebaut: Token-Modus + Email-Info-Seite |
| `api/auth/change-password.php` | Neu: Rate-limited 10/h, ARGON2ID rehash, invalidiert Remember-Tokens |
| `api/auth/forgot-password.php` | Neu: User-enum-safe, Token in DB (password_resets table) |
| `api/settings/quality.php` | Neu: POST → 720p/1080p Session-Setting, Free capped auf 720p |
| `includes/auth.php` | Bug-Fix: Doppelter SQL-Block entfernt (string-interpolation SQL injection). Welcome-Bonus 50 Kristalle bei Register. |
| `api/render-final.php` | Plan-Enforcement: Free → 720p, Starter+/Pro → 1080p |

### E2E-Test Status (Session 7)
- ✅ Upload → Analyse → KI-Bild → Render → Download — **vollständig getestet und bestätigt**
- ✅ KIE_AI_API_KEY gesetzt und aktiv (health.php: `kie_key_set: true`)
- ✅ Auth: Register → Login → Dashboard → Profil → Logout — alle ohne Fehler
- ✅ Welcome-Bonus: 50 Kristalle korrekt, Transaktion sichtbar

### Was NICHT geändert werden soll (Don't Touch)
- `api/analyze.php`, `api/replace-slot.php` — Funktionieren korrekt
- `api/cleanup.php` — Korrekt, wartet auf CLEANUP_SECRET in Render
- `docker/apache.conf`, `Dockerfile` — Korrekt konfiguriert
- `data/ready-videos.json` — 12 Demo-Einträge, gut so

### Nächste offene Aufgaben (nach Priorität)
1. **[User-Aktion]** `index.html` via IONOS FTP hochladen → Root-403 fixen
2. **[User-Aktion]** `CLEANUP_SECRET` in Render-Dashboard → min. 20 Zeichen → Redeploy
3. **[Agent]** Render Cron-Service prüfen ob aktiv (render.yaml `type: cron` deployed?)
4. **[Agent]** Email-Verifizierung bei Register (Mailgun oder SMTP, V0.5)
5. **[Agent]** Starter+ Plan Stripe-Integration (V0.4)
6. **[Entscheidung]** Domain: cinematic-vision-studio.de als primäre Domain behalten?
