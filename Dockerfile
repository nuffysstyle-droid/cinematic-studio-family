# Dockerfile — Cinematic Studio Family
# PHP 8.2 + Apache + FFmpeg für Render.com
#
# Entscheidung Phase 4: Dockerfile statt Buildpacks
# → FFmpeg-Version kontrollierbar, zuverlässiger Build
#
# Lokales Testen:
#   docker build -t cinematic-studio .
#   docker run -p 8080:80 -e FFMPEG_PATH=/usr/bin/ffmpeg cinematic-studio
#
# Render: Apache lauscht auf $PORT (z. B. 10000) — gehandhabt vom Entrypoint.

FROM php:8.2-apache

# ── System-Abhängigkeiten ─────────────────────────────────────────────────────
RUN apt-get update && apt-get install -y --no-install-recommends \
        ffmpeg \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" gd \
    && rm -rf /var/lib/apt/lists/*

# ── PHP-Konfiguration ─────────────────────────────────────────────────────────
RUN { \
        echo 'upload_max_filesize = 150M'; \
        echo 'post_max_size = 155M'; \
        echo 'max_execution_time = 360'; \
        echo 'memory_limit = 512M'; \
        echo 'session.gc_maxlifetime = 3600'; \
    } > /usr/local/etc/php/conf.d/csf.ini

# ── Apache-Konfiguration ──────────────────────────────────────────────────────
RUN a2enmod rewrite headers

COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

# ports.conf entschlacken — Listen wird im Entrypoint dynamisch gesetzt.
RUN sed -i '/^[[:space:]]*Listen[[:space:]]/d' /etc/apache2/ports.conf \
    && echo 'Listen ${APACHE_PORT}' >> /etc/apache2/ports.conf

# ── Arbeitsverzeichnis ────────────────────────────────────────────────────────
WORKDIR /var/www/html

# ── Projektdateien kopieren ───────────────────────────────────────────────────
COPY . .

# ── Verzeichnisse anlegen + Berechtigungen setzen ────────────────────────────
# Auf Render werden storage/ und data/ via Symlink auf die Persistent Disk
# (/var/www/html/render-data) umgebogen — das übernimmt der Entrypoint.
RUN mkdir -p \
        storage/uploads/images \
        storage/uploads/videos \
        storage/exports \
        storage/thumbnails \
        storage/temp \
        storage/elements \
        data/projects \
    && chown -R www-data:www-data storage data \
    && chmod -R 755 storage data

# ── Entrypoint Script ─────────────────────────────────────────────────────────
COPY docker/entrypoint.sh /usr/local/bin/csf-entrypoint.sh
RUN chmod +x /usr/local/bin/csf-entrypoint.sh

# ── Umgebungsvariablen ────────────────────────────────────────────────────────
ENV FFMPEG_PATH=/usr/bin/ffmpeg
ENV FFPROBE_PATH=/usr/bin/ffprobe
ENV FFMPEG_TIMEOUT=300
ENV PERSIST_ROOT=/var/www/html/render-data

# ── Port ──────────────────────────────────────────────────────────────────────
# Dokumentarisch — der tatsächliche Port wird vom Entrypoint aus $PORT übernommen.
EXPOSE 10000

# ── Startbefehl ───────────────────────────────────────────────────────────────
ENTRYPOINT ["/usr/local/bin/csf-entrypoint.sh"]
