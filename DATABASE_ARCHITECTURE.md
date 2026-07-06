# DATABASE_ARCHITECTURE.md — Cinematic Vision Studio
# Datenarchitektur: SQLite, Dateispeicher, Backup, Wachstumspfad

| Feld | Wert |
|---|---|
| Version | 1.0 (Architecture Lock, 2026-07-05) — D-007 |
| Engine | SQLite 3 via PDO · WAL · `foreign_keys=ON` · `busy_timeout=5000` · `synchronous=NORMAL` |
| Pfad | Render: `/var/www/html/render-data/cinematic.db` (Persistent Disk) · Lokal: `storage/cinematic.db` |
| Schema-Verwaltung | `PRAGMA user_version` + idempotente `csf_db_init()`; Änderungen NUR über Migrationsschritte in `includes/db.php` |

---

## 1. Grundsatz: Zwei Speicherwelten, klare Grenze

| Welt | Was | Warum |
|---|---|---|
| **SQLite** | Identität & Geld: users, Ledger, Tokens, (künftig) Käufe, Audit | transaktional, konsistent, klein |
| **Dateisystem** | Medien & Jobs: `storage/jobs/<job_id>/` (Quellvideo, Slots, Thumbs, Export, `meta.json` mit `LOCK_EX`) | große Binärdaten gehören nicht in die DB |

Verbindung: `crystal_transactions.job_id` referenziert den Job-Ordnernamen (String, bewusst
ohne FK — Jobs sind vergänglich, der Ledger ist ewig).

---

## 2. Ist-Schema (verifiziert aus `includes/db.php`, Version 1)

```sql
users (
  id INTEGER PK AUTOINCREMENT,
  email TEXT UNIQUE NOT NULL COLLATE NOCASE,
  password_hash TEXT NOT NULL,                 -- ARGON2ID
  plan TEXT NOT NULL DEFAULT 'free' CHECK(plan IN ('free','starter','pro')),
  crystals_balance INTEGER NOT NULL DEFAULT 0 CHECK(crystals_balance >= 0),
  email_verified INTEGER NOT NULL DEFAULT 0,   -- vorhanden, heute UNGENUTZT (D-010!)
  remember_token TEXT UNIQUE, remember_expires INTEGER,  -- Altfelder, echte Tokens s.u.
  created_at TEXT DEFAULT (datetime('now','utc')),
  updated_at TEXT DEFAULT (datetime('now','utc'))
)
crystal_transactions (                         -- LEDGER: append-only Audit aller 💎
  id INTEGER PK, user_id → users ON DELETE CASCADE,
  amount INTEGER NOT NULL,                     -- + Gutschrift / − Kosten
  action TEXT NOT NULL,                        -- 'welcome_bonus','ai_image','purchase',…
  job_id TEXT, note TEXT,
  created_at TEXT DEFAULT (datetime('now','utc'))
)
login_attempts ( id, ip_hash TEXT, attempted_at INTEGER unixepoch )   -- Brute-Force-Fenster
remember_tokens ( id, user_id →, token_hash TEXT UNIQUE, expires_at INTEGER, created_at )
password_resets ( id, user_id →, token_hash TEXT UNIQUE, expires_at INTEGER, created_at )
  -- ⚠️ wird ad hoc in api/auth/forgot-password.php erzeugt → wandert in csf_db_init() (Migration v2)
```

Datumsformate: UTC — Texttabellen `datetime('now','utc')` (ISO-8601), Token-Tabellen Unix-Epoch.
**Regel:** neue Tabellen verwenden einheitlich ISO-8601-UTC-Text.

---

## 3. Ziel-Schema-Erweiterungen (Migration v2/v3 — Phase 2/3)

