# MASTER_BLUEPRINT.md — Cinematic Vision Studio
# Die Plattform-Gesamtarchitektur (Projekt-Bibel, Leitdokument)

| Feld | Wert |
|---|---|
| Dokument | MASTER_BLUEPRINT.md — Leitdokument der Projektbibliothek |
| Version | 1.0 (Architecture Lock) |
| Datum | 2026-07-05 |
| Status | 🔒 Architecture Locked — verbindlich nach Freigabe durch Björn |
| Autoren | Expertenteam-Session (CEO, CTO, Architekten, UX/UI, SaaS, Security, DevOps, DB, Marketing) |
| Übergeordnet | CLAUDE.md (Betriebshandbuch) — dieses Dokument definiert das ZIELBILD |

---

## 1. Executive Summary

Cinematic Vision Studio (CVS) ist ab sofort **EIN Produkt**: eine Premium Creator-SaaS-Plattform,
auf der Familien und Creator cinematic Videos in unter 10 Minuten produzieren — Upload,
KI-Veredelung, Render, Download, alles im Browser.

Der heutige Zustand (zwei Hosts, zwei Studios, zwei Bezahlmodelle, 15 duplizierte statische
Seiten) wird in eine **einheitliche Plattform auf einer Domain** überführt:

> **Eine Domain. Ein Design. Eine Navigation. Ein Studio. Eine Währung. Ein Login.**

Dieses Dokument definiert das Zielbild. Die Migration dorthin ist in
[PRODUCT_ROADMAP.md](PRODUCT_ROADMAP.md) phasiert, jede Grundsatzentscheidung ist in
[DECISION_LOG.md](DECISION_LOG.md) begründet.

---

## 2. Plattform-Prinzipien (nicht verhandelbar)

| # | Prinzip | Bedeutung |
|---|---|---|
| P1 | **Ein Produkt** | Kein Bereich fühlt sich wie eine separate Website an. Kein Host-Wechsel, kein Design-Bruch, kein doppelter Workflow. |
| P2 | **Eine Domain** | `cinematic-vision-studio.de` ist die einzige öffentliche Adresse. Die `onrender.com`-URL verschwindet aus jeder Nav, jedem Link, jedem OG-Tag (bleibt nur technischer Origin). |
| P3 | **Ein Design-System** | Tokens und Komponenten aus [DESIGN_SYSTEM.md](DESIGN_SYSTEM.md) / [COMPONENT_LIBRARY.md](COMPONENT_LIBRARY.md). Referenz-Implementierung: `immobilienvideos.html`. |
| P4 | **Eine Navigation** | Ein serverseitig eingebundenes Nav/Footer-Partial (D-005) statt 15 duplizierter Kopien. Eine Änderung = eine Datei. |
| P5 | **Ein Studio** | `studio-demo.php` wird DAS Studio (`/studio`). Die Legacy-BYOK-Suite wird stillgelegt bzw. modulweise ins Studio migriert (D-002). |
| P6 | **Eine Währung** | Kristalle sind die einzige Abrechnungseinheit für KI-Leistungen. BYOK endet (D-003, D-004). Details: [CRYSTAL_SYSTEM.md](CRYSTAL_SYSTEM.md). |
| P7 | **Einfachheit vor Technik** | Flat PHP + Vanilla JS bleibt (D-006). Kein Framework, kein Build-Step, solange die Plattform damit skaliert. Komplexität muss sich ihren Platz verdienen. |
| P8 | **Sicherheit by Default** | Jeder neue Endpunkt: Rate-Limit + Validierung + Escaping. Baseline: [SECURITY.md](SECURITY.md). |

---

## 3. Das Produkt: Module der Plattform

CVS besteht aus **10 Modulen**. Jedes Modul hat einen Zweck, einen Platz in der Navigation
und einen definierten Reifegrad.

