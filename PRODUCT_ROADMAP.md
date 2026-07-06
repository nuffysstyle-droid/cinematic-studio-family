# PRODUCT_ROADMAP.md — Cinematic Vision Studio
# Produkt-Roadmap: von 62 % zur Premium-Plattform

| Feld | Wert |
|---|---|
| Version | 1.0 (Architecture Lock, 2026-07-05) |
| Basis | Master-Audit 62 % · Zielbild: MASTER_BLUEPRINT.md |
| Regel | Eine Phase startet erst nach Björns Freigabe und endet mit erfüllten Exit-Kriterien. Kein Phasen-Mix. |

---

## Phasenübersicht

| Phase | Name | Kern-Ergebnis | Dauer (Schätzung) |
|---|---|---|---|
| **0** | Architecture Lock | Diese Bibel, Björns Entscheidungen O-1…O-6 | läuft |
| **1** | Fundament & Plattform-Shell | Sauberes Repo, sichere APIs, EINE Domain, EINE Nav | 2–3 Wochen |
| **2** | Konto & Monetarisierung | E-Mail-Verifizierung, echte Kristall-Buchung, Stripe | 2–3 Wochen |
| **3** | Produkt-Tiefe | Admin, Academy-Rewrite, Studio-Module (O-1), Shop-Entscheid | 3–4 Wochen |
| **4** | Premium-Polish & Wachstum | SEO/JSON-LD, Performance, E2E-Suite, Monitoring, Marketing | fortlaufend |

---

## Phase 0 — Architecture Lock (AKTIV)

**Scope:** Projektbibliothek (15 Dokumente), Entscheidungsregister, keinerlei Code.
**Exit-Kriterien:**
- [ ] Bibel vollständig (alle 15 Dokumente vorhanden)
- [ ] Björn: „Bibel freigegeben"
- [ ] O-1 bis O-6 entschieden (mindestens O-2, O-4, O-5 für Phase-1/2-Planungssicherheit)

---

## Phase 1 — Fundament & Plattform-Shell

**Ziel:** Aus zwei Websites wird EINE Plattform-Hülle. Noch keine neuen Features.

**Arbeitspakete (Reihenfolge verbindlich):**
1. **Repo-Freeze & Hygiene** — Ist-Stand committen (Rollback-Punkt), Null-Byte-Müll +
   Screenshots archivieren, Worktrees prunen, Doku-Altbestand als superseded markieren
2. **Sofort-Sicherheit** — contact-submit: Rate-Limit + Honeypot + CORS-Eingrenzung;
   auth.php-Lockout-Bugfix; Security-Header-Set (SECURITY.md §4)
3. **Sofort-Performance** — Hero-Video preload/poster; Kristall-Icons → WebP;
   Google-Fonts-Rest entfernen
4. **SEO-Fundament** — robots.txt, sitemap.xml, fehlende canonicals
5. **Plattform-Shell** — Partials (nav/footer/head) bauen; alle IONOS-Seiten zu
   PHP-Templates auf Render migrieren; Clean-URL-Rewrites + 301-Map
6. **Domain-Umzug (O-5)** — Custom Domain auf Render, DNS bei IONOS umstellen,
   IONOS-Webspace stilllegen (Redirect-Fallback)

**Exit-Kriterien:**
- [ ] Eine Domain serviert alle Seiten, Nav/Footer aus EINEM Partial
- [ ] Login-Status + Kristall-Pill in der Nav auf JEDER Seite sichtbar
- [ ] Alle Alt-URLs antworten mit 301 auf neue Routen (Stichprobe 20 URLs)
- [ ] contact-submit nicht mehr anonym massenaufrufbar (Rate-Limit-Test)
- [ ] Lighthouse Mobile Home ≥ 80 Performance (heute geschätzt < 50)
- [ ] Kein Playwright-Upload-Deploy mehr nötig

**Explizit NICHT in Phase 1:** Stripe, E-Mail-System, Admin, Academy-Rewrite, Redesign Login/Dashboard.

---

## Phase 2 — Konto & Monetarisierung

**Ziel:** Aus Besuchern werden verifizierte Kunden; aus Kristallen wird Umsatz.

