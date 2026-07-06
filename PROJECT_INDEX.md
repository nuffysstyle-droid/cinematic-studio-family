# PROJECT_INDEX.md — Cinematic Vision Studio
# Inhaltsverzeichnis der Projektbibliothek (Navigations-Karte)

| Feld | Wert |
|---|---|
| Dokument | PROJECT_INDEX.md — Meta-Index der Projekt-Bibel |
| Version | 1.0 (Architecture Lock, 2026-07-05) |
| Zweck | Einstiegskarte: Welches Dokument beantwortet welche Frage? Was hängt von was ab? |
| Geltung | Die 15 Bibel-Dokumente sind die verbindliche Quelle (D-013). Dieser Index ist Navigationshilfe, kein zusätzlicher Beschluss. |
| Pflegeregel | Kommt ein Bibel-Dokument hinzu oder ändert seinen Zweck, wird ZUERST hier die Zeile aktualisiert. |

---

## 1. Wie man diese Bibliothek liest

**Reihenfolge bei jedem Session-Start (aus CLAUDE.md §17):**
1. [CLAUDE.md](CLAUDE.md) vollständig — Phase, Regeln, Stop-Regeln, offene Entscheidungen.
2. [MASTER_BLUEPRINT.md](MASTER_BLUEPRINT.md) — das Zielbild.
3. Danach das für die Aufgabe zuständige Fachdokument (Schnell-Map in §3).
4. `git status` prüfen, dann kurze Lage-Meldung an Björn.

**Grundsatz:** Die Bibel führt, der Code folgt. Wer etwas Grundsätzliches ändert,
aktualisiert erst das betroffene Dokument, dann den Code.

---

## 2. Die Bibliothek im Überblick (16 Dokumente)

> Legende Reifegrad: 📚 = verbindlich (Architecture Lock) · 🧭 = Navigations-/Meta-Dokument

### 2.1 Rahmen & Strategie

| Dokument | Zweck | Inhalt (Kurz) | Wann verwenden | Abhängigkeiten |
|---|---|---|---|---|
| 📚 [CLAUDE.md](CLAUDE.md) | Betriebshandbuch & Projektgedächtnis — die operativen Spielregeln | Aktive Phase, harte Do's/Don'ts, Stop-Regeln (§20), Risiken, Quick Wins, Session-Protokoll, offene Entscheidungen | **Immer zuerst**, vor jeder Aktion jeder Session | Rahmt alle; verweist auf MASTER_BLUEPRINT und alle Fachdocs |
| 📚 [MASTER_BLUEPRINT.md](MASTER_BLUEPRINT.md) | Leitdokument: das Zielbild „eine Plattform" | Prinzipien P1–P8, 10 Module, Ziel-Architektur, Informations­architektur/Nav, Journeys J1–J6, Widersprüche W1–W10, Skalierung S0–S5 | Grundsatz-/Scope-Fragen, Onboarding, „Wohin führt das Produkt?" | Untergeordnet CLAUDE; ruft alle Fachdocs auf; umgesetzt via DECISION_LOG |
| 📚 [PRODUCT_ROADMAP.md](PRODUCT_ROADMAP.md) | Reihenfolge: Phasen, Meilensteine, Abnahme | Phasen P0–P4, Arbeitspakete (verbindliche Reihenfolge), Exit-Kriterien, KPIs | „Was ist als Nächstes dran? In welcher Phase gehört das hin?" | MASTER_BLUEPRINT (Zielbild); DECISION_LOG (offene O-Entscheidungen als Phasen-Blocker) |
| 📚 [DECISION_LOG.md](DECISION_LOG.md) | Entscheidungsregister (ADR-Stil) — Gedächtnis der Grundsatz­entscheidungen | Gelockte Entscheidungen D-001…D-015, offene Entscheidungen O-1…O-6, Superseded-Liste | **Vor jeder Grundsatzfrage** — „Gibt es dazu schon eine Entscheidung?" | Wird aus allen Docs referenziert; O-Einträge blocken Roadmap-Phasen |

