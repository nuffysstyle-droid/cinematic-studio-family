# CLAUDE.md — Cinematic Vision Studio
# MASTER-BETRIEBSHANDBUCH & PROJEKTGEDÄCHTNIS

> **Pflichtlektüre für jeden Agenten (Claude, Fable, Sub-Agents, Council).**
> Diese Datei ist die Single Source of Truth. Sie wird bei JEDEM Session-Start
> vollständig gelesen, BEVOR irgendeine Aktion ausgeführt wird.
> Sie ersetzt die alte CLAUDE.md (Stand Session 11) und basiert auf dem
> **Master-Audit vom 2026-07-05** (Gesamtbewertung: 62 %).

| Feld | Wert |
|---|---|
| **Dokument-Version** | 2.0 (Master-Betriebshandbuch) |
| **Erstellt aus** | Master-Audit 2026-07-05 (Read-Only, komplette Codebase geprüft) |
| **Gültig ab** | 2026-07-05 |
| **Aktive Phase** | ✅ **ARCHITECTURE LOCK COMPLETE** — Bibliothek (15 Dok. + PROJECT_INDEX) vollständig & konsistenzgeprüft am 2026-07-05. Start von Phase 1 (Code) bleibt gated auf Björns „Bibel freigegeben" + Entscheidungen O-1…O-6. |
| **Owner / Entscheider** | Björn (b.orlandoBusiness@gmx.de) |
| **Projekt-Bibel** | MASTER_BLUEPRINT.md (Zielbild) + 13 Fachdokumente + diese Datei — siehe Abschnitt 17 |

---

## 1. Projektname & Identität

| Feld | Wert |
|---|---|
| **Produktname** | Cinematic Vision Studio (CVS) |
| **Repo / Codebase** | cinematic-studio-family |
| **Code-Version** | 0.3.2 |
| **Marketing-Site (IONOS)** | https://cinematic-vision-studio.de → `/scene-editor-test.html` |
| **App (Render.com)** | https://cinematic-studio-family.onrender.com |
| **GitHub** | nuffysstyle-droid/cinematic-studio-family (Branch: `main`, Auto-Deploy auf Render!) |
| **Alt-Name (Legacy)** | „Cinematic Studio Family" — taucht noch in Code-Kommentaren und der alten Studio-Suite auf |

**Zwei-Hosts-Architektur (wichtig zu verstehen):**
- **IONOS** = statische HTML-Seiten (Marketing, Shop, Academy, Kontakt, Rechtliches) + `api/contact-submit.php`. Deployment: manuell per Upload.
- **Render** = PHP-App (Studio, Login, Dashboard, API, FFmpeg, SQLite). Deployment: automatisch bei Git-Push auf `main`.

---

## 2. Vision

Familien und Creator sollen Urlaubsvideos, Geburtstagsfilme, Jahresrückblicke und
Social-Media-Clips in **Kinoqualität** produzieren können — **ohne Vorkenntnisse,
in unter 10 Minuten**, komplett im Browser.

Das Produkt kombiniert serverseitige Videoverarbeitung (FFmpeg) mit KI-Bildgenerierung
(Kie.ai) zu einem vollständigen **Upload → Edit → Render → Download**-Flow.

**Nordstern:** Von „hochwertige Website mit Studio-Demo" zu einer
**Premium Creator-SaaS-Plattform auf 100.000-€-Niveau** — mit echtem Payment,
einheitlicher Marke und professionellem Betrieb.

Der Weg dorthin führt **nicht über mehr Features, sondern über Fokus**:
ein Studio, ein Bezahlmodell, eine Designsprache von Landing Page bis Dashboard.

---

## 3. Zielgruppe

- **Primär:** Familien, die Erinnerungen dokumentieren (Urlaub, Geburtstag, Jahresrückblick)
- **Sekundär:** Content Creator (TikTok/Shorts/Reels), die cinematic Videos ohne teures Equipment produzieren
- **Tertiär (B2B-Nische):** Immobilienvideos (eigene Landing Page `immobilienvideos.html` — zugleich Design-Master-Referenz)
- **Skill-Level:** Keine Video-Vorkenntnisse — guided UX, template-basiert
- **Plattform:** Browser (Desktop + Mobile), Windows + macOS
- **Sprache:** Deutsch (UI, Content, Kommunikation)

---

## 4. Produktentscheidung: EIN Studio, EIN Bezahlmodell

**Das strategische Grundprinzip dieses Projekts. Es überstimmt alle Einzelentscheidungen.**

### Ist-Zustand (Problem — vom Audit aufgedeckt)
Es existieren aktuell **zwei parallele Produkte** in einer Codebase:

| | Neues Produkt | Altes Produkt (Legacy) |
|---|---|---|
| **Studio** | `studio-demo.php` | Sidebar-Suite: `video-studio.php`, `image-studio.php`, `tiktok-studio.php`, `tiktok-animation.php`, `tiktok-sticker.php`, `merge-clips.php`, `elements.php`, `ready-videos.php`, `trailer-builder.php`, `new-project.php`, `settings.php`, `api-key.php` |
| **Bezahlmodell** | **Kristalle** (interne Währung, Server-Key, 50 Welcome-Bonus) | **BYOK** — „Bring Your Own Key" (User trägt eigenen Kie.ai-API-Key ein) |
| **Design** | Teilweise CVS-Designsystem | Altes Emoji-Sidebar-Design, teils Alt-Branding |
| **Beworben durch** | Haupt-CTA aller IONOS-Seiten | **Alle 24 Academy-Guides** verlinken auf die alte Suite! |

