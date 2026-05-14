# memory/architecture.md — Systemarchitektur

> Letzte Aktualisierung: 2026-05-14

---

## Überblick

```
┌─────────────────────────────────────────────────────────────┐
│                    Browser (User)                           │
│  HTML + Vanilla JS + CSS  ←→  fetch() + FormData           │
└──────────────────────┬──────────────────────────────────────┘
                       │  HTTPS
┌──────────────────────▼──────────────────────────────────────┐
│              Render.com (Docker Container)                  │
│  PHP 8.2 + Apache (mod_php)                                 │
│                                                             │
│  api/          → JSON-Endpunkte                             │
│  includes/     → Shared Library (FFmpeg, Config)            │
│  storage/      → Jobs, Uploads, Exports (Persistent Disk)   │
│  data/         → JSON-Datenhaltung (Persistent Disk)        │
└──────────────────────┬──────────────────────────────────────┘
                       │
          ┌────────────┼────────────────────┐
          ▼            ▼                    ▼
    FFmpeg 7.1.3   Kie.ai API         Render Disk
    (serverseitig)  (HTTPS extern)    (1 GB persistent)
```

---

## Architektur-Prinzipien

| Prinzip | Umsetzung |
|---|---|
| **Flat PHP** | Kein MVC-Framework, kein Routing-Layer |
| **No Build Step** | Kein npm, kein Composer, kein Webpack |
| **Server-Side Rendering** | PHP rendert HTML-Pages direkt |
| **JSON Storage** | Flat Files mit `LOCK_EX`/`LOCK_SH` — kein DB |
| **Stateless API** | Jeder Request ist unabhängig |
| **Secure by Default** | `escapeshellarg()`, `realpath()`, MIME-Check, LOCK_EX |

---

## Verzeichnisstruktur

```
cinematic-studio-family/
│
├── ── PAGES (PHP-Rendered) ────────────────────────────────
│   ├── index.php                # Startseite
│   ├── studio-demo.php          # Haupt-Demo-UI (MVP Core)
│   ├── dashboard.php            # Projektübersicht
│   ├── new-project.php          # Projekt anlegen
│   ├── image-studio.php         # Bild-Prompt-Generator
│   ├── video-studio.php         # Video-Prompt-Generator
│   ├── tiktok-studio.php        # TikTok Hook+CTA Generator
│   ├── tiktok-animation.php     # Animation-Service
│   ├── tiktok-sticker.php       # Sticker-Service
│   ├── trailer-builder.php      # Trailer-Timeline
│   ├── ready-videos.php         # Premium-Video-Showroom
│   ├── academy.php              # Guides & Tutorials
│   ├── elements.php             # Element Library
│   ├── merge-clips.php          # Clip-Merge-UI
│   ├── settings.php             # App-Einstellungen (stub)
│   ├── api-key.php              # API-Key Session-Handling UI
│   ├── crystals.php             # Kristalle (Dummy)
│   ├── shop.php                 # Shop (Dummy)
│   └── ...
│
├── ── API ENDPOINTS ───────────────────────────────────────
│   api/
│   ├── analyze.php              # POST: Video → Slots + meta.json
│   ├── replace-slot.php         # POST: Slot-Ersatz in meta.json
│   ├── get-job.php              # GET: Job-Status + meta.json lesen
│   ├── render-final.php         # POST: FFmpeg Render → MP4
│   ├── generate-ai.php          # POST: Kie.ai Task starten → taskId
│   ├── ai-status.php            # GET: Kie.ai Task pollen → Bild DL
│   ├── upload.php               # POST: Datei-Upload (MIME-Check)
│   ├── export.php               # POST: Export-Job starten
│   ├── progress.php             # GET: Export-Fortschritt
│   ├── merge-clips.php          # POST: Clips zusammenführen
│   ├── health.php               # GET: Server + FFmpeg + AI Status
│   ├── test-key.php             # POST: API-Key in Session speichern
│   ├── projects.php             # GET/POST/PUT/DELETE: Projekte CRUD
│   ├── elements.php             # GET/POST: Element Library
│   ├── generate-tiktok.php      # POST: TikTok Prompt generieren
│   ├── generate-trailer.php     # POST: Trailer-Timeline generieren
│   ├── generate-image.php       # POST: Bild-Prompt (Placeholder)
│   ├── generate-video.php       # POST: Video-Prompt (Placeholder)
│   ├── job-status.php           # GET: Job-Status allgemein
│   ├── save-element.php         # POST: Element speichern
│   ├── animation-request.php    # POST: Animation-Anfrage (JSON)
│   └── sticker-request.php      # POST: Sticker-Anfrage (JSON)
│
├── ── INCLUDES (Shared Library) ───────────────────────────
│   includes/
│   ├── config.php               # APP_NAME, Pfade, MIME-Typen, Session
│   ├── functions.php            # FFmpeg-Service (checkFfmpegAvailable etc.)
│   ├── header.php               # HTML-Head + Nav
│   ├── footer.php               # HTML-Footer
│   ├── sidebar.php              # Seitennavigation
│   ├── prompt-engine.php        # Prompt-Vorlagen-System
│   └── guidance.php             # Smart Guidance Component
│
├── ── ASSETS ──────────────────────────────────────────────
│   assets/
│   ├── css/app.css              # Design System (Dark Cinematic)
│   └── js/
│       ├── app.js               # Globale UI-Logik
│       ├── editor.js            # Timeline-Editor
│       ├── upload.js            # Drag & Drop Upload
│       └── progress.js          # Export-Fortschritt-Polling
│
├── ── STORAGE (Persistent Disk via Symlink) ───────────────
│   storage/
│   ├── uploads/videos/          # Original-Videos
│   ├── uploads/images/          # Hochgeladene Replacement-Bilder
│   ├── jobs/{job_id}/
│   │   ├── meta.json            # Slot-Status + AI-Felder
│   │   └── replacements/        # slot_NN_{type}_{rand}.jpg/mp4
│   ├── exports/                 # Fertige MP4s
│   ├── thumbnails/{job_id}/     # Slot-Thumbnails
│   ├── temp/                    # Temp-Clips für Concat
│   └── elements/                # Element-Library-Dateien
│
├── ── DATA ────────────────────────────────────────────────
│   data/
│   ├── projects/projects.json   # Projekt-Liste
│   ├── elements.json            # Element Library
│   ├── ready-videos.json        # Premium Showroom (12 Einträge)
│   ├── animation-requests.json  # Anfragen-Queue
│   ├── sticker-requests.json    # Anfragen-Queue
│   └── export-jobs.json         # Export-Job-Status
│
└── ── DOCKER / DEPLOYMENT ─────────────────────────────────
    Dockerfile                   # php:8.2-apache + FFmpeg + Liberation
    render.yaml                  # Render-Service-Config + Disk
    docker/
    ├── apache.conf              # VirtualHost + PassEnv KIE_AI_API_KEY
    └── entrypoint.sh            # Port + Symlinks + Apache-Start
```

