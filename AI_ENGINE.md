# AI_ENGINE.md — Cinematic Vision Studio
# KI-Architektur: Kie.ai-Integration, Lebenszyklus, Kosten, Ausbau

| Feld | Wert |
|---|---|
| Version | 1.0 (Architecture Lock, 2026-07-05) |
| Provider | Kie.ai — Flux Kontext (Bild). Async Task-API, JPEG-Output, ~14 Tage CDN-Haltbarkeit |
| Kern-Dateien (Ist) | `api/generate-ai.php`, `api/ai-status.php`, `includes/functions.php`, `includes/prompt-engine.php` (Legacy, wiederverwendbar) |
| Grundsatz | API-Key NUR serverseitig (`KIE_AI_API_KEY` Env). BYOK ist beendet (D-004). Jede Generierung kostet Kristalle (D-009). |

---

## 1. Architektur-Überblick

```
Studio (Browser)                        Server (Render)                          Kie.ai
────────────────                        ───────────────                          ──────
„Generieren" ──POST /api/generate-ai──▶ validate (Login, Slot, Prompt)
                                        Rate-Limit 10/h/IP  ✔ vorhanden
                                        Kristalle RESERVIEREN (D-009) 🆕
                                        ──── createTask (flux/kontext) ────────▶ taskId
◀── {task_id, job_id} ─────────────────
   poll alle ~3s
„Status?" ───GET /api/ai-status ──────▶ ──── record-info?taskId ──────────────▶ status
                                        bei SUCCESS: resultImageUrl
                                        SSRF-Guard: Host→DNS→private IP block ✔
                                        Download → storage/jobs/<id>/slots/
                                        meta.json update (LOCK_EX) ✔
                                        Kristalle FINAL buchen / bei Fehler STORNO 🆕
◀── {status, thumb, kie_flag} ─────────
```

**Warum asynchron + serverseitiger Download:** Kie-CDN-Links verfallen (~14 Tage) —
das Ergebnis wird deshalb sofort in den Job-Storage kopiert; der Client sieht nie den
Provider direkt (Key-Schutz, SSRF-Kontrolle, austauschbarer Provider).

---

## 2. Vertrag der Endpunkte (Ist, bleibt stabil)

**POST `api/generate-ai.php`** — Input: `job_id`, `slot_index`, `prompt` (validiert/limitiert),
optional Stil-Presets. Output: `{status:"ok", task_id}` oder Fehler-Envelope.
Schutz: Login erforderlich, Rate-Limit `generate_ai` 10/h, Prompt-Längenbegrenzung.

**GET `api/ai-status.php`** — Input: `job_id`, `slot_index`. Output-Zustände:
`pending | processing | success | failed` (+ `kie_flag` Provider-Detail).
Schutz: SSRF-Prüfung der `resultImageUrl` (Host auflösen, private/reservierte IPs → 502).

---

## 3. Fehler-Taxonomie & Verhalten

| Fehlerklasse | Verhalten heute | Ziel |
|---|---|---|
| Provider 401/403 (Key) | Fehler-Envelope | + Admin-Alarm (Key abgelaufen/Quota) |
| Task failed (Content/Modell) | `failed` + Meldung | + KEINE Kristall-Buchung (Storno), Prompt-Hinweis an User |
| Timeout/Netz | Poll läuft weiter / Fehler | Max-Poll-Dauer 5 Min → `failed` + Storno |
| SSRF-Verdacht | 502 blockiert | unverändert (gelockt) |

**Refund-Regel (D-009):** Buchungsmodell „reserve → commit/storno": Abbuchung wird bei
Task-Start als Ledger-Eintrag `ai_image` angelegt; endet der Task in `failed`, schreibt
der Status-Endpunkt automatisch die Gegenbuchung `ai_image_refund` (gleicher `job_id`-Bezug).
Doppelte Stornos verhindert der Ledger-Check auf existierende Refund-Buchung pro Task.

---

## 4. Kristall-Bepreisung der KI (Verknüpfung CRYSTAL_SYSTEM.md §4)

| Aktion | Modell | Kristalle (Startwert, O-4) |
|---|---|---|
| KI-Bild Standard | Flux Kontext Pro | **5 💎** |
| KI-Bild Max | Flux Kontext Max | **8 💎** |
| KI-Video (Zukunft, §8) | Kie Video-Endpunkte | je Sekunde, TBD nach Provider-Preis |

Kalkulationsprinzip: Kristallpreis ≥ 2,5× Provider-Einkauf (Marge deckt Fehlversuche,
Server, Payment-Gebühren). Preise stehen NICHT im Code verstreut, sondern in einer
zentralen Preis-Map (`includes/pricing.php`, Phase 2) — eine Quelle für UI + Buchung.

---

## 5. Prompt-Schicht

- **Prompt Master** (`/prompt-master`) bleibt client-seitiger Baukasten (kostenlos, SEO-Magnet).
- `includes/prompt-engine.php` (Legacy, 19 KB Regelwerk) wird als serverseitige
  Prompt-Veredelung in den Studio-Flow übernommen (Phase 3, O-1): User-Eingabe →
  Engine ergänzt Kamera/Licht/Stil-Vokabular → besseres Ergebnis pro Kristall.
- Prompt-Richtlinien für User (Academy-Guide): konkret statt abstrakt, Licht/Kamera
  benennen, keine Marken/Personen-Namen (Content-Policy des Providers).

---

## 6. Sicherheits- & Kosten-Leitplanken

1. Key nie im Client, nie im Repo — nur Render-Env (`KIE_AI_API_KEY`). ✔
2. Rate-Limit bleibt AUCH nach Kristall-Einführung (Schutz gegen Bug-Loops/Scripting). ✔
3. Tages-Budget-Schalter (Phase 3): globale Obergrenze KI-Calls/Tag; darüber → freundliche
   Wartemeldung + Admin-Alarm (Schutz vor Kostenexplosion).
4. Unverifizierte Konten: keine KI-Aktionen (D-010).
5. Provider-Abstraktion: alle Kie-Aufrufe laufen über zentrale Helfer
   (`csf_kie_*`) — ein Provider-Wechsel ändert genau eine Datei.

---

## 7. Beobachtbarkeit (Phase 3/4)

Pro Task loggen (DB-Tabelle `ai_tasks`, geplant): user_id, job_id, model, dauer_ms,
ergebnis (success/failed/refund), kristalle. Admin-Dashboard zeigt: Erfolgsquote,
Ø-Dauer, Kristall-Umsatz/Tag, Fehlerhäufung (ADMIN_SYSTEM.md §4).

---

## 8. Ausbaustufen

| Stufe | Feature | Voraussetzung |
|---|---|---|
| A | KI-Bild (Ist) + echte Buchung | Phase 2 |
| B | Prompt-Engine-Veredelung serverseitig | Phase 3 |
| C | KI-Video (Kie Video-API) als Kristall-Produkt | stabile Marge aus A, Disk/CDN-Plan (Blueprint §9 S4) |
| D | Stil-Vorlagen („Cinematic Looks") als Shop-Produkte | Shop-Entscheid O-3 |
