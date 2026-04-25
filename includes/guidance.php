<?php
// Guidance — seitenspezifische Hinweise und Hilfetext-Definitionen
// Wird von den Studio-Seiten eingebunden und im UI als Tooltips/Guides angezeigt

$guidance = [
    'image-studio'     => 'Erstelle und bearbeite Bilder für dein Familienprojekt.',
    'video-studio'     => 'Schneide und kombiniere Videos mit Musik und Effekten.',
    'tiktok-studio'    => 'Erstelle kurze, vertikale Videos für TikTok & Reels.',
    'tiktok-animation' => 'Füge Animationen und Bewegtbild-Elemente hinzu.',
    'tiktok-sticker'   => 'Gestalte individuelle Sticker für deine Videos.',
    'elements'         => 'Verwalte wiederverwendbare Design-Elemente.',
    'ready-videos'     => 'Fertige Vorlagen-Videos, direkt verwendbar.',
    'trailer-builder'  => 'Baue cinematische Trailer aus deinen Clips.',
    'academy'          => 'Lernbereich: Tipps, Tutorials und Best Practices.',
];

function getGuidance(string $page): string {
    global $guidance;
    return $guidance[$page] ?? '';
}