### Beschlossene Richtung
- Es wird **genau EIN Studio** und **genau EIN Bezahlmodell** geben.
- **Empfehlung aus dem Audit (Entscheidung durch Björn noch offen, siehe Abschnitt 19):**
  `studio-demo.php` + **Kristalle** wird der Produktkern. Die BYOK-Suite wird entweder
  (a) archiviert oder (b) als „Pro-Werkzeuge" hinter Login in das Kristall-Modell integriert.
- **Bis Björn entschieden hat:** KEINE Arbeit an einem der beiden Studios, die die
  Entscheidung vorwegnimmt. Keine Löschung der alten Suite (Academy verlinkt darauf!).

---

## 5. Aktueller Stand in Prozent

### Gesamt: **62 / 100** (Master-Audit 2026-07-05)

| Bereich | Score | Kernbefund |
|---|---|---|
| Studio-Funktionalität (Pipeline) | **85** | Upload→Analyse→KI→Render→Download E2E-bestätigt, Job-Locks, Cleanup, Health |
| Design-Konsistenz IONOS-Site | **82** | Nav/Footer auf 14 Seiten nahezu identisch |
| Navigation/UX-Struktur | **80** | 9 Links + Login + CTA + Burger überall gleich |
| Backend-Code-Qualität | **78** | strict_types, lückenloses Shell-Escaping, PDO prepared |
| Content (Academy/Portfolio) | **75** | 24 Guides — aber sie lehren das alte BYOK-Studio |
| Deployment/Ops (Render) | **70** | render.yaml + Cron + Disk sauber; IONOS-Deploy fragil |
| Security | **70** | Auth exzellent; Kontakt-API offen, keine Security-Header |
| Performance | **55** | 7,6-MB-Hero-Video `preload="auto"`, 1,7-MB-Icons |
| SEO | **45** | Keine robots.txt/sitemap, kein JSON-LD, „test" in Homepage-URL |
| Monetarisierung/SaaS-Reife | **40** | Kein Payment, Shop ohne Kauf-Flow, keine E-Mail-Verifizierung |
| Brand-Einheit Website ↔ App | **40** | Login/Dashboard/Profil: 0 % CVS-Design |
| Repo-/Projekthygiene | **35** | 42 untracked Files, Müll-Dateien, ~29 MB Screenshots im Root |

### Seiten-Scores (0–100)
| Seite | Score | | Seite | Score |
|---|---|---|---|---|
| immobilienvideos.html (Master-Referenz) | 88 | | studio-demo.php | 76 |
| portfolio.html | 82 | | crystals.html | 72 |
| Rechtsseiten (5×) | 80 | | academy.html | 70 |
| prompt-generator.html | 78 | | shop.html | 68 |
| scene-editor-test.html (Homepage) | 74 | | kontakt.html | 65 |
| calendar.html | 60 | | dashboard.php / profile.php | 58 |
| login.php / forgot-password.php | 55 | | Alte Studio-Suite (11 Seiten) | 45 |

---

## 6. Bestehende Hauptbereiche

### 6.1 IONOS Marketing-Site (statisch, live)
15 Seiten: Homepage (`scene-editor-test.html`), `immobilienvideos.html`, `portfolio.html`,
`shop.html`, `crystals.html`, `academy.html` (24 Guides), `prompt-generator.html`
(client-side, funktional), `kontakt.html` (Formular → `api/contact-submit.php`),
`calendar.html` (Buchung), 5 Rechtsseiten, `index.html` (Redirect).

### 6.2 Render-App (PHP 8.2 + Apache + FFmpeg, live)
- **Auth-System:** Register (ARGON2ID, 50 Welcome-Kristalle), Login (Brute-Force-Schutz 5/15min,
  Remember-Me 30d, Session-Fixation-Schutz), Logout, Passwort ändern/vergessen
- **Studio:** `studio-demo.php` — auth-aware, Kristall-Balance, 720p/1080p-Toggle
- **Dashboard/Profil:** Kristalle, Plan, Jobs, Transaktionen
- **Legacy-Studio-Suite:** 11 BYOK-Seiten (siehe Abschnitt 4)

### 6.3 API (`api/`)
- **Kern:** `upload.php` (≤50 MB, finfo-MIME-Check), `analyze.php` (FFprobe→Slots),
  `replace-slot.php`, `render-final.php` (FFmpeg concat, V3-Audio, Job-Lock, Plan-Enforcement),
  `generate-ai.php` + `ai-status.php` (Kie.ai Flux Kontext, SSRF-Schutz mit DNS-Check,
  Rate-Limit 10/h), `health.php`, `cleanup.php`, `settings/quality.php`, `auth/*` (6 Endpunkte)