```sql
-- v2 (Phase 2: Konto & Monetarisierung)
ALTER TABLE users ADD COLUMN role TEXT NOT NULL DEFAULT 'user' CHECK(role IN ('user','admin'));   -- D-012
ALTER TABLE users ADD COLUMN status TEXT NOT NULL DEFAULT 'active' CHECK(status IN ('active','locked'));
email_verifications ( id, user_id →, token_hash UNIQUE, expires_at, created_at )                  -- D-010
purchases (            -- Stripe-Käufe; Ledger bleibt Quelle der 💎-Wahrheit
  id, user_id →, stripe_session_id TEXT UNIQUE, stripe_event_id TEXT UNIQUE,  -- Idempotenz!
  package_code TEXT, amount_eur_cents INTEGER, crystals INTEGER,
  status TEXT CHECK(status IN ('pending','paid','failed','refunded')), created_at
)

-- v3 (Phase 3: Betrieb)
audit_log ( id, admin_id →users, action TEXT, target_type TEXT, target_id TEXT, meta TEXT(JSON), created_at )
ai_tasks  ( id, user_id →, job_id TEXT, task_id TEXT UNIQUE, model TEXT, status TEXT,
            duration_ms INTEGER, crystals INTEGER, created_at )                                   -- AI_ENGINE §7
```

**Migrations-Disziplin:** jede Version = ein Block in `csf_db_migrate()`, rückwärts nie;
vor jeder Migration Backup-Pflicht (§6); Test lokal gegen Kopie der Produktions-DB.

---

## 4. Ledger-Invarianten (Geld-Wahrheit)

1. `crystal_transactions` ist **append-only** — kein UPDATE/DELETE, Korrektur = Gegenbuchung.
2. `users.crystals_balance` ist ein Cache der Ledger-Summe; Abbuchung nur atomar
   (`UPDATE … WHERE balance >= cost`, existiert in `csf_auth_spend_crystals`).
3. Nachtjob (Phase 3): Konsistenz-Check Balance == SUM(ledger) pro User; Abweichung → Admin-Alarm.
4. Stripe-Gutschriften nur über Webhook mit `stripe_event_id`-Idempotenz (nie doppelt buchen).

---

## 5. Dateispeicher-Vertrag (`storage/`)

```
storage/jobs/<job_id>/          # job_id: zufällig, nicht ratbar
  source.mp4 · meta.json (LOCK_EX!) · slots/*.jpg|mp4 · thumbs/*.jpg · final_*.mp4
storage/uploads|temp|exports|thumbnails|elements/   # Legacy-Suite-Pfade (Sunset D-004)
storage/rate_limits/            # File-basierte Limits (SHA-256-IP)
```
Regeln: Zugriff NUR über API nach `csf_validate_path()`+realpath; `.htaccess deny` bleibt
unantastbar; Aufbewahrung: Jobs/Exports 48 h, Temp 2 h (Cron 03:00 UTC + probabilistisch 1/50).

`data/*.json` (ready-videos, projects, …) gehört zur Legacy-Suite → friert ein, Sunset mit D-004.

---

## 6. Backup & Restore (heute UNGEREGELT — Pflicht ab Phase 1) 🔴

| Was | Wie | Wann |
|---|---|---|
| SQLite | Cron-Erweiterung `bin/backup-db.php`: `VACUUM INTO '/var/www/html/render-data/backups/cinematic-YYYYMMDD.db'`, 7 Generationen rotierend | täglich 03:10 UTC |
| Offsite | Backup-Download über authentifizierten Admin-Endpunkt (CLEANUP_SECRET-Muster) → lokale Kopie bei Björn | wöchentlich, manuell (Phase 3: automatisiert) |
| Medien | NICHT gesichert (vergänglich by design, 48 h) — Nutzer werden im UI darauf hingewiesen („Video herunterladen!") | — |
| Restore-Probe | Backup lokal öffnen, `PRAGMA integrity_check`, Login-Test | monatlich |

**Stop-Regel (CLAUDE.md §20.3):** Kein Schema-Eingriff ohne frisches Backup.

---

## 7. Wachstumspfad → Postgres (Trigger aus D-007)

Auslöser (einer genügt): > ~5.000 aktive User · gehäufte `SQLITE_BUSY` trotz WAL ·
zweite schreibende Instanz nötig.
Vorbereitung ist eingebaut: nur portable Typen/CHECKs, keine SQLite-Spezialitäten in
Queries (kein `INSERT OR REPLACE` in neuem Code — `ON CONFLICT` verwenden), zentrale
PDO-Erzeugung (`csf_db()`) = eine Umschaltstelle. Migrationsweg: pgloader/CSV-Export,
Doppellauf-Woche mit Read-Vergleich, dann Cutover.
