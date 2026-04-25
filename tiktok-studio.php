<?php
require_once 'includes/config.php';
require_once 'includes/guidance.php';
$pageTitle = 'TikTok Studio';
$extraJs   = 'editor.js';
require_once 'includes/header.php';
?>

<!-- TODO: Vertikales 9:16 Format, Schnitt, Musik, Untertitel, Export -->
<section class="placeholder">
    <h2>TikTok Studio</h2>
    <p><?= getGuidance('tiktok-studio') ?></p>
    <p>Kurzvideo-Editor (9:16) — Inhalt folgt.</p>
    <nav class="sub-nav">
        <a href="tiktok-animation.php">↳ Animation</a>
        <a href="tiktok-sticker.php">↳ Sticker</a>
    </nav>
</section>

<?php require_once 'includes/footer.php'; ?>
