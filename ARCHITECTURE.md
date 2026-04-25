# ARCHITECTURE.md — Cinematic Studio Family

## Status: Architektur vollständig festgelegt (Stand 2026-04-25)

### Festgelegte Entscheidungen
| Entscheidung       | Wert                                          |
|--------------------|-----------------------------------------------|
| App-Typ            | **Web-App** (Browser-Frontend)                |
| Ziel-Plattformen   | **Windows + macOS**                           |
| Video-Verarbeitung | **FFmpeg nativ (serverseitig)**               |
| Deployment         | **Cloud-gehostet** (öffentliche URL, kein Install) |
| Auth / Login       | **Kein Login in v1** — kein Registrierungszwang |
| API-Key-Handling   | **Session-temporär** — nie serverseitig gespeichert |

---

## Architektur-Übersicht

```
Browser (React)
   ↕  REST + SSE
Node.js API Server auf Render  ←→  FFmpeg (child_process)
   ↕
Server-Disk temporär (Uploads werden nach Export gelöscht)
```

- **Frontend:** React SPA, im Browser, über öffentliche Render-URL erreichbar
- **Backend:** Node.js/Express auf Render, verarbeitet Uploads und FFmpeg-Jobs
- **Datei-Speicher:** Server-Disk temporär — Upload → Verarbeitung → Download → Löschen
- **API-Keys:** Nur in `sessionStorage` des Browsers, nie serverseitig gespeichert
- **Upgrade-Pfad:** Server-Disk → Cloudflare R2/S3 sobald Persistenz nötig wird

---

## Sicherheits-Regeln (API-Key)

| Regel | Umsetzung |
|---|---|
| Key nie persistent speichern | Nur `sessionStorage` im Browser — wird bei Tab-Schließen gelöscht |
| Key nie an Backend senden | Alle AI-Calls direkt vom Frontend mit dem User-Key |
| Key nie loggen | Kein Logging von Authorization-Headern im Backend |
| Key nie in URL | Kein API-Key als Query-Parameter |

---

## Projektstruktur (geplant)

```
cinematic-studio-family/
├── frontend/                  # React SPA
│   ├── src/
│   │   ├── components/        # UI-Komponenten
│   │   ├── pages/             # Haupt-Screens
│   │   ├── hooks/             # Custom React Hooks
│   │   ├── store/             # Zustand State Management
│   │   ├── api/               # API-Client (fetch/axios)
│   │   └── styles/            # Tailwind CSS
│   ├── public/
│   └── index.html
├── backend/                   # Node.js API Server
│   ├── src/
│   │   ├── index.ts           # Server-Einstiegspunkt
│   │   ├── routes/            # API-Routen
│   │   ├── services/
│   │   │   ├── ffmpeg.service.ts     # FFmpeg-Wrapping
│   │   │   ├── project.service.ts    # Projekt-CRUD
│   │   │   └── export.service.ts     # Export-Pipeline
│   │   ├── middleware/        # Auth, Error, Upload
│   │   └── types/             # Shared TypeScript Typen
│   └── uploads/               # Temporäre Upload-Dateien
├── shared/                    # Typen & Interfaces (Frontend + Backend)
│   └── types.ts
├── projects/                  # Gespeicherte Projekte (JSON)
└── [Konfigurationsdateien]
```

---

## Tech-Stack (festgelegt)

| Schicht          | Technologie                  | Begründung                          |
|------------------|------------------------------|-------------------------------------|
| Frontend         | React + TypeScript           | Etabliert, große Ökosystem          |
| Build-Tool       | Vite                         | Schnell, React-optimiert            |
| Styling          | Tailwind CSS                 | Rapid UI, kein CSS-Overhead         |
| State Management | Zustand                      | Leichtgewichtig, React-nativ        |
| API-Client       | Axios                        | Interceptors, einfaches Error-Handling |
| Backend          | Node.js + Express + TypeScript | FFmpeg child_process, Datei-IO     |
| Video-Processing | FFmpeg (nativ, serverseitig) | Volle Codec-Unterstützung, schnell  |
| Datei-Upload     | Multer                       | Multipart-Upload für Videos         |
| Projekt-Daten    | JSON-Dateien (lokal)         | Einfach, kein DB-Setup nötig        |
| Testing          | Vitest + Playwright          | Modern, schnell                     |

