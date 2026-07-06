# QA_MASTERPLAN.md — Cinematic Vision Studio
# Qualitätssicherung: Teststrategie, E2E-Suite, Budgets, Deploy-Gates

| Feld | Wert |
|---|---|
| Version | 1.0 (Architecture Lock, 2026-07-05) — D-014 |
| Werkzeug | Playwright (einziges Browser-Test-Framework) · PHP-Checks via CLI |
| Prinzip | Wenige, harte Tests auf den Geld- und Vertrauenspfaden — statt hundert brüchiger UI-Tests. |

---

## 1. Teststrategie (pragmatische Pyramide)

| Ebene | Was | Wann |
|---|---|---|
| Smoke (automatisch) | 5 Kern-Flows (§2) gegen lokalen Server | vor JEDEM Push (Deploy-Gate §7) |
| Post-Deploy-Smoke | Home lädt, Login-Seite lädt, `/api/health.php` ok:true | nach jedem Deploy gegen Live |
| Design-Review (manuell) | Checkliste §5 gegen Referenzseite | bei jeder Seiten-Migration/-Änderung |
| Regression (erweitert) | volle E2E-Suite + Responsive-Matrix | vor Phasen-Abschluss |
| Sicherheits-Checks | Negativtests §4 | Phase-Abschluss + nach Security-Änderungen |

Unit-Tests: nur für reine Funktionen mit Geld-Logik (Preis-Map, Ledger-Helfer) als
schlanke PHP-CLI-Asserts (`bin/tests/*.php`) — kein Framework-Zwang (D-006).

## 2. Die 5 Kern-Flows (Playwright, Sperr-Kriterium)

| # | Flow | Muss-Assertions |
|---|---|---|
| F1 | Registrieren → verifizieren → einloggen | Konto entsteht; ohne Verifizierung keine 💎 (ab Phase 2); nach Verifizierung Balance 50; Login setzt Session; Lockout-Text nennt echte Minuten (Bug L5) |
| F2 | Upload → Analyse → Render → Download | test_video.mp4 (5 s): 2 Slots erkannt; Render endet `done`; MP4 abrufbar; Job-Lock: zweiter Render parallel → 409 |
| F3 | KI-Bild | Prompt → task_id; Poll bis success; Slot-Thumb ersetzt; 💎: −5 gebucht (Phase 2); simulierter Fehlschlag → Refund-Buchung |
| F4 | Kauf (ab Phase 2, Stripe-Testmode) | Paket → Checkout → Test-Karte → Webhook → Balance +100; doppelter Webhook-Replay → KEINE Doppelgutschrift |
| F5 | Kontakt-Briefing | valide Daten → ok:true; Honeypot gefüllt → abgelehnt; 6. Request/h → 429 |

Testdaten: eigene Test-User (`e2e+<ts>@example.com`), lokale DB-Kopie — NIE gegen
Produktions-DB schreiben. Test-Assets: `test_video.mp4` klein im Repo unter `bin/tests/assets/`.

## 3. Responsive- & Browser-Matrix

Viewports: 375×812 (mobil) · 768×1024 (Tablet) · 1440×900 (Desktop).
Browser: Chromium (Pflicht), WebKit (Stichprobe je Phase). Prüfpunkte mobil:
Burger-Menü öffnet/schließt, CTA erreichbar, kein horizontales Scrollen, 44px-Targets.

## 4. Sicherheits-Negativtests (je Phase-Abschluss)

Ohne Login auf `/api/v1/studio/*` → 401 · als `user` auf `/admin` → 404 ·
Upload .php-als-.mp4 → abgelehnt (finfo) · Pfad-Traversal `job_id=../` → abgelehnt ·
CSRF-loser POST (ab Phase 2) → 403 · Security-Header vorhanden (curl-Check-Skript) ·
SQLi-Probe in Login (`' OR 1=1--`) → normales Fehlverhalten.

## 5. Design-Review-Checkliste (pro migrierte Seite, gegen `immobilienvideos.html`)

☐ Nav/Footer aus Partial, Links korrekt, aktiver Zustand ☐ Grain .28/z-0, Aurora, Lightbars
☐ Nur Token-Farben (Stichprobe DevTools), nur lokale Fonts ☐ Buttons `.btn-cvs--gold/--ghost`
☐ Reveals mit Stagger, kein Layout-Shift ☐ Title/Description/Canonical/OG gesetzt
☐ Mobil-Check (§3) ☐ Konsole fehlerfrei ☐ Screenshot als Abnahme-Artefakt → `archive/qa-screenshots/`
(NIE ins Root — Audit-Lektion!)

## 6. Performance-Budgets (hart ab Phase 1)

| Metrik | Budget |
|---|---|
| LCP mobil (Home) | < 2,5 s (Lighthouse-Throttling) |
| HTML+CSS+JS pro Seite (ohne Medien) | ≤ 300 KB |
| Einzelbild | ≤ 200 KB (WebP), Icons ≤ 60 KB |
| Hero-Video | `preload="metadata"` + poster; Autoplay nur muted |
| Lighthouse Performance mobil | ≥ 80 (Home, Kristalle, Studio-Login) |
| CLS | < 0,1 (width/height auf Bildern) |

## 7. Deploy-Gate (Pflicht-Reihenfolge vor jedem `git push origin main`)

1. Smoke-Suite F1–F5 lokal grün (Playwright headless)
2. Geänderte Seiten: Design-Checkliste §5
3. `git status` sauber erklärbar (keine Überraschungs-Dateien)
4. Diff-Zusammenfassung an Björn → **explizite Freigabe**
5. Push → Render-Deploy beobachten → Post-Deploy-Smoke gegen Live
6. Rot? → Render Rollback (DEPLOYMENT §3) → Ursache VOR erneutem Versuch fixen

## 8. Fehler-Triage

| Sev | Definition | Reaktion |
|---|---|---|
| S1 | Geld/Daten falsch (💎-Doppelbuchung, Datenleck), Plattform down | sofort: Rollback/Endpunkt aus, Björn informieren |
| S2 | Kern-Flow gebrochen (F1–F5) | Fix vor jedem weiteren Feature |
| S3 | Funktionsfehler mit Workaround | ins Phasen-Backlog |
| S4 | Kosmetik | Sammel-PR |

Jeder S1/S2 erhält einen Kurz-Eintrag in DECISION_LOG (Ursache → Gegenmaßnahme),
damit dieselbe Fehlerklasse nicht zweimal passiert.
