<?php
require_once 'includes/config.php';
require_once 'includes/guidance.php';
$pageTitle = 'Trailer Builder';
$extraJs   = 'editor.js';
require_once 'includes/header.php';
?>

<!-- TODO: Cinematic Trailer aus eigenen Clips, Musik, Titelkarten, FFmpeg-Export -->
<section class="placeholder">
    <h2>Trailer Builder</h2>
    <p><?= getGuidance('trailer-builder') ?></p>
    <p>Trailer-Editor — Inhalt folgt.</p>
</section>

<?php require_once 'includes/footer.php'; ?>
