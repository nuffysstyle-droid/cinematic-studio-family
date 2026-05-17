# memory/current-problems.md — Aktuelle Probleme & Tech Debt

> Letzte Aktualisierung: 2026-05-17 (Session 11)
> Priorität: P0 = Kritisch blockierend · P1 = Feature-blockierend · P2 = Wichtig · P3 = Nice-to-fix

---

## 🔴 P0 — Kritische Blocker

*(Keine aktuellen P0-Blocker — KIE_AI_API_KEY ist gesetzt und aktiv, health.php bestätigt.)*

---

## 🟡 P1 — Feature-blockierend (User-Aktionen ausstehend)

### v0.4.0 Env-Vars nicht in Render eingetragen
- **Symptom:** Mailgun-Emails (Welcome, Reset) werden nicht gesendet. Stripe-Checkout nicht verfügbar.
- **Fix:** Render Dashboard → Environment → folgende 7 Vars eintragen → Redeploy:
  - `MAILGUN_API_KEY` — key-... (Mailgun Dashboard)
  - `MAILGUN_DOMAIN` — mg.cinematic-vision-studio.de
  - `APP_FROM_EMAIL` — Cinematic Vision Studio <noreply@mg...>
  - `APP_URL` — https://cinematic-studio-family.onrender.com
  - `STRIPE_SECRET_KEY` — sk_live_... oder sk_test_...
  - `STRIPE_WEBHOOK_SECRET` — whsec_... (nach Webhook-Anlage)
  - `STRIPE_PRICE_ID_STARTER` — price_... (nach Product-Anlage)
- **Stripe Webhook URL:** `https://cinematic-studio-family.onrender.com/api/stripe/webhook.php`
- **Events:** `checkout.session.completed`, `customer.subscription.deleted`

### PR noch nicht gemergt (v0.4.0 nicht auf main)
- **Branch:** `claude/amazing-kapitsa-68b643` → muss in `main` gemergt werden
- **Commits:** e36f877 (v0.4.0 Features) + fc920f2 (Link-Fixes)
- **Wirkung:** Nach Merge → Render Auto-Deploy → v0.4.0 live

## 🟡 P2 — Technische Schulden

### scene-editor-test.html Mobile Menü "Shop Beta"
- **Symptom:** Live-Seite zeigt "🛍️ Shop Beta" im Mobile Menü statt "🛍️ Shop"
- **Fix:** Datei aus Worktree `amazing-kapitsa-68b643/scene-editor-test.html` auf IONOS hochladen
- **Status:** Fix committed (fc920f2), noch nicht deployed

### CLEANUP_SECRET eingetragen ✅
- **Status:** Gesetzt und aktiv (Session 8 bestätigt)

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
