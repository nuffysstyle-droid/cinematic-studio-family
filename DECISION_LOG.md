# DECISION_LOG.md — Cinematic Vision Studio
# Architektur-Entscheidungsregister (ADR-Stil)

| Feld | Wert |
|---|---|
| Version | 1.0 (Architecture Lock, 2026-07-05) |
| Regel | Jede Grundsatzentscheidung bekommt einen D-Eintrag BEVOR sie umgesetzt wird. Einträge werden nie gelöscht, nur durch neue ersetzt (Status: Superseded). |
| Status-Semantik | 🔒 **LOCKED** = vom Expertenteam festgeschrieben, verbindlich mit Björns Bibel-Freigabe · 🟡 **OFFEN** = braucht Björns Wahl · ⚪ **SUPERSEDED** = ersetzt |

---

## 1. Gelockte Entscheidungen

### D-001 · Eine Plattform auf einer Domain 🔒
- **Kontext:** Zwei Hosts (IONOS statisch + Render App) erzeugen Session-Bruch, doppelte Deploys, CORS-Probleme, 15× duplizierte Navigation.
- **Entscheidung:** `cinematic-vision-studio.de` wird als Custom Domain auf den Render-Service gelegt. Render serviert ALLES (Marketing + App + API). IONOS bleibt nur Registrar/DNS (+ E-Mail-Postfach).
- **Begründung:** Ein Origin = eine Session = Login/Kristalle überall sichtbar; ein Deploy-Weg (Git); eine Security-Baseline; Playwright-Upload-Deploy entfällt.
- **Alternativen:** (a) Split behalten + Design angleichen — behebt Session-Bruch nicht; (b) alles zu IONOS — kein FFmpeg/Docker möglich. Beide verworfen.
- **Konsequenzen:** DNS-Umstellung + 301-Map nötig (DEPLOYMENT.md §5); statische Seiten werden PHP-Templates (D-005); Render trägt allen Traffic (Skalierungspfad Blueprint §9).

### D-002 · Ein Studio 🔒
- **Kontext:** `studio-demo.php` (Kristalle) und die Legacy-BYOK-Suite (11 Seiten) konkurrieren; die Academy bewirbt die Legacy-Suite.
- **Entscheidung:** `/studio` (heute studio-demo.php) ist DAS Studio. Die Legacy-Suite wird aus Navigation und Academy entfernt. Werthaltige Legacy-Werkzeuge (Trailer-Builder, TikTok-Tools, Element-Library, Prompt-Engine) werden — falls Björn Option (b) wählt (O-1) — später als Studio-Module unter dem Kristall-Modell neu integriert, nicht als eigene Seiten weitergeführt.
- **Begründung:** Zwei Studios = doppelte Pflege, verwirrende Journey, gespaltene Qualität. Das Kristall-Studio ist auth-integriert, designnäher und geschäftsmodellkonform.
- **Konsequenzen:** Academy-Rewrite (24 Guides); Legacy-Routen bekommen Redirects; kein Feature-Ausbau an Legacy-Seiten mehr (Code-Freeze für die Suite ab sofort).

### D-003 · Ein Bezahlmodell: Kristalle + Pläne 🔒
- **Kontext:** BYOK (User zahlt Kie.ai direkt) vs. Kristalle (wir verkaufen Guthaben).
- **Entscheidung:** Kristalle sind die einzige Abrechnungseinheit für KI-Leistungen. Pläne (Free/Starter/Pro) steuern Limits + Qualität; Kristalle steuern Verbrauch. Verkauf via Stripe.
- **Begründung:** BYOK ist kein skalierbares SaaS (kein Umsatz, Support-Last, Key-Sicherheitsrisiken beim User). Kristalle erlauben Marge, Bundles, Boni, klare UX.
- **Konsequenzen:** CRYSTAL_SYSTEM.md wird verbindlich; jede KI-Aktion MUSS buchen (D-009); AGB/Widerruf-Erweiterung nötig (O-6).

### D-004 · BYOK-Sunset 🔒
- **Entscheidung:** Der öffentliche BYOK-Pfad (api-key.php, test-key.php, Session-Key-Logik) wird eingestellt, sobald die Academy migriert ist. Bis dahin: eingefroren, nicht beworben.
- **Begründung:** Folge aus D-002/D-003. Zwei Modelle parallel = doppelte Doku, doppelter Support, inkonsistente Botschaft.
- **Konsequenzen:** Academy-Guides zu API-Key/Credits entfallen bzw. werden zu Kristall-Guides.

### D-005 · Server-Partials statt duplizierter Navigation 🔒
- **Kontext:** Nav/Footer existieren 15× als Kopie in statischem HTML; jede Änderung = 15 Edits + fragiler Upload.
- **Entscheidung:** Nav, Footer, Head-Meta werden PHP-Partials (`includes/partials/nav.php`, `footer.php`, `head.php`). Alle Seiten werden dünne PHP-Templates, die Partials einbinden.
- **Begründung:** Wartbarkeit (1 Datei statt 15); Auth-Zustand in der Nav wird erst dadurch möglich; SEO-Meta zentral steuerbar.
- **Konsequenzen:** Umwandlung .html → .php pro Seite (mechanisch, risikoarm, seitenweise abnehmbar); Rewrites halten alte URLs am Leben (D-008).