| Modul | Route (Ziel) | Zweck | Zugang | Reifegrad heute |
|---|---|---|---|---|
| **Home** | `/` | Markenauftritt, Konversion zum Studio | öffentlich | 74 % (liegt auf `/scene-editor-test.html`) |
| **Studio** | `/studio` | Kernprodukt: Upload → Slots → KI → Render → Download | Login (Free nutzbar) | 76 % (heute `studio-demo.php`) |
| **Dashboard** | `/dashboard` | Jobs, Kristall-Konto, Plan, Transaktionen | Login | 58 % (Design fehlt) |
| **Kristalle** | `/kristalle` | Pläne + Kristall-Pakete, Kauf (Stripe) | öffentlich | 72 % (heute Warteliste) |
| **Academy** | `/academy` | Lernen: Guides, die ins Studio deep-linken | öffentlich | 70 % (Inhalte veraltet: BYOK) |
| **Prompt Master** | `/prompt-master` | Prompt-Baukasten für KI-Video/Bild (heute `prompt-generator.html`) | öffentlich, Premium-Features später | 78 % |
| **Portfolio** | `/portfolio` | Showcase, Social Proof, Kategorien | öffentlich | 82 % |
| **Shop** | `/shop` | Templates/Assets (V2: echter Checkout) | öffentlich | 68 % (kein Kauf-Flow) |
| **Buchung + Kontakt** | `/buchung`, `/kontakt` | B2B-Strecke: Briefing, Termin | öffentlich | 60–65 % |
| **Admin** | `/admin` | Betrieb: User, Kristalle, Jobs, Moderation | Rolle `admin` | 0 % — geplant ([ADMIN_SYSTEM.md](ADMIN_SYSTEM.md)) |

**Sonderseiten:** `/immobilienvideos` (B2B-Landing + Design-Referenz), Rechtsseiten
(`/impressum`, `/datenschutz`, `/agb`, `/widerruf`, `/cookies`), `/login`, `/profil`.

---

## 4. Ziel-Architektur (technisch)

### 4.1 Heute (Ist) — der Zustand, den wir auflösen

```
┌──────────────── IONOS (statisch) ────────────────┐   ┌────────── Render.com (Docker) ──────────┐
│ cinematic-vision-studio.de                       │   │ cinematic-studio-family.onrender.com     │
│ 15× HTML (Nav/Footer 15× dupliziert)             │   │ PHP 8.2 + Apache + FFmpeg + SQLite        │
│ + api/contact-submit.php (mail, ungeschützt)     │──▶│ Studio, Login, Dashboard, API              │
│ Deploy: manuell (Playwright-Klick-Skript)        │   │ + Legacy-BYOK-Suite (11 Seiten, 16 APIs)  │
└──────────────────────────────────────────────────┘   │ Deploy: Git-Push auf main (auto)           │
                                                        └────────────────────────────────────────────┘
Probleme: 2 Hosts, 2 Designs, 2 Studios, 2 Bezahlmodelle, Session endet an der Host-Grenze.
```

### 4.2 Zielbild (Architecture Locked, D-001)

```
                    cinematic-vision-studio.de  (Custom Domain → Render)
┌───────────────────────────────────────────────────────────────────────────────┐
│                      RENDER WEB SERVICE (Docker: PHP 8.2 + Apache + FFmpeg)   │
│                                                                               │
│  Präsentationsschicht (PHP-Templates + shared Partials)                       │
│   /  /portfolio  /academy  /shop  /kristalle  /prompt-master  /kontakt  ...   │
│   /studio  /dashboard  /profil  /login          /admin (Phase 3)              │
│   → includes/partials/nav.php + footer.php  (EINE Nav für ALLES, D-005)       │
│                                                                               │
│  API-Schicht  /api/v1/*   (JSON, Rate-Limits, Auth)  → API_ARCHITECTURE.md    │
│                                                                               │
│  Dienste: FFmpeg-Pipeline · Kie.ai-Engine (AI_ENGINE.md) · Kristall-Ledger    │
│           Mailer (Verifizierung/Reset) · Stripe (Checkout + Webhook)          │
│                                                                               │
│  Daten:  SQLite (WAL) auf Persistent Disk  +  storage/ (Jobs, Uploads)        │
│          → DATABASE_ARCHITECTURE.md                                           │
└───────────────────────────────────────────────────────────────────────────────┘
   IONOS = nur noch Domain-Registrar + DNS (+ optional E-Mail-Postfach)
   Cron  = Render Cron-Service (Cleanup 03:00 UTC)
   301-Map: alte IONOS-Pfade (.html) → neue Routen (DEPLOYMENT.md §5)
```

