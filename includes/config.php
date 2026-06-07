<?php
declare(strict_types=1);

// App-Konfiguration
define('APP_NAME',    'Cinematic Vision Studio');
define('APP_VERSION', '0.1.0');
define('BASE_URL', '');

// Pfade
define('STORAGE_PATH', __DIR__ . '/../storage/');
define('DATA_PATH',    __DIR__ . '/../data/');

// Erlaubte Upload-Typen und Maximalgröße
define('ALLOWED_VIDEO_TYPES', ['video/mp4', 'video/quicktime', 'video/x-matroska', 'video/webm']);
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp']);
define('MAX_UPLOAD_BYTES', 50 * 1024 * 1024); // 50 MB — konsistent mit studio-demo.php

// API Provider
define('API_PROVIDER_LINK',         'https://kie.ai');
define('API_PROVIDER_CREDITS_LINK', 'https://kie.ai/pricing');
define('API_KEY_MIN_LENGTH', 20);

// Session starten (API-Key nur in Session, nie persistent)
// Session-Name konsistent über alle Seiten — muss VOR session_start() gesetzt werden.
if (session_status() === PHP_SESSION_NONE) {
    $sessionName = getenv('PHP_SESSION_NAME') ?: 'csf_session';
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_name($sessionName);
    session_start();
}