- **Legacy-API (BYOK):** `export.php`, `job-status.php`, `generate-tiktok/-trailer/-video/-image.php`,
  `projects.php`, `elements.php`, `save-element.php`, `animation-request.php`,
  `sticker-request.php`, `save-request.php`, `test-key.php`, `merge-clips.php`, `progress.php`, `get-job.php`
- **IONOS-seitig:** `contact-submit.php` (⚠️ ungeschützt, siehe Abschnitt 7)

### 6.4 Includes (`includes/`)
`auth.php` (sehr solide), `db.php` (SQLite PDO, WAL, Schema-Init), `config.php`,
`functions.php` (FFmpeg-Service, `csf_ffmpeg_run` escaped jedes Argument,
`csf_validate_path` + realpath), `rate_limit.php` (file-based, SHA-256-IP-Hash),
`mailer.php` (PHP mail()/msmtp), `job_service.php`, `prompt-engine.php`, `guidance.php`,
`header.php`/`sidebar.php`/`footer.php` (Legacy-Layout)

### 6.5 Assets (`assets/`)
- `css/cvs-core.css` (CVS-Designsystem) + `css/app.css` (Legacy-App)
- `js/` (nav, editor, progress, upload, app)
- `fonts/` (lokal: Syne, DM Sans — Google-CDN ist verboten)
- Logos: `cvs-logo-icon.png` (Nav), `cvs-logo.png`, `cvs-logo-blue.jpg` (alle unoptimiert 300+ KB)
- `cvs-hero-loop.mp4` (7,6 MB Hero-Video), `icon/` (3 Kristall-PNGs à 1,7 MB),
  `portfolio/` + `showcase/portfolio/` (⚠️ Duplikat-Bäume)

### 6.6 Storage & Daten
- `storage/` (uploads, jobs, exports, thumbnails, temp, rate_limits) — per `.htaccess` `Require all denied` ✅
- `data/` (JSON: ready-videos, projects …) — ebenfalls dicht ✅
- SQLite: Render Persistent Disk `/var/www/html/render-data/cinematic.db` (lokal: `storage/cinematic.db`)

### 6.7 Infrastruktur
- `Dockerfile` (php:8.2-apache + FFmpeg 7.1.3 + fonts-liberation), `docker/apache.conf`
  (PassEnv KIE_AI_API_KEY), `docker/entrypoint.sh` ($PORT-Handling, Disk-Symlinks)
- `render.yaml`: Web-Service (Starter, 1-GB-Disk, Health-Check `/index.php`) + Cron
  `csf-cleanup-cron` (03:00 UTC; Deployment-Status unklar — Fallback: probabilistischer
  Cleanup 1/50 + HTTP `/api/cleanup.php?key=CLEANUP_SECRET`)
- Render Env-Vars gesetzt & verifiziert: `KIE_AI_API_KEY`, `CLEANUP_SECRET`
- IONOS-Deploy: `ionos-upload*.mjs` / `deploy-*.mjs` (Playwright-Klick-Automation, manueller Login — fragil)

### 6.8 Tech-Stack (verbindlich — Änderung nur nach Diskussion mit Björn)
| Schicht | Technologie |
|---|---|
| Backend | PHP 8.2 + Apache (mod_php), kein Framework, kein Composer |
| Frontend | Vanilla JS + HTML5 + CSS3, kein npm, kein Build-Tool |
| Video | FFmpeg 7.1.3 serverseitig |
| KI | Kie.ai (Flux Kontext Pro/Max), Async Task-API |
| DB | SQLite 3 via PDO (WAL) |
| Hosting | Render.com (Docker) + IONOS (statisch) |
| Browser-Tests | Playwright (KEIN Claude-in-Chrome) |
| Lokale Shell | PowerShell (`; if ($?) {}` statt `&&`) |

---

## 7. Aktive Hauptprobleme (aus Master-Audit 2026-07-05)

Priorisiert. 🔴 = kritisch, 🟠 = hoch, 🟡 = mittel.

1. 🔴 **`api/contact-submit.php` ist spam-offen:** `Access-Control-Allow-Origin: *`,
   KEIN Rate-Limit, kein Honeypot — live auf IONOS erreichbar. `rate_limit.php` existiert
   bereits und muss nur eingebunden werden.
2. 🔴 **Produkt-Schisma:** Zwei Studios + zwei Geschäftsmodelle parallel (Abschnitt 4).
   Die Academy schult das Modell, das die Website nicht mehr verkauft.
3. 🔴 **Kristall-Farming-Vektor:** Registrierung OHNE E-Mail-Verifizierung schenkt
   50 Kristalle. Einzige Bremse: 10 Registrierungen/h/IP. Wird teuer, sobald Kristalle
   echte Kie.ai-Kosten decken.
4. 🟠 **Homepage-URL heißt `scene-editor-test.html`** — „test" in der kanonischen URL
   zerstört Premium-Wahrnehmung und SEO.
5. 🟠 **`availability.html` existiert nicht**, wird aber von `ionos-upload.mjs` referenziert
   und von `availability.php` (Render-Redirect) als Ziel verlinkt → toter Link.
