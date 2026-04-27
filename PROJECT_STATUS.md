# PROJECT_STATUS.md — Cinematic Studio Family

## Aktueller Status
**Phase:** Phase 2 — Kern-Features V1  
**Stand:** 2026-04-27  
**Version:** 0.0.1

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
| Backend-Setup        | ✅ Fertig       | Upload-API + Projects-CRUD fertig          |
| Frontend-Setup       | ✅ Fertig       | CSS + JS Fundament vollständig           |
| Kern-Features        | 🟡 In Arbeit    | Prompt Engine, API-Key, Image/Video Studio, Element Library, Smart Guidance fertig |
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
1. TODO #19 — dashboard.php: Projektübersicht + CRUD
2. TODO #20 — new-project.php: Projekt-Erstellformular
3. Phase 3: TikTok Studio + Trailer Builder

---

## Bekannte Risiken
- Video-Verarbeitung im Browser ist ressourcenintensiv → Plattformwahl kritisch
- Dateigröße von Rohmaterial kann sehr hoch sein → Speicherstrategie frühzeitig klären
