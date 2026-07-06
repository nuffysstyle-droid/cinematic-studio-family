# CRYSTAL_SYSTEM.md — Cinematic Vision Studio
# Kristall-Ökonomie: Währung, Preise, Ledger, Stripe, Missbrauchsschutz

| Feld | Wert |
|---|---|
| Version | 1.0 (Architecture Lock, 2026-07-05) — D-003, D-009, D-010 |
| Kernsatz | Kristalle (💎) sind die EINZIGE Verbrauchswährung der Plattform. Pläne steuern Fähigkeiten, Kristalle steuern Verbrauch. |
| Kritischer Ist-Befund | Der Ledger existiert und funktioniert — aber KEIN Endpunkt bucht heute ab. KI ist faktisch gratis. Scharfschaltung = Phase 2, D-009. |

---

## 1. Ökonomisches Modell

```
        PLÄNE (Abo, monatlich)                    KRISTALLE (Verbrauch)
  steuern WAS man darf                      steuern WIE VIEL KI man nutzt
  Free    → 720p, 15 s, 3 Slots             Verdienen: Welcome-Bonus (nach Verifizierung!),
  Starter → 1080p, Audio, mehr Slots                    Abo-Inklusivkontingent, Aktionen/Admin
  Pro     → Priorität, Pro-Module           Ausgeben:  KI-Bild, KI-Video (später), Premium-Stile
```
Nicht-Kristall-Leistungen (Upload, Schnitt, Render in Plan-Qualität) bleiben
kristallfrei — Kristalle bepreisen ausschließlich externe KI-Kosten (klar erklärbar,
Marge kalkulierbar). Renders bleiben durch Rate-Limit (15/h) geschützt.

---

## 2. Der Ledger (Quelle der Wahrheit)

Tabelle `crystal_transactions` (existiert ✔, DATABASE_ARCHITECTURE §2/§4):
append-only, `amount` ±, `action`-Katalog verbindlich:

| action | amount | Auslöser |
|---|---|---|
| `welcome_bonus` | +50 | E-Mail-Verifizierung (D-010 — heute fälschlich bei Registrierung) |
| `purchase` | +Paket | Stripe-Webhook (idempotent via event_id) |
| `plan_allowance` | +monatlich | Abo-Verlängerung (Phase 2b) |
| `ai_image` | −5/−8 | Task-Start (Reservierung) |
| `ai_image_refund` | +Gegenwert | Task-Fehlschlag (automatisch, AI_ENGINE §3) |
| `admin_grant` / `admin_revoke` | ± | Admin mit Pflicht-Begründung (ADMIN_SYSTEM §4) |

Invarianten: Balance-Cache == Ledger-Summe (Nachtprüfung) · niemals UPDATE/DELETE ·
Abbuchung nur atomar mit `WHERE balance >= cost`.

---

## 3. UX-Regeln (Vertrauen entscheidet)

1. **Vor** jeder kostenpflichtigen Aktion: „Kostet X 💎 · danach: Y 💎" (COMPONENT_LIBRARY §5).
2. Balance permanent sichtbar (Nav-Pill, eingeloggt — Blueprint §5.1).
3. Fehlgeschlagene Generierung = sichtbare automatische Erstattung (Toast: „+5 💎 zurückerstattet").
4. Bei 0 💎: Aktion nicht verstecken, sondern Button → „Kristalle aufladen" (Conversion-Punkt).
5. Transaktionshistorie im Dashboard: jede Buchung mit Klartext-Beschreibung.

---

## 4. Preisliste Verbrauch (Startwerte — Feinjustierung O-4)

| Leistung | 💎 | Kalkulationsanker |
|---|---|---|
| KI-Bild (Flux Kontext Pro) | **5** | ≥ 2,5× Provider-Einkauf inkl. Fehlversuchsquote |
| KI-Bild (Flux Kontext Max) | **8** | dito |
| KI-Video / Sek. (Zukunft) | TBD | erst nach Provider-Preis (AI_ENGINE §8) |

Technik: zentrale Preis-Map `includes/pricing.php` (eine Quelle für UI, Buchung, Academy-Texte).

---

## 5. Pakete & Pläne (VORSCHLAG — Björn entscheidet O-4)

| Paket | 💎 | Preis | 💎/€ |
|---|---|---|---|
| Starter-Pack | 100 | 4,99 € | 20,0 |
| Creator-Pack ⭐ | 300 | 12,99 € | 23,1 |
| Studio-Pack | 800 | 29,99 € | 26,7 |

| Plan | Preis/Monat | Enthält |
|---|---|---|
| Free | 0 € | 720p, 15 s, 3 Slots, Welcome-50 nach Verifizierung |
| Starter+ | 7,99 € | 1080p, Audio, mehr Slots, +50 💎/Monat |
| Pro | 24,99 € | alles + Priorität + Pro-Module (O-1) + 200 💎/Monat |

Prinzipien: größere Pakete = besserer Kurs (Anreiz), Abo enthält Kristalle
(Retention), Preise psychologisch unter 5/13/30. Nach 60 Tagen Echtdaten: Review.

---

## 6. Stripe-Integration (Phase 2)

**Flow:** `/kristalle` → `POST /api/v1/billing/checkout {package}` → Stripe Checkout
(hosted, Kartendaten NIE bei uns) → `success_url` mit Session-Ref → UI wartet auf
Webhook-Bestätigung → Balance-Toast.
**Webhook `POST /api/v1/billing/webhook`:** Signaturprüfung (`STRIPE_WEBHOOK_SECRET`),
nur `checkout.session.completed` (V1), Idempotenz über `purchases.stripe_event_id UNIQUE`,
dann Ledger-`purchase` + Beleg-Mail. Abo (Starter+/Pro): Stripe Billing,
`invoice.paid` → `plan_allowance` (Phase 2b — Pakete zuerst, Abos danach).
**Fehlerfälle:** Webhook vor Redirect (ok, idempotent) · Webhook verspätet (UI-Pending-Hinweis)
· Refund über Stripe → manuelle Gegenbuchung + Konto-Notiz (V1).
**Go-Live-Blocker:** Rechtliches O-6 (AGB digitale Güter, Widerruf, Preisangaben, USt).

---

## 7. Missbrauchsschutz

D-010 Verifizierungspflicht (Bonus erst danach) · Register-Limit 10/h/IP ✔ ·
KI-Limits bleiben ✔ · Tagesbudget global (AI_ENGINE §6.3) · Ledger-Anomalie-Check
(Nachtjob) · Admin-Sicht auf Top-Verbraucher (ADMIN_SYSTEM §3). Phase 4 bei Bedarf:
Device-Fingerprint-Heuristik, Auszahlungs-/Gift-Funktionen bleiben ausgeschlossen
(kein Geldwäsche-Vektor — Kristalle sind nicht rücktauschbar, steht in AGB/O-6).
