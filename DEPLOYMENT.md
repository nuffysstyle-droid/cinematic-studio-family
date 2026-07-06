# DEPLOYMENT.md — Cinematic Vision Studio
# Deployment & Betrieb: Ist, Zielbild, Domain-Migration, Rollback

| Feld | Wert |
|---|---|
| Version | 1.0 (Architecture Lock, 2026-07-05) — D-001, D-008 |
| Goldene Regel | Push auf `main` = SOFORTIGER Live-Deploy (Render autoDeploy). Niemals ohne Freigabe + QA-Gate pushen (CLAUDE.md §20.1). |

---

## 1. Ist-Zustand (zwei Deploy-Welten — wird aufgelöst)

| | Render (App) | IONOS (Marketing) |
|---|---|---|
| Auslöser | Git-Push auf `main` | manuell: `ionos-upload*.mjs` (Playwright klickt Webinterface) |
| Risiko | ungetesteter Push geht live | fragil; referenziert nicht existente `availability.html`; kein Rollback; Live ≠ Repo |
| Status | ✅ bleibt (einziger Weg im Zielbild) | 🔻 stirbt mit Domain-Umzug (O-5) |

**Render-Konfiguration (render.yaml, verifiziert):** Web-Service `cinematic-studio-family`
(Docker php:8.2-apache + FFmpeg 7.1.3 + fonts-liberation, Plan Starter, `$PORT` via
entrypoint), Persistent Disk `csf-storage` 1 GB → `/var/www/html/render-data`
(Symlinks storage/ + data/), Health-Check `/index.php`, Cron `csf-cleanup-cron`
täglich 03:00 UTC (Deploy-Status prüfen — Dashboard zeigte zuletzt Free-Plan-Konflikt;
Fallback: probabilistischer Cleanup 1/50 + `/api/cleanup.php?key=…` ✔).

## 2. Environment-Variablen (vollständige Referenz)

| Variable | Zweck | Status |
|---|---|---|
| `KIE_AI_API_KEY` | Kie.ai (PassEnv in apache.conf) | ✅ gesetzt |
| `CLEANUP_SECRET` | manueller Cleanup-Endpunkt | ✅ gesetzt |
| `FFMPEG_PATH` / `FFPROBE_PATH` / `FFMPEG_TIMEOUT` | Binärpfade/Timeout | ✅ render.yaml |
| `PHP_SESSION_NAME` | Session-Cookie-Name (`csf_session`) | ✅ |
| `PERSIST_ROOT` | Disk-Mount | ✅ |
| `STRIPE_SECRET_KEY` / `STRIPE_WEBHOOK_SECRET` | Payment | 🆕 Phase 2 (Test→Live getrennt) |
| `MAIL_*` (Provider gem. O-2) | Transaktionsmails | 🆕 Phase 2 |

## 3. Ziel-Deployment (eine Pipeline)

```
lokal entwickeln → QA-Gate (QA_MASTERPLAN §7: Smoke-E2E + Checkliste)
→ Björn-Freigabe → git push origin main → Render Build (Docker) → Health-Check
→ Post-Deploy-Smoke (Playwright gegen Live: Home, Login, health.php)
→ bei Rot: Render Dashboard → „Rollback to previous deploy" (1 Klick)
```
Kein zweiter Deploy-Weg. Statische Inhalte sind Teil des Git-Repos (D-005).

## 4. Domain-Migration (D-001, Zeitpunkt O-5) — Ablaufplan

1. **Vorbereitung:** Plattform-Shell fertig (alle Seiten auf Render lauffähig unter
   onrender.com-URL), 301-Map (§5) implementiert und getestet
2. Render: Custom Domains `cinematic-vision-studio.de` + `www.` hinzufügen
3. IONOS DNS: `www` CNAME → onrender-Ziel; Apex per A/ALIAS gemäß Render-Anleitung;
   TTL vorher auf 300 s senken
4. TLS: Render stellt Zertifikate automatisch (verifizieren!), dann HSTS aktivieren (SECURITY §4)
5. IONOS-Webspace: Rest-`.htaccess` mit Redirect auf neue Domain (Übergangs-Fallback),
   danach stilllegen; E-Mail-/DNS-Verträge bei IONOS UNANGETASTET lassen
6. Nacharbeit: Google Search Console Property + sitemap einreichen; OG/Canonicals prüfen
7. **Rollback-Plan:** DNS zurückstellen (TTL 300 s = schnell); IONOS-Webspace bleibt
   bis +30 Tage unverändert als Sicherung

## 5. 301-Redirect-Map (Auszug, wird bei Umsetzung vervollständigt)

| Alt | Neu |
|---|---|
| `/scene-editor-test.html` | `/` |
| `/kontakt.html`, `/contact.php` | `/kontakt` |
| `/crystals.html`, `/crystals.php` | `/kristalle` |
| `/prompt-generator.html` | `/prompt-master` |
| `/academy.html` · `/shop.html` · `/portfolio.html` · `/calendar.html` · `/immobilienvideos.html` · Rechtsseiten | gleichnamige Clean-Route |
| `/studio-demo.php` | `/studio` |
| `/ki-videos.html`, `/ki-videos.php`, `/availability.*` | `/portfolio` bzw. `/buchung` |
| Legacy-Suite (`/video-studio.php` …) | `/studio` (Phase 3: 410 für tote BYOK-APIs) |

## 6. Backup & Jobs (Betriebskalender)

Täglich 03:00 Cleanup (Jobs>48 h, Temp>2 h) ✔ · 03:10 DB-Backup 🆕 (DATABASE §6) ·
wöchentlich Offsite-Backup-Download 🆕 · monatlich Restore-Probe + Dependency-Blick
(PHP/FFmpeg-Basisimage aktualisieren = bewusster Dockerfile-Commit).

## 7. Monitoring (Phase 3/4)

UptimeRobot (o. ä.) auf `/api/health.php` (Keyword `"ok":true`), Alarm an Björn-Mail ·
Render-Log-Blick nach jedem Deploy · Fehler-Sammel-Log serverseitig (`error_log` →
Render Logs) · Kennzahlen im Admin (ADMIN_SYSTEM §3). Statuspage: nicht nötig in V1.
