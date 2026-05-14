# memory/current-problems.md — Aktuelle Probleme & Tech Debt

> Letzte Aktualisierung: 2026-05-14
> Priorität: P0 = Kritisch blockierend · P1 = Feature-blockierend · P2 = Wichtig · P3 = Nice-to-fix

---

## 🔴 P0 — Kritische Blocker

### KIE_AI_API_KEY nicht in PHP sichtbar
- **Symptom:** `health.php` zeigt `kie_key_set: false` / `generate-ai.php` liefert 503
- **Ursache:** Apache `mod_env` gibt Container-Env-Vars ohne `PassEnv` nicht an PHP weiter
- **Deployed Fix:** `PassEnv KIE_AI_API_KEY` in `docker/apache.conf`, `a2enmod env` in `Dockerfile` (Commit `a77997c`)
- **Verbleibender Blocker:** User muss Key im Render-Dashboard eintragen + Redeploy abwarten
- **Diagnose:** `curl https://cinematic-studio-family.onrender.com/api/health.php` → `ai.kie_key_set` prüfen
- **Datei:** `docker/apache.conf`, `api/generate-ai.php`, `api/ai-status.php`

### AI E2E-Test nicht abgeschlossen
- **Symptom:** Kein echter Kie.ai Generierungs-Test durchgeführt
- **Ursache:** Abhängig von KIE_AI_API_KEY (s. oben)
- **Nächster Schritt:** Nach Key-Eintragung: Upload→Analyse→generate-ai→ai-status→render-final

---

## 🟡 P2 — Technische Schulden

### Audio-Preservation nicht implementiert
- **Symptom:** Alle gerenderten Videos haben keinen Ton
- **Ursache:** FFmpeg Concat-Demuxer benötigt homogene Streams; Text/Bild-Slots haben keinen Audio-Stream
- **Workaround:** `-an` (kein Audio) in allen Slots → Concat funktioniert
- **Fix-Pfad:** Alle Slots auf selben Audio-Standard normalisieren (AAC, 44.1kHz) ODER Audio-Track separat zusammenführen
- **Datei:** `api/render-final.php`
- **Aufwand:** ~2-4 Stunden

### elements.php "Bearbeiten"-Button deaktiviert
- **Symptom:** Klick auf Bearbeiten-Button macht nichts (visuell disabled)
- **Ursache:** `api/elements.php` gibt HTTP 501 (Not Implemented) zurück
- **Datei:** `elements.php`, `api/elements.php`
- **Fix:** CRUD-Logik in `api/elements.php` implementieren (POST/PUT/DELETE)
- **Aufwand:** ~1-2 Stunden

### Logo-Upload nicht mit api/upload.php verbunden
- **Symptom:** Logo-Upload in `tiktok-animation.php` und `tiktok-sticker.php` zeigt UI aber funktioniert nicht
- **Ursache:** UI-Element vorhanden, aber API-Call fehlt oder ist Dummy
- **Datei:** `tiktok-animation.php`, `tiktok-sticker.php`
- **Fix:** Upload-Handler an `api/upload.php` anschließen
- **Aufwand:** ~30 Minuten

### Anfrage-Modal in ready-videos.php sendet nicht wirklich
- **Symptom:** "Anfragen"-Button zeigt Toast, aber keine Daten werden gespeichert
- **Ursache:** Modal-Submit-Handler ist Dummy (nur Toast-Feedback)
- **Datei:** `ready-videos.php`
- **Fix:** Form-Submit an Backend-Endpunkt (Email oder JSON-File)
- **Aufwand:** ~1 Stunde

---

## 🟢 P3 — Kleine Verbesserungen

### API_PROVIDER_LINK ist Platzhalter
- **Symptom:** `includes/config.php` enthält `'https://DEIN-REFERRAL-LINK-HIER'`
- **Fix:** Echten Kie.ai Affiliate-Link eintragen (falls Programm existiert)
- **Datei:** `includes/config.php`
- **Aufwand:** 5 Minuten

### health.php Debug-Felder in Produktion
- **Symptom:** `health.php` gibt Env-Var-Keys aus (für Diagnose nützlich, aber Production-Verbose)
- **Fix:** `env_keys` und `server_keys_custom` nur wenn `?debug=1` oder nur in Dev
- **Datei:** `api/health.php`
- **Aufwand:** 15 Minuten

### Polling-Mechanik in progress.js synchron
- **Symptom:** Export-Fortschritt läuft, aber Polling-Loop ist nicht optimal
- **Datei:** `assets/js/progress.js`, `api/merge-clips.php`, `api/export.php`
- **Fix:** Besseres Backoff, max. Retry-Count

---

## Bekannte Free-Plan-Grenzen (bewusst akzeptiert, kein Bug)

| Limit | Wert | Begründung |
|---|---|---|
| Max Video-Länge | 15s | RAM-Schutz Free-Plan |
| Kein Audio | ja | Concat-Homogenität |
| 720p statt 1080p | ja | RAM-Budget |
| Ephemeral wenn kein Disk | ja | Disk vorhanden (1 GB) |
| No Login | ja | V1 Single-User-App |
| Kristalle Dummy | ja | V1 Pre-Revenue |

---

## Closed / Resolved

| Problem | Datum | Lösung |
|---|---|---|
| FFmpeg `drawtext` Emoji-Crash | 2026-05-13 | `csf_drawtext_escape()` mit Emoji-Strip |
| `mb_substr()` fehlt lokal | 2026-05-13 | Nur relevant lokal, Docker hat mbstring |
| Font-Path Windows vs. Linux | 2026-05-13 | `RENDER_FONT_PATH` Konstante, nur im Docker testen |
| Doppelte `Access-Control-Allow-Origin` Header | 2026-05-01 | CORS nur in Apache, kein PHP-Header |
| Playwright same-URL reload liefert altes DOM | 2026-05-13 | `about:blank` navigate first |
| Kie.ai API Docs SPA (404 bei direktem Fetch) | 2026-05-14 | `sitemap.xml` + gezielter Page-Fetch |
