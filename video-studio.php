<?php
require_once 'includes/config.php';
require_once 'includes/guidance.php';
$pageTitle = 'Video Studio';
$extraJs   = 'editor.js';
require_once 'includes/header.php';
?>

<!-- TODO: Video-Upload, Timeline-Editor, Schnitt, Musik, Export via FFmpeg -->
<section class="placeholder">
    <h2>Video Studio</h2>
    <p><?= getGuidance('video-studio') ?></p>
    <p>Timeline-Editor und FFmpeg-Export — Inhalt folgt.</p>
</section>

<?php require_once 'includes/footer.php'; ?>
