<?php
declare(strict_types=1);

/* Serve the enhanced crystals.html directly — single source of truth */
$htmlPath = __DIR__ . '/crystals.html';
if (is_file($htmlPath)) {
    header('Content-Type: text/html; charset=utf-8');
    header('Cache-Control: public, max-age=300');
    readfile($htmlPath);
    exit;
}

/* Fallback redirect if HTML file is missing */
header('Location: https://cinematic-vision-studio.de/crystals.html', true, 301);
exit;
