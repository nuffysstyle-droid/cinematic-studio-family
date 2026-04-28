# Dockerfile — Cinematic Studio Family
# PHP 8.2 + Apache + FFmpeg für Render.com
#
# Entscheidung Phase 4: Dockerfile statt Buildpacks
# → FFmpeg-Version kontrollierbar, zuverlässiger Build
#
# Lokales Testen:
#   docker build -t cinematic-studio .
#   docker run -p 8080:80 -e FFMPEG_PATH=/usr/bin/ffmpeg cinematic-studio

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
RUN a2enmod rewrite

COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

# ── Arbeitsverzeichnis ────────────────────────────────────────────────────────
WORKDIR /var/www/html

# ── Projektdateien kopieren ───────────────────────────────────────────────────
COPY . .

# ── Verzeichnisse anlegen + Berechtigungen setzen ────────────────────────────
# Render Disk wird unter /var/www/html/storage gemountet (render.yaml).
# Diese Verzeichnisse werden beim Start erstellt falls die Disk leer ist.
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

# ── Umgebungsvariablen ────────────────────────────────────────────────────────
ENV FFMPEG_PATH=/usr/bin/ffmpeg
ENV FFPROBE_PATH=/usr/bin/ffprobe
ENV FFMPEG_TIMEOUT=300

# ── Port ──────────────────────────────────────────────────────────────────────
EXPOSE 80
