<?php
require_once 'includes/config.php';
require_once 'includes/guidance.php';
$pageTitle = 'Image Studio';
$extraJs   = 'editor.js';
require_once 'includes/header.php';
?>

<!-- TODO: Bild-Upload, KI-Bildgenerierung, Bearbeitung, Export -->
<section class="placeholder">
    <h2>Image Studio</h2>
    <p><?= getGuidance('image-studio') ?></p>
    <p>Bild-Editor und KI-Generierung — Inhalt folgt.</p>
</section>

<?php require_once 'includes/footer.php'; ?>
