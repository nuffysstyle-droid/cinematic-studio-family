# PROJECT_STATUS.md — Cinematic Studio Family

## Aktueller Status
**Phase:** Pre-Development / Planung  
**Stand:** 2026-04-25  
**Version:** 0.0.1 (Projektstart)

---

## Projektbeschreibung
Cinematic Studio Family ist eine Anwendung zur professionellen Erstellung und Verwaltung von Familienvideos, Foto-Diashows und cinematischen Erinnerungen. Zielgruppe sind Familien, die Urlaubsvideos, Geburtstagsfilme oder Jahresrückblicke in Kinoqualität produzieren möchten — ohne professionelle Vorkenntnisse.

---

## Status-Übersicht

| Bereich              | Status         | Notizen                        |
|----------------------|----------------|-------------------------------|
| Projektplanung       | ✅ Fertig       | Memory-Dateien angelegt       |
| Architektur          | ✅ Fertig       | Web-App + Node + FFmpeg nativ  |
| UI/UX Design         | 🔲 Ausstehend   | Wireframes noch nicht erstellt |
| Backend-Setup        | 🟡 In Arbeit    | PHP + FFmpeg, Struktur angelegt |
| Frontend-Setup       | 🟡 In Arbeit    | Vanilla JS + CSS, Struktur angelegt |
| Kern-Features        | 🔲 Ausstehend   | Siehe TODO.md                  |
| Testing              | 🔲 Ausstehend   | —                              |
| Deployment           | 🔲 Ausstehend   | —                              |

---

## Offene Entscheidungen (benötigen User-Input)

- [x] **App-Typ:** Web-App (React Frontend + Node.js Backend)
- [x] **Tech-Stack:** React + Vite + TypeScript / Node.js + Express
- [x] **Video-Verarbeitung:** FFmpeg nativ, serverseitig
- [x] **Ziel-Plattformen:** Windows + macOS
- [x] **Deployment-Modell:** Cloud-gehostet, öffentliche URL
- [x] **Auth:** Kein Login in v1, keine Registrierung
- [x] **API-Key:** Session-temporär im Browser, nie serverseitig gespeichert
- [x] **Cloud-Provider:** Render ✅
- [x] **Datei-Speicher:** Server-Disk temporär (v1), Upgrade-Pfad zu R2/S3 geplant

---

## Nächste Schritte
1. Offene Entscheidungen mit User klären
2. ARCHITECTURE.md befüllen
3. TODO.md priorisieren
4. Ersten Prototyp skizzieren

---

## Bekannte Risiken
- Video-Verarbeitung im Browser ist ressourcenintensiv → Plattformwahl kritisch
- Dateigröße von Rohmaterial kann sehr hoch sein → Speicherstrategie frühzeitig klären
