# README_DEPLOY.md — Render Deployment

**Cinematic Studio Family** auf [Render.com](https://render.com) deployen.
Diese Anleitung führt dich Schritt für Schritt durch die einmalige Einrichtung.
Sobald der Service einmal läuft, wird jeder Push auf den `master`-Branch
automatisch deployed (`autoDeploy: true` in `render.yaml`).

---

## 1. Voraussetzungen

| Punkt                  | Was du brauchst                                                   |
|------------------------|-------------------------------------------------------------------|
| Render-Account         | Kostenlose Anmeldung unter https://render.com — Starter-Plan ($7/Monat) für persistenten Speicher empfohlen |
| GitHub-Repo            | Dieses Repo (oder ein Fork) bei GitHub gehostet                   |
| GitHub ↔ Render        | Render mit deinem GitHub-Account verbunden (Render → Account Settings → GitHub) |
| Branch                 | `master` ist der Deploy-Branch (siehe `render.yaml`)              |
| Lokaler Build-Test     | Optional: `docker build -t csf-test .` läuft lokal ohne Fehler    |

---

## 2. Deployment in 5 Schritten

### Schritt 1 — GitHub-Repo zu Render verbinden

1. Bei Render einloggen → **New +** → **Web Service**
2. *Connect a repository* → falls noch nicht geschehen: **Configure GitHub App** klicken und Render Zugriff auf dieses Repo geben.
3. Repo `cinematic-studio-family` aus der Liste wählen.

### Schritt 2 — Service via Blueprint anlegen

Render erkennt die `render.yaml` automatisch und schlägt **„Deploy from Blueprint"** vor.

1. Auf der Seite *New +* alternativ direkt **Blueprint** wählen → Repo + Branch `master` angeben.
2. Render parst `render.yaml` und zeigt:
   - 1 Web Service: `cinematic-studio-family`
   - 1 Disk: `csf-storage` (1 GB, Mount `/var/www/html/render-data`)
3. Auf **Apply** klicken.

> **Hinweis:** Du kannst den Service auch manuell anlegen (*New + → Web Service*),
> Render übernimmt dann die `render.yaml`-Werte trotzdem. Beides funktioniert.

### Schritt 3 — Konfiguration prüfen (sollte aus render.yaml übernommen sein)

| Feld           | Wert                              |
|----------------|-----------------------------------|
| Name           | `cinematic-studio-family`         |
| Region         | Frankfurt (empfohlen) oder Oregon |
| Branch         | `master`                          |
| Runtime        | Docker                            |
| Plan           | Starter ($7/mo)                   |
| Health Check   | `/index.php`                      |
| Auto-Deploy    | Yes                               |

### Schritt 4 — Persistent Disk wird automatisch angelegt

Die `disk:`-Sektion in `render.yaml` erstellt automatisch:

- **Name:** `csf-storage`
- **Mount Path:** `/var/www/html/render-data`
- **Größe:** 1 GB (jederzeit live vergrößerbar im Dashboard)

Beim ersten Container-Start sorgt `docker/entrypoint.sh` für:

1. Anlegen der Standard-Ordner (`storage/uploads/{images,videos}`, `storage/exports`, `storage/thumbnails`, `storage/temp`, `storage/elements`, `data/projects`).
2. Symlinks `/var/www/html/storage` → `render-data/storage` und `/var/www/html/data` → `render-data/data`.
3. Berechtigung `www-data:www-data` auf der Disk.

### Schritt 5 — Erster Deploy

Render startet den Build automatisch nach dem Apply.

1. **Logs verfolgen:** *Service-Dashboard → Logs* — du siehst:
   - `[entrypoint] APACHE_PORT=10000`
   - `[entrypoint] Persistent disk gefunden: /var/www/html/render-data`
   - `[entrypoint] symlink: /var/www/html/storage → /var/www/html/render-data/storage`
   - `apache2: AH00558` (normal, kein Fehler)
2. Deploy-Status sollte nach 3–6 Min auf **Live** wechseln.
3. URL kopieren: `https://cinematic-studio-family-XXXX.onrender.com`

---

## 3. Environment Variables

Werden via `render.yaml` automatisch gesetzt — hier zur Referenz, falls du im Dashboard etwas ändern willst.

| Key                | Default-Wert                   | Zweck                                              |
|--------------------|--------------------------------|----------------------------------------------------|
| `PORT`             | (von Render injiziert)         | Apache-Listen-Port — Entrypoint übernimmt es       |
| `FFMPEG_PATH`      | `/usr/bin/ffmpeg`              | Pfad zur FFmpeg-Binary (apt-Installation)          |
| `FFPROBE_PATH`     | `/usr/bin/ffprobe`             | Pfad zur FFprobe-Binary                            |
| `FFMPEG_TIMEOUT`   | `300`                          | Sekunden — Hard-Limit pro FFmpeg-Job               |
| `PHP_SESSION_NAME` | `csf_session`                  | Session-Cookie-Name                                |
| `PERSIST_ROOT`     | `/var/www/html/render-data`    | Mount-Punkt der Persistent Disk                    |

> **API-Keys (Anthropic, OpenAI, …) gehören NICHT in render.yaml.**
> Setze sie im Dashboard unter *Environment* als _Secret_ — sie landen dann
> nur als verschlüsselte ENV-Vars in den Containern und nicht im Git.

---

## 4. Live-Test Checkliste

Nach „Live"-Status arbeite diese Liste ab. Bei Fehlern siehe Abschnitt 5.

- [ ] Seite lädt unter `https://<service>.onrender.com`
- [ ] `/api/health.php` zeigt `"ffmpeg": { "available": true, ... }` und `"storage_writable": true`
- [ ] **Image Studio:** Prompt-Generierung funktioniert (API-Key ggf. setzen)
- [ ] **Video Studio:** MP4-Convert-Flow läuft durch (kleine Test-Datei)
- [ ] **Upload:** ein Bild und ein Video (~100 MB) werden akzeptiert
- [ ] **Merge Clips:** zwei MP4-Clips erfolgreich zusammenführen
- [ ] **Export MP4:** Preset 720p ✅, Preset 1080p ✅
- [ ] **Thumbnail:** PNG aus exportiertem Video generieren
- [ ] **Persistenz:** Service neustarten (Manual Deploy), Uploads + Projekte sind noch da

> **Smoketest-Befehl:**
> ```bash
> curl -s https://<service>.onrender.com/api/health.php | jq
> ```

---

## 5. Bekannte Stolpersteine + Lösungen

| Problem                               | Diagnose / Lösung                                                                                          |
|---------------------------------------|------------------------------------------------------------------------------------------------------------|
| **FFmpeg fehlt** (`available: false`) | Logs: `[entrypoint]` ok? Dann `data/ffmpeg-debug.log` prüfen (über Render Shell). Im Zweifel **Manual Deploy → Clear build cache & deploy**. |
| **PORT-Bind-Error** (Apache crashed)  | Im Logs nach `AH00072: make_sock` suchen. Entrypoint sollte `APACHE_PORT=...` ausgeben — fehlt das, wurde `entrypoint.sh` nicht ausführbar deployed (`chmod +x` im Dockerfile prüfen). |
| **502 Bad Gateway**                   | Apache lauscht auf falschem Port. Check: Logs zeigen `[entrypoint] APACHE_PORT=` — Wert muss `$PORT` sein, nicht `80`. |
| **Disk voll**                         | Render-Dashboard → Disks → Resize (live, kein Restart nötig). 1 GB → 5 GB / 10 GB.                        |
| **Cold Start (Free Plan)**            | Starter-Plan empfohlen. Free Plan schläft nach 15 Min Inaktivität → 30–60 s Lade-Delay beim ersten Hit.    |
| **Healthcheck schlägt fehl**          | `/index.php` muss 200 zurückgeben. Falls `config.php` einen Fatal wirft (z. B. fehlendes `data/`), zeigt das Log den Stacktrace. |
| **Uploads nach Restart weg**          | Disk nicht korrekt gemountet. Render-Dashboard → Service → Environment → Disks: muss `csf-storage` zeigen. Andernfalls schreibt der Container in den Container-FS (flüchtig). |
| **`Permission denied` beim Schreiben**| Disk-Rechte: `chown -R www-data:www-data /var/www/html/render-data` läuft im Entrypoint — bei Persist-Hiccups einmal manuell via Render Shell ausführen. |

### Render Shell (für Diagnose)

Service-Dashboard → **Shell** → liefert Bash-Zugriff im laufenden Container. Nützlich für:

```bash
ls -la /var/www/html/render-data/storage/uploads/
cat /var/www/html/data/ffmpeg-debug.log
ffmpeg -version
df -h /var/www/html/render-data
```

---

## 6. Persistent Disk — wichtige Hinweise

- **An genau einen Service gebunden:** Disks lassen sich nicht zwischen Services teilen. Wenn du die App klonen willst (z. B. Staging), brauchst du eine zweite Disk.
- **Backups:** Render bietet keine automatischen Disk-Snapshots. Manueller Pfad:
  - Render-CLI: `render disks download csf-storage --path /tmp/backup.tar.gz`
  - Oder: Cron-Job in der App (`api/`-Endpoint), der `render-data/` als Tarball nach S3/extern schiebt.
- **Resize:** Live möglich (no-downtime). Verkleinern geht *nicht* — nur größer.
- **Bei Disk-Reset:** Wenn du die Disk im Dashboard löschst und neu erstellst, ist der Inhalt weg. Render-Service muss anschließend einmal redeployed werden, damit der Entrypoint die Ordnerstruktur neu anlegt.

---

## 7. Custom Domain (optional)

1. Render-Dashboard → Service → **Settings → Custom Domains**.
2. Domain eingeben (z. B. `studio.cinematic-family.de`).
3. Render zeigt einen `CNAME`-Record an, den du beim Domain-Provider eintragen musst.
4. Nach DNS-Propagation (5 Min – 24 h) stellt Render automatisch ein TLS-Zertifikat aus (Let's Encrypt).
5. HTTPS-Redirect ist standardmäßig aktiv — kein zusätzlicher Schritt nötig.

---

## 8. Updates ausspielen

Sobald alles steht, ist der Workflow simpel:

```bash
git push origin master
```

Render erkennt den Push, baut neu und tauscht den Container **zero-downtime** aus
(Health Check muss vor Cut-over grün sein). Build-Dauer ~3–5 Min.

Manueller Deploy: Render-Dashboard → **Manual Deploy → Deploy latest commit**
(oder *Clear build cache & deploy* nach Dockerfile-Änderungen, die Caching-Bugs verursachen).

---

**Stand:** 2026-04-29 · TODO #38 · Phase 5
