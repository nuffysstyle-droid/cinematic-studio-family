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
| `memory/workflow-design-reference.md` | Design-Alignment-Workflow (immobilienvideos.html = Master) | Bei Design-/CSS-Tasks |

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

### Aktueller Status (Session 11 / Save-Point, 2026-06-06)
- ✅ Design Alignment Gate geöffnet — `immobilienvideos.html` = Master Reference
- ✅ `scene-editor-test.html` Design-Alignment abgeschlossen (inkl. Font-Fix: Google-CDN → lokal, Cursor-Disable)
- ✅ `portfolio.html` Design-Alignment abgeschlossen (Aurora, Nav, Buttons, Footer, Lightbars, Scroll-Progress, Reveal-Animationen)
- ✅ `shop.html` Design-Alignment abgeschlossen (Aurora, Nav, Buttons, Footer, Lightbars, Scroll-Progress, Reveal-Animationen)
- ✅ `crystals.html` Design-Alignment abgeschlossen (Score 100/100, approved)
- ⬜ `academy.html` — nächstes Target
- ✅ E2E-Test bestätigt: Upload → Analyse → KI-Bild → Render → Download
- ✅ Auth-System live: Register, Login, Logout, Profil, Passwort-Reset
- ✅ `KIE_AI_API_KEY` + `CLEANUP_SECRET` in Render gesetzt
- ✅ Auth-System live: Register, Login, Logout, Profil, Passwort-Reset
- ✅ Dashboard + Studio Auth-Integration funktional
- ✅ `KIE_AI_API_KEY` + `CLEANUP_SECRET` in Render gesetzt
- ⚠️ Render Cron-Service nicht deployed (Free-Plan; Fallback aktiv: 1/50 probabilistisch + HTTP-Endpoint)

---

## Nächste Prioritäten (Stand 2026-06-06 — Session 11, Design Alignment Gate)

### Design & UX (Gate 2 — aktiv)
1. ✅ `scene-editor-test.html` → Design-Alignment abgeschlossen
2. ✅ `portfolio.html` → Design-Alignment abgeschlossen
3. ✅ `shop.html` → Design-Alignment abgeschlossen
4. ✅ `crystals.html` → Design-Alignment abgeschlossen (Score 98/100)
5. **P1:** `academy.html` → immobilienvideos.html Design-DNA (nächstes Target)
6. **P2:** `prompt-generator.html` → immobilienvideos.html Design-DNA
7. **P2:** `calendar.html` → immobilienvideos.html Design-DNA
8. **P3:** Legal pages (impressum, datenschutz, agb, cookies, widerruf)
9. **Archiv:** `ki-videos.html` — wird nicht als Standalone aligned (Content geht in Portfolio + Shop)

### Tech (V0.4+)
10. **P2:** Email-Verifizierung bei Register (V0.5 — Mailgun oder SMTP)
11. **P2:** Starter+ Plan / Stripe-Integration (V0.4 — 1080p-Freischaltung)
12. **P3:** Render Cron-Service deployen (Starter-Plan aktivieren → `csf-cleanup-cron`)
13. **P3:** Polling-Backoff in `progress.js` (Exponentielles Backoff + max. Retry-Count)

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

---

## CVS Session Management Rules

1. **Nach jeder abgeschlossenen Seite:**
   - `git status`
   - `git diff --stat`
   - Kurze Zusammenfassung der Änderungen

2. **Nach jedem Major Milestone:**
   - `MEMORY.md` aktualisieren
   - `CLAUDE.md` aktualisieren
   - `TODO.md` aktualisieren
   - `memory/workflow-design-reference.md` aktualisieren

3. **Immer pflegen:**
   - Aktuelle Master Design Reference
   - Aktive Target-Page
   - Nächste Target-Page
   - Aktueller Projekt-Status

4. **Am Ende jeder Session:** SESSION HANDOVER mit:
   - Abgeschlossene Arbeit
   - Laufende Arbeit
   - Nächste Aktion
   - Bekannte Issues

5. **Kontext-Monitoring:**
   - Geschätzte Context-Nutzung nach jeder Antwort melden (low / medium / high / critical)
   - Warnung bei high / critical
   - Bei >70%: Compact Handover erstellen, alle Memory-Dateien aktualisieren, neuen Chat empfehlen

6. **Vor neuem Chat:**
   - Projekt-State speichern
   - Workflow-State speichern
   - Active-Task-State speichern
   - Design-Reference-State speichern

7. **Nie Projekt-Analyse von vorne starten**, wenn Memory-Dateien bereits den aktuellen State enthalten.
