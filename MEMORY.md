# MEMORY.md — Cinematic Vision Studio
# Master-Index für alle AI-Agenten

> Pflichtlektüre VOR jeder Arbeit. Lese zuerst diese Datei, dann die verlinkten Dateien.
> Letzte Aktualisierung: 2026-05-23

---

## Readiness Checklist (vor jedem Code-Task)
- [ ] Diese Datei gelesen?
- [ ] Relevante `memory/`-Datei gelesen?
- [ ] Error Bank (`memory/error-bank.md`) geprüft?
- [ ] Task klar definiert?
- [ ] Wenn Info in Memory steht → NUTZEN, nicht fragen

---

## Master Code Standards

### Absolute Verbote
- KEINE API-Endpoint-Raterei — `memory/byok-system.md` lesen
- KEINE redundanten Fragen zu Dingen die dokumentiert sind
- KEINE Mock-Fallbacks (picsum.photos, example.com etc.)
- KEIN Code ohne vorherigen Plan
- KEINE Hypothesen ohne Verifikation
- KEIN `innerHTML` für User-Daten → `textContent` / DOM-API / `<template>`-Cloning
- KEIN npm, kein Composer, kein Build-Tool in V1
- KEIN externes JS-Framework (React, Vue, jQuery)
- KEINE neuen CSS-Hex-Werte → bestehende CSS-Variablen verwenden
- KEIN S3/R2 in V1 (ephemeral FS + Render Disk reicht)
- KEIN cURL (`file_get_contents` + `stream_context` reicht)

### Harte Do's (PHP-Code)
- ✅ `declare(strict_types=1);` in JEDER PHP-Datei
- ✅ `escapeshellarg()` auf ALLEN Shell-Argumenten
- ✅ `csf_validate_path()` + `realpath()` + `CSF_STORAGE_ROOT`-Prefix vor jedem File-Access
- ✅ `LOCK_EX` bei jedem `meta.json`-Write
- ✅ `htmlspecialchars((string)$var)` — IMMER `(string)`-Cast vor `htmlspecialchars`!
- ✅ Toast-Feedback + Error-Box bei jeder API-Aktion
- ✅ Mobile: 44px Touch-Targets, Stack-Layout unter 600px
- ✅ Playwright für alle Browser-Tests (kein Claude_in_Chrome)
- ✅ PowerShell für alle lokalen Projektbefehle
- ✅ SSRF-Schutz bei externen URLs (Kie.ai CDN whitelist)
- ✅ Rate Limiting bei jedem Auth- oder KI-Endpoint
- ✅ Job-Locks (`flock(LOCK_EX|LOCK_NB)`) vor langen Render-Operationen

### Fehler-Update Format (nach jedem Task)
```
FEHLER-UPDATE [DATUM]
- Neue Fehler: [Liste oder "keine"]
- Code-Qualität: Security ✅/⚠️ | Logic ✅/⚠️ | Performance ✅/⚠️
- Nächste Priorität: [Was als nächstes]
```

→ Bei neuen Fehlern: Eintrag in `memory/error-bank.md` als ERR-NNN

---

## Memory-Dateien Index

| Datei | Inhalt | Wann lesen |
|---|---|---|
| `memory/architecture.md` | Systemarchitektur, Verzeichnisstruktur, Datenmodell, Security | Bei Architektur-Fragen, neuen Endpunkten |
| `memory/deployment.md` | Render.com Setup, Docker, Env-Vars, Deploy-Prozess | Bei Deployment-Problemen, Env-Var-Fragen |
| `memory/ffmpeg.md` | FFmpeg-Integration, Render-Pipeline, Slot-Typen, Escaping | Bei Video-Processing-Tasks |
| `memory/byok-system.md` | Kie.ai API, Endpoints, PHP-Adapter, Rate Limits | Bei KI-Generierungs-Tasks |
| `memory/video-pipeline.md` | E2E Flow Upload → Analyse → Render → Download | Bei Pipeline-Änderungen |
| `memory/roadmap.md` | Milestones, offene TODOs, Council-Entscheidungen | Bei Feature-Planung |
| `memory/business.md` | Pricing, Zielgruppen, Wettbewerb, Metriken | Bei Business-Entscheidungen |
| `memory/current-problems.md` | Offene Bugs, Tech Debt, Limits | IMMER lesen |
| `memory/error-bank.md` | Fehlergedächtnis mit Root Causes + Fixes | IMMER vor dem Coden lesen |

