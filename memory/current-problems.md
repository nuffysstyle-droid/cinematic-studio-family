# memory/current-problems.md — Aktuelle Probleme & Tech Debt

> Letzte Aktualisierung: 2026-05-14 (Session 5)
> Priorität: P0 = Kritisch blockierend · P1 = Feature-blockierend · P2 = Wichtig · P3 = Nice-to-fix

---

## 🔴 P0 — Kritische Blocker

### KIE_AI_API_KEY nicht in Render eingetragen
- **Symptom:** `health.php` zeigt `kie_key_set: false` / `generate-ai.php` liefert 503
- **Ursache:** Nutzer muss Key manuell im Render-Dashboard eintragen
- **Deployed Fix:** `PassEnv KIE_AI_API_KEY` in `docker/apache.conf`, `a2enmod env` in `Dockerfile`
- **Verbleibender Blocker:** **User-Aktion nötig:** Render Dashboard → Environment Variables → `KIE_AI_API_KEY` eintragen → Redeploy
- **Diagnose:** `curl https://cinematic-studio-family.onrender.com/api/health.php` → `ai.kie_key_set` prüfen
- **Datei:** `docker/apache.conf`, `api/generate-ai.php`, `api/ai-status.php`

### AI E2E-Test nicht abgeschlossen
- **Symptom:** Kein echter Kie.ai Generierungs-Test durchgeführt
- **Ursache:** Abhängig von KIE_AI_API_KEY (s. oben)
- **Nächster Schritt:** Nach Key-Eintragung: Upload → Analyse → generate-ai → ai-status → render-final

---

## 🟡 P2 — Technische Schulden

### CLEANUP_SECRET nicht in Render eingetragen
- **Symptom:** `api/cleanup.php?key=...` liefert 403
- **Fix:** **User-Aktion:** Render Dashboard → `CLEANUP_SECRET` (min. 20 Zeichen) → Redeploy
- **Datei:** `api/cleanup.php`, `docker/apache.conf` (`PassEnv CLEANUP_SECRET` bereits gesetzt)

---

## 🟢 P3 — Kleine Verbesserungen

### API_PROVIDER_LINK ist Platzhalter
- **Symptom:** `includes/config.php` enthält `'https://kie.ai'` — kein Affiliate-Link
- **Fix:** Echten Kie.ai Referral-Link eintragen (falls Programm existiert)
- **Datei:** `includes/config.php`
- **Aufwand:** 5 Minuten

### health.php Debug-Felder in Produktion
- **Symptom:** `health.php` gibt Env-Var-Keys aus (nur bei `?debug=1`, akzeptabel)
- **Fix:** Optional: Debug-Endpoint mit CLEANUP_SECRET absichern
- **Datei:** `api/health.php`

### Polling-Mechanik in progress.js
- **Symptom:** Export-Fortschritt-Polling ohne Backoff
- **Datei:** `assets/js/progress.js`
- **Fix:** Exponentielles Backoff + max. Retry-Count

---

## Bekannte Free-Plan-Grenzen (bewusst akzeptiert, kein Bug)

| Limit | Wert | Begründung |
|---|---|---|
| Max Video-Länge | 15s | RAM-Schutz Free-Plan |
| 720p statt 1080p | ja | RAM-Budget |
| Ephemeral wenn kein Disk | ja | Disk vorhanden (1 GB csf-storage) |
| No Login | ja | V1 Single-User-App |
| Kristalle Dummy | ja | V1 Pre-Revenue |

---

## Resolved / Closed

| Problem | Datum | Lösung |
|---|---|---|
| FFmpeg `drawtext` Emoji-Crash | 2026-05-13 | `csf_drawtext_escape()` mit Emoji-Strip |
| `mb_substr()` fehlt lokal | 2026-05-13 | Nur relevant lokal, Docker hat mbstring |
| Font-Path Windows vs. Linux | 2026-05-13 | `RENDER_FONT_PATH` Konstante |
| Doppelte CORS-Header | 2026-05-01 | CORS nur in Apache, kein PHP-Header |
| Kein Audio-Stream (concat homogenität) | 2026-05-14 | Session 2: anullsrc-AAC für alle Slot-Typen; Session 4: V3 Original-Audio-Erhalt via ffprobe |
| elements.php Bearbeiten-Button disabled | 2026-05-14 | Session 3: Edit-Modal + api/elements.php update-Action |
| Logo-Upload nicht verbunden | 2026-05-14 | Session 3: `uploadLogoIfNeeded()` in tiktok-animation.php + tiktok-sticker.php |
| Anfrage-Modal ready-videos.php dummy | 2026-05-14 | Session 3: `api/save-request.php` erstellt, Modal-Submit verbunden |
| Job-Lock fehlt bei parallelem Render | 2026-05-14 | Session 2: `flock(LOCK_EX\|LOCK_NB)` auf render.lock → 409 |
| storage/jobs 403 für KI-Bilder | 2026-05-14 | Session 4: `<Directory /var/www/html/storage/jobs>` mit `Require all granted` in apache.conf |
| Probabilistischer Cleanup fehlt | 2026-05-14 | Session 3: `csf_cleanup_old_jobs()` in functions.php, api/cleanup.php, bin/cleanup-cron.php |
| 8 Seiten nur im Worktree | 2026-05-14 | Session 5: Alle 8 Seiten in Main-Project kopiert |
| .gitignore UTF-16 Korruption | 2026-05-14 | Session 5: PowerShell-Rewrite auf UTF-8 |
| scene-editor-test.html Hub unvollständig | 2026-05-14 | Session 5: 6 fehlende Studio-Cards hinzugefügt |
| api/export.php Docblock veraltet | 2026-05-14 | Session 5: Docblock und Inline-Kommentare korrigiert |
| studio-demo.php Wallet 💎 500 | 2026-05-14 | Session 5: Wallet-Pill → 💎 Free, Footer © 2026 |
| KI-Bild Button in studio-demo.php fehlend | 2026-05-14 | Session 5: AI-Prompt-Textarea + generateAiImage() + pollAiStatus() implementiert |
