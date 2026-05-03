#!/bin/sh
# docker/entrypoint.sh — Cinematic Studio Family
# Wird beim Container-Start ausgeführt:
#   1) Dynamischen PORT von Render übernehmen ($PORT, sonst 80)
#   2) Persistent-Disk-Layout vorbereiten (Symlinks für storage/ + data/)
#   3) Berechtigungen setzen
#   4) Apache im Vordergrund starten
#
# Hinweis: lokal ohne Render-Disk fällt das Skript sauber zurück
# (es wird einfach das vorhandene /var/www/html/storage verwendet).

set -e

# ── 1) Apache Port ────────────────────────────────────────────────────────────
# Render setzt $PORT (typisch 10000). Lokal: Default 80.
APACHE_PORT="${PORT:-80}"
export APACHE_PORT
echo "[entrypoint] APACHE_PORT=${APACHE_PORT}"

# ports.conf konsequent auf APACHE_PORT umschreiben (idempotent).
# Apache evaluiert ${APACHE_PORT} aus der Umgebung.
if [ -f /etc/apache2/ports.conf ]; then
    # Vorhandene Listen-Zeilen entfernen, eine saubere setzen
    sed -i '/^[[:space:]]*Listen[[:space:]]/d' /etc/apache2/ports.conf
    echo "Listen \${APACHE_PORT}" >> /etc/apache2/ports.conf
fi

# ── 2) Persistent-Disk Setup ──────────────────────────────────────────────────
# Render Disk wird unter /var/www/html/render-data gemountet (siehe render.yaml).
# Wir spiegeln storage/ und data/ darauf via Symlink — so bleibt der App-Code
# unverändert, aber alle Schreibvorgänge landen auf dem persistenten Volume.
PERSIST_ROOT="${PERSIST_ROOT:-/var/www/html/render-data}"

if [ -d "${PERSIST_ROOT}" ]; then
    echo "[entrypoint] Persistent disk gefunden: ${PERSIST_ROOT}"

    # Standard-Verzeichnisstruktur sicherstellen (idempotent)
    mkdir -p \
        "${PERSIST_ROOT}/storage/uploads/images" \
        "${PERSIST_ROOT}/storage/uploads/videos" \
        "${PERSIST_ROOT}/storage/exports" \
        "${PERSIST_ROOT}/storage/thumbnails" \
        "${PERSIST_ROOT}/storage/temp" \
        "${PERSIST_ROOT}/storage/elements" \
        "${PERSIST_ROOT}/storage/jobs" \
        "${PERSIST_ROOT}/data/projects"

    # Existierende lokale Ordner durch Symlinks ersetzen.
    # rm -rf nur ausführen wenn KEIN Symlink (ein bereits gesetzter Symlink
    # bleibt erhalten, das vermeidet Datenverlust beim Neustart).
    if [ ! -L /var/www/html/storage ]; then
        rm -rf /var/www/html/storage
        ln -sf "${PERSIST_ROOT}/storage" /var/www/html/storage
        echo "[entrypoint] symlink: /var/www/html/storage -> ${PERSIST_ROOT}/storage"
    fi
    if [ ! -L /var/www/html/data ]; then
        rm -rf /var/www/html/data
        ln -sf "${PERSIST_ROOT}/data" /var/www/html/data
        echo "[entrypoint] symlink: /var/www/html/data -> ${PERSIST_ROOT}/data"
    fi

    # Berechtigungen auf der Disk
    chown -R www-data:www-data "${PERSIST_ROOT}" || true
else
    echo "[entrypoint] Keine Persistent Disk unter ${PERSIST_ROOT} — Fallback auf lokales storage/"
fi

# Berechtigungen final sicherstellen (Symlink-Targets folgen automatisch)
chown -R www-data:www-data /var/www/html/storage /var/www/html/data 2>/dev/null || true

# ── 3) Apache starten ─────────────────────────────────────────────────────────
echo "[entrypoint] starting apache2-foreground on port ${APACHE_PORT}"
exec apache2-foreground