**Warum das die richtige Architektur ist (Kurzbegründung, Details D-001):**
1. Ein Origin = eine Session = Login-Status überall sichtbar (Nav zeigt „eingeloggt", Kristall-Stand auf jeder Seite).
2. Nav/Footer/SEO/Analytics nur noch an einer Stelle pflegbar.
3. Der fragile Playwright-Upload-Deploy entfällt ersatzlos — deployt wird nur noch via Git.
4. `contact-submit.php` läuft hinter derselben Security-Baseline wie alle APIs.
5. Kein CORS-Problem mehr zwischen Formularen und API.

### 4.3 Nicht-Ziele (bewusst ausgeschlossen)

- ❌ Kein Microservice-Split, kein Kubernetes, kein S3/R2 in V1–V2 (Render Disk reicht; Trigger in DATABASE_ARCHITECTURE.md §7)
- ❌ Kein JS-Framework/SPA — die Plattform bleibt server-gerendert mit progressivem Vanilla JS (D-006)
- ❌ Keine Mehrsprachigkeit vor V3 (Markt: DACH)
- ❌ Keine native App — Mobile = responsive Web (Budgets: QA_MASTERPLAN.md §6)

---

## 5. Informationsarchitektur & Navigation

### 5.1 Globale Navigation (ein Partial, überall identisch)

```
[Logo CVS]   Home · Studio · Kristalle · Academy · Prompt Master · Portfolio · Shop · Kontakt · Buchung
                                                  [Login/Avatar]  [🎬 Studio starten — Gold-CTA]
```

Regeln:
- **Ausgeloggt:** Ghost-Button „Login" + Gold-CTA „Studio starten" (→ `/studio`, führt zur Login-Wall).
- **Eingeloggt:** Avatar-Menü (Dashboard, Profil, Logout) + Kristall-Pill (Balance, klickbar → `/kristalle`).
- **Aktiver Zustand:** `.nav-link.active` auf aktueller Route.
- **Mobile (<900px):** Burger → Vollbild-Overlay, gleiche Reihenfolge, CTA zuoberst, 44px-Targets.
- `immobilienvideos` erscheint nicht in der Hauptnav (B2B-Landing), aber im Footer unter „Leistungen".

### 5.2 Footer (ein Partial)

3 Spalten (Produkt / Ressourcen / Rechtliches) + Legal-Bottom — exakt die heutige
`.cvs-footer-master`-Struktur der Referenzseite. Footer nimmt zusätzlich auf:
Immobilienvideos, Social-Links.

### 5.3 Seitenhierarchie

```
Home
├─ Studio ──── Dashboard ── Profil            (App-Kern, Login-Bereich)
├─ Kristalle ─ Checkout (Stripe)              (Monetarisierung)
├─ Academy ─── Guide-Detail (Modal/Anchor)    (Aktivierung/Retention)
├─ Prompt Master                              (Acquisition-Magnet, SEO)
├─ Portfolio ─ Immobilienvideos               (Social Proof, B2B)
├─ Shop                                       (V2-Monetarisierung)
├─ Kontakt ─── Buchung                        (B2B-Funnel)
└─ Rechtliches (5 Seiten)
```

---

## 6. User Journeys (verbindlich geplant)

### J1 — Erstkontakt → erster Render („Time to Wow" < 10 Min)
1. Besucher landet auf `/` (organisch, Social, Ad) → Hero mit Video-Loop + Gold-CTA
2. Klick „Studio starten" → `/studio` → Login-Wall mit Register-Tab (Wertversprechen: „50 Kristalle geschenkt")
3. Registrierung (E-Mail + Passwort) → **Verifizierungs-Mail** (D-010) → Bestätigung
4. Studio-Onboarding: Demo-Video vorgeladen ODER eigener Upload (≤50 MB, ≤15 s)
5. Slot-Analyse → 1 Slot mit KI-Bild ersetzen (kostet Kristalle, Welcome-Guthaben deckt es)
6. Render 720p → Download + Share-Hinweis
7. Follow-up-Mail nach 24 h: „Dein nächstes Video" + Academy-Link
**KPI:** Registrierung→erster Render < 10 Min; Aktivierungsquote > 40 %.

### J2 — Wiederkehrender Creator (Kern-Loop)
Login → Dashboard (letzte Jobs, Balance) → „Weiterarbeiten" (Job-Restore via `?job_id=`)
oder neues Projekt → Render → Download. Bei Kristallmangel: Inline-Hinweis mit CTA `/kristalle`.
**KPI:** ≥2 Renders/Monat pro aktivem User.

