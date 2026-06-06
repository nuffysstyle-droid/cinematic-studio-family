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
| **Version** | 0.3.2 (Quality Audit, CSS-Fixes, Nav-Fixes) |
| **Live-URL** | https://cinematic-studio-family.onrender.com |
| **IONOS-URL** | https://cinematic-vision-studio.de/scene-editor-test.html |
| **GitHub** | nuffysstyle-droid/cinematic-studio-family |
| **Stand** | 2026-05-16 (Session 8) |

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
| **Ruflo (claude-flow)** | npx ruflo@3.6.30 (MCP) | Multi-Agent Swarm (Coder+Reviewer+Tester+Security+Docs) |

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
- ✅ `CLEANUP_SECRET` — gesetzt, manuelles Cleanup via `/api/cleanup.php?key=...` aktiv

---

## Aktuelle Probleme (Stand 2026-05-16, Session 8)

| Problem | Priorität | Status |
|---|---|---|
| Render Cron-Service nicht deployed | 🟡 P2 | Render zeigt Free-Plan → Cron braucht Starter. Fallback: 1/50 probabilistisch nach Render + HTTP `/api/cleanup.php?key=CLEANUP_SECRET` |
| Email-Verifizierung fehlt | 🟡 P2 | V0.5 Backlog — Mailgun oder PHP mail() |
| Stripe/Payment nicht integriert | 🟡 P2 | V1.0 Backlog |

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

## Was wurde in Session 11 gebaut (2026-06-06)

> Design Alignment Gate — `immobilienvideos.html` ist der Master-Reference für alle IONOS-Seiten.

### Session 11 — Ziel
`scene-editor-test.html` an `immobilienvideos.html`-Designsprache angleichen:
- `.cvs-nav-simple` Navigation (Gradient-Fade, Gold-Hover, Blur)
- `.cvs-aurora` Hintergrund (CSS-Orbs statt Canvas)
- `.btn-cvs--gold` / `.btn-cvs--ghost` Premium-Buttons
- `.cvs-footer-master` Footer mit 3 Spalten
- `.lightbar` Section-Separatoren
- `#cvs-progress` Scroll-Progress-Bar
- Film-Grain auf Referenz-Level (`opacity:.28`, `z-index:0`)
- Custom-Cursor deaktiviert
- Alle bestehenden Funktionen (Video, Partikel, Portfolio-Filter, Stat-Counter, Mobile-Nav, Reveal-Animationen) erhalten

### Session 11 — Änderungen (Stand jetzt)
| Datei | Was geändert |
|---|---|
| `scene-editor-test.html` | **CSS Overrides:** Aurora-Orbs, Lightbar, Premium Buttons, Footer-Master, Scroll-Progress, Nav-Override, Eyebrow/Title Gold, Cursor-Disable, Reveal-Animationen |
| `scene-editor-test.html` | **HTML:** Canvas aurora → CSS-Orbs, Cursor entfernt, Nav-Klassen ergänzt, Footer-Struktur auf 3-Spalten + Legal-Bottom umgebaut, Mobile-Nav erweitert, Lightbars zwischen Sections eingefügt |
| `scene-editor-test.html` | **JS:** Aurora-Canvas entfernt → Gold Scroll-Progress-Bar, Magnetic Buttons um `.btn-cvs` erweitert |
| `scene-editor-test.html` | **Finishing:** Logo-Source auf `cvs-logo-icon.png`, Mobile-CTA `.mob-cta` + Reorder, Booking-CTA auf `.btn-cvs--gold`, Content-Safety `z-index:1` |
| `portfolio.html` | **Design-DNA-Alignment:** Lokale Fonts, `.btn-cvs--gold`/`.btn-cvs--ghost`, Lightbars, Scroll-Progress-Bar, Reveal-Animationen (10 Karten + Header + CTA), Open-Graph-Meta-Tags |
| `shop.html` | **Design-DNA-Alignment:** Lokale Fonts, `.cvs-aurora`, `.btn-cvs--gold`/`.btn-cvs--ghost`, Lightbars, Scroll-Progress-Bar, Reveal-Animationen (5 Karten + Header + CTA), Open-Graph-Meta-Tags, Nav-Logo auf `cvs-logo-icon.png`, Footer-Struktur auf 3-Spalten + Legal-Bottom |
| `crystals.html` | **Design-DNA-Alignment:** Lokale Fonts, `.cvs-aurora`, Film-Grain-Fix, `.btn-cvs--gold`/`.btn-cvs--ghost` (CTA-Banner), Lightbars, Scroll-Progress-Bar, Reveal-Animationen (Plans 3×, Packs 3×, Uses 6×, FAQ 6×, Header + CTA), Open-Graph-Meta-Tags, Nav-Logo auf `cvs-logo-icon.png`, Footer-Legal-Bottom bereinigt |