---

## Projekt-Schnellreferenz

| Feld | Wert |
|---|---|
| **Produktname** | Cinematic Vision Studio |
| **Version** | 0.3.2 (Quality Audit, CSS-Fixes, Nav-Fixes) |
| **Live (Render)** | https://cinematic-studio-family.onrender.com |
| **Live (IONOS)** | https://cinematic-vision-studio.de/scene-editor-test.html |
| **GitHub** | nuffysstyle-droid/cinematic-studio-family |
| **Backend** | PHP 8.2 + Apache (mod_php), Docker |
| **Frontend** | Vanilla JS + HTML5 + CSS3 (kein Framework) |
| **Video-Engine** | FFmpeg 7.1.3 (serverseitig) |
| **AI-Provider** | Kie.ai (Flux Kontext Pro/Max) |
| **DB** | SQLite 3 via PDO (WAL-Modus) |
| **Storage** | JSON + `LOCK_EX` + SQLite, Render Persistent Disk 1 GB |
| **Hosting** | Render.com Starter ($7/mo) + IONOS Static |
| **Fonts** | Liberation Sans (`fonts-liberation`, Linux-native) |
| **Working OS** | Windows + PowerShell 5.1 + Git Bash |
| **Browser-Tests** | Playwright (NICHT Claude_in_Chrome) |

### Aktueller Status (Session 8, 2026-05-16)
- ✅ E2E-Test bestätigt: Upload → Analyse → KI-Bild → Render → Download
- ✅ Auth-System live: Register, Login, Logout, Profil, Passwort-Reset
- ✅ Dashboard + Studio Auth-Integration funktional
- ✅ `KIE_AI_API_KEY` + `CLEANUP_SECRET` in Render gesetzt
- ✅ Quality Audit aller HTML/PHP-Seiten abgeschlossen
- ⚠️ Render Cron-Service nicht deployed (Free-Plan; Fallback aktiv: 1/50 probabilistisch + HTTP-Endpoint)

---

## Nächste Prioritäten (Stand 2026-05-28 — Session 9, Design-Audit)

### Design & UX (Gate 2 — aktiv)
1. **P1:** Film Grain + DM Sans Font auf allen 7 Drop-In-Seiten (schnellster Premium-Win)
2. **P1:** Gold-Farbton angleichen (`#f5c542` → `#d4a93c`) auf allen Drop-In-Seiten
3. **P2:** Academy Cards + Modal mit Glass-Panel-Styling aufwerten
4. **P2:** Prompt Generator `gen-card` mit Glass-Panel + Glow aufwerten
5. **P2:** Shop + KI-Videos mit echtem Content statt nur "Coming Soon"
6. **P3:** Landing Page CTA zu Academy / Prompt Generator verstärken

### Tech (V0.4+)
7. **P2:** Email-Verifizierung bei Register (V0.5 — Mailgun oder SMTP)
8. **P2:** Starter+ Plan / Stripe-Integration (V0.4 — 1080p-Freischaltung)
9. **P3:** Render Cron-Service deployen (Starter-Plan aktivieren → `csf-cleanup-cron`)
10. **P3:** Polling-Backoff in `progress.js` (Exponentielles Backoff + max. Retry-Count)

→ Detaillierte Design-Gap-Analyse in `memory/design-audit.md`

---

## Agent-Regeln (kompakt)

1. **Diese Datei VOR jeder Arbeit lesen.** Dann `CLAUDE.md`, dann relevante `memory/`-Datei.
2. **Nie ohne Freigabe committen** — bei expliziter Erlaubnis direkt committen + pushen.
3. **Keine Frameworks einführen** — Flat PHP bleibt Flat PHP.
4. **Keine Dateien löschen** ohne explizite Anweisung.
5. **Nach jedem Feature: CLAUDE.md + MEMORY.md + error-bank.md aktualisieren.**
6. **`htmlspecialchars` immer mit `(string)`-Cast:** `htmlspecialchars((string)$var)`.
7. **Playwright für Browser-Tests** — kein Claude_in_Chrome, kein zweites Fenster.
8. **Council-Trigger:** Bei Entscheidungen mit mehreren validen Optionen → vorschlagen.
9. **Bei neuem Bug → sofort als ERR-NNN in `memory/error-bank.md` eintragen.**