### 2.2 Gestaltung

| Dokument | Zweck | Inhalt (Kurz) | Wann verwenden | Abhängigkeiten |
|---|---|---|---|---|
| 📚 [DESIGN_SYSTEM.md](DESIGN_SYSTEM.md) | Verbindliches Design-System v1.0 | Farb-Tokens, Typografie (Syne/DM Sans), Atmosphäre-Schichten (Grain/Aurora/Lightbar), Motion, Responsive, A11y-Minimum | **Jede** UI-/Seitenarbeit, jede Farb-/Layout-Frage | Quelle: `cvs-core.css` + `immobilienvideos.html`; Partner COMPONENT_LIBRARY; beschlossen in D-011 |
| 📚 [COMPONENT_LIBRARY.md](COMPONENT_LIBRARY.md) | Komponentenkatalog (Klassen, Zustände) | Nav, Buttons, Struktur-, Karten-, Formular-, Studio-Komponenten mit Status ✅/🔁/🆕 | Neue UI bauen — „Welche Klasse/Komponente gibt es dafür?" | Erbt Tokens aus DESIGN_SYSTEM; Abnahme über QA_MASTERPLAN §5 |

### 2.3 Technische Fachdokumente

| Dokument | Zweck | Inhalt (Kurz) | Wann verwenden | Abhängigkeiten |
|---|---|---|---|---|
| 📚 [AI_ENGINE.md](AI_ENGINE.md) | KI-Architektur (Kie.ai Flux Kontext) | Task-Lebenszyklus, Endpunkt-Verträge, Fehler-Taxonomie, Refund-Regel, Kristall-Bepreisung, Ausbaustufen A–D | KI-Bild/-Video-Features, Provider-Fragen | CRYSTAL_SYSTEM (Preise/Buchung), API_ARCHITECTURE (Endpunkte), SECURITY (SSRF/Key); D-009 |
| 📚 [DATABASE_ARCHITECTURE.md](DATABASE_ARCHITECTURE.md) | Datenarchitektur SQLite + Dateispeicher | Ist-Schema, Ziel-Migrationen v2/v3, Ledger-Invarianten, Storage-Vertrag, **Backup** (heute ungeregelt 🔴), Postgres-Pfad | DB-/Schema-/Storage-/Backup-Arbeit | CRYSTAL_SYSTEM (Ledger-Wahrheit), DEPLOYMENT (Backup-Cron/Env); D-007 |
| 📚 [API_ARCHITECTURE.md](API_ARCHITECTURE.md) | API-Konventionen & Endpunkt-Inventar | Antwort-Envelope, HTTP-Codes, Ist-Inventar (Kern + Legacy 🧊), Ziel-Namensraum `/api/v1`, Rate-Limit-Matrix, Job-Datenvertrag | Jede API-Arbeit — „Wie sieht ein neuer Endpunkt aus?" | SECURITY (Rate-Limit/CSRF), alle Feature-Docs; Doku-Pflicht vor Implementierung |
| 📚 [SECURITY.md](SECURITY.md) | Sicherheits-Baseline & Maßnahmenplan | Ist-Stärken (nicht anfassen), Lücken L1–L9, Bedrohungsmodell, Header-Set, CSRF-Design, Anti-Spam, Secrets, DSGVO, Incident-Plan | **Jeder** Endpunkt, Header, Secret, Formular | API_ARCHITECTURE, DEPLOYMENT (HSTS/Domain/Env); beschlossen in D-015 |

### 2.4 Betrieb & Geschäft