### Status
- ✅ `scene-editor-test.html` — Design-Alignment abgeschlossen
- ✅ `portfolio.html` — Design-Alignment abgeschlossen
- ✅ `shop.html` — Design-Alignment abgeschlossen
- ✅ `crystals.html` — Design-Alignment abgeschlossen (Score 100/100, approved)
- ⬜ `academy.html` — nächstes Target

### Regeln für diesen Workflow
1. `immobilienvideos.html` = Master Design Reference.
2. `academy.html` = aktives Target (nächste Seite).
3. Nach academy.html: page-by-page (prompt-generator.html → calendar.html → ...).
4. Commit nur nach visuellem Approval.
5. Kein Backend, kein Login/Dashboard, kein MCP während dieses Gates.

---

## SESSION HANDOVER — Save-Point (2026-06-06)

### Abgeschlossene Arbeit
- `scene-editor-test.html` — Design-Alignment an `immobilienvideos.html` abgeschlossen. Zusätzliche Fixes: Google-Fonts-CDN → lokale `assets/fonts/fonts.css`, Custom-Cursor explizit deaktiviert (`cursor:auto!important`).
- `portfolio.html` — Design-Alignment abgeschlossen: Aurora, Nav-Overrides, Premium-Buttons (`.btn-cvs--gold`/`.btn-cvs--ghost`), Footer-Master, Lightbars, Scroll-Progress-Bar, Reveal-Animationen mit Staggering, Open-Graph-Meta-Tags.
- `shop.html` — Design-Alignment abgeschlossen: Aurora, Nav-Overrides, Premium-Buttons, Footer-Master, Lightbars, Scroll-Progress-Bar, Reveal-Animationen mit Staggering, Open-Graph-Meta-Tags, Google-Fonts-CDN → lokale Fonts.
- `crystals.html` — Design-Alignment abgeschlossen: Aurora, Film-Grain-Fix (z-index:0/opacity:.28), Nav-Overrides, Premium-Buttons (CTA-Banner), Footer-Legal-Bottom bereinigt, Lightbars, Scroll-Progress-Bar, Reveal-Animationen mit Staggering, Open-Graph-Meta-Tags, Google-Fonts-CDN → lokale Fonts.
- Memory-Dateien aktualisiert: MEMORY.md, CLAUDE.md, TODO.md, workflow-design-reference.md.

### Laufende Arbeit
- Keine. Save-Point wurde erstellt.

### Nächste geplante Aktion
- `academy.html` Design-Alignment an `immobilienvideos.html` beginnen.

### Bekannte Issues / Abweichungen (academy.html Vorbereitung)
- `academy.html` nutzt noch Google-Fonts-CDN (nicht lokale Fonts).
- `academy.html` hat keinen `.cvs-aurora`-Hintergrund.
- `academy.html` hat keine `.lightbar`-Separatoren.
- `academy.html` hat keine `#cvs-progress`-Scroll-Leiste.
- `academy.html` hat keine `.reveal`-Scroll-Animationen.
- `academy.html` Buttons nutzen `.btn-gold`/`.btn-ghost` statt `.btn-cvs--gold`/`.btn-cvs--ghost`.
- `academy.html` Film-Grain ist `body::before` mit `z-index:2` und `opacity:.5` — Master-Reference nutzt `body::after` mit `z-index:0` und `opacity:.28`.
- `academy.html` Logo-Quelle ist `assets/cvs-logo.png` — Master-Reference nutzt `assets/cvs-logo-icon.png`.
- Keine Console-Errors erwartet (vanilla JS, bewährte Patterns).

### Archiv-Entscheidung
- `ki-videos.html` wird **nicht** als Standalone-Seite aligned. KI-Video-Content wird in `portfolio.html` (Showcase) und `shop.html` (Produkte/Templates) integriert.

### Git-Status (Uncommitted)
- 19 modified Files (inkl. scene-editor-test.html, portfolio.html, shop.html, Memory-Dateien, cvs-core.css).
- 0 staged commits.
4. Commit nur nach visuellem Approval.
5. Kein Backend, kein Login/Dashboard, kein MCP während dieses Gates.

---

## CVS Session Management Rules

1. Nach jeder abgeschlossenen Seite: `git status` + `git diff --stat` + Kurzzusammenfassung
2. Nach jedem Major Milestone: MEMORY.md + CLAUDE.md + TODO.md + workflow-design-reference.md aktualisieren
3. Immer pflegen: Master Reference, Active Target, Next Target, Projekt-Status
4. Session-Ende: SESSION HANDOVER (abgeschlossen, laufend, nächste Aktion, Issues)
5. Context-Monitoring nach jeder Antwort (low / medium / high / critical)
6. Vor neuem Chat: Projekt-State + Workflow-State + Task-State + Design-Reference-State speichern
7. Nie Projekt-Analyse von vorne starten, wenn Memory aktuell ist.

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

## Was wurde in Session 8 gebaut (2026-05-16)

> Dieser Block ist für jeden neuen Agenten. Lesen, dann loslegen.

### Session 8 — Geänderte Dateien

