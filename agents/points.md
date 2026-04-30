# agents/points.md — Pflichtlektüre für alle Agenten

> **HINWEIS:** Diese Datei wird von **jedem** Agent (auch Sub-Agents) **vor Arbeitsbeginn** gelesen.
> Wenn du diese Datei änderst, halte sie kurz, scannbar, faktisch.

---

## Projekt-Identität

**Name:** Cinematic Studio Family
**Zielgruppe:** Familien, die Urlaubsvideos / Geburtstagsfilme / Jahresrückblicke in Kinoqualität produzieren — ohne professionelle Vorkenntnisse.
**Kern-Wert:** Vom Foto/Clip zum fertigen Familien-Video in unter 10 Minuten.

---

## Tech-Stack (verbindlich)

| Schicht | Wahl | Begründung |
|---------|------|-----------|
| Backend (API) | **PHP 8.2** + Apache (Docker auf Render.com) | FFmpeg via apt-get, Persistent Disk |
| Frontend (HTML) | **IONOS Webspace** + Vanilla JS | Eigene Domain, persistent, kein Cold-Start |
| CORS | **Apache-Layer** (Source of Truth) | KEIN PHP-Header — sonst doppelte Origin-Header |
| Architektur | **Flat** (kein MVC-Framework) | Verständlichkeit > Skalierung in V1 |
| Video-Engine | **FFmpeg + ffprobe** nativ, serverseitig | Volle Kontrolle, kein WASM-Overhead |
| Storage | **JSON-Files** mit `LOCK_EX` / `LOCK_SH` | Kein DB-Setup nötig in V1 |
| API-Key | Nur in `$_SESSION` | Nie persistiert, nie geloggt |

### Aktiver Frontend/Backend-Split (Phase 2)
- **Frontend:** `scene-editor-test.html` (lokal im Repo-Root, deployed auf IONOS)
- **Backend:** `https://cinematic-studio-family.onrender.com/api/*`
- **Storage:** Render Persistent Disk → `storage/uploads/videos/`, `storage/thumbnails/`, `storage/jobs/`

---

## Don'ts (harte Regeln)

- ❌ **Kein `innerHTML`** für User-Daten — ausschließlich `textContent` + `<template>`-Cloning + DOM-API.
- ❌ **Kein externes Framework** (React, Vue, Tailwind, jQuery — alles draußen).
- ❌ **Kein npm / Composer** in V1.
- ❌ **Kein S3 / R2** in V1 — ephemeral filesystem auf Render ist akzeptiert.
- ❌ **Kein Auth / Login** in V1 — Single-User-App.
- ❌ **Keine Trend-Architekturen** ohne klaren Bedarf (kein Microservice, kein GraphQL, kein SSR).
- ❌ **Kein neuer Hex-Wert** in CSS — bestehende CSS-Variablen (`--bg-*`, `--text-*`, `--accent-blue`) wiederverwenden.

---

## Do's (Qualitäts-Standards)

- ✅ `declare(strict_types=1);` in jeder PHP-Datei.
- ✅ `escapeshellarg()` auf **allen** Shell-Argumenten — keine Ausnahme.
- ✅ `csf_validate_path()` mit `realpath()` + `CSF_STORAGE_ROOT`-Prefix-Check vor jedem File-Access.
- ✅ Mobile: 44px Touch-Targets, Stack-Layout unter 600px.
- ✅ Dark Cinematic Design durchgängig.
- ✅ Toast-Feedback + Error-Box bei jeder API-Aktion.

---

## Working Environment

- **Shell:** Windows PowerShell 5.1 (powershell.exe)
- **OS:** Windows
- **Pfade:** Backslash-Style, Spaces in `"..."`
- **Chaining:** Nur `; if ($?) { ... }` — kein `&&`/`||`
- **Encoding:** UTF-8 mit BOM bei `Out-File` / `Set-Content` → immer `-Encoding utf8`

---

## Aktueller Stand (auto-updaten nach jedem TODO)

