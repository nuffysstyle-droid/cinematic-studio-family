<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$navItems = [
    ['file' => 'index.php',            'label' => 'Home',              'icon' => '🏠'],
    ['file' => 'dashboard.php',        'label' => 'Dashboard',         'icon' => '📊'],
    ['file' => 'new-project.php',      'label' => 'Neues Projekt',     'icon' => '➕'],
    ['file' => 'image-studio.php',     'label' => 'Image Studio',      'icon' => '🖼️'],
    ['file' => 'video-studio.php',     'label' => 'Video Studio',      'icon' => '🎬'],
    ['file' => 'merge-clips.php',      'label' => 'Multi-Scene Export','icon' => '⛓️'],
    ['file' => 'tiktok-studio.php',    'label' => 'TikTok Studio',     'icon' => '🎵'],
    ['file' => 'tiktok-animation.php', 'label' => '↳ Animation',       'icon' => '✨'],
    ['file' => 'tiktok-sticker.php',   'label' => '↳ Sticker',         'icon' => '🎨'],
    ['file' => 'elements.php',         'label' => 'Elements',          'icon' => '🧩'],
    ['file' => 'ready-videos.php',     'label' => 'Ready Videos',      'icon' => '📹'],
    ['file' => 'trailer-builder.php',  'label' => 'Trailer Builder',   'icon' => '🎞️'],
    ['file' => 'academy.php',          'label' => 'Academy',           'icon' => '🎓'],
    ['file' => 'settings.php',         'label' => 'Einstellungen',     'icon' => '⚙️'],
    ['file' => 'api-key.php',          'label' => 'API Key',           'icon' => '🔑'],
];
?>
<aside class="sidebar">
    <div class="sidebar-logo">
        <span><?= APP_NAME ?></span>
    </div>
    <nav class="sidebar-nav">
        <?php foreach ($navItems as $item): ?>
            <a href="<?= BASE_URL ?>/<?= $item['file'] ?>"
               class="nav-item <?= $currentPage === $item['file'] ? 'active' : '' ?>">
                <span class="nav-icon"><?= $item['icon'] ?></span>
                <span class="nav-label"><?= $item['label'] ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
</aside>
