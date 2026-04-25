<?php
require_once 'includes/config.php';
require_once 'includes/guidance.php';
$pageTitle = 'Academy';
require_once 'includes/header.php';
?>

<!-- TODO: Tutorials, Tipps, Best Practices, Video-Guides für alle Studios -->
<section class="placeholder">
    <h2>Academy</h2>
    <p><?= getGuidance('academy') ?></p>
    <p>Lernbereich — Inhalt folgt.</p>
</section>

<?php require_once 'includes/footer.php'; ?>