| Feld | Wert |
|------|------|
| **Aktive Phase** | **Scene Replacement Editor (Phase 2)** — User-Numbering |
| **Render Backend** | Live · `/api/health.php` ✅ · `/api/analyze.php` ✅ · FFmpeg+ffprobe laufen |
| **IONOS Frontend** | `scene-editor-test.html` deployed, ruft Render-API auf |
| **Phase 2 Backend** | `replace-slot.php` ✅ · `get-job.php` ✅ · `meta.json` Schema definiert |
| **Phase 2 Frontend** | Slot-Speichern + DOM-API-Refactor ✅ (lokal — IONOS-Push offen) |
| **Storage-Struktur** | `storage/jobs/{job_id}/meta.json` + `storage/jobs/{job_id}/replacements/` |
| **V1 Multi-Page (Phase 0–5)** | Pausiert — Phase 4 ✅, #38 Setup ✅ (Live-Klick beim User) |

---

## Bekannte technische Schulden (vor V1-Launch beheben oder bewusst akzeptieren)

| Schuld | Datei | Priorität |
|--------|-------|-----------|
| Logo-Upload nicht mit `api/upload.php` verbunden | `tiktok-animation.php`, `tiktok-sticker.php` | P2 |
| Anfrage-Modal sendet nicht wirklich (nur Toast) | `ready-videos.php` | P2 |
| "Bearbeiten"-Button disabled (API 501) | `elements.php` + `api/elements.php` | P2 |
| `API_PROVIDER_LINK` ist Platzhalter | `includes/config.php` | P3 |
| Polling-Mechanik existiert, läuft aber synchron | `assets/js/progress.js` + `api/merge-clips.php` / `api/export.php` | P3 |
| Share-Funktion (Download / WhatsApp / YouTube) entfallen — TODO #34 wurde umgewidmet | – | P2 falls erneut gewünscht |

---

## Phase-Übersicht

| Phase | Status | Inhalt |
|-------|--------|--------|
| Phase 0 — Setup | ✅ | Memory-Files, Tech-Stack, Plattform |
| Phase 1 — Fundament | ✅ | 14 PHP-Seiten, includes/, api/, assets/ |
| Phase 2 — Kern | ✅ | Prompt Engine, Studios, Elements, Guidance, Dashboard |
| Phase 3 — TikTok+ | 🧊 | TikTok Studio, Animation, Sticker, Trailer, Showroom, Academy |
| Phase 4 — Export | ✅ | FFmpeg-Service, Clip-Merge, Export-API, Polling, Progress-UI, Error-Box |
| Phase 5 — Release | 🟡 | Security ✅, Render-Deploy 🟡, Settings, Tests |
| Scene Editor (User „Phase 2") | 🟡 | Render+IONOS live, analyze ✅, **replace-slot ✅**, **get-job ✅**, meta.json ✅, UI ✅ (Push-IONOS pending) |

---

## Council-Format (wenn aktiviert)

5 parallele Advisor-Perspektiven:
1. **Contrarian** — Wo bricht das? Welche Bugs?
2. **Architect** — Was ist das echte Problem? First Principles.
3. **Expansionist** — 10x-Opportunity statt 10%-Verbesserung?
4. **Outsider** — Ist die UX intuitiv? Aus Nutzer-Sicht.
5. **Executor** — Können wir das nächste Woche deployen?

Jeder Advisor: 150–200 Wörter, direkt, kein Hedging.
Danach Chairman-Synthesis: Agree / Clash / Blind Spots / Recommendation / The One Thing.

---

## UltraPlan-Modus (Standard AKTIV)

Vor jeder nicht-trivialen Aktion (>3 Tool-Calls oder Code in >2 Dateien):

1. **Verstehen** — Echtes Problem in 1 Satz.
2. **Annahmen** — 3 Annahmen, welche unbewiesen?
3. **Plan** — 5–10 Schritte mit Tool + Erwartung.
4. **Risiko** — Was kann brechen? Welcher Schritt destruktiv?
5. **Abbruch** — Wann zurückfragen?

User reagiert mit "go" / "warte" / "ändere X" — KEINE Ausführung ohne OK.

Skip nur bei: Read-Aktionen, Status-Checks, Edits unter 20 Zeilen.

---

## Doku-Pflicht nach jedem TODO

Auto-update + commit:
- `PROJECT_STATUS.md`
- `CHANGELOG.md`
- `TODO.md`
- `agents/points.md` (falls sich Tech-Stack / Constraints geändert haben)

Commit-Format:
```
feat: #<nr> <kurzbeschreibung>

Co-Authored-By: Claude Sonnet 4.6 <noreply@anthropic.com>
```

---

## Review-Gate nach jedem TODO

Nach Commit immer:
> "TODO #X erledigt. Bereit für TODO #Y?"

Auf User-OK warten, NICHT autonom weitermachen.