### D-006 · Tech-Stack bleibt: Flat PHP + Vanilla JS 🔒
- **Kontext:** Verlockung, für die „Plattform" auf Framework/SPA/Build-Chain zu wechseln.
- **Entscheidung:** PHP 8.2 + Apache + Vanilla JS + CSS bleiben für V1–V2 gesetzt. Kein npm, kein Composer, kein Framework.
- **Begründung:** Der Stack trägt das Produkt nachweislich (E2E-bestätigt); ein Rewrite würde Monate kosten und null Kundennutzen liefern; Ein-Personen-Betrieb bleibt beherrschbar.
- **Revisionstrigger:** Interaktivität des Studios übersteigt Vanilla-JS-Beherrschbarkeit (Mess-Signal: wiederholte State-Bugs) ODER Team wächst > 2 Devs.

### D-007 · SQLite bleibt, Postgres-Trigger definiert 🔒
- **Entscheidung:** SQLite (WAL) auf Render Disk bleibt Standard-DB. Migration zu Managed Postgres erst bei: > ~5.000 aktiven Usern ODER gehäuften SQLITE_BUSY-Fehlern ODER zweiter schreibender Instanz.
- **Begründung:** SQLite ist bei diesem Lastprofil (wenige Writes, ein Prozess) schnell und wartungsfrei; das Schema ist Postgres-kompatibel gehalten (DATABASE_ARCHITECTURE.md §7).
- **Konsequenzen:** Backup-Pflicht definieren (DATABASE_ARCHITECTURE.md §6) — heute ungeregelt!