| Dokument | Zweck | Inhalt (Kurz) | Wann verwenden | Abhängigkeiten |
|---|---|---|---|---|
| 📚 [CRYSTAL_SYSTEM.md](CRYSTAL_SYSTEM.md) | Kristall-Ökonomie & Monetarisierung | Ökonomie-Modell (Pläne × Kristalle), Ledger + action-Katalog, UX-Regeln, Preisliste, Pakete/Pläne (Vorschlag), Stripe-Flow, Missbrauchsschutz | Payment, Preise, Ledger, Stripe | DATABASE (ledger/purchases), AI_ENGINE (KI-Preise), DEPLOYMENT (Stripe-Env); D-003/009/010 |
| 📚 [ADMIN_SYSTEM.md](ADMIN_SYSTEM.md) | Admin-Modul (Betrieb ohne SSH/SQL) | Rollen/Zugriff, Seitenstruktur `/admin`, Admin-API, Sicherheits-Invarianten, MVP-Zuschnitt (Phase 3) | Support-/Betriebs-Funktionen, Phase-3-Planung | DATABASE (role/audit_log), SECURITY (Rolle/CSRF), API_ARCHITECTURE (`/api/v1/admin/*`); D-012 |
| 📚 [DEPLOYMENT.md](DEPLOYMENT.md) | Deploy, Domain-Migration, Rollback | Ist zwei Deploy-Welten, Env-Referenz, Ziel-Pipeline, Domain-Umzug (O-5), 301-Map, Betriebskalender, Monitoring | Deploy, Domain, Env-Var, Rollback, Cron | SECURITY (Header/HSTS), DATABASE (Backup-Job), DECISION_LOG (O-5); D-001/008 |
| 📚 [QA_MASTERPLAN.md](QA_MASTERPLAN.md) | Teststrategie & Deploy-Gates | Test-Pyramide, 5 Kern-Flows F1–F5, Responsive-Matrix, Security-Negativtests, Design-Review-Checkliste, Performance-Budgets, Deploy-Gate | **Vor jedem Push**, jede Seiten-Migration, jeder Phasen-Abschluss | Testet alle Flows aller Docs; nutzt DESIGN_SYSTEM §-Checkliste; D-014 |

### 2.5 Ordnung & Navigation

| Dokument | Zweck | Inhalt (Kurz) | Wann verwenden | Abhängigkeiten |
|---|---|---|---|---|
| 📚 [FILE_STRUCTURE.md](FILE_STRUCTURE.md) | Ist-/Ziel-Dateibaum & Ordnungsregeln | Annotierter Ist-Baum (✅/🧊/🗑/📚), Ziel-Baum, dauerhafte Ordnungsregeln (Root heilig, kebab-case, ein Ort pro Sache) | Dateien anlegen/verschieben/aufräumen, Cleanup-Phase | DEPLOYMENT (Redirect-Map bei Umbenennung); D-013 |
| 🧭 [PROJECT_INDEX.md](PROJECT_INDEX.md) | **Dieses Dokument** — Navigations-Karte der Bibliothek | Zweck/Inhalt/Verwendung/Abhängigkeiten je Dokument, Aufgaben-Map, Historik-Übersicht | Einstieg, Orientierung, „Welches Dokument brauche ich?" | Verweist auf alle 15 Bibel-Dokumente |

---

## 3. Schnell-Map: Aufgabe → Pflichtlektüre

| Aufgabe betrifft… | Zuerst lesen |
|---|---|
| Regeln, Phase, „darf ich das?" | [CLAUDE.md](CLAUDE.md) (+ §20 Stop-Regeln) |
| Produkt-Zielbild, Module, Journeys | [MASTER_BLUEPRINT.md](MASTER_BLUEPRINT.md) |
| Reihenfolge / nächster Schritt / Phase | [PRODUCT_ROADMAP.md](PRODUCT_ROADMAP.md) |
| UI, Seiten, Farben, Typo, Motion | [DESIGN_SYSTEM.md](DESIGN_SYSTEM.md) + [COMPONENT_LIBRARY.md](COMPONENT_LIBRARY.md) |
| KI-Funktionen (Bild/Video) | [AI_ENGINE.md](AI_ENGINE.md) |
| Kristalle, Preise, Payment, Stripe | [CRYSTAL_SYSTEM.md](CRYSTAL_SYSTEM.md) |
| Datenbank, Schema, Storage, Backup | [DATABASE_ARCHITECTURE.md](DATABASE_ARCHITECTURE.md) |
| API-Endpunkte, Rate-Limits | [API_ARCHITECTURE.md](API_ARCHITECTURE.md) |
| Sicherheit, Header, CSRF, Secrets | [SECURITY.md](SECURITY.md) |
| Admin-Bereich, Betrieb, Support | [ADMIN_SYSTEM.md](ADMIN_SYSTEM.md) |
| Deploy, Domain, Env, Rollback | [DEPLOYMENT.md](DEPLOYMENT.md) |
| Tests, Abnahme, Deploy-Gate | [QA_MASTERPLAN.md](QA_MASTERPLAN.md) |
| Dateien, Struktur, Aufräumen | [FILE_STRUCTURE.md](FILE_STRUCTURE.md) |
| „Gibt es dazu schon eine Entscheidung?" | [DECISION_LOG.md](DECISION_LOG.md) |