### J3 — Kauf (Monetarisierung)
Trigger: Balance zu niedrig (Studio-Inline-CTA) oder `/kristalle` direkt →
Paket wählen → Stripe Checkout (hosted) → Webhook schreibt Ledger-Gutschrift →
Redirect zurück mit Erfolgs-Toast + neuer Balance. Fehlerpfad: Abbruch → zurück ohne Buchung.
**KPI:** Free→Paid Conversion ≥ 3 % in 90 Tagen.

### J4 — Lernen (Aktivierung/Retention)
`/academy` → Guide (kategorisiert: Basics/Creator/Pro) → jeder Guide endet mit
Deep-Link ins Studio mit vorgewähltem Kontext. **Alle 24 Guides werden auf das
Kristall-Studio umgeschrieben** (heute: BYOK-Anleitungen — Blocker für D-004).

### J5 — B2B (Immobilien/Unternehmen)
`/immobilienvideos` oder `/portfolio` → `/kontakt` (Briefing-Formular, 24-h-Zusage)
oder `/buchung` (Kalender) → Mail an Studio-Postfach → manuelle Betreuung.
**KPI:** ≥5 qualifizierte Briefings/Monat.

### J6 — Admin (Betrieb, Phase 3)
`/admin` → Kennzahlen (User, Renders, Kristall-Umlauf, Fehlerquote) → User-Detail
(Kristalle gutschreiben, Plan ändern, sperren) → Job-Monitor → Audit-Log.
Details: [ADMIN_SYSTEM.md](ADMIN_SYSTEM.md).

---

## 7. Gefundene Widersprüche & ihre Auflösung (Architecture Lock)

| # | Widerspruch (Ist) | Auflösung (Ziel) | Decision |
|---|---|---|---|
| W1 | Zwei Studios (studio-demo vs. BYOK-Suite) | Ein Studio unter `/studio`; Legacy-Werkzeuge werden Studio-Module oder entfallen | D-002 |
| W2 | Zwei Bezahlmodelle (Kristalle vs. BYOK) | Nur Kristalle; BYOK-Sunset inkl. Academy-Rewrite | D-003/D-004 |
| W3 | Kristalle werden angezeigt, aber **nie abgebucht** (kein Endpunkt ruft `csf_auth_spend_crystals`) | Jede KI-Aktion bucht Kristalle über den Ledger; Preise: CRYSTAL_SYSTEM.md §4 | D-009 |
| W4 | Zwei Hosts mit Session-Bruch | Eine Domain auf Render | D-001 |
| W5 | 15× duplizierte Nav/Footer in statischem HTML | Server-Partials | D-005 |
| W6 | Zwei CSS-Systeme (cvs-core.css vs. app.css) + Insel-Designs (login/dashboard) | Ein Design-System; app.css stirbt mit der Legacy-Suite | D-011 |
| W7 | Homepage-URL enthält „test" | `/` wird Home; 301-Map | D-008 |
| W8 | Zwei Portfolio-Asset-Bäume (assets/portfolio ↔ assets/showcase/portfolio) | Ein Baum: `assets/media/portfolio/` (Migration in Cleanup-Phase) | D-013 |
| W9 | Zwei Redirect-Mechanismen (ki-videos.html + ki-videos.php) | Rewrites zentral in Apache-Konfiguration | D-008 |
| W10 | `email_verified`-Spalte existiert, wird aber nirgends gesetzt/geprüft | Verifizierungspflicht vor Kristall-Nutzung | D-010 |

---

## 8. Fehlende Module (heute nicht vorhanden, im Zielbild eingeplant)

1. **Admin-System** — es gibt heute KEINE Möglichkeit, User zu verwalten, Kristalle
   gutzuschreiben oder Jobs zu inspizieren außer per SQL/SSH → [ADMIN_SYSTEM.md](ADMIN_SYSTEM.md)
2. **E-Mail-System** — Verifizierung, Passwort-Reset-Versand, Kaufbelege → Roadmap Phase 2
3. **Payment** — Stripe Checkout + Webhook + Rechnungsdaten → [CRYSTAL_SYSTEM.md](CRYSTAL_SYSTEM.md) §6
4. **Beobachtbarkeit** — Fehler-Aggregation, Uptime-Alarm, Render-Metriken → [DEPLOYMENT.md](DEPLOYMENT.md) §7
5. **Backup-Strategie** — SQLite-Sicherung ist heute ungeregelt → [DATABASE_ARCHITECTURE.md](DATABASE_ARCHITECTURE.md) §6
6. **Rechts-/Steuer-Baustein für Verkauf** — AGB-Erweiterung Kristalle/Abo, Widerruf digitale
   Güter, Rechnungsstellung (mit Björn/Steuerberater zu klären, O-6)

