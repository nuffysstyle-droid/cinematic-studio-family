# Error Bank — Cinematic Vision Studio
# Strukturiertes Fehlergedächtnis (Root Causes, Fixes, Regeln)

> IMMER vor dem Coden lesen. Bei neuem Fehler: neuen ERR-NNN-Eintrag anlegen.
> Letzte Aktualisierung: 2026-05-23

Status-Legende: ✅ FIXED | 🔴 OPEN | 🟡 KNOWN (akzeptiert / Tech Debt)

---

## Schnellindex

| ID | Status | Kurzbeschreibung |
|---|---|---|
| ERR-001 | ✅ FIXED | FFmpeg `drawtext` Emoji-Crash |
| ERR-002 | ✅ FIXED | `mb_substr()` fehlt lokal |
| ERR-003 | ✅ FIXED | Font-Path Windows vs. Linux |
| ERR-004 | ✅ FIXED | Doppelte CORS-Header |
| ERR-005 | ✅ FIXED | Kein Audio-Stream bei concat |
| ERR-006 | ✅ FIXED | elements.php Bearbeiten-Button disabled |
| ERR-007 | ✅ FIXED | Logo-Upload nicht verbunden |
| ERR-008 | ✅ FIXED | Anfrage-Modal ready-videos.php dummy |
| ERR-009 | ✅ FIXED | Job-Lock fehlt bei parallelem Render |
| ERR-010 | ✅ FIXED | storage/jobs 403 für KI-Bilder |
| ERR-011 | ✅ FIXED | Probabilistischer Cleanup fehlt |
| ERR-012 | ✅ FIXED | 8 Seiten nur im Worktree |
| ERR-013 | ✅ FIXED | `.gitignore` UTF-16 Korruption |
| ERR-014 | ✅ FIXED | scene-editor-test.html Hub unvollständig |
| ERR-015 | ✅ FIXED | `api/export.php` Docblock veraltet |
| ERR-016 | ✅ FIXED | studio-demo.php Wallet 💎 500 falsch |
| ERR-017 | ✅ FIXED | KI-Bild Button in studio-demo.php fehlend |
| ERR-018 | ✅ FIXED | CSS `display:flex/grid` überschrieb `[hidden]` |
| ERR-019 | ✅ FIXED | `contact.php` Nav hatte 9 relative Links |
| ERR-020 | 🟡 KNOWN | `KIE_AI_API_KEY` nicht in Render eingetragen (Session 8: gesetzt ✅) |
| ERR-021 | 🟡 KNOWN | `CLEANUP_SECRET` nicht in Render eingetragen (Session 8: gesetzt ✅) |
| ERR-022 | 🔴 OPEN | `API_PROVIDER_LINK` Platzhalter |
| ERR-023 | 🔴 OPEN | `health.php` Debug-Felder in Produktion |
| ERR-024 | 🔴 OPEN | Polling-Mechanik in `progress.js` ohne Backoff |
| ERR-025 | 🟡 KNOWN | Render Cron-Service nicht deployed |
| ERR-026 | 🔴 OPEN | Email-Verifizierung fehlt |
| ERR-027 | 🔴 OPEN | Stripe/Payment nicht integriert |

---

## Behobene Fehler

