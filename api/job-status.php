<?php
/**
 * api/job-status.php — Job Status Polling (Platzhalter V1)
 * Wird mit echter Seedance/Kie.ai API in Phase 4 verbunden.
 */

require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Methode nicht erlaubt.']);
    exit;
}

// TODO Phase 4: job_id aus Query lesen, echten API-Status abfragen
// $jobId = trim($_GET['job_id'] ?? '');

echo json_encode([
    'success' => true,
    'status'  => 'placeholder',
    'message' => 'Job status polling will be connected with the real API later.',
]);
