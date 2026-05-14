# memory/video-pipeline.md — Video-Pipeline End-to-End

> Letzte Aktualisierung: 2026-05-14

---

## Kompletter Flow (V0.1.0 MVP)

```
┌─────────────────────────────────────────────────────────────────┐
│                    STUDIO DEMO (studio-demo.php)                │
└─────────────────────────────────────────────────────────────────┘
           │
           ▼
┌──────────────────────────────────┐
│  1. UPLOAD (api/upload.php)      │
│  POST, multipart, field: "file"  │
│  MIME-Check: video/mp4, etc.     │
│  Output: { url, filename, type } │
└────────────────┬─────────────────┘
                 │
                 ▼
┌──────────────────────────────────┐
│  2. ANALYSE (api/analyze.php)    │
│  POST, multipart, field: "video" │
│  FFmpeg ffprobe → Dauer, FPS     │
│  Slot-Berechnung:                │
│    ≤6s  → 2 Slots                │
│    >6s  → 3 Slots (max 15s)      │
│  Thumbnails generiert            │
│  meta.json angelegt (LOCK_EX)    │
│  Output: { job_id, slots[], ... }│
└────────────────┬─────────────────┘
                 │
                 ▼
┌──────────────────────────────────────────────────────────────┐
│  3. SLOT-KONFIGURATION (pro Slot, beliebig oft wiederholbar) │
│                                                              │
│  Option A: Text-Titelkarte                                   │
│    api/replace-slot.php POST { slot, type:"text", text }     │
│    → meta.json: replaced=true, text="...", replacement_file=null│
│                                                              │
│  Option B: Bild-Upload                                       │
│    api/upload.php POST { file: image }                       │
│    api/replace-slot.php POST { slot, type:"image", file }    │
│    → meta.json: replaced=true, replacement_type=image, ...  │
│                                                              │
│  Option C: KI-Generierung (V2)                               │
│    api/generate-ai.php POST { job_id, slot, prompt, model }  │
│    → Kie.ai Task gestartet, taskId in meta.json              │
│    api/ai-status.php GET { job_id, slot }                    │
│    → Polling bis successFlag=1                               │
│    → Bild DL → storage/jobs/{id}/replacements/slot_NN_ai.jpg │
│    → meta.json: replaced=true, replacement_type=image, ...  │
│                                                              │
│  Option D: Slot unverändert lassen                           │
│    → kein API-Call nötig                                     │
│    → render-final.php schneidet Original                     │
└────────────────┬─────────────────────────────────────────────┘
                 │
                 ▼
┌──────────────────────────────────┐
│  4. RENDER (api/render-final.php)│
│  POST { job_id }                 │
│                                  │
│  Liest meta.json → Slots         │
│  Pro Slot FFmpeg-Kommando:       │
│    A: lavfi black + drawtext     │
│    B: -loop 1 -i <img>           │
│    C: -i <video>                 │
│    D: -ss <start> -i <original>  │
│  Alle auf 1280×720 skaliert      │
│  Alle mit -an (kein Ton)         │
│  Concat → final.mp4              │
│  meta.json: final_video, rendered│
│  Output: { download_url, ... }   │
└────────────────┬─────────────────┘
                 │
                 ▼
┌──────────────────────────────────┐
│  5. DOWNLOAD                     │
│  GET /storage/exports/<file>.mp4 │
│  Direkter Datei-Zugriff          │
└──────────────────────────────────┘
```

---

## Slot-Datenmodell (meta.json pro Slot)

```json
{
  "slot": 1,
  "start_seconds": 0.0,
  "end_seconds": 2.67,
  "duration_seconds": 2.67,
  "thumbnail": "/storage/thumbnails/job_.../slot_01.jpg",
  "replace_allowed": true,
  "text_allowed": true,
  "replaced": false,
  "replacement_file": null,
  "replacement_type": null,
  "text": null,
  "ai_generated": false,
  "ai_provider": null,
  "ai_model": null,
  "ai_status": null,
  "ai_task_id": null,
  "ai_prompt": null,
  "ai_created_at": null,
  "updated_at": null
}
```

**replacement_type Werte:** `null` | `"image"` | `"video"`
**ai_status Werte:** `null` | `"pending"` | `"done"` | `"failed"`

---

## Free-Plan-Einschränkungen (V0.1.0)

| Limit | Wert | Implementiert in |
|---|---|---|
| Max. Videolänge | 15 Sekunden | `api/analyze.php` |
| Max. Slots | 3 | `api/analyze.php` |
| Ausgabe-Auflösung | 720p (1280×720) | `RENDER_OUT_W/H` in `render-final.php` |
| Ausgabe-FPS | 30 | `RENDER_OUT_FPS` |
| Ton | Kein (−an) | alle FFmpeg-Kommandos |
| FFmpeg-Preset | `ultrafast` | `RENDER_PRESET` |
| CRF | 20 | `RENDER_CRF` |
| Storage | Persistent Disk (1 GB) | `render.yaml` + `entrypoint.sh` |

---

## V2 Pipeline-Erweiterungen (geplant)

| Feature | Beschreibung | Blockiert durch |
|---|---|---|
| **Audio-Preservation** | Audio-Track aus Original erhalten | Concat-Homogenität mit Replacements |
| **1080p Render** | Vollauflösung für Starter+ | RAM (512 MB Free-Plan) |
| **Längere Videos** | >15s, mehr Slots | CPU-Zeit + Storage |
| **KI-Video-Slots** | Kie.ai Video-Generierung als Slot-Typ | Latenz (60-300s async) |
| **Multi-Video-Merge** | Mehrere Input-Videos zusammenführen | Architecture |
| **Transition-Effekte** | Blende, Cut-Typen zwischen Slots | FFmpeg complexity |

---

## Render-Zeiten (empirisch, Free-Plan)

| Operation | Typische Dauer |
|---|---|
| Upload 5 MB Video | ~2s |
| Analyse (8s Video, 3 Slots) | ~3-5s |
| Thumbnail-Generierung (3 Slots) | ~2s |
| Slot-Render: Text-Titelkarte (2.67s) | ~1-2s |
| Slot-Render: Bild-Replacement (2.67s) | ~1-2s |
| Slot-Render: Original-Schnitt (2.67s) | ~2-3s |
| Concat (3 Slots) | ~1s |
| **Gesamt-Render (8s Video, 3 Slots)** | **~10-15s** |
| Kie.ai Flux Kontext Pro (Bild) | ~5-15s (extern) |