---

## 4. Abhängigkeits-Landkarte (verkürzt)

```
                         CLAUDE.md  (Regeln/Phase — rahmt ALLES)
                              │
                     MASTER_BLUEPRINT.md  (Zielbild)
        ┌──────────────┬───────────┴───────────┬───────────────┐
 PRODUCT_ROADMAP   DESIGN_SYSTEM         Fach-Ebene         DECISION_LOG
   (Phasen)         └ COMPONENT_LIBRARY   │                 (D-… / O-…)
                                          │
        AI_ENGINE ─ CRYSTAL_SYSTEM ─ DATABASE_ARCHITECTURE ─ API_ARCHITECTURE ─ SECURITY
                                          │
                        ADMIN_SYSTEM · DEPLOYMENT · QA_MASTERPLAN · FILE_STRUCTURE
```
Querschnitts-Docs, die fast überall hineinwirken: **SECURITY** (jeder Endpunkt),
**QA_MASTERPLAN** (jede Änderung), **DECISION_LOG** (jede Grundsatzfrage).

---

## 5. Historische Dokumente (superseded — NICHT verbindlich)

Diese Dateien liegen noch im Repo, sind aber durch die Bibel ersetzt (DECISION_LOG **D-013**).
Sie dienen ausschließlich als historische Referenz und wandern in der Cleanup-Phase (Phase 1)
nach `archive/docs-legacy/` — **jetzt nicht löschen/verschieben** (Löschverbot, CLAUDE.md §20.2).

| Historisch | Ersetzt durch |
|---|---|
| `ARCHITECTURE.md` (04/2026) | MASTER_BLUEPRINT + API/DATABASE_ARCHITECTURE |
| `README_DEPLOY.md` | DEPLOYMENT.md |
| `PROJECT_STATUS.md`, `NEXT_SESSION_HANDOFF.md`, `TODO.md` | PRODUCT_ROADMAP + CLAUDE.md |
| `WORKSPACE_CLEANUP_AUDIT.md`, `WORKSPACE_CLEANUP_DONE.md` | FILE_STRUCTURE.md |
| `AGENT.md`, `CLAUDE_INSTRUCTIONS.md`, `PROMPT_TEMPLATES.md` | CLAUDE.md |
| `memory/*.md` (architecture, business, byok-system, deployment, ffmpeg, roadmap, video-pipeline, current-problems, design-audit, workflow-design-reference, …) | jeweiliges Fachdokument der Bibel |
| `docs/project-overview.md` | MASTER_BLUEPRINT.md |

**Ausnahme (bleibt gültig):** `CHANGELOG.md` ist kein superseded-Dokument, sondern die
fortlaufende Versionshistorie und bleibt im Root.

---

## 6. Vollständigkeits-Status

Alle 15 Bibel-Dokumente sind vorhanden und untereinander konsistent verlinkt.
PROJECT_INDEX.md (dieses Dokument) ergänzt sie als 16. Navigations-Meta-Dokument.
Der detaillierte Prüfbericht (Widersprüche, Lücken, offene Punkte) steht in der
Session-Zusammenfassung und wird bei Bedarf als eigener Review-Abschnitt in CLAUDE.md gepflegt.