---

## Kern-Module (geplant)

### 1. Media Import
- Unterstützte Formate: MP4, MOV, MKV, JPG, PNG, HEIC
- Drag & Drop Upload via Browser → Backend speichert in `/uploads`
- Thumbnail-Generierung via FFmpeg (`ffmpeg -ss 00:00:01 -vframes 1`)

### 2. Timeline Editor
- Drag & Drop Clip-Anordnung (Frontend, React DnD)
- Trim, Cut, Split (Metadaten im Frontend, Render auf Server)
- Mehrspurige Audio/Video-Timeline

### 3. Template Engine
- Vorlagen: Jahresrückblick, Urlaub, Geburtstag, Baby-Film
- Animationen, Übergänge, Titelkarten
- Anpassbare Text-Overlays

### 4. Audio-Modul
- Hintergrundmusik-Bibliothek (royalty-free, lokal gebündelt)
- Lautstärke-Hüllkurven
- Voice-Over-Aufnahme (Web Audio API → Upload)

### 5. Export-Engine (FFmpeg serverseitig)
- Server empfängt Timeline-JSON → baut FFmpeg-Command → rendert Video
- Ausgabe: MP4 (H.264), WebM, GIF
- Auflösungen: 720p, 1080p, 4K
- Progress via Server-Sent Events (SSE)

### 6. Projekt-Verwaltung
- Projekte als JSON gespeichert in `/projects/`
- Autosave (alle 30s, PATCH /projects/:id)
- Projektvorschau (Thumbnail des ersten Clips)

---

## API-Routen (geplant)

```
POST   /api/upload              → Datei hochladen
GET    /api/projects            → Alle Projekte
POST   /api/projects            → Neues Projekt
GET    /api/projects/:id        → Projekt laden
PUT    /api/projects/:id        → Projekt speichern
DELETE /api/projects/:id        → Projekt löschen
POST   /api/export/:id          → Export starten
GET    /api/export/:id/progress → Export-Fortschritt (SSE)
GET    /api/thumbnail/:fileId   → Thumbnail abrufen
```

---

## Datenmodell

```typescript
// shared/types.ts

interface Project {
  id: string
  name: string
  createdAt: string
  updatedAt: string
  clips: Clip[]
  audioTracks: AudioTrack[]
  template?: string
  exportSettings: ExportSettings
}

interface Clip {
  id: string
  fileId: string        // Referenz auf Upload
  fileName: string
  startTime: number     // Sekunden im Quellmaterial
  endTime: number
  timelinePosition: number
  duration: number
  filters: Filter[]
}

interface AudioTrack {
  id: string
  fileId: string
  volume: number        // 0–1
  timelinePosition: number
}

interface ExportSettings {
  format: 'mp4' | 'webm' | 'gif'
  resolution: '720p' | '1080p' | '4k'
  fps: 24 | 30 | 60
}
```

---

## Offene Architektur-Fragen
- [x] **Deployment-Modell:** Cloud-gehostet ✅
- [x] **Auth:** Kein Login in v1 ✅
- [x] **API-Key-Handling:** Session-temporär, nie serverseitig ✅
- [x] **Cloud-Provider:** **Render** ✅ (Node.js + FFmpeg, einfaches Deployment)
- [x] **Datei-Speicher:** **Server-Disk temporär** ✅ (Dateien nach Export gelöscht)
- [ ] **Projekt-Persistenz:** JSON in Cloud-Disk oder DB (SQLite/Postgres)? ← v2

> **Upgrade-Pfad (v2+):** Wenn User-Accounts + dauerhafte Mediathek benötigt werden → Migration zu Cloudflare R2 / S3.
