<?php
// Prompt Engine — baut AI-Prompts aus strukturierten Parametern
// API-Key kommt ausschließlich vom Client (sessionStorage), nie serverseitig gespeichert

/**
 * Gibt einen leeren Prompt-Kontext zurück.
 * Wird von den Studio-Seiten befüllt und via JS an die AI gesendet.
 */
function buildPromptContext(string $type, array $params = []): array {
    return [
        'type'   => $type,
        'params' => $params,
        // Platzhalter — Prompt-Templates folgen in einem späteren Task
    ];
}