**Arbeitspakete:**
1. E-Mail-System (O-2): Versand-Infrastruktur + Templates (Verifizierung, Reset, Kaufbeleg)
2. Verifizierungspflicht (D-010): Flow, `email_verified` scharf, Welcome-Bonus bei Verifizierung
3. **Echte Kristall-Buchung (D-009):** KI-Bild bucht ab, Fehlschlag erstattet; Balance-UX im Studio
4. Stripe: Checkout (Pakete + Starter-Abo), Webhook → Ledger, Kaufhistorie im Dashboard
5. Rechtliches (O-6): AGB/Widerruf/Preisangaben aktualisiert — **Blocker für Go-Live**
6. App-Design-Angleichung: login/dashboard/profil auf Design-System (D-011)

**Exit-Kriterien:**
- [ ] Testkauf mit Stripe-Testkarte: Geld → Kristalle → KI-Bild → Render (durchgängig)
- [ ] Unverifiziertes Konto kann keine Kristalle ausgeben (Test)
- [ ] Jede KI-Aktion erzeugt exakt eine Ledger-Buchung (Stichprobe + Storno-Test)
- [ ] Login/Dashboard/Profil bestehen Design-Review gegen Referenzseite

---

## Phase 3 — Produkt-Tiefe

**Ziel:** Betreibbarkeit + Inhalte + Modulausbau.

**Arbeitspakete:**
1. Admin-Modul (ADMIN_SYSTEM.md): Kennzahlen, User-Verwaltung, Kristall-Gutschrift, Job-Monitor, Audit-Log
2. Academy-Rewrite: 24 Guides auf Kristall-Studio; neue Guide-Struktur (Basics/Creator/Pro)
3. Studio-Module gemäß O-1 (empfohlen: Trailer-Builder, TikTok-Formate, Element-Library — als Tabs/Modi im Studio, kristallbasiert)
4. Shop gemäß O-3
5. Legacy-Sunset: BYOK-Routen → Redirects; Legacy-Code nach `archive/legacy-app/`

**Exit-Kriterien:**
- [ ] Admin kann ohne SSH/SQL: User finden, Kristalle gutschreiben, Job einsehen, User sperren
- [ ] Kein Academy-Guide verweist mehr auf BYOK/api-key
- [ ] Legacy-Seiten sind aus dem öffentlichen Raum verschwunden (301 + Archiv)

---

## Phase 4 — Premium-Polish & Wachstum (fortlaufend)

**Themen:** JSON-LD-Strukturdaten; Portfolio → WebP + Lazyload; CSP-Rollout (erst Report-Only);
Playwright-E2E-Suite als Deploy-Gate (QA_MASTERPLAN.md); Uptime-/Fehler-Monitoring;
Conversion-Optimierung (A/B auf Hero/CTA/Preisseite); Content-Marketing über Prompt Master
(SEO-Magnet) und Academy; KI-Video-Generierung als neues Kristall-Produkt (AI_ENGINE.md §8).

**Dauerhafte KPIs (ab Phase 2 gemessen):**
| KPI | Ziel |
|---|---|
| Registrierung → erster Render | < 10 Min, Quote > 40 % |
| Free → Paid | ≥ 3 % in 90 Tagen |
| Monatlich aktive Creator | wachsend, ≥ 2 Renders/User/Monat |
| Render-Fehlerquote | < 2 % |
| Uptime | ≥ 99,5 % |

---

## Meilenstein-Karte (Kurzansicht)

```
P0 ── Bibel ── Björn-Freigabe ▶ P1 ── Hygiene ── Security ── Shell ── DOMAIN LIVE ▶
P2 ── E-Mail ── Kristall-Buchung ── STRIPE LIVE (nach O-6!) ▶
P3 ── Admin ── Academy neu ── Module ▶ P4 ── Polish/Growth (Dauerbetrieb)
```

**Pflege:** Nach jedem Phasen-Abschluss wird dieses Dokument aktualisiert
(Exit-Kriterien abgehakt, Learnings notiert) und CLAUDE.md §9 (Phase) umgestellt.
