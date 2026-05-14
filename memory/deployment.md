# memory/deployment.md — Deployment & Infrastruktur

> Letzte Aktualisierung: 2026-05-14

---

## Render.com Setup

| Parameter | Wert |
|---|---|
| **Service-Name** | cinematic-studio-family |
| **Service-ID** | srv-d7pmktog4nts73b5ossg |
| **Plan** | Free → Starter ($7/mo empfohlen für Disk) |
| **Runtime** | Docker (`php:8.2-apache` Base Image) |
| **Branch** | `main` (Auto-Deploy bei Push) |
| **Port** | `$PORT` (Render injiziert, typisch 10000) |
| **Live-URL** | https://cinematic-studio-family.onrender.com |
| **Health-Check** | `/index.php` (Render intern) |

---

## Docker-Stack

### Dockerfile (Schlüssel-Inhalte)

```dockerfile
FROM php:8.2-apache

# System: FFmpeg + Liberation Sans + GD
RUN apt-get install -y ffmpeg fonts-liberation libfreetype6-dev libjpeg62-turbo-dev libpng-dev

# PHP Extensions: GD (für Image-Processing)
RUN docker-php-ext-install gd

# Apache Modules
RUN a2enmod rewrite headers env

# PHP Config
upload_max_filesize = 150M
post_max_size = 155M
max_execution_time = 360
memory_limit = 512M

# Env-Vars (Render injiziert zusätzlich)
FFMPEG_PATH=/usr/bin/ffmpeg
FFPROBE_PATH=/usr/bin/ffprobe
FFMPEG_TIMEOUT=300
PERSIST_ROOT=/var/www/html/render-data

ENTRYPOINT ["/usr/local/bin/csf-entrypoint.sh"]
```

### docker/apache.conf (Schlüssel-Konfiguration)

```apache
<VirtualHost *:${APACHE_PORT}>
    PassEnv KIE_AI_API_KEY          ← KRITISCH: ohne dies sieht PHP getenv() nicht
    Header always set Access-Control-Allow-Origin "*"
    Header always set Access-Control-Allow-Methods "GET, POST, OPTIONS"
    ...
</VirtualHost>
```

**⚠️ Wichtig:** `PassEnv` + `a2enmod env` ist erforderlich, damit PHP via
`getenv('KIE_AI_API_KEY')` auf Render-Environment-Variables zugreifen kann.
Fallback-Chain im PHP-Code: `getenv() ?: $_SERVER[...] ?: $_ENV[...]`

### docker/entrypoint.sh

1. `APACHE_PORT` aus `$PORT` (Render) oder Default 80 setzen
2. Persistent Disk Setup: Symlinks `storage/ → render-data/storage/`, `data/ → render-data/data/`
3. `chown -R www-data` auf storage/data
4. `apache2-foreground` starten

---

## Persistent Disk

| Parameter | Wert |
|---|---|
| **Disk-Name** | csf-storage |
| **Mount-Path** | `/var/www/html/render-data` |
| **Größe** | 1 GB (Free Plan) |
| **Binding** | An Service `cinematic-studio-family` gebunden |

**Struktur auf Disk:**
```
/var/www/html/render-data/
├── storage/
│   ├── uploads/videos/
│   ├── uploads/images/
│   ├── jobs/
│   ├── exports/
│   ├── thumbnails/
│   ├── temp/
│   └── elements/
└── data/
    └── projects/
```

---

## Umgebungsvariablen

### In render.yaml (automatisch gesetzt)
| Key | Wert |
|---|---|
| `FFMPEG_PATH` | `/usr/bin/ffmpeg` |
| `FFPROBE_PATH` | `/usr/bin/ffprobe` |
| `FFMPEG_TIMEOUT` | `300` |
| `PHP_SESSION_NAME` | `csf_session` |
| `PERSIST_ROOT` | `/var/www/html/render-data` |

### Manuell im Render-Dashboard setzen
| Key | Beschreibung | Status |
|---|---|---|
| `KIE_AI_API_KEY` | Kie.ai API-Key für Bildgenerierung | ⚠️ Muss gesetzt werden |

### Automatisch von Render injiziert
| Key | Beschreibung |
|---|---|
| `PORT` | HTTP-Port (typisch 10000) |
| `RENDER` | `true` |
| `RENDER_SERVICE_ID` | Service-Identifier |
| `RENDER_GIT_COMMIT` | Aktueller Commit-Hash |

---

## Deploy-Prozess

```
1. git push origin main
   ↓ Render erkennt Push (Auto-Deploy aktiv)
2. Docker Image Build (~3-5 min, wenn Dockerfile geändert)
   ↓ oder Pull Layer-Cache (~1-2 min ohne Dockerfile-Änderung)
3. Container Start + entrypoint.sh
   ↓ Persistent Disk gemountet, Symlinks gesetzt
4. Apache lauscht auf $PORT
   ↓ Render schaltet Traffic um
5. Health-Check /index.php → 200 OK → Deploy erfolgreich
```

---

## Free Plan Limits (produktionskritisch)

| Limit | Wert | Auswirkung |
|---|---|---|
| RAM | 512 MB | FFmpeg-Preset `ultrafast`, max 3 gleichzeitige Jobs |
| CPU | Shared | Render-Zeit ~5-15s pro 15s-Video |
| HTTP-Timeout | ~30s | Sync nur für kurze Ops — AI-Polling async |
| Ephemeral FS | Nein (Disk vorhanden) | Jobs + Exports persistent (solange Disk da) |
| Sleep on Idle | Nein (Starter+) | Free-Plan schläft → Render.yaml auf `starter` |

---

## Häufige Deploy-Probleme & Lösungen

| Problem | Ursache | Lösung |
|---|---|---|
| `KIE_AI_API_KEY` nicht sichtbar | `PassEnv` fehlte oder Deploy nicht neu | `docker/apache.conf` prüfen + Manual Deploy |
| FFmpeg nicht gefunden | PATH-Problem in PHP | `FFMPEG_PATH` ENV-Var prüfen |
| 503 auf alle Requests | Container crasht beim Start | Render Logs checken → `entrypoint.sh` |
| Storage nicht beschreibbar | Disk nicht gemountet | `api/health.php` → `storage_writable` |
| Cold Start langsam | Free Plan → Service schläft | Plan auf `starter` upgraden |

---

## Monitoring & Debugging

```bash
# Health-Check
curl https://cinematic-studio-family.onrender.com/api/health.php

# Erwartete Antwort wenn alles OK + Key gesetzt:
{
  "ok": true,
  "php": "8.2.31",
  "ffmpeg": { "available": true, "version": "7.1.3-0+deb13u1" },
  "storage_writable": true,
  "ai": { "kie_key_set": true, "kie_key_source": "getenv" }
}
```
