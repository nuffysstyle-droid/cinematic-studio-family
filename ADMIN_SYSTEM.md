# ADMIN_SYSTEM.md — Cinematic Vision Studio
# Admin-Modul: Betrieb der Plattform ohne SSH & SQL

| Feld | Wert |
|---|---|
| Version | 1.0 (Architecture Lock, 2026-07-05) — D-012 |
| Status | Heute NICHT vorhanden (Audit-Befund: Betrieb nur per DB-Zugriff möglich) — Umsetzung Phase 3 |
| Prinzip | Admin ist eine ROLLE derselben App, kein Parallelsystem. Jede Admin-Aktion ist auditiert und reversibel über Gegenbuchung, nie über Datenlöschung. |

---

## 1. Zweck & Abgrenzung

Der Admin-Bereich macht drei Dinge — nicht mehr:
1. **Sehen:** Zustand der Plattform (User, Umsatz, Jobs, Fehler) auf einen Blick.
2. **Helfen:** Support-Fälle lösen (Kristalle gutschreiben, Passwort-Reset anstoßen, Konto entsperren).
3. **Schützen:** Missbrauch stoppen (Konto sperren, Task-Ausreißer erkennen).

Kein CMS, kein Feature-Builder, keine Massen-Editoren — bewusst klein (Ein-Betreiber-Realität).

---

## 2. Rollen & Zugriff

| Element | Festlegung |
|---|---|
| Rollenmodell | `users.role ∈ {user, admin}` (Migration v2, DATABASE_ARCHITECTURE §3) — genau EINE Admin-Rolle, keine Rechte-Matrix in V1 |
| Ernennung | ausschließlich manuell per einmaligem Setup-Script/SQL durch Björn (nie über UI — kein Privilege-Escalation-Pfad) |
| Gate | `csf_auth_require_role('admin')` (Erweiterung von auth.php): 404 statt 403 für Nicht-Admins (Bereich bleibt unsichtbar) |
| Zusatzschutz | Admin-Session: Re-Login nach 24 h; alle Admin-POSTs mit CSRF (SECURITY §5); optional (Phase 4): TOTP-2FA für role=admin |
| Sichtbarkeit | `/admin` taucht in keiner Navigation, Sitemap oder robots.txt auf |

---

## 3. Seitenstruktur `/admin`

| Route | Inhalt | Aktionen |
|---|---|---|
| `/admin` (Übersicht) | Kennzahlen heute/7 Tage: neue User, verifiziert %, Renders, KI-Tasks (Erfolg %), 💎 ausgegeben/gekauft, Fehlerzähler, Disk-Füllstand (aus health-Daten) | — |
| `/admin/users` | Suche (E-Mail), Liste: Plan, Balance, verified, status, created | → Detail |
| `/admin/users/{id}` | Stammdaten, Ledger-Auszug, Käufe, letzte Jobs | 💎 gutschreiben/abziehen (mit Pflicht-Begründung → Ledger-`note` + audit_log) · Plan ändern · sperren/entsperren · Verifizierungs-/Reset-Mail erneut senden |
| `/admin/jobs` | Scan `storage/jobs/` + `ai_tasks`: Status, Alter, Größe, User | Job-Details ansehen · verwaiste Jobs aufräumen (ruft bestehende Cleanup-Logik) |
| `/admin/purchases` | Stripe-Käufe (Tabelle `purchases`), Abgleich-Status | Beleg erneut senden · (Refund läuft über Stripe-Dashboard, hier nur Verlinkung + Gegenbuchung) |
| `/admin/audit` | `audit_log` chronologisch, filterbar | nur lesen |

UI: Design-System-konform (dunkle Tabellen, `.cvs-card`), funktional vor schön —
aber KEIN Fremd-Framework (D-006).

---

## 4. Admin-API (`/api/v1/admin/*`, Phase 3)

`GET stats` · `GET users?q=` · `GET users/{id}` · `POST users/{id}/crystals {amount,reason}` ·
`POST users/{id}/plan {plan}` · `POST users/{id}/status {locked|active}` ·
`POST users/{id}/resend {verify|reset}` · `GET jobs` · `GET audit`
Alle: role-Gate + CSRF + Rate-Limit (30/h je Admin) + audit_log-Pflicht.

**Audit-Regel:** Jede schreibende Admin-Aktion erzeugt VOR Ausführung einen
`audit_log`-Eintrag (admin_id, action, target, meta JSON). Kristall-Eingriffe laufen
ausschließlich über `csf_auth_add_crystals`/Gegenbuchung — nie direktes UPDATE der Balance.

---

## 5. Sicherheits-Invarianten

1. Kein Admin-Endpunkt ohne `role=admin` UND CSRF UND Audit-Eintrag.
2. Ledger bleibt append-only — auch für Admins (Korrektur = Gegenbuchung mit Begründung).
3. Admin kann keine Passwörter sehen/setzen — nur Reset-Mail auslösen.
4. Konto-Löschung durch Admin: V1 nur „sperren"; echtes Löschen (DSGVO-Antrag) bleibt
   manueller, dokumentierter Vorgang bis O-6 geklärt ist.
5. `/admin` wird in Playwright-Suite negativ getestet: als User → 404 (QA_MASTERPLAN §4).

---

## 6. MVP-Zuschnitt (Phase 3, bewusst klein)

**Muss:** Übersicht-Kennzahlen · User-Suche/Detail · 💎-Gutschrift mit Begründung ·
Sperren · audit_log. **Kann warten (Phase 4):** Job-Browser-Komfort, Purchases-Ansicht
(solange Stripe-Dashboard reicht), 2FA, Kennzahlen-Charts.
