# Cinematic Vision Studio — Projektübersicht

> Für Menschen geschrieben. Kein technisches Vorwissen nötig.
> Letzte Aktualisierung: 2026-05-14

---

## Was ist Cinematic Vision Studio?

Cinematic Vision Studio ist eine Web-App, mit der Familien professionelle Erinnerungsvideos
erstellen können — ohne Video-Bearbeitungs-Kenntnisse, direkt im Browser, in unter 10 Minuten.

**Das Versprechen:** Upload. Szenen gestalten. Render. Download.

---

## Für wen ist es gemacht?

- Eltern, die den Familienurlaub als cinematic Film aufbereiten wollen
- Menschen, die Geburtstagsfilme, Jahresrückblicke oder Erinnerungs-Videos erstellen
- Hobbyisten, die kurze cinematic Clips für Instagram oder YouTube Shorts produzieren
- Lokale Unternehmen (einfache Produkt-Videos ohne teures Equipment)

---

## Was kann es heute (Version 0.1.0)?

```
1. Video hochladen (bis 15 Sekunden, max. 50 MB)
2. Das Video wird automatisch in 2-3 Szenen aufgeteilt
3. Jede Szene kann gestaltet werden:
   → Text: Schwarze Titelkarte mit weißem Text ("Sommerurlaub 2026")
   → Bild: Eigenes Foto als Szene einsetzen
   → KI-Bild: KI generiert ein Bild nach deinem Text-Prompt (neu!)
   → Original: Die Szene bleibt unverändert
4. "Rendern" — das finale Video wird in 720p zusammengebaut
5. MP4-Download direkt im Browser
```

**Aktuell Live:** https://cinematic-studio-family.onrender.com/studio-demo.php

---

## Wie funktioniert es technisch?

Die App läuft komplett auf einem Server (Render.com). Das bedeutet:
- Kein Download nötig — alles im Browser
- Das Video-Bearbeiten passiert serverseitig mit FFmpeg (professionelles Video-Tool)
- KI-Bilder werden über Kie.ai generiert (Flux Kontext Modell)

**Für Entwickler:** PHP 8.2 + Apache auf Docker, FFmpeg 7.1.3, Vanilla JS, kein Framework.

---

## Was kommt als nächstes?

### Kurzfristig (nächste Wochen)
- **KI-Bilder vollständig aktivieren** — Nutzer können mit Text-Prompts Szenen generieren lassen
- **Audio-Erhalt** — Original-Ton bleibt im fertigen Video

### Mittelfristig (nächste Monate)
- **Längere Videos** — mehr als 15 Sekunden
- **Bessere Qualität** — 1080p statt 720p
- **Accounts + Projekte speichern** — eigene Videos dauerhaft verfügbar

### Langfristig (Vision)
- **Bezahl-Modell** — Kristalle für KI-Generierungen, monatliche Abos
- **KI-Video-Szenen** — komplette Szenen von KI generieren lassen
- **Mobile App** — als App auf dem Handy nutzen

---

## Was sind "Kristalle"?

Kristalle sind die geplante interne Währung. Jede KI-Bild-Generierung kostet eine bestimmte
Anzahl Kristalle. Kristalle können per Top-Up nachgekauft werden. So bleibt die Basis-App
günstig, und nur wer viele KI-Features nutzt, zahlt mehr.

**Heute:** Noch nicht aktiviert (Demo-Modus).

---

## Projekt-Status

| Was | Stand |
|---|---|
| **Version** | 0.1.0 (Free MVP) |
| **Live seit** | 13. Mai 2026 |
| **Zahlende Nutzer** | 0 (Pre-Revenue) |
| **Nächster Schritt** | KI-Bildgenerierung aktivieren + Audio |

---

## Wo liegt was?

```
CLAUDE.md              → KI-Kontext (für Claude Code / AI-Agents)
memory/                → Detaillierte technische Dokumentation
├── business.md        → Geschäftsmodell, Pricing, Zielgruppe
├── architecture.md    → Systemarchitektur, Dateien, APIs
├── deployment.md      → Render-Setup, Docker, Env-Vars
├── ffmpeg.md          → Video-Verarbeitung
├── byok-system.md     → KI-Integration (Kie.ai)
├── video-pipeline.md  → Kompletter Video-Flow
├── roadmap.md         → Was wann kommt
└── current-problems.md→ Bekannte Probleme
docs/
└── project-overview.md→ Diese Datei
PROJECT_STATUS.md      → Aktueller Stand (Snapshot)
CHANGELOG.md           → Was wann geändert wurde
TODO.md                → Aufgaben-Liste
agents/points.md       → Kurzkontext für AI-Agents (Legacy)
```

---

## Kontakt / Owner

- **GitHub:** https://github.com/nuffysstyle-droid/cinematic-studio-family
- **Email:** nuffysstyle@gmail.com