---

## 9. Skalierungspfad (wann wächst was)

| Stufe | Auslöser | Maßnahme |
|---|---|---|
| S0 (heute) | — | Render Starter, SQLite, Disk 1 GB |
| S1 | Disk > 70 % oder Renders > 50/Tag | Disk 5–10 GB (Live-Resize), FFmpeg-Preset-Tuning |
| S2 | RAM-Druck / Render-Warteschlangen | Render Standard-Plan; Job-Queue statt synchronem Render |
| S3 | > ~5.000 aktive User oder häufige SQLITE_BUSY | Migration SQLite → Managed Postgres (Schema kompatibel entworfen) |
| S4 | Video-KI (große Dateien, CDN-Bedarf) | Objekt-Storage (R2/S3) NUR für Exporte + CDN davor |
| S5 | Team > 1 Entwickler | CI-Pipeline mit Test-Gate (QA_MASTERPLAN.md §7) wird Pflicht |

Grundsatz: **Jede Stufe wird erst gezündet, wenn ihr Auslöser messbar eintritt.**

---

## 10. Projektbibliothek — Struktur der Bibel

| Dokument | Inhalt |
|---|---|
| [CLAUDE.md](CLAUDE.md) | Betriebshandbuch: Phase, Regeln, Session-Protokoll |
| **MASTER_BLUEPRINT.md** | Dieses Dokument: Zielbild, Module, Journeys, Prinzipien |
| [PRODUCT_ROADMAP.md](PRODUCT_ROADMAP.md) | Phasen, Meilensteine, Erfolgskriterien |
| [DESIGN_SYSTEM.md](DESIGN_SYSTEM.md) | Tokens, Typografie, Motion, Regeln |
| [COMPONENT_LIBRARY.md](COMPONENT_LIBRARY.md) | Komponentenkatalog mit Klassen & Zuständen |
| [AI_ENGINE.md](AI_ENGINE.md) | Kie.ai-Integration, Task-Lifecycle, Kosten, Zukunft |
| [DATABASE_ARCHITECTURE.md](DATABASE_ARCHITECTURE.md) | Schema, Dateispeicher, Backup, Postgres-Pfad |
| [API_ARCHITECTURE.md](API_ARCHITECTURE.md) | Endpunkt-Inventar, Konventionen, /api/v1-Ziel |
| [SECURITY.md](SECURITY.md) | Baseline, Bedrohungen, offene Punkte, Policies |
| [ADMIN_SYSTEM.md](ADMIN_SYSTEM.md) | Admin-Modul: Rollen, Funktionen, Datenmodell |
| [CRYSTAL_SYSTEM.md](CRYSTAL_SYSTEM.md) | Ökonomie, Preise, Ledger, Stripe, Anti-Abuse |
| [DEPLOYMENT.md](DEPLOYMENT.md) | Ist/Ziel-Deploy, Domain-Migration, Env, Rollback |
| [QA_MASTERPLAN.md](QA_MASTERPLAN.md) | Teststrategie, E2E-Suite, Budgets, Gates |
| [FILE_STRUCTURE.md](FILE_STRUCTURE.md) | Ist-Baum (annotiert) + Ziel-Baum |
| [DECISION_LOG.md](DECISION_LOG.md) | Alle Entscheidungen mit Begründung (ADR-Stil) |

**Pflegeregel:** Ändert eine Session etwas Grundsätzliches, wird ZUERST das betroffene
Bibel-Dokument aktualisiert, DANN implementiert. Die Bibel ist führend, nicht der Code.

---

## 11. Abnahme dieser Phase

Diese Planungsphase gilt als abgeschlossen, wenn:
1. Björn die Bibel gelesen und freigegeben hat („Bibel freigegeben"),
2. die offenen Entscheidungen O-1 bis O-6 (DECISION_LOG.md §3) getroffen sind,
3. CLAUDE.md die Freigabe + Phase „UMSETZUNG PHASE 1" dokumentiert.

Bis dahin gilt: **PLANEN. DOKUMENTIEREN. KEIN CODE.**