6. 🟠 **Auth-Lockout-Bug** (`includes/auth.php:155`): Restzeit-Berechnung ergibt immer 0
   → „Bitte 0 Minuten warten."
7. 🟠 **Keine Security-Header** auf beiden Hosts (CSP, X-Frame-Options,
   X-Content-Type-Options, HSTS, Referrer-Policy fehlen komplett).
8. 🟠 **Repo ≠ Live:** 14 modifizierte + 42 untracked Dateien; IONOS-Stand nur manuell
   nachvollziehbar; kein sauberer Rollback-Punkt.
9. 🟡 **Performance Landing Page:** 7,6-MB-Video mit `preload="auto"` + 148-KB-HTML;
   crystals.html lädt 3 Icons à 1,7 MB.
10. 🟡 **SEO-Fundament fehlt:** keine robots.txt, keine sitemap.xml, kein JSON-LD;
    kontakt.html + academy.html ohne canonical.
11. 🟡 **Design-Abweichler:** kontakt.html (keine Aurora/Lightbars/Premium-Buttons),
    calendar.html (teilweise), crystals.html (1× Google-Fonts-CDN-Rest),
    Login/Dashboard/Profil (0 % CVS-Design).
12. 🟡 **innerHTML in 12 Dateien** trotz Projektverbot (aktuell nur statische Strings,
    kein akutes XSS — aber Regel-Erosion).
13. 🟡 **Stale Doku:** `memory/current-problems.md` (Stand 14.05.) listet gelöste Probleme
    als P0; PROJECT_STATUS.md, NEXT_SESSION_HANDOFF.md veraltet.

---

## 8. Kritische Risiken

| Risiko | Konsequenz bei Ignorieren |
|---|---|
| **Push auf `main` = sofortiger Render-Deploy** | Ungetesteter Code geht live. Niemals pushen ohne Björns Freigabe. |
| **SQLite auf Render-Disk enthält echte User** | Schema-Änderung ohne Backup = Datenverlust echter Accounts. |
| **Alte Studio-Suite löschen** | 24 Academy-Guides zeigen ins Leere; SEO-/User-Vertrauensschaden. |
| **`scene-editor-test.html` umbenennen ohne 301** | Alle internen Links, OG-Tags und externe Verweise brechen. |
| **Screenshots im Root pauschal löschen** | `CVS Header.png`, `luxmation-brief-ref.png` sind mutmaßlich Design-Referenzen. |
| **Assets umbenennen (Leerzeichen/Umlaute)** | Flächendeckende 404s, wenn nicht ALLE HTML-Referenzen simultan angepasst werden. |
| **`.htaccess` in storage/ oder data/ anfassen** | Einziger Direktzugriffs-Schutz für User-Daten fällt. |
| **CORS auf contact-submit blind festnageln** | Formular bricht, falls www/non-www/onrender-Varianten es nutzen. |
| **IONOS-Deploy-Skripte blind ausführen** | Referenzieren nicht-existente Dateien; Klick-Automation kann falsch zielen. |
| **mail() ohne SPF/DKIM-Prüfung für Transaktionsmails** | Verifizierungs-/Reset-Mails landen im Spam → Registrierung wirkt kaputt. |

---

## 9. Aktuelle Phase

# ✅ ARCHITECTURE LOCK COMPLETE (Bibliothek vollständig & konsistenzgeprüft)

**Stand 2026-07-05 (Konsistenz-Review abgeschlossen):** Die vollständige Projekt-Bibel
(15 Dokumente + [PROJECT_INDEX.md](PROJECT_INDEX.md) als Navigations-Index) ist vorhanden,
untereinander konsistent verlinkt und auf Widersprüche/Lücken geprüft. Das Zielbild
„EINE Plattform, eine Domain, ein Studio, eine Währung" ist architektonisch gelockt
(D-001 bis D-015 in [DECISION_LOG.md](DECISION_LOG.md)). Die Prüfung ergab nur kleinere,
nicht-blockierende Klärungspunkte (siehe Session-Report) — keine strukturellen Mängel.

**Der Phasen-Wechsel zu Phase 1 (Code) bleibt gated** auf zwei Dinge (unverändert,
MASTER_BLUEPRINT §11): (1) Björns Freigabe „Bibel freigegeben" und (2) die offenen
Entscheidungen O-1 bis O-6. „Architecture Lock Complete" heißt: **Planung/Dokumentation
ist fertig — NICHT, dass Codearbeit begonnen hat.** Bis zur Freigabe gilt weiterhin:
PLANEN. DOKUMENTIEREN. KEIN CODE.

**Das bedeutet konkret:**
- ✅ Erlaubt: Lesen, Analysieren, Pläne schreiben, diese Datei aktualisieren
- ❌ Gesperrt: Code-Änderungen, Löschungen, Verschiebungen, Commits, Pushes, Deployments
- ❌ Gesperrt: Ausführen der Quick Wins (Abschnitt 12) — sie sind geplant, nicht freigegeben
- 🔒 **Design Lock:** `immobilienvideos.html` bleibt Master-Design-Referenz. Keine neuen
  CSS-Hex-Werte, keine neuen Button-Klassen, keine Design-Experimente.
