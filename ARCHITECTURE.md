# ARCHITECTURE.md — Cinematic Studio Family

## Status: Architektur vollständig festgelegt (Stand 2026-04-26)

### Festgelegte Entscheidungen
| Entscheidung       | Wert                                               |
|--------------------|----------------------------------------------------|
| App-Typ            | **PHP Web-App** (kein Framework, einfaches MVP)    |
| Struktur           | **Flat PHP** — kein Monorepo, kein Build-Step      |
| Ziel-Plattformen   | **Windows + macOS** (Browser-basiert)              |
| Video-Verarbeitung | **FFmpeg nativ** via PHP `exec()`                  |
| Deployment         | **Render** (Cloud, öffentliche URL)                |
| Datei-Speicher     | **Server-Disk temporär** (nach Export gelöscht)    |
| Auth / Login       | **Kein Login in v1**                               |
| API-Key-Handling   | **Session-temporär** — nur `sessionStorage` Browser |

---

## Architektur-Übersicht

```
Browser (HTML + CSS + JS)
   ↕  HTTP / fetch()
PHP Seiten + API-Endpunkte auf Render
   ↕
FFmpeg (exec/shell_exec)    storage/    data/
```

Keine Build-Pipeline. Kein npm. PHP läuft direkt auf dem Server.

---

## Projektstruktur

```
cinematic-studio-family/
├── index.php                  # Startseite / Dashboard
├── editor.php                 # Video-Editor
├── export.php                 # Export-Seite
│
├── includes/                  # Wiederverwendbare PHP-Komponenten
│   ├── header.php
│   ├── footer.php
│   ├── nav.php
│   └── functions.php          # Hilfsfunktionen (FFmpeg, JSON, etc.)
│
├── api/                       # API-Endpunkte (JSON-Response)
│   ├── upload.php             # POST: Datei hochladen
│   ├── projects.php           # GET/POST/PUT/DELETE: Projekte
│   ├── export.php             # POST: Export starten
│   ├── progress.php           # GET: Export-Fortschritt (SSE)
│   └── thumbnail.php          # GET: Thumbnail liefern
│
├── assets/
│   ├── css/
│   │   └── app.css            # Haupt-Stylesheet
│   └── js/
│       ├── app.js             # Haupt-JS (fetch, UI-Logik)
│       ├── editor.js          # Timeline-Editor-Logik
│       └── upload.js          # Drag & Drop Upload
│
├── storage/                   # Temporäre Dateien (in .gitignore)
│   ├── uploads/               # Hochgeladene Rohdateien
│   ├── exports/               # Fertige Export-Videos
│   └── thumbnails/            # Generierte Thumbnails
│
├── data/                      # JSON-Projektdaten (in .gitignore)
│   └── projects/              # Eine .json Datei pro Projekt
│
└── [Konfigurationsdateien]
```

---

## Tech-Stack

| Schicht          | Technologie              | Begründung                            |
|------------------|--------------------------|---------------------------------------|
| Backend          | PHP 8.x                  | Kein Build-Step, direkt deploybar     |
| Frontend         | Vanilla JS + HTML + CSS  | Kein Framework-Overhead in v1         |
| Video-Processing | FFmpeg via PHP `exec()`  | Volle Codec-Unterstützung             |
| Datei-Upload     | PHP `$_FILES`            | Nativ, kein Paket nötig               |
| Projekt-Daten    | JSON-Dateien in `data/`  | Einfach, lesbar, kein DB-Setup        |
| Deployment       | Render (PHP runtime)     | Einfaches Cloud-Deployment            |
| Export-Progress  | Server-Sent Events (SSE) | PHP `ob_flush()` + `flush()`          |

---

## API-Endpunkte

```
POST   api/upload.php              → Datei hochladen → { fileId, fileName }
GET    api/projects.php            → Alle Projekte   → [ project, ... ]
POST   api/projects.php            → Neues Projekt   → { id, ... }
GET    api/projects.php?id=X       → Projekt laden   → { project }
PUT    api/projects.php?id=X       → Projekt speichern
DELETE api/projects.php?id=X       → Projekt löschen
POST   api/export.php              → Export starten  → { jobId }
GET    api/progress.php?job=X      → Fortschritt SSE → data: { percent }
GET    api/thumbnail.php?file=X    → Thumbnail-Bild
```

---

## Datenmodell (JSON)

```json
// data/projects/{id}.json
{
  "id": "abc123",
  "name": "Urlaub 2026",
  "createdAt": "2026-04-26T10:00:00Z",
  "updatedAt": "2026-04-26T10:00:00Z",
  "clips": [
    {
      "id": "clip1",
      "fileId": "upload_xyz",
      "fileName": "video.mp4",
      "startTime": 0,
      "endTime": 10,
      "timelinePosition": 0,
      "duration": 10
    }
  ],
  "audioTracks": [],
  "exportSettings": {
    "format": "mp4",
    "resolution": "1080p",
    "fps": 30
  }
}
```

---

## Sicherheits-Regeln

| Regel | Umsetzung |
|---|---|
| API-Key nie persistent speichern | Nur `sessionStorage` im Browser |
| API-Key nie ans Backend senden | Alle AI-Calls direkt vom Browser |
| Upload-Validierung | Nur erlaubte MIME-Types, max. Dateigröße prüfen |
| Kein `exec()` mit User-Input | FFmpeg-Parameter werden server-seitig gebaut, nie direkt aus Request |
| `storage/` nicht webzugänglich | Via `.htaccess` oder Render-Konfiguration absichern |

---

## Offene Fragen (v2)
- [ ] **Projekt-Persistenz:** JSON-Dateien reichen für v1 — SQLite/DB erst in v2
- [ ] **Datei-Speicher Upgrade:** Server-Disk → Cloudflare R2/S3 in v2
- [ ] **Auth:** Kein Login in v1 — User-Accounts erst in v2
