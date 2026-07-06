# SECURITY.md — Cinematic Vision Studio
# Sicherheitsarchitektur: Baseline, Bedrohungen, Maßnahmenplan

| Feld | Wert |
|---|---|
| Version | 1.0 (Architecture Lock, 2026-07-05) — D-015 |
| Geltung | Beide Hosts bis zur Vereinigung, danach die eine Plattform |
| Grundsatz | Secure by default: Jeder Endpunkt ist erst fertig, wenn Auth, Rate-Limit, Validierung, Escaping und Fehlerpfad benannt sind. |

---

## 1. Ist-Stärken (Audit-bestätigt — NICHT anfassen, Referenzniveau)

- **Auth:** ARGON2ID + Rehash, Brute-Force 5/15min (IP-Hash), Session-Fixation-Schutz,
  Remember-Me als SHA-256-Hash mit Rolling-Renewal, User-Enumeration-Schutz (Timing-Dummy)
- **Shell:** ausnahmslos `escapeshellarg` via `csf_ffmpeg_run`/`csf_ffprobe_run`/analyze
- **Upload:** `is_uploaded_file` + finfo-MIME + Größen-/Dauer-Limits + Extension-Map
- **Pfade:** `csf_validate_path` + realpath + Storage-Root-Prefix
- **SSRF:** Ergebnis-URLs werden DNS-aufgelöst, private/reservierte IPs blockiert
- **SQL:** durchgängig Prepared Statements, `EMULATE_PREPARES=false`
- **Storage/Data:** `.htaccess Require all denied` (Tabu-Zone, CLAUDE.md §20.6)
- **Sessions:** httponly, secure (hinter TLS), SameSite=Lax, Name konfigurierbar

## 2. Bekannte Lücken → Maßnahme → Phase

| # | Lücke | Risiko | Maßnahme | Phase |
|---|---|---|---|---|
| L1 | `contact-submit.php`: CORS `*`, kein Rate-Limit, kein Honeypot | 🔴 Spam-Relay, Kosten, Blacklisting | Rate-Limit 5/h + Honeypot + Origin-Allowlist (www/non-www) + Längen-Checks (vorhanden ✔) | **1** |
| L2 | Register schenkt 50 💎 ohne Verifizierung | 🔴 Kristall-Farming | D-010: Bonus erst bei Verifizierung; `email_verified` scharf | 2 |
| L3 | Keine Security-Header | 🟠 Clickjacking, MIME-Sniffing | §4-Header-Set in Apache-Conf | **1** |
| L4 | Kein CSRF-Token (nur SameSite=Lax) | 🟠 Rest-Risiko (Subdomain/GET-Lücken) | §5 Token-Design für alle state-changing POSTs | 2 |
| L5 | Lockout-Restzeit-Bug (auth.php:155 → „0 Minuten") | 🟡 UX/Vertrauen | Berechnung: ältesten Versuch im Fenster ermitteln → Restzeit = 15 min − Alter | **1** |
| L6 | `innerHTML` in 12 Dateien (statisch, kein akutes XSS) | 🟡 Regel-Erosion | Neu-Code verboten; Bestand bei Berührung migrieren | laufend |
| L7 | mail() ohne SPF/DKIM-Strategie | 🟡 Zustellbarkeit | O-2 (Mailgun/SMTP) + SPF/DKIM/DMARC-DNS | 2 |
| L8 | Kein Backup der User-DB | 🟠 Datenverlust | DATABASE_ARCHITECTURE §6 | **1** |
| L9 | php-server.log & QA-Screenshots im Root | 🟡 Info-Leak-Potenzial | Cleanup-Archivierung | 1 |

## 3. Bedrohungsmodell (Kurzform)

| Angreifer | Vektor | Primäre Verteidigung |
|---|---|---|
| Spam-Bots | Kontakt-API, Register | Rate-Limits, Honeypot, Verifizierung |
| Kristall-Farmer | Massenregistrierung | D-010 + IP-Limits + (Phase 3) Device-Heuristik |
| Kostentreiber | KI-Loop-Missbrauch | 💎-Pflicht + 10/h + Tagesbudget (AI_ENGINE §6.3) |
| Injection | Upload/Prompt/Pfade | bestehende Escaping-/Validierungs-Schicht (§1) |
| Session-Diebstahl | XSS/Netz | httponly+TLS, CSP (Phase 4), innerHTML-Verbot |
| Insider-Fehler | versehentlicher Push auf main | CLAUDE.md Stop-Regeln + QA-Gate |

## 4. Security-Header-Baseline (Phase 1, Apache global)

```
X-Frame-Options: DENY                      (Studio bettet nichts, niemand bettet uns)
X-Content-Type-Options: nosniff
Referrer-Policy: strict-origin-when-cross-origin
Permissions-Policy: camera=(), microphone=(), geolocation=()
Strict-Transport-Security: max-age=31536000; includeSubDomains   (erst NACH Domain-Umzug O-5)
Content-Security-Policy: Phase 4, zuerst Report-Only (Inline-JS/CSS-Bestand macht sofortiges Enforce unrealistisch)
```

## 5. CSRF-Design (Phase 2)

Session-gebundenes Token (`$_SESSION['csrf']`, 32 Byte hex), Ausgabe als Meta-Tag im
Head-Partial; `assets/js/api.js` (🆕 zentraler Fetch-Wrapper) sendet es als
`X-CSRF-Token`-Header; Server-Helfer `csf_csrf_require()` prüft per `hash_equals` auf
allen state-changing POSTs. Ausnahmen (dokumentationspflichtig): Stripe-Webhook
(signaturgeprüft), reine Login-/Register-Posts (durch Rate-Limit + SameSite gedeckt).

## 6. Formular-Anti-Spam-Standard

Honeypot-Feld (CSS-versteckt, Name unauffällig) + Mindest-Ausfüllzeit (Timestamp-Feld ≥ 3 s)
+ Rate-Limit. KEIN externes Captcha in V1 (DSGVO/UX) — erst bei nachweisbarem Druck.

## 7. Secrets & Konfiguration

Nur Render-Env-Vars (`KIE_AI_API_KEY`, `CLEANUP_SECRET`, künftig `STRIPE_SECRET_KEY`,
`STRIPE_WEBHOOK_SECRET`, `MAIL_*`). Nie im Repo, nie im Client, nie im Log.
Rotation: bei Verdacht sofort; Stripe-Keys getrennt Test/Live. `.env`-Dateien bleiben
gitignored; `.mcp.json` enthält keine Secrets (verifiziert).

## 8. Datenschutz (DSGVO-Kurzlage)

Datenminimierung ✔ (E-Mail + Hash, IPs nur als SHA-256), lokale Fonts ✔ (Rest in
crystals.html entfernen — Phase 1), Medien-Aufbewahrung 48 h ✔. Offen: AVV mit
Render/Kie/Stripe/Mailprovider dokumentieren + Datenschutzerklärung aktualisieren
(zusammen mit O-6, Phase 2). Betroffenenrechte: Konto-Löschung = users-DELETE
(CASCADE auf Tokens/Ledger — Ledger-Aufbewahrung vs. Löschpflicht mit O-6 klären).

## 9. Incident-Grundplan

Verdacht → (1) betroffenen Endpunkt per Deploy deaktivieren, (2) Keys rotieren,
(3) Render-Logs sichern, (4) Björn informieren, (5) Nachanalyse in DECISION_LOG.
Sicherheitsfunde durch Agenten: sofort melden, nie still fixen (CLAUDE.md §20.11).