### ERR-001 — FFmpeg `drawtext` Emoji-Crash
- **Datum:** 2026-05-13
- **Status:** ✅ FIXED
- **Was:** FFmpeg `drawtext`-Filter brach beim Rendern von Titelkarten mit Emojis ab — Render-Job-Abbruch ohne MP4-Output.
- **Warum (Root Cause):** Liberation Sans Font hat keine Emoji-Glyphs; FFmpeg's `drawtext` versagt bei unsupported Unicode-Codepoints, statt sie zu ignorieren.
- **Fix:** `csf_drawtext_escape()` in `includes/functions.php` eingeführt — strippt Emojis (Codepoint-Filter) und escaped FFmpeg-Sonderzeichen (`:`, `\`, `'`).
- **Regel:** ALLE Text-Strings, die in `drawtext` landen, MÜSSEN durch `csf_drawtext_escape()` laufen. Kein direktes Einsetzen von User-Text in FFmpeg-Filter.
- **Dateien:** `includes/functions.php`, `api/render-final.php`

### ERR-002 — `mb_substr()` fehlt lokal
- **Datum:** 2026-05-13
- **Status:** ✅ FIXED
- **Was:** `mb_substr()` warf "undefined function" auf Windows-Dev-Umgebung.
- **Warum (Root Cause):** Lokale PHP-Installation hatte `mbstring`-Extension nicht aktiviert.
- **Fix:** Nur lokal relevant — Docker-Image (`php:8.2-apache`) hat mbstring. Lokale `php.ini` muss `extension=mbstring` enthalten.
- **Regel:** Auf Linux/Docker funktioniert mbstring out-of-the-box. Lokale Dev-Setup-Doku in `memory/deployment.md` enthält mbstring-Hinweis.
- **Dateien:** lokale `php.ini`

### ERR-003 — Font-Path Windows vs. Linux
- **Datum:** 2026-05-13
- **Status:** ✅ FIXED
- **Was:** `drawtext` schlug fehl, weil hartcodierter Font-Pfad (`/usr/share/fonts/...`) auf Windows nicht existiert und umgekehrt.
- **Warum (Root Cause):** Cross-Platform-Pfade nicht abstrahiert.
- **Fix:** `RENDER_FONT_PATH`-Konstante in `includes/config.php` — wird je Umgebung gesetzt (Docker: Linux-Pfad, lokal: Windows-Pfad).
- **Regel:** Keine hartkodierten Filesystem-Pfade. Immer über Konstante in `config.php`.
- **Dateien:** `includes/config.php`, `api/render-final.php`

### ERR-004 — Doppelte CORS-Header
- **Datum:** 2026-05-01
- **Status:** ✅ FIXED
- **Was:** Browser-Console: "Multiple `Access-Control-Allow-Origin` values" → CORS-Request abgelehnt.
- **Warum (Root Cause):** CORS-Header sowohl in Apache-Config (`docker/apache.conf`) als auch in PHP-Endpoints via `header()` gesetzt.
- **Fix:** CORS-Header NUR in Apache-Config. PHP-Header entfernt.
- **Regel:** Single Source of Truth für HTTP-Header — Apache-Config. Kein `header('Access-Control-...')` in PHP.
- **Dateien:** `docker/apache.conf`, mehrere `api/*.php` (PHP-CORS-Header entfernt)

### ERR-005 — Kein Audio-Stream bei concat (V3 Original-Audio)
- **Datum:** 2026-05-14
- **Status:** ✅ FIXED
- **Was:** FFmpeg `concat` schlug fehl, wenn Slots gemischte Audio-/Stumm-Typen waren. Kein finales MP4.
- **Warum (Root Cause):** `concat`-Demuxer verlangt homogene Stream-Konfiguration. Bild-Slots haben keinen Audio-Track, Video-Slots schon.
- **Fix:** `anullsrc=cl=stereo:r=44100` als AAC-Audio-Track für alle Bild- und Text-Slots erzwingen. V3: ffprobe prüft Original-Video → wenn Audio vorhanden, `-map 0:a` für diesen Slot.
- **Regel:** Bei `concat` müssen ALLE Slots dieselbe Stream-Topologie haben (Video + Audio). Vor `concat` immer Audio-Spur erzwingen.
- **Dateien:** `api/render-final.php`, `includes/functions.php`

### ERR-006 — elements.php Bearbeiten-Button disabled
- **Datum:** 2026-05-14
- **Status:** ✅ FIXED
- **Was:** Edit-Button auf `elements.php` war funktionslos / disabled.
- **Warum (Root Cause):** Edit-Modal nicht implementiert, `api/elements.php` hatte keine update-Action.
- **Fix:** Edit-Modal in `elements.php` hinzugefügt, `api/elements.php` mit `action=update`-Branch erweitert.
- **Regel:** Disabled-Buttons im UI sind Tech-Debt. Entweder funktional oder entfernen.
- **Dateien:** `elements.php`, `api/elements.php`

### ERR-007 — Logo-Upload nicht verbunden
- **Datum:** 2026-05-14
- **Status:** ✅ FIXED
- **Was:** Logo-Upload in `tiktok-animation.php` und `tiktok-sticker.php` lud nicht hoch.
- **Warum (Root Cause):** `uploadLogoIfNeeded()` Helper-Funktion fehlte im Submit-Flow.
- **Fix:** `uploadLogoIfNeeded()` in beiden Seiten implementiert, Logo wird vor dem Render hochgeladen.
- **Regel:** Asset-Uploads müssen VOR Render-Submit abgeschlossen sein. Helper-Funktion wiederverwenden.
- **Dateien:** `tiktok-animation.php`, `tiktok-sticker.php`

### ERR-008 — Anfrage-Modal ready-videos.php dummy
- **Datum:** 2026-05-14
- **Status:** ✅ FIXED
- **Was:** Anfrage-Modal auf `ready-videos.php` zeigte Toast, aber speicherte nichts.
- **Warum (Root Cause):** Backend-Endpoint `api/save-request.php` existierte nicht.
- **Fix:** `api/save-request.php` erstellt — speichert Anfragen in `data/requests.json` mit `LOCK_EX`.
- **Regel:** Kein UI ohne funktionierendes Backend. Mock-Buttons sind verboten.
- **Dateien:** `api/save-request.php`, `ready-videos.php`

### ERR-009 — Job-Lock fehlt bei parallelem Render
- **Datum:** 2026-05-14
- **Status:** ✅ FIXED
- **Was:** Parallele Render-Requests auf denselben Job → korrupte Output-Dateien, Race Condition.
- **Warum (Root Cause):** Kein Locking-Mechanismus auf Job-Ebene.
- **Fix:** `flock(LOCK_EX|LOCK_NB)` auf `render.lock`-Datei. Zweiter Request → HTTP 409 Conflict.
- **Regel:** Lange Operationen auf Shared Resources brauchen Locks. `LOCK_NB` für non-blocking + 409-Response.
- **Dateien:** `api/render-final.php`

### ERR-010 — storage/jobs 403 für KI-Bilder
- **Datum:** 2026-05-14
- **Status:** ✅ FIXED
- **Was:** Generierte KI-Bilder waren in `storage/jobs/.../` gespeichert, aber Browser bekam 403.
- **Warum (Root Cause):** Apache-Default verbietet Zugriff auf das Verzeichnis.
- **Fix:** `<Directory>` mit `Require all granted` in `docker/apache.conf` für `storage/jobs`.
- **Regel:** Neue Storage-Verzeichnisse → Apache-Freigabe in `docker/apache.conf` prüfen.
- **Dateien:** `docker/apache.conf`

### ERR-011 — Probabilistischer Cleanup fehlt
- **Datum:** 2026-05-14
- **Status:** ✅ FIXED
- **Was:** `storage/jobs/` wuchs unbegrenzt, Disk lief voll.
- **Warum (Root Cause):** Kein Cleanup-Mechanismus für alte Jobs.
- **Fix:** `csf_cleanup_old_jobs()` in `includes/functions.php`, getriggert mit 1/50 Wahrscheinlichkeit nach Render. Zusätzlich CLI-Script `bin/cleanup-cron.php` + HTTP-Endpoint `api/cleanup.php?key=CLEANUP_SECRET`.
- **Regel:** Disk-Storage braucht Retention-Policy. Cleanup probabilistisch + Cron-Backup.
- **Dateien:** `includes/functions.php`, `api/cleanup.php`, `bin/cleanup-cron.php`, `render.yaml`

### ERR-012 — 8 Seiten nur im Worktree
- **Datum:** 2026-05-14
- **Status:** ✅ FIXED
- **Was:** 8 PHP-Seiten existierten nur in einem Git-Worktree, fehlten im Main-Project → Render-Deploy hatte Broken-Links.
- **Warum (Root Cause):** Worktree-Workflow nicht sauber gemerged.
- **Fix:** Alle 8 Seiten manuell in Main-Project kopiert + committed.
- **Regel:** Worktrees sind temporär — finale Dateien gehören in `main`. Vor Deploy: `git status` prüfen.
- **Dateien:** 8 PHP-Files (`tiktok-*.php`, `merge-clips.php`, `video-studio.php`, etc.)

### ERR-013 — `.gitignore` UTF-16 Korruption
- **Datum:** 2026-05-14
- **Status:** ✅ FIXED
- **Was:** `.gitignore` wurde von Git nicht erkannt — Dateien wurden trotzdem getrackt.
- **Warum (Root Cause):** PowerShell hatte `.gitignore` als UTF-16 LE mit BOM gespeichert; Git erwartet UTF-8.
- **Fix:** PowerShell-Rewrite mit `Out-File -Encoding utf8` (ohne BOM).
- **Regel:** ALLE Text-Dateien für Git/Linux-Tools MÜSSEN UTF-8 sein. Bei PowerShell-Writes explizit `-Encoding utf8` setzen.
- **Dateien:** `.gitignore`

### ERR-014 — scene-editor-test.html Hub unvollständig
- **Datum:** 2026-05-14
- **Status:** ✅ FIXED
- **Was:** Landing-Page-Hub zeigte nur einen Teil der Studio-Cards; 6 Studios fehlten.
- **Warum (Root Cause):** Hub-Page nicht synchron mit neu hinzugefügten Studio-Seiten gehalten.
- **Fix:** 6 fehlende Studio-Cards in `scene-editor-test.html` hinzugefügt.
- **Regel:** Bei neuer Studio-Seite → Hub-Page (`scene-editor-test.html`) MUSS in derselben Session aktualisiert werden.
- **Dateien:** `scene-editor-test.html`

### ERR-015 — `api/export.php` Docblock veraltet
- **Datum:** 2026-05-14
- **Status:** ✅ FIXED
- **Was:** Docblock + Inline-Kommentare in `api/export.php` beschrieben veraltete Funktionalität.
- **Warum (Root Cause):** Code refactored, Kommentare nicht mit-aktualisiert.
- **Fix:** Docblock und Inline-Kommentare korrigiert.
- **Regel:** Bei Refactoring IMMER Docblock + Kommentare mit-aktualisieren.
- **Dateien:** `api/export.php`

### ERR-016 — studio-demo.php Wallet 💎 500 falsch
- **Datum:** 2026-05-14
- **Status:** ✅ FIXED
- **Was:** Wallet-Pill in Studio zeigte "💎 500" hart-kodiert — falsch für Free-User (50 Welcome-Kristalle).
- **Warum (Root Cause):** Dummy-Wert nicht durch echte Daten ersetzt.
- **Fix:** Wallet-Pill auf "💎 Free" für Free-User; bei Auth dynamisch aus DB. Footer-Copyright auf © 2026 aktualisiert.
- **Regel:** Keine Dummy-Zahlen im Live-UI. Echte Werte aus DB oder generisches Label.
- **Dateien:** `studio-demo.php`

### ERR-017 — KI-Bild Button in studio-demo.php fehlend
- **Datum:** 2026-05-14
- **Status:** ✅ FIXED
- **Was:** Slot-Replacement-UI hatte keinen KI-Bild-Button — User konnte Kie.ai-Bilder nicht aus dem Studio nutzen.
- **Warum (Root Cause):** Feature nur in `api/generate-ai.php` implementiert, UI-Hookup vergessen.
- **Fix:** AI-Prompt-Textarea + `generateAiImage()` + `pollAiStatus()` in `studio-demo.php` implementiert.
- **Regel:** Backend-Feature ohne UI-Hookup = nicht live. Definition-of-Done: Backend + UI + E2E-Test.
- **Dateien:** `studio-demo.php`

### ERR-018 — CSS `display:flex/grid` überschrieb HTML-`hidden`-Attribut
- **Datum:** 2026-05-16 (Session 8)
- **Status:** ✅ FIXED
- **Was:** Error-Boxen auf `merge-clips.php`, `video-studio.php` u.a. waren bei Seitenload SICHTBAR, obwohl `hidden`-Attribut gesetzt war.
- **Warum (Root Cause):** CSS-Spezifität — `.error-box { display: flex; }` überschreibt das User-Agent-Default `[hidden] { display: none; }`. W3C-Standard verlangt explizite `!important`-Reset bei Custom-Display-Werten.
- **Fix:** `[hidden] { display: none !important; }` als W3C-Standard-Reset in `assets/css/app.css`.
- **Regel:** Bei `display:flex/grid/inline-block` in CSS MUSS `[hidden] { display: none !important; }` global gelten. Niemals HTML-`hidden`-Attribut durch CSS überschreiben.
- **Dateien:** `assets/css/app.css`

### ERR-019 — `contact.php` Nav hatte 9 relative Links
- **Datum:** 2026-05-16 (Session 9)
- **Status:** ✅ FIXED
- **Was:** Nav auf `contact.php` (Render) hatte 9 relative Links — Links zu IONOS-Seiten (z. B. `shop.html`) führten auf Render statt IONOS → 404.
- **Warum (Root Cause):** Render-Seiten und IONOS-Seiten leben auf unterschiedlichen Hosts; relative Links resolven gegen den aktuellen Host.
- **Fix:** Nav-Reduktion auf 5 Links mit absoluten URLs: IONOS-URLs (`https://cinematic-vision-studio.de/...`) für IONOS-Seiten, relative URLs für Render-Seiten. Login-Button hinzugefügt.
- **Regel:** Cross-Host-Links MÜSSEN absolute URLs sein. Nav-Standard: 5 Links (Home, Shop, Academy, Studio, Kontakt) + Login.
- **Dateien:** `contact.php`

---

## Offene Fehler / Tech Debt

### ERR-020 — `KIE_AI_API_KEY` nicht in Render eingetragen
- **Datum:** initial offen, Session 8 verifiziert gesetzt
- **Status:** 🟡 KNOWN (laut CLAUDE.md Session 8 jetzt gesetzt ✅, aber als historische Notiz behalten)
- **Was:** KI-Generierung schlug initial fehl mit "API-Key fehlt".
- **Warum (Root Cause):** Env-Var nicht im Render-Dashboard hinterlegt.
- **Fix:** Render-Dashboard → Environment → `KIE_AI_API_KEY` setzen. Verifiziert via `/api/health.php` → `kie_key_set: true`.
- **Regel:** Vor jedem KI-Deploy: `/api/health.php` aufrufen und alle Env-Var-Flags prüfen.
- **Dateien:** Render-Dashboard (Env), `api/health.php`

### ERR-021 — `CLEANUP_SECRET` nicht in Render eingetragen
- **Datum:** initial offen, Session 8 verifiziert gesetzt
- **Status:** 🟡 KNOWN (Session 8 jetzt gesetzt ✅)
- **Was:** Manuelles Cleanup via `/api/cleanup.php?key=...` ohne Secret nicht möglich.
- **Warum (Root Cause):** Env-Var fehlte.
- **Fix:** `CLEANUP_SECRET` im Render-Dashboard gesetzt.
- **Regel:** Alle Admin-Endpoints brauchen Secret-Token-Auth. Token NIE in Repo.
- **Dateien:** Render-Dashboard (Env), `api/cleanup.php`

### ERR-022 — `API_PROVIDER_LINK` ist Platzhalter
- **Datum:** offen
- **Status:** 🔴 OPEN (P3)
- **Was:** Konstante `API_PROVIDER_LINK` in `includes/config.php` ist ein Dummy-String — wird im UI angezeigt.
- **Warum (Root Cause):** Final-URL für externen API-Provider-Hinweis noch nicht festgelegt.
- **Fix (geplant):** Echte URL eintragen oder Konstante + UI-Verwendung entfernen.
- **Regel:** Keine Platzhalter-URLs im Live-UI. Entweder echt oder weglassen.
- **Dateien:** `includes/config.php`

### ERR-023 — `health.php` Debug-Felder in Produktion
- **Datum:** offen
- **Status:** 🔴 OPEN (P3)
- **Was:** `/api/health.php` liefert interne Stats (Job-Counts, Storage-Größen) öffentlich.
- **Warum (Root Cause):** Initial als Public-Health-Check gebaut; sensitive Felder dazugekommen.
- **Fix (geplant):** Optional: Verbose-Mode nur bei `?key=CLEANUP_SECRET` aktivieren; öffentlich nur `ok/php/ffmpeg/storage/ai`.
- **Regel:** Public-Endpoints liefern nur das Nötigste. Debug-Info nur mit Auth.
- **Dateien:** `api/health.php`

### ERR-024 — Polling-Mechanik in `progress.js` ohne Backoff
- **Datum:** offen
- **Status:** 🔴 OPEN (P3)
- **Was:** `progress.js` pollt `/api/ai-status.php` in festem Intervall — bei langen KI-Jobs (~40s) entstehen ~20+ Requests pro Job.
- **Warum (Root Cause):** Polling-Loop ohne Backoff-Logik.
- **Fix (geplant):** Exponentielles Backoff (z.B. 1s → 2s → 4s → 8s, max 10s) + max. Retry-Count (z.B. 60 = 5 Min Hard-Timeout).
- **Regel:** Polling immer mit Backoff + Hard-Timeout. Keine Endlos-Loops.
- **Dateien:** `assets/js/progress.js` (oder Äquivalent in `studio-demo.php`)

### ERR-025 — Render Cron-Service nicht deployed
- **Datum:** offen
- **Status:** 🟡 KNOWN (P2)
- **Was:** `csf-cleanup-cron`-Service aus `render.yaml` nicht aktiv — Cleanup läuft nur probabilistisch.
- **Warum (Root Cause):** Render Cron-Services erfordern Starter-Plan; Dashboard zeigt aktuell Free-Plan.
- **Fix (geplant):** Render-Plan auf Starter upgraden → Cron deployed sich automatisch via `render.yaml`. Fallback aktiv: 1/50 probabilistisch nach Render + HTTP-Endpoint `/api/cleanup.php?key=CLEANUP_SECRET`.
- **Regel:** Cron-Jobs sind Nice-to-have, kein Single Point of Failure. Probabilistischer Fallback muss immer mitlaufen.
- **Dateien:** `render.yaml`, `bin/cleanup-cron.php`, Render-Dashboard

### ERR-026 — Email-Verifizierung fehlt
- **Datum:** offen
- **Status:** 🔴 OPEN (P2, V0.5 Backlog)
- **Was:** Bei Register wird Email gespeichert, aber NICHT verifiziert — User kann beliebige Email eintragen.
- **Warum (Root Cause):** Mail-Provider noch nicht entschieden (Mailgun vs. PHP `mail()` vs. SMTP).
- **Fix (geplant):** V0.5 — Mailgun-Integration mit Verifizierungs-Token, Welcome-Email + Reset-Email.
- **Regel:** Bis dahin: User können sich registrieren, aber Email-basierte Features (Reset, Notifications) sind eingeschränkt.
- **Dateien:** `api/auth/register.php`, `api/auth/forgot-password.php`, `includes/auth.php`

### ERR-027 — Stripe/Payment nicht integriert
- **Datum:** offen
- **Status:** 🔴 OPEN (P2, V1.0 Backlog)
- **Was:** Kristall-Pakete + Starter+ Plan sind UI-only — keine echten Transaktionen.
- **Warum (Root Cause):** Payment-Provider-Entscheidung + Compliance (UStG, AGB, Widerruf) noch offen.
- **Fix (geplant):** V0.4 (Starter+ Plan, Stripe Subscriptions) und V1.0 (Kristall-Käufe einmalig). DB-Tabellen (`transactions`, `subscriptions`) bereits angelegt.
- **Regel:** Solange kein Payment: Free-Plan + 50 Welcome-Kristalle. Keine echten Geldflüsse fingieren.
- **Dateien:** `dashboard.php`, `crystals.html`, geplante `api/payment/*`

---

## Format für neue Einträge

```markdown
### ERR-NNN — Kurztitel
- **Datum:** YYYY-MM-DD
- **Status:** ✅ FIXED | 🔴 OPEN | 🟡 KNOWN
- **Was:** Symptom — was hat der User / Test gesehen?
- **Warum (Root Cause):** Eigentliche technische Ursache.
- **Fix:** Was wurde konkret gemacht (oder: geplant)?
- **Regel:** Was gilt ab jetzt projektweit?
- **Dateien:** Welche Dateien sind betroffen?
```

→ Nach jedem Eintrag: Schnellindex-Tabelle oben aktualisieren.
