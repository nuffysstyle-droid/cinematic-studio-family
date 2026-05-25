<?php
/**
 * includes/job_service.php — Minimaler Job-Service für Phase 1
 *
 * Nur Job-State-Foundation. Keine Queue-, Worker- oder Billing-Logik.
 */

declare(strict_types=1);

require_once __DIR__ . '/db.php';

/**
 * Erstellt einen neuen Job oder gibt einen bestehenden Job für denselben
 * Idempotency-Key zurück.
 *
 * @param int $userId
 * @param string $jobType
 * @param array<string, mixed> $payload
 * @param string $idempotencyKey
 * @return array<string, mixed>
 */
function csf_job_create(int $userId, string $jobType, array $payload, string $idempotencyKey): array
{
    $pdo = csf_db();
    $payloadJson = json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);

    $existing = csf_job_get_by_idempotency($idempotencyKey);
    if ($existing !== null) {
        return $existing;
    }

    $statement = $pdo->prepare(<<<'SQL'
        INSERT INTO jobs (
            user_id,
            job_type,
            state,
            progress_percent,
            progress_stage,
            estimated_remaining,
            last_worker_message,
            idempotency_key,
            payload_json
        ) VALUES (
            :user_id,
            :job_type,
            :state,
            :progress_percent,
            :progress_stage,
            :estimated_remaining,
            :last_worker_message,
            :idempotency_key,
            :payload_json
        )
    SQL);

    $statement->execute([
        ':user_id'             => $userId,
        ':job_type'            => $jobType,
        ':state'               => 'draft',
        ':progress_percent'    => 0,
        ':progress_stage'      => null,
        ':estimated_remaining' => null,
        ':last_worker_message' => null,
        ':idempotency_key'     => $idempotencyKey,
        ':payload_json'        => $payloadJson,
    ]);

    $jobId = (int) $pdo->lastInsertId();

    return csf_job_get((int) $jobId);
}

/**
 * Lädt einen Job per ID.
 *
 * @param int $jobId
 * @return array<string, mixed>|null
 */
function csf_job_get(int $jobId): ?array
{
    $pdo = csf_db();
    $statement = $pdo->prepare('SELECT * FROM jobs WHERE id = :id');
    $statement->execute([':id' => $jobId]);

    $row = $statement->fetch();
    if ($row === false) {
        return null;
    }

    return csf_job_normalize_row($row);
}

/**
 * Lädt einen Job per Idempotency-Key.
 *
 * @param string $idempotencyKey
 * @return array<string, mixed>|null
 */
function csf_job_get_by_idempotency(string $idempotencyKey): ?array
{
    $pdo = csf_db();
    $statement = $pdo->prepare('SELECT * FROM jobs WHERE idempotency_key = :idempotency_key');
    $statement->execute([':idempotency_key' => $idempotencyKey]);

    $row = $statement->fetch();
    if ($row === false) {
        return null;
    }

    return csf_job_normalize_row($row);
}

/**
 * Aktualisiert den Job-State und optionale Fortschrittsfelder.
 *
 * @param int $jobId
 * @param string $state
 * @param array<string, mixed> $meta
 * @return bool
 */
function csf_job_update_state(int $jobId, string $state, array $meta = []): bool
{
    $pdo = csf_db();

    $statement = $pdo->prepare(<<<'SQL'
        UPDATE jobs
        SET
            state = :state,
            progress_percent = :progress_percent,
            progress_stage = :progress_stage,
            estimated_remaining = :estimated_remaining,
            last_worker_message = :last_worker_message,
            updated_at = datetime('now','utc')
        WHERE id = :id
    SQL);

    $statement->execute([
        ':id'                 => $jobId,
        ':state'              => $state,
        ':progress_percent'   => (int) ($meta['progress_percent'] ?? 0),
        ':progress_stage'     => $meta['progress_stage'] ?? null,
        ':estimated_remaining' => $meta['estimated_remaining'] ?? null,
        ':last_worker_message' => $meta['last_worker_message'] ?? null,
    ]);

    return $statement->rowCount() === 1;
}

/**
 * Normalisiert eine Job-Row in ein konsistentes Array.
 *
 * @param array<string, mixed> $row
 * @return array<string, mixed>
 */
function csf_job_normalize_row(array $row): array
{
    $payload = $row['payload_json'] ?? '{}';

    $decodedPayload = json_decode((string) $payload, true);
    if (!is_array($decodedPayload)) {
        $decodedPayload = [];
    }

    return [
        'id'                  => (int) $row['id'],
        'user_id'             => (int) $row['user_id'],
        'job_type'            => (string) $row['job_type'],
        'state'               => (string) $row['state'],
        'progress_percent'    => (int) $row['progress_percent'],
        'progress_stage'      => $row['progress_stage'] !== null ? (string) $row['progress_stage'] : null,
        'estimated_remaining' => $row['estimated_remaining'] !== null ? (int) $row['estimated_remaining'] : null,
        'last_worker_message' => $row['last_worker_message'] !== null ? (string) $row['last_worker_message'] : null,
        'idempotency_key'     => (string) $row['idempotency_key'],
        'payload'             => $decodedPayload,
        'created_at'          => (string) $row['created_at'],
        'updated_at'          => (string) $row['updated_at'],
    ];
}
