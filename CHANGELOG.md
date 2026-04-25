# CHANGELOG.md — Cinematic Studio Family

Alle bedeutenden Änderungen werden hier dokumentiert.  
Format basiert auf [Keep a Changelog](https://keepachangelog.com/de/1.0.0/).

---

## [Unreleased]

### Hinzugefügt
- Projekt-Memory-Dateien angelegt (PROJECT_STATUS.md, ARCHITECTURE.md, TODO.md, CHANGELOG.md, CLAUDE_INSTRUCTIONS.md, PROMPT_TEMPLATES.md)
- TODO #1 abgeschlossen

### Entschieden
- App-Typ: Web-App (React + Node.js)
- Plattformen: Windows + macOS
- Video-Verarbeitung: FFmpeg nativ, serverseitig
- Deployment: Cloud-gehostet (öffentliche URL, kein lokaler Install)
- Auth: Kein Login in v1, keine Registrierung
- API-Key: nur Session-temporär im Browser (sessionStorage), nie serverseitig
- ARCHITECTURE.md vollständig überarbeitet inkl. Sicherheits-Regeln für API-Keys
- TODO #2, #3, #4 abgeschlossen

---

## [0.0.1] — 2026-04-25

### Hinzugefügt
- Projektordner `cinematic-studio-family/` erstellt
- Initiale Planungs-Dokumentation

---

<!-- VORLAGE FÜR NEUE EINTRÄGE:

## [X.Y.Z] — YYYY-MM-DD

### Hinzugefügt
- Neue Features

### Geändert
- Änderungen an bestehenden Features

### Behoben
- Bug-Fixes

### Entfernt
- Entfernte Features

### Sicherheit
- Sicherheits-Fixes

-->