---

## Datenmodell: meta.json (Kern-Datenstruktur)

```json
{
  "job_id": "job_20260513_120000_abcd1234",
  "video_file": "/storage/uploads/videos/job_.../input.mp4",
  "duration_seconds": 8.0,
  "slot_count": 3,
  "created_at": "2026-05-13T12:00:00+00:00",
  "slots": [
    {
      "slot": 1,
      "start_seconds": 0,
      "end_seconds": 2.67,
      "duration_seconds": 2.67,
      "thumbnail": "/storage/thumbnails/job_.../slot_01.jpg",
      "replace_allowed": true,
      "text_allowed": true,
      "replaced": true,
      "replacement_file": null,
      "replacement_type": null,
      "text": "Familien-Urlaub 2026",
      "ai_generated": false,
      "ai_provider": null,
      "ai_model": null,
      "ai_status": null,
      "ai_task_id": null,
      "ai_prompt": null,
      "ai_created_at": null,
      "updated_at": "2026-05-13T12:05:00+00:00"
    }
  ],
  "final_video": "/storage/exports/job_..._final_3f9a.mp4",
  "rendered_at": "2026-05-13T12:10:00+00:00"
}
```

---

## Sicherheits-Architektur

| Bedrohung | Maßnahme |
|---|---|
| Shell-Injection | `escapeshellarg()` auf allen Shell-Args |
| Path-Traversal | `realpath()` + `CSF_STORAGE_ROOT`-Prefix-Check |
| MIME-Spoofing | `finfo(FILEINFO_MIME_TYPE)` auf Datei-Content |
| XSS | `textContent` statt `innerHTML`, `<template>`-Cloning |
| AI-Key-Leak | Nur `getenv()` + `$_SERVER`-Fallback, nie geloggt |
| Directory-Listing | `.htaccess` in `storage/`, `data/`, `includes/` |
| Race Conditions | `LOCK_EX` auf alle meta.json-Writes |
| SSRF | resultImageUrl muss `https://` beginnen |

---

## API-Endpunkt-Referenz (Schlüssel-Endpunkte)

| Endpunkt | Methode | Input | Output |
|---|---|---|---|
| `/api/analyze.php` | POST (multipart) | `video` (file) | `{job_id, slots[], ...}` |
| `/api/replace-slot.php` | POST | `job_id, slot, type, file/text` | `{status, slot}` |
| `/api/render-final.php` | POST | `job_id` | `{status, download_url, ...}` |
| `/api/generate-ai.php` | POST | `job_id, slot_number, prompt, model?` | `{status:pending, task_id}` |
| `/api/ai-status.php` | GET | `?job_id=&slot_number=` | `{status, replacement_file?}` |
| `/api/health.php` | GET | — | `{ok, php, ffmpeg, ai}` |
| `/api/upload.php` | POST (multipart) | `file` (image/video) | `{success, url, type}` |