### D-008 · Clean URLs + 301-Map 🔒
- **Entscheidung:** Zielrouten ohne Dateiendung (`/studio`, `/kristalle`, `/academy` …) via Apache mod_rewrite. Alle Alt-URLs (`scene-editor-test.html`, `*.html`, Legacy-`*.php`) erhalten permanente 301s. Home wandert von `scene-editor-test.html` auf `/`.
- **Begründung:** Premium-Wahrnehmung („test" in der Homepage-URL ist geschäftsschädigend), SEO-Konsolidierung, stabile Links für Werbung.
- **Konsequenzen:** Redirect-Tabelle wird in DEPLOYMENT.md §5 gepflegt; OG-Tags/Canonicals werden auf neue Routen umgestellt.

### D-009 · Kristalle werden echt gebucht 🔒
- **Kontext (Audit-Fund):** `csf_auth_spend_crystals()` existiert, wird aber von KEINEM Endpunkt aufgerufen — KI-Generierung ist heute faktisch gratis (nur Rate-Limit 10/h).
- **Entscheidung:** Jede kostenpflichtige Aktion (KI-Bild, später KI-Video, 1080p-Render für Free-User) bucht VOR Ausführung Kristalle ab; bei Fehlschlag der Aktion wird automatisch rückerstattet (Storno-Buchung im Ledger).
- **Begründung:** Ohne echte Buchung ist das Geschäftsmodell Fiktion und jeder KI-Call brennt Geld.
- **Konsequenzen:** Preisliste in CRYSTAL_SYSTEM.md §4 (Startwerte gesetzt, Feinjustierung O-4); Refund-Logik wird Teil der AI-Engine-Spezifikation (AI_ENGINE.md §6).

### D-010 · E-Mail-Verifizierung vor Kristall-Nutzung 🔒
- **Kontext:** Registrierung schenkt 50 Kristalle ohne Verifizierung → Farming-Vektor (Audit 🔴).
- **Entscheidung:** Die Spalte `users.email_verified` (existiert bereits!) wird scharf geschaltet: Unverifizierte Konten können das Studio ansehen, aber keine Kristalle ausgeben. Welcome-Bonus wird erst bei Verifizierung gutgeschrieben.
- **Konsequenzen:** E-Mail-Versandweg nötig (O-2); Registrierungs-Flow bekommt Verifizierungs-Schritt (Journey J1).

### D-011 · Design-System v1.0 = cvs-core.css 🔒
- **Entscheidung:** Die Token-Palette „Black · Electric Blue · Cinematic Gold" aus `assets/css/cvs-core.css` ist das verbindliche Design-System (DESIGN_SYSTEM.md). `immobilienvideos.html` ist die Referenz-Implementierung. `app.css` (Legacy) wird nicht weiterentwickelt und stirbt mit der Legacy-Suite.
- **Konsequenzen:** Login/Dashboard/Profil/Studio werden auf diese Tokens migriert; Insel-Farbwerte (z. B. `#f59e0b` in login.php) verschwinden.

### D-012 · Admin als Rolle, nicht als System daneben 🔒
- **Entscheidung:** `users` bekommt eine `role`-Spalte (`user`/`admin`); `/admin` ist ein geschütztes Modul derselben App mit eigenem Audit-Log. Kein separates Admin-Deployment, kein Fremd-Tool.
- **Begründung:** Ein-Betreiber-Realität; dieselbe Auth-/Security-Basis; minimale Angriffsfläche (ADMIN_SYSTEM.md §5).

### D-013 · Diese Bibel ersetzt die Alt-Doku 🔒
- **Entscheidung:** Die 15 Bibel-Dokumente im Root sind die einzige verbindliche Quelle. `ARCHITECTURE.md` (04/2026), `README_DEPLOY.md`, `PROJECT_STATUS.md`, `NEXT_SESSION_HANDOFF.md`, `WORKSPACE_CLEANUP_*.md` sowie die `memory/*.md`-Detaildateien gelten als **historisch** (superseded). Sie werden in der Cleanup-Phase nach `archive/docs-legacy/` verschoben — NICHT jetzt (Löschverbot).
- **Konsequenzen:** CLAUDE.md §17 verweist ausschließlich auf die Bibel; Asset-Duplikate (assets/portfolio ↔ assets/showcase/portfolio) werden in der Cleanup-Phase zu `assets/media/` konsolidiert.

### D-014 · Playwright ist das einzige Test-Framework 🔒
- **Entscheidung:** Alle Browser-/E2E-Tests laufen über Playwright (QA_MASTERPLAN.md). Kein Claude-in-Chrome, keine zweite Test-Infrastruktur.

### D-015 · Security-Baseline verbindlich 🔒
- **Entscheidung:** (1) Header-Set auf jeder Antwort (SECURITY.md §4); (2) CSRF-Token für alle state-changing Browser-POSTs zusätzlich zu SameSite=Lax; (3) Rate-Limit-Pflicht für jeden öffentlichen POST; (4) `innerHTML`-Verbot gilt auch für statische Strings in NEUEM Code (Bestand wird bei Berührung migriert).
- **Begründung:** Audit-Befunde (offene Kontakt-API, fehlende Header); Regel-Erosion stoppen.

---

## 2. Kürzel-Konventionen

- **D-xxx** = gelockte Architektur-Entscheidung · **O-x** = offene Entscheidung (Björn) · **W-x** = Widerspruch (Blueprint §7)
- Neue Einträge: fortlaufend nummerieren, niemals umnummerieren.

---

## 3. Offene Entscheidungen (Björn) 🟡

| # | Frage | Optionen | Team-Empfehlung | Blockiert |
|---|---|---|---|---|
| O-1 | Legacy-Werkzeuge: archivieren oder als Studio-Module integrieren? | (a) archivieren · (b) integrieren (Phase 3+) | (b) für Trailer/TikTok/Elements — echter Produktwert; Rest (a) | Phase-3-Scope |
| O-2 | E-Mail-Versandweg | Mailgun · IONOS-SMTP · mail()+msmtp | Mailgun (Zustellbarkeit, DKIM einfach) — IONOS-SMTP als kostenlose Alternative | D-010, Phase 2 |
| O-3 | Shop-Zukunft | (a) echter Checkout · (b) Showcase · (c) offline | (b) jetzt, (a) nach Kristall-Launch | Phase 3 |
| O-4 | Preise: €-Beträge für Kristall-Pakete & Pläne | siehe CRYSTAL_SYSTEM.md §5 (Vorschlag) | Vorschlag annehmen, nach 60 Tagen Daten justieren | Stripe-Setup |
| O-5 | Domain-Umzug: Zeitpunkt der DNS-Umstellung | sofort in Phase 1 · nach Phase 2 | Ende Phase 1 (erst Plattform-Shell, dann Umzug) | D-001-Ausführung |
| O-6 | Rechtliches für Verkauf (AGB Kristalle/Abo, Widerruf digitale Güter, USt) | Steuerberater/Anwalt einbinden | vor Stripe-Golive zwingend klären | Phase-2-Go-Live |

---

## 4. Superseded ⚪

| Alt | Ersetzt durch |
|---|---|
| ARCHITECTURE.md (April 2026) | MASTER_BLUEPRINT.md + API/DATABASE_ARCHITECTURE.md |
| README_DEPLOY.md | DEPLOYMENT.md |
| memory/roadmap.md, memory/current-problems.md, PROJECT_STATUS.md, TODO.md (Phasen-Teile) | PRODUCT_ROADMAP.md + CLAUDE.md |
| memory/design-audit.md, memory/workflow-design-reference.md | DESIGN_SYSTEM.md + COMPONENT_LIBRARY.md |
| CLAUDE.md v2.0 „30-Tage-Masterplan" (Abschnitt 10) | PRODUCT_ROADMAP.md (Phasenmodell) — CLAUDE.md verweist |
