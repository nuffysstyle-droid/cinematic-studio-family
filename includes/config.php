<?php
// App-Konfiguration
define('APP_NAME', 'Cinematic Studio Family');
define('APP_VERSION', '0.1.0');
define('BASE_URL', '');

// Pfade
define('STORAGE_PATH', __DIR__ . '/../storage/');
define('DATA_PATH',    __DIR__ . '/../data/');

// Erlaubte Upload-Typen und Maximalgröße
define('ALLOWED_VIDEO_TYPES', ['video/mp4', 'video/quicktime', 'video/x-matroska']);
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp']);
define('MAX_UPLOAD_BYTES', 500 * 1024 * 1024); // 500 MB

// API Provider — Referral-Link hier eintragen
define('API_PROVIDER_LINK',         'https://DEIN-REFERRAL-LINK-HIER');
define('API_PROVIDER_CREDITS_LINK', 'https://DEIN-REFERRAL-LINK-HIER/credits');
define('API_KEY_MIN_LENGTH', 20); // Mindestlänge für einfache Validierung

// Session starten (API-Key nur in Session, nie persistent)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