| Datei | Was geändert |
|---|---|
| `assets/css/app.css` | **Bug-Fix:** `[hidden] { display: none !important; }` — CSS `display:flex/grid` überschrieb HTML-`hidden`-Attribut → Error-Boxen auf merge-clips.php, video-studio.php etc. waren sichtbar beim Seitenload |
| `contact.php` | **Nav-Fix:** 9 relative Links → 5 absolute Links (IONOS-URLs für IONOS-Seiten, relative URLs für Render-Seiten). Login-Button hinzugefügt. |

### Session 8 — Überprüfte Seiten (Quality Audit)

| Seite | Status | Befund |
|---|---|---|
| `cinematic-vision-studio.de/` | ✅ | Redirect → scene-editor-test.html (IONOS index.html hochgeladen) |
| `scene-editor-test.html` | ✅ | Alle Nav-Links korrekt (absolute IONOS-URLs + Render-URLs) |
| `studio-demo.php` | ✅ | Login/Register-Flow, Free-Pill, Zurück-Link — alles korrekt |
| `login.php` | ✅ | Tabs (Login/Register), Passwort-Stärke, Remember-Me |
| `tiktok-studio.php` | ✅ | Sidebar-Nav, BYOK-Button (kein Fehler) |
| `video-studio.php` | ✅ | Sidebar-Nav, BYOK-Button (kein Fehler) |
| `merge-clips.php` | ✅ | Error-Box jetzt korrekt hidden (CSS-Fix!) |
| `elements.php` | ✅ | Sidebar-Nav, Loading-State korrekt |
| `contact.php` | ✅ | Nav gefixed (5 Links, absolute URLs) |
| `ready-videos.php` | ✅ | 12 Videos, 8 Kategorien, Sidebar korrekt |
| `trailer-builder.php` | ✅ | 200 OK, Funktional |
| `api/health.php` | ✅ | ok:true, FFmpeg 7.1.3, KI-Key set, Storage writable |
| `academy.html` (IONOS) | ✅ | 13 Guides, 11 Themen |
| `shop.html` (IONOS) | ✅ | Premium Assets-Seite korrekt |

### Render Env-Vars (verifiziert Session 8)
- ✅ `KIE_AI_API_KEY` — gesetzt + aktiv
- ✅ `CLEANUP_SECRET` — gesetzt + aktiv
- ℹ️ Render Cron-Service: **nicht deployed** (Dashboard zeigt Free-Plan; Cron erfordert Starter). Fallback: probabilistischer Cleanup 1/50 nach Render + HTTP-Endpoint aktiv.

### Was NICHT geändert werden soll (Don't Touch)
- `api/analyze.php`, `api/replace-slot.php` — Funktionieren korrekt
- `api/generate-ai.php`, `api/ai-status.php` — KI-Flow funktioniert
- `docker/apache.conf`, `Dockerfile` — Korrekt konfiguriert
- `data/ready-videos.json` — 12 Demo-Einträge, gut so
- `includes/auth.php`, `includes/db.php` — Auth-System stabil

### Nächste offene Aufgaben (nach Priorität)
1. **[Agent]** Email-Verifizierung bei Register (Mailgun oder SMTP, V0.5)
2. **[Agent]** Starter+ Plan: Stripe-Integration, 1080p-Freischaltung (V0.4)
3. **[User-Aktion optional]** Render Cron-Service: Starter-Plan aktivieren → `csf-cleanup-cron` Service automatisch deployed
4. **[Entscheidung]** Domain: cinematic-vision-studio.de als primäre Domain permanent behalten?

---

## Was wurde in Session 11 gebaut (2026-06-06)

> Design Alignment Gate — `immobilienvideos.html` ist der Master-Reference für alle IONOS-Seiten.

### Session 11 — Ziel
`scene-editor-test.html` an `immobilienvideos.html`-Designsprache angleichen:
- `.cvs-nav-simple` Navigation (Gradient-Fade, Gold-Hover, Blur)
- `.cvs-aurora` Hintergrund (CSS-Orbs statt Canvas)
- `.btn-cvs--gold` / `.btn-cvs--ghost` Premium-Buttons
- `.cvs-footer-master` Footer mit 3 Spalten
- `.lightbar` Section-Separatoren
- `#cvs-progress` Scroll-Progress-Bar
- Film-Grain auf Referenz-Level (`opacity:.28`, `z-index:0`)
- Custom-Cursor deaktiviert
- Alle bestehenden Funktionen (Video, Partikel, Portfolio-Filter, Stat-Counter, Mobile-Nav, Reveal-Animationen) erhalten

### Regeln für diesen Workflow
1. `immobilienvideos.html` = Master Design Reference.
2. `scene-editor-test.html` = aktives Target.
3. Nach dieser Seite: page-by-page (portfolio.html → shop.html → crystals.html → ...).
4. Commit nur nach visuellem Approval.
5. Kein Backend, kein Login/Dashboard, kein MCP während dieses Gates.
