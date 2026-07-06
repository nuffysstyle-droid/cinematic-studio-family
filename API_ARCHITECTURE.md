# API_ARCHITECTURE.md — Cinematic Vision Studio
# API-Architektur: Konventionen, Inventar, Zielbild /api/v1

| Feld | Wert |
|---|---|
| Version | 1.0 (Architecture Lock, 2026-07-05) |
| Stil | Pragmatisches JSON-über-HTTPS (kein REST-Purismus, kein GraphQL) — Flat-PHP-Endpunkte |
| Auth | Session-Cookie (httponly, SameSite=Lax) + CSRF-Token für state-changing POSTs (D-015) |

---

## 1. Verbindliche Konventionen (gelten ab sofort für JEDEN neuen Endpunkt)

1. **Antwort-Envelope:**
   Erfolg `{"status":"ok", …payload}` · Fehler `{"status":"error","message":"…","code":"snake_case_code"}`
   (`JSON_UNESCAPED_UNICODE`, `Content-Type: application/json; charset=utf-8`)
2. **HTTP-Codes:** 200 ok · 400 invalid input · 401 auth_required · 402 insufficient_crystals 🆕 ·
   403 plan_required/forbidden · 404 not_found · 405 method · 409 conflict/job_lock ·
   422 validation · 429 rate_limited · 5xx server. Fehlermeldungen deutsch, nutzerlesbar, ohne Interna.
3. **Jeder öffentliche POST:** `csf_rate_limit_check()` Pflicht (Limits §4) + Input-Validierung
   VOR jeder Seiteneffekt-Ausführung.
4. **Method-Guard** (405 bei falscher Methode), **kein** offenes CORS (Ausnahme dokumentationspflichtig).
5. **Idempotenz** bei kritischen Aktionen: Render nutzt Job-Lock ✔; Stripe-Webhook nutzt event_id ✔ (geplant).
6. Keine Stack-Traces/Pfade nach außen; Details ins Server-Log.

---

## 2. Ist-Inventar

### Kern-API (bleibt, wird v1) ✅
| Endpunkt | Methode | Zweck | Schutz heute |
|---|---|---|---|
| `api/upload.php` | POST | Video-Upload ≤50 MB | finfo-MIME, is_uploaded_file ✔ |
| `api/analyze.php` | POST | FFprobe → Slots, Thumbs | escapeshell* ✔ |
| `api/replace-slot.php` | POST | Slot ersetzen (Bild/Video/Text) | Pfad-Validierung ✔ |
| `api/render-final.php` | POST | FFmpeg-Concat, Audio, Export | Job-Lock, Plan-Gate ✔ |
| `api/generate-ai.php` | POST | Kie-Task starten | Login, 10/h ✔ · 💎-Buchung 🆕 D-009 |
| `api/ai-status.php` | GET | Task pollen, Ergebnis sichern | SSRF-Guard ✔ |
| `api/settings/quality.php` | POST | 720p/1080p Session | Plan-Gate ✔ |
| `api/health.php` | GET | Status ok/ffmpeg/storage/ai | öffentlich (ok) |
| `api/cleanup.php` | GET | manueller Cleanup | CLEANUP_SECRET ✔ |
| `api/contact-submit.php` | POST | Briefing-Mail | 🔴 UNGESCHÜTZT → Phase 1 |
| `api/auth/register|login|logout|me|change-password|forgot-password` | POST/GET | Konto | ARGON2ID, Brute-Force-Schutz ✔ · Verifizierung 🆕 D-010 |

### Legacy-API (BYOK — CODE-FREEZE, Sunset D-004) 🧊
`export.php, job-status.php, generate-tiktok/-trailer/-video/-image.php, projects.php,
elements.php, save-element.php, animation-request.php, sticker-request.php,
save-request.php, test-key.php, merge-clips.php, progress.php, get-job.php`
→ kein Ausbau, keine Verlinkung; Abschaltung mit Redirect/410 in Phase 3.

---

## 3. Ziel-Namensraum `/api/v1/` (Phase 1–3, mit Rewrites)

```
/api/v1/health                    GET
/api/v1/auth/register|login|logout|me|password  (wie heute, +CSRF)
/api/v1/account/verify-email      POST   token → email_verified=1 + Welcome-Bonus  🆕
/api/v1/studio/upload|analyze|slots|render      (heutige Kern-API, umbenannt via Rewrite)
/api/v1/ai/generate               POST   💎-Reservierung + Task            (heute generate-ai)
/api/v1/ai/status                 GET    commit/storno                     (heute ai-status)
/api/v1/billing/checkout          POST   Stripe-Session für Paket/Plan     🆕
/api/v1/billing/webhook           POST   Stripe-Events (signiert, idempotent) 🆕
/api/v1/billing/history           GET    Käufe + Ledger des Users          🆕
/api/v1/contact/briefing          POST   (heute contact-submit) + Rate-Limit/Honeypot
/api/v1/admin/*                   Phase 3, role=admin (ADMIN_SYSTEM.md §4)
```
Technik: Alt-Pfade bleiben als Rewrite-Aliase funktionsfähig (kein Client bricht);
neue Clients nutzen nur v1. Versionierung = Verzeichnis; v2 erst bei Bruch-Änderung.

---

## 4. Rate-Limit-Matrix (Soll)

| Aktion | Limit | Schlüssel |
|---|---|---|
| Login | 5 / 15 min | IP (DB) ✔ |
| Register | 10 / h | IP ✔ |
| KI-Generierung | 10 / h | IP ✔ (+ 💎 als ökonomische Bremse) |
| Render | 15 / h | IP ✔ |
| Briefing/Kontakt | **5 / h + Honeypot** | IP 🆕 |
| Verify-Mail erneut senden | 3 / h | User 🆕 |
| Checkout-Erzeugung | 10 / h | User 🆕 |

---

## 5. Job-Datenvertrag (Studio ↔ API)

`storage/jobs/<job_id>/meta.json` (Schreibzugriff NUR serverseitig, `LOCK_EX`):
Kernfelder `job_id, status(created|analyzed|rendering|done|failed), source{duration,width,height},
slots[{index,type(original|image|video|text),src,ai_provider?,ai_flag?}], quality(720|1080),
final_video?, created_at (ISO-UTC)`. Erweiterungen additiv, nie Feld-Umdeutung.

---

## 6. Dokumentationspflicht

Neuer Endpunkt = neue Zeile in §2/§3 + Limit in §4 VOR Implementierung
(Bibel führt, Code folgt — Blueprint §10).