- **Phasenwechsel nur durch explizite Freigabe von Björn** (z. B. „Starte Woche 1" /
  „Quick Wins freigegeben").

**Phasen-Folge (verbindlich in [PRODUCT_ROADMAP.md](PRODUCT_ROADMAP.md)):**
P0 Architecture Lock (AKTIV) → P1 Fundament & Plattform-Shell → P2 Konto & Monetarisierung
→ P3 Produkt-Tiefe (Admin, Academy, Module) → P4 Premium-Polish & Wachstum

---

## 10. Master-Roadmap 30 Tage

> ⚠️ **Superseded (2026-07-05):** Verbindlich ist jetzt das Phasenmodell in
> [PRODUCT_ROADMAP.md](PRODUCT_ROADMAP.md) (P0–P4 mit Exit-Kriterien). Die Wochen-Skizze
> unten bleibt als inhaltliche Referenz erhalten — die Arbeitspakete sind in die
> Roadmap-Phasen überführt.

### Woche 1 — Fundament & Hygiene („Aufräumen vor dem Umbau")
- Alle 11 Quick Wins (Abschnitt 12)
- Git konsolidieren: 14 modifizierte Dateien reviewen → committen; 42 untracked triagieren
- IONOS-Deploy-Prozess dokumentieren; Dateilisten der Skripte korrigieren (availability.html raus)
- Memory-Docs auf Ist-Stand bringen (current-problems.md, PROJECT_STATUS.md)

### Woche 2 — Produkt-Entscheidung & Brand-Einheit
- **Björns Entscheidung umsetzen: EIN Studio, EIN Währungsmodell**
- Academy-Guides (24×) auf das gewählte Modell umschreiben
- `login.php`, `dashboard.php`, `profile.php`, `forgot-password.php` auf CVS-Designsystem
  (cvs-core.css, Syne/DM Sans, Nav/Footer-Master)
- kontakt.html + calendar.html Design-Alignment abschließen

### Woche 3 — Monetarisierung (der eigentliche SaaS-Schritt)
- E-Mail-System: SMTP/Mailgun + Registrierungs-Verifizierung (blockt Kristall-Farming)
- Stripe Checkout: Starter+-Abo + Kristall-Pakete (crystals.html: Warteliste → Kaufen)
- Shop: echte Produkte mit Checkout ODER ehrliche Repositionierung als Showcase
- Willkommens-/Reset-Mails über mailer.php

### Woche 4 — Premium-Polish & Betrieb
- Homepage nativ auf `/` (301-Kette von scene-editor-test.html erhalten!)
- JSON-LD (Organization, Product, FAQPage auf crystals/academy)
- Playwright-E2E-Suite für 5 Kern-Flows (Login, Upload→Render, KI-Bild, Kontakt, Kauf)
- Uptime-Monitoring auf `/api/health.php`, Portfolio-Bilder → WebP, CSP einführen

---

## 11. Reihenfolge der Umsetzung (verbindlich)

1. Repo einfrieren: aktuellen Stand committen (nach Review durch Björn) → Rollback-Punkt
2. Root-Cleanup (Müll-Dateien, Screenshots → archive/, Log löschen) → Commit
3. `contact-submit.php` absichern (Rate-Limit + Honeypot + CORS eingrenzen) → deployen
4. Performance-Fixes (Video-preload, Kristall-Icons) → IONOS-Upload
5. SEO-Basics (robots.txt, sitemap.xml, canonicals) → IONOS-Upload
6. Security-Header auf beiden Hosts
7. auth.php-Lockout-Bugfix + E-Mail-Verifizierung vorbereiten
8. **⛔ GATE: Produktentscheidung Studio/Währung durch Björn**
9. App-Seiten-Redesign (login/dashboard/profile im CVS-Design)
10. Academy-Content-Migration auf das gewählte Modell
11. Stripe-Integration
12. Homepage-URL-Migration mit 301s
13. E2E-Test-Suite + Monitoring

**Regel:** Kein Schritt wird übersprungen. Schritt 9–13 sind blockiert, bis Gate 8 entschieden ist.

---

## 12. Quick Wins — GEPLANT, NOCH NICHT AUSFÜHREN ⛔

| # | Maßnahme | Aufwand | Wirkung |
|---|---|---|---|
| 1 | Hero-Video `preload="metadata"` + poster-Bild | 10 min | LCP massiv besser |
| 2 | 3 Kristall-Icons (à 1,7 MB) → WebP à ~50 KB | 15 min | −5 MB auf crystals.html |
| 3 | rate_limit.php in contact-submit.php + Honeypot | 30 min | Spam-Kanal zu |
| 4 | robots.txt + sitemap.xml | 30 min | SEO-Grundstein |
| 5 | Lockout-Berechnung auth.php:155 fixen | 10 min | Korrekte Fehlermeldung |
| 6 | Google-Fonts-Zeile aus crystals.html entfernen | 2 min | DSGVO + Konsistenz |
| 7 | canonical + OG auf kontakt.html/academy.html | 15 min | SEO-Parität |
| 8 | 9 Null-Byte-Dateien + php-server.log löschen, Screenshots → archive/ | 20 min | Root professionell |
| 9 | `git worktree prune` + 8 Worktrees aufräumen | 10 min | −67 MB |
| 10 | Security-Header (X-Frame-Options, X-Content-Type-Options, Referrer-Policy) | 45 min | Security-Rating |
| 11 | immobilienvideos.html in Nav/Footer verlinken | 10 min | Beste Seite sichtbar |

**Ausführung erst nach Björns Freigabe („Quick Wins freigegeben" o. ä.).**

---

## 13. Produktive Dateien (behalten & pflegen)

**IONOS-Site (15):** scene-editor-test.html, immobilienvideos.html, portfolio.html,
shop.html, crystals.html, academy.html, prompt-generator.html, kontakt.html,
calendar.html, impressum.html, datenschutz.html, agb.html, widerruf.html,
cookies.html, index.html (Redirect)

**Render-App:** studio-demo.php, login.php, dashboard.php, profile.php,
forgot-password.php, index.php, crystals.php (serviert crystals.html),
Redirect-Stubs: academy.php, shop.php, portfolio.php, prompt-generator.php,
contact.php, availability.php, ki-videos.php

**API-Kern (13):** upload.php, analyze.php, replace-slot.php, render-final.php,
generate-ai.php, ai-status.php, health.php, cleanup.php, contact-submit.php,
settings/quality.php, auth/* (6 Dateien)

**Includes (alle 14):** auth.php, db.php, config.php, functions.php, rate_limit.php,
mailer.php, job_service.php, prompt-engine.php, guidance.php, header.php,
sidebar.php, footer.php, .htaccess

**Infrastruktur:** Dockerfile, docker/*, render.yaml, bin/cleanup-cron.php,
assets/css/*, assets/js/*, assets/fonts/*, .gitignore, .dockerignore

**Status UNKLAR (wartet auf Gate 8):** Legacy-Studio-Suite (11 PHP-Seiten) +
Legacy-API (16 Endpunkte) — funktionieren, werden von Academy beworben,
widersprechen aber dem Kristall-Modell.

---

## 14. Dateien, die wahrscheinlich archiviert werden müssen

**Null-Byte-Müll im Root (löschen, 9 Dateien):**
`'E-Mail`, `.cvs-nav-img`, `1)`, `70%`, `s.style.outline`, `window.scrollTo(0`, `{`, `{,+`, `{})`

**QA-Screenshots im Root (~29 MB, 33 Dateien → archive/qa-screenshots/):**
7× `Screenshot 2026-06-16 *.png`, 13× `kontakt-*.png`, 5× `crystals-wow-*.png` /
`crystals-final-wow.png`, 2× `impressum-preview*.png`, screenshot-academy.png,
screenshot-impressum-nav.png, crop-academy-nav.png, crop-calendar-nav.png,
cmp-contact-full.png, contact-fix-verify.png, portfolio-desktop.png,
`GUCKE doch selbewr-06-15 233455.png`, `Unten der abschnitt.png`

**Erst mit Björn klären, dann archivieren:** `CVS Header.png`, `luxmation-brief-ref.png`
(mutmaßlich Design-Referenzen)

**Dev-Artefakte:** php-server.log, .playwright-mcp/ (2 MB), 8 Worktrees in
.claude/worktrees/ (67 MB)

**Stale Doku (aktualisieren oder archivieren):** memory/current-problems.md,
NEXT_SESSION_HANDOFF.md, WORKSPACE_CLEANUP_*.md, PROJECT_STATUS.md

**Duplikate (konsolidieren):** assets/portfolio/ ↔ assets/showcase/portfolio/
(≥10 identische Dateinamen), ki-videos.html ↔ ki-videos.php,
impressum-preview.png ↔ impressum-preview2.png, 3 parallele Logo-Dateien

---

## 15. Dateien, die riskant sind

| Datei | Risiko |
|---|---|
| `api/contact-submit.php` | 🔴 Spam-offen (CORS *, kein Rate-Limit) — LIVE |
| `api/auth/register.php` | 🔴 50 Gratis-Kristalle ohne E-Mail-Verifizierung → Farming |
| `includes/auth.php` | 🟠 Zeile 155: Lockout-Restzeit-Bug („0 Minuten") |
| `ionos-upload.mjs`, `deploy-*.mjs` | 🟠 Referenzieren nicht-existente availability.html; Klick-Automation fragil |
| `php-server.log` | 🟠 Info-Leak, falls je auf Webspace hochgeladen |
| `docker/apache.conf` + IONOS-.htaccess | 🟠 Keine Security-Header definiert |
| 14 modifizierte + 42 untracked Git-Dateien | 🟠 Live ≠ Repo ≠ Commit — kein Rollback möglich |
| `storage/.htaccess`, `data/.htaccess` | 🟢 Korrekt — aber NIEMALS anfassen (einziger Schutz) |

---

## 16. Regeln für alle zukünftigen Arbeiten

### Harte Don'ts (Code)
- ❌ Kein `innerHTML` für User-Daten → `textContent` / DOM-API / `<template>`-Cloning
- ❌ Kein npm, kein Composer, kein Build-Tool, kein JS-Framework (React/Vue/jQuery)
- ❌ Keine neuen CSS-Hex-Werte → bestehende CSS-Variablen aus cvs-core.css verwenden
- ❌ Kein Google-Fonts-CDN → nur lokale Fonts (assets/fonts/)
- ❌ Kein S3/R2 in V1, kein cURL (file_get_contents + stream_context)
- ❌ Keine neuen Dateien im Root ohne Notwendigkeit (Screenshots → archive/, Skripte → bin/)

### Harte Do's (Code)
- ✅ `declare(strict_types=1)` in jeder PHP-Datei
- ✅ `escapeshellarg()` auf ALLEN Shell-Argumenten (bzw. `csf_ffmpeg_run`/`csf_ffprobe_run` nutzen)
- ✅ `csf_validate_path()` + realpath vor jedem File-Access
- ✅ `LOCK_EX` bei jedem meta.json-Write
- ✅ `htmlspecialchars((string)$var)` — immer mit (string)-Cast
- ✅ Toast-Feedback + Error-Box bei jeder API-Aktion
- ✅ Mobile: 44px Touch-Targets, Stack-Layout unter 600px
- ✅ Rate-Limiting auf jeden neuen öffentlichen POST-Endpunkt (rate_limit.php existiert!)

### Prozess-Regeln (Agenten)
1. **Diese Datei VOR jeder Arbeit vollständig lesen.**
2. **NIE committen/pushen ohne explizite Freigabe** — Push auf main = Live-Deploy auf Render!
3. **KEINE Dateien löschen** ohne explizite Anweisung von Björn.
4. **Design Lock respektieren:** immobilienvideos.html = Master-Referenz.
5. **Playwright für Browser-Tests** — kein Claude-in-Chrome.
6. **PowerShell-Syntax lokal:** `; if ($?) {}` statt `&&`.
7. **Nach jedem abgeschlossenen Arbeitspaket:** git status + diff-Zusammenfassung an Björn.
8. **Nach jedem Major Milestone:** CLAUDE.md aktualisieren (Phase, Stand, Probleme).
9. **Commit nur nach visuellem Approval** bei Design-Änderungen.
10. **Bei mehreren validen Optionen:** Optionen mit Empfehlung an Björn, nicht selbst entscheiden.

---

## 17. Session-Start-Protokoll — was Claude/Fable ZUERST lesen muss

**Jede neue Session beginnt in dieser Reihenfolge:**

1. **Diese CLAUDE.md** — vollständig. Insbesondere: Abschnitt 9 (aktive Phase!),
   Abschnitt 19 (offene Entscheidungen), Abschnitt 20 (Stop-Regeln).
2. **[MASTER_BLUEPRINT.md](MASTER_BLUEPRINT.md)** — das Zielbild (Module, Journeys,
   Prinzipien). Danach je nach Aufgabe das zuständige Bibel-Dokument:

   | Aufgabe betrifft… | Pflichtlektüre |
   |---|---|
   | Phasen/Reihenfolge | [PRODUCT_ROADMAP.md](PRODUCT_ROADMAP.md) |
   | UI/Seiten/Design | [DESIGN_SYSTEM.md](DESIGN_SYSTEM.md) + [COMPONENT_LIBRARY.md](COMPONENT_LIBRARY.md) |
   | KI-Funktionen | [AI_ENGINE.md](AI_ENGINE.md) |
   | Kristalle/Payment | [CRYSTAL_SYSTEM.md](CRYSTAL_SYSTEM.md) |
   | Datenbank/Storage | [DATABASE_ARCHITECTURE.md](DATABASE_ARCHITECTURE.md) |
   | API-Endpunkte | [API_ARCHITECTURE.md](API_ARCHITECTURE.md) |
   | Sicherheit | [SECURITY.md](SECURITY.md) |
   | Admin | [ADMIN_SYSTEM.md](ADMIN_SYSTEM.md) |
   | Deploy/Domain | [DEPLOYMENT.md](DEPLOYMENT.md) |
   | Tests/Abnahme | [QA_MASTERPLAN.md](QA_MASTERPLAN.md) |
   | Dateien/Struktur | [FILE_STRUCTURE.md](FILE_STRUCTURE.md) |
   | Grundsatzfragen | [DECISION_LOG.md](DECISION_LOG.md) — Entscheidung existiert evtl. schon! |

3. **`git status`** — hat sich der Arbeitsstand seit dem letzten Save-Point verändert?
4. **Dann:** Kurze Zusammenfassung an Björn: „Phase X aktiv, letzter Stand Y,
   nächster geplanter Schritt Z — soll ich fortfahren?"

⚠️ Alt-Doku (`memory/*.md`, TODO.md, PROJECT_STATUS.md, ARCHITECTURE.md, README_DEPLOY.md)
ist **superseded** (DECISION_LOG §4) — nur noch als historische Referenz nutzen.

**Niemals:** Projekt-Analyse von vorne starten, wenn diese Datei aktuell ist.
**Niemals:** Direkt mit Code-Änderungen beginnen, ohne die aktive Phase zu prüfen.

---

## 18. Nächster empfohlener Schritt

> **Stand 2026-07-05 (Architecture Lock):** Die Projekt-Bibel ist vollständig.
> Nächster Schritt liegt bei **Björn**:
> 1. Bibel lesen (Einstieg: MASTER_BLUEPRINT.md, dann DECISION_LOG.md §3)
> 2. Offene Entscheidungen **O-1 bis O-6** treffen (DECISION_LOG.md §3)
> 3. Freigabe aussprechen: **„Bibel freigegeben"** → Phase 1 (PRODUCT_ROADMAP.md) startet
>
> Erst nach dieser Freigabe wird wieder Code angefasst. Die Bibel-Dateien selbst sind
> noch **uncommitted** — der erste Commit von Phase 1 ist der Bibel-Commit (Rollback-Punkt).

---

## 19. Offene Entscheidungen für Björn

| # | Entscheidung | Optionen | Audit-Empfehlung |
|---|---|---|---|
| 1 | **Welches Studio bleibt?** | (a) studio-demo.php + Kristalle, Legacy archivieren · (b) studio-demo + Kristalle, Legacy als „Pro-Tools" integrieren · (c) BYOK behalten | **(a) oder (b)** — Kristalle sind das SaaS-Modell; BYOK skaliert nicht als Business |
| 2 | **Homepage-URL-Migration?** | scene-editor-test.html → index.html nativ (mit 301) — ja/nein/wann | **Ja, in Woche 4** (nicht früher — erst Fundament) |
| 3 | **Shop-Zukunft?** | (a) Echter Checkout (Stripe) · (b) Showcase ohne Kaufversprechen · (c) vorerst offline | **(b) jetzt, (a) nach Kristall-Launch** |
| 4 | **Screenshots CVS Header.png + luxmation-brief-ref.png** | Design-Referenz (→ memory/ oder assets/) oder Müll (→ archive/) | Klären — vermutlich Referenz |
| 5 | **Quick-Wins-Freigabe** | „Quick Wins freigegeben" → Woche 1 startet | Sofort möglich |
| 6 | **E-Mail-Versand-Weg** | Mailgun (API) vs. IONOS-SMTP vs. PHP mail() + msmtp | Mailgun oder SMTP — mail() ohne SPF/DKIM landet im Spam |

---

## 20. Stop-Regeln — wann Claude/Fable SOFORT nachfragen muss

**Arbeit sofort anhalten und Björn fragen, wenn:**

1. Eine Aktion einen **Push auf `main`** erfordern würde (= Live-Deploy auf Render).
2. Eine Aktion **Dateien löschen oder verschieben** würde, die nicht explizit in der
   freigegebenen Aufgabenliste stehen.
3. Eine Aktion die **SQLite-DB, das Schema oder die Render-Disk** verändern würde.
4. Eine Aktion die **Legacy-Studio-Suite** löschen/umbauen würde, bevor Gate 8
   (Produktentscheidung) entschieden ist.
5. Eine Aktion `scene-editor-test.html` **umbenennen** oder Redirect-Ketten ändern würde.
6. Eine Aktion **`.htaccess` in storage/ oder data/** berühren würde.
7. Eine Aktion **neue Kosten** verursachen würde (Render-Plan, Mailgun, Stripe, Domains).
8. Eine Aktion **Live-Systeme** betrifft (IONOS-Upload, Render-Env-Vars, DNS).
9. **Widersprüche** zwischen dieser Datei und dem tatsächlichen Code-/Repo-Zustand
   auffallen → erst melden, dann handeln.
10. Die Aufgabe einen **Phasenwechsel** bedeuten würde (z. B. von Planung zu Umsetzung),
    ohne dass Björn ihn explizit freigegeben hat.
11. **Sicherheitsrelevante Funde** (Secrets im Code, neue Angriffsflächen) — sofort melden,
    nie eigenmächtig „still fixen".
12. Eine Anweisung dieser Datei **mit einer User-Anweisung kollidiert** → User-Anweisung
    gewinnt, aber der Konflikt wird benannt.

---

## Anhang: Projekthistorie (Kurzfassung)

- **V0.1.0–V0.3.1 (April–Mai 2026):** MVP-Pipeline, KI-Integration, Auth-System,
  Dashboard — E2E-Test bestätigt (Session 7). Details: CHANGELOG.md.
- **Session 11 (Juni 2026):** Design Alignment Gate — scene-editor-test, portfolio,
  shop, crystals an immobilienvideos.html-Designsprache angeglichen.
- **Juni 2026:** kontakt.html neu gebaut, Phase-0-Nav-Updates, Legacy contact.php → Redirect.
- **2026-07-05:** Master-Audit (62 %) → diese Datei (v2.0) ersetzt die alte CLAUDE.md
  als Betriebshandbuch. Alte Session-Blöcke: siehe Git-History der CLAUDE.md.

*Ende des Betriebshandbuchs. Nächste Pflicht-Aktualisierung: nach Björns Entscheidungen (Abschnitt 19) oder nach Abschluss von Woche 1.*
