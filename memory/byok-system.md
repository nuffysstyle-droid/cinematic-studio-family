# memory/byok-system.md — API-Key & KI-Provider System

> Letzte Aktualisierung: 2026-05-14

---

## Aktuelles System: Server-Key (nicht BYOK)

**Entscheidung V2:** API-Key wird NICHT vom User mitgebracht (kein BYOK).
Der Key liegt auf dem Server als Environment Variable.

| Parameter | Wert |
|---|---|
| **Key-Name** | `KIE_AI_API_KEY` |
| **Quelle** | `getenv('KIE_AI_API_KEY')` → `$_SERVER[...]` → `$_ENV[...]` |
| **Speicherung** | Render-Environment-Variable (nie in Code, nie in DB) |
| **Logging** | Niemals — kein `error_log()` mit Key-Wert |

### Warum Server-Key statt BYOK?
- Einfachere UX (User muss keinen eigenen API-Key besorgen)
- Zentrale Kontrolle über Kosten
- Kreditbasiertes Monetarisierungsmodell (Kristalle) möglich
- Sicherheitsrisiko auf User-Seite vermieden

---

## Kie.ai Integration

### Provider
- **Name:** Kie.ai
- **Website:** https://kie.ai
- **Docs:** https://docs.kie.ai
- **Billing:** Credit-basiert (10–50 Credits pro Bild-Generierung)

### Implementierter Endpoint: Flux Kontext

**Generation:**
```
POST https://api.kie.ai/api/v1/flux/kontext/generate
Authorization: Bearer <KEY>
Content-Type: application/json

{
  "prompt": "...",
  "model": "flux-kontext-pro",   // oder "flux-kontext-max"
  "aspectRatio": "16:9",
  "outputFormat": "jpeg",
  "safetyTolerance": 2
}

Response: { "code": 200, "data": { "taskId": "task_flux_abc123" } }
```

**Polling:**
```
GET https://api.kie.ai/api/v1/flux/kontext/record-info?taskId=<id>
Authorization: Bearer <KEY>

Response: {
  "data": {
    "taskId": "task_flux_abc123",
    "successFlag": 0,   // 0=GENERATING, 1=SUCCESS, 2=CREATE_FAILED, 3=GEN_FAILED
    "response": {
      "resultImageUrl": "https://cdn.kie.ai/..."   // gültig 14 Tage
    }
  }
}
```

### Weitere verfügbare Endpoints (noch nicht implementiert)

| Model | Endpoint | Pattern |
|---|---|---|
| Flux Kontext Pro/Max | `/api/v1/flux/kontext/generate` | A (model-spezifisch) |
| Seedream v4 text-to-image | `/api/v1/jobs/createTask` mit `model: "bytedance/seedream-v4-..."` | B (universal) |
| Flux 2 Pro | `/api/v1/jobs/createTask` | B (universal) |
| Kling Video | `/api/v1/jobs/createTask` | B (universal) |

Pattern B Universal: `POST /api/v1/jobs/createTask`, Polling: `GET /api/v1/jobs/recordInfo?taskId=`

---

## PHP-Adapter: api/generate-ai.php

### Flow
```
POST /api/generate-ai.php
  { job_id, slot_number, prompt, model? }
     ↓ Validierung (job_id Regex, slot 1-12, prompt 1-500 Zeichen)
     ↓ Limit-Check (max 1 AI/Slot, max 3 AI/Job)
     ↓ getenv('KIE_AI_API_KEY') auslesen
     ↓ POST → api.kie.ai → taskId
     ↓ meta.json LOCK_EX updaten (ai_task_id, ai_status: pending, ...)
  Response: { status: "pending", task_id, model, started_at }
```

### Gespeicherte Meta-Felder pro Slot

```json
{
  "ai_generated": true,
  "ai_provider": "kie.ai",
  "ai_model": "flux-kontext-pro",
  "ai_status": "pending | done | failed",
  "ai_task_id": "task_flux_abc123",
  "ai_prompt": "Cinematic sunset...",
  "ai_created_at": "2026-05-13T12:00:00+00:00",
  "ai_completed_at": "2026-05-13T12:01:30+00:00",
  "ai_flag": null
}
```

---

## PHP-Adapter: api/ai-status.php

### Flow
```
GET /api/ai-status.php?job_id=...&slot_number=...
     ↓ meta.json LOCK_SH lesen → taskId + ai_model
     ↓ ai_status "done"/"failed" → sofort zurückgeben (kein Kie.ai-Call)
     ↓ GET → api.kie.ai/record-info → successFlag
     ↓ Flag 0: { status: "generating" }
     ↓ Flag 1: Bild downloaden → MIME-Check → storage speichern
               meta.json LOCK_EX updaten (replacement_file, replaced: true, ai_status: done)
               { status: "done", replacement_file }
     ↓ Flag 2/3: meta.json ai_status: failed
               { status: "failed", kie_flag }
```

### Sicherheits-Checks im Download-Pfad
1. `resultImageUrl` muss `https://` beginnen (SSRF-Schutz)
2. `finfo(FILEINFO_MIME_TYPE)` auf heruntergeladenem Blob (CDN-Header nicht vertraut)
3. Erlaubte MIME: `image/jpeg`, `image/png`, `image/webp`
4. Zufälliger Dateiname: `slot_NN_ai_{rand}.jpg`

---

## Limits & Rate-Limiting

| Limit | Wert | Implementiert |
|---|---|---|
| Max AI-Gens pro Slot | 1 (pending/done → 409) | ✅ |
| Max AI-Gens pro Job | 3 | ✅ |
| Kie.ai Rate Limit | 429-Handling | ✅ |
| Video-Generierung | Verboten (zu teuer/langsam) | ✅ |
| max. Prompt-Länge | 500 Zeichen | ✅ |

---

## Legacy: BYOK (Session-basiert) — Phase 1 System

Das alte System (`api/test-key.php`, `$_SESSION['api_key']`) ist noch im Code
aber für die Kie.ai-Integration nicht mehr relevant. Es wurde für direkte
Browser→AI-Calls designed, die in V2 nicht mehr verwendet werden.

**Dateien:** `api/test-key.php`, `api-key.php`
**Status:** Legacy-Code, nicht entfernen (Breaking Change für andere Flows)

---

## Geplante Erweiterungen (V2+)

| Feature | Beschreibung |
|---|---|
| Kristalle-System | Credits pro AI-Generierung abziehen |
| Multi-Model-Selector | User wählt zwischen Flux Pro/Max/Seedream |
| Prompt-Templates | Vorlagen für häufige Szenen-Typen |
| Batch-Generierung | Alle leeren Slots mit einem Klick |
| Video-Generierung | Kling/Sora2 für kurze AI-Video-Clips |
