<?php
/**
 * api/projects.php — Projekt-CRUD auf JSON-Basis
 * Speicherort: data/projects/projects.json
 */

require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

// ----------------------------------------------------------------
// Hilfsfunktionen
// ----------------------------------------------------------------

function projectsFilePath(): string {
    return DATA_PATH . 'projects/projects.json';
}

function loadProjects(): array {
    $path = projectsFilePath();

    if (!is_dir(dirname($path))) {
        mkdir(dirname($path), 0755, true);
    }

    if (!file_exists($path)) {
        return [];
    }

    $raw = file_get_contents($path);
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

function saveProjects(array $projects): bool {
    $path = projectsFilePath();
    $json = json_encode(array_values($projects), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return file_put_contents($path, $json, LOCK_EX) !== false;
}

function generateId(): string {
    return bin2hex(random_bytes(8));
}

function respond(bool $success, mixed $data = null, string $error = '', int $status = 200): never {
    http_response_code($status);
    if ($success) {
        echo json_encode(['success' => true, 'data' => $data]);
    } else {
        echo json_encode(['success' => false, 'error' => $error]);
    }
    exit;
}

// ----------------------------------------------------------------
// Action auslesen (GET oder POST)
// ----------------------------------------------------------------

$method = $_SERVER['REQUEST_METHOD'];
$action = trim($_GET['action'] ?? $_POST['action'] ?? '');

// POST-Body als JSON unterstützen
$body = [];
if ($method === 'POST') {
    $raw = file_get_contents('php://input');
    if ($raw) {
        $body = json_decode($raw, true) ?? [];
    }
    // $_POST hat Vorrang wenn kein JSON-Body
    if (!empty($_POST)) {
        $body = array_merge($body, $_POST);
    }
}

function input(string $key, mixed $default = ''): string {
    global $body;
    $val = $body[$key] ?? $_GET[$key] ?? $default;
    return is_string($val) ? trim($val) : (string) $default;
}

// ----------------------------------------------------------------
// Router
// ----------------------------------------------------------------

if (empty($action)) {
    respond(false, null, 'Kein action-Parameter angegeben.', 400);
}

switch ($action) {

    // ------------------------------------------------------------
    // LIST — alle Projekte zurückgeben
    // ------------------------------------------------------------
    case 'list':
        $projects = loadProjects();
        // Neueste zuerst
        usort($projects, fn($a, $b) => strcmp($b['updated_at'], $a['updated_at']));
        respond(true, $projects);

    // ------------------------------------------------------------
    // CREATE — neues Projekt anlegen
    // ------------------------------------------------------------
    case 'create':
        $title       = input('title');
        $type        = input('type');
        $description = input('description');

        if ($title === '') {
            respond(false, null, 'Feld "title" ist erforderlich.', 400);
        }
        if ($type === '') {
            respond(false, null, 'Feld "type" ist erforderlich.', 400);
        }

        $now     = date('c'); // ISO 8601
        $project = [
            'id'          => generateId(),
            'title'       => $title,
            'type'        => $type,
            'description' => $description,
            'created_at'  => $now,
            'updated_at'  => $now,
        ];

        $projects   = loadProjects();
        $projects[] = $project;

        if (!saveProjects($projects)) {
            respond(false, null, 'Projekt konnte nicht gespeichert werden.', 500);
        }

        http_response_code(201);
        respond(true, $project);

    // ------------------------------------------------------------
    // GET — einzelnes Projekt per id
    // ------------------------------------------------------------
    case 'get':
        $id = input('id');
        if ($id === '') {
            respond(false, null, 'Feld "id" ist erforderlich.', 400);
        }

        $projects = loadProjects();
        foreach ($projects as $project) {
            if ($project['id'] === $id) {
                respond(true, $project);
            }
        }

        respond(false, null, 'Projekt nicht gefunden.', 404);

    // ------------------------------------------------------------
    // UPDATE — Felder eines Projekts aktualisieren
    // ------------------------------------------------------------
    case 'update':
        $id = input('id');
        if ($id === '') {
            respond(false, null, 'Feld "id" ist erforderlich.', 400);
        }

        $projects = loadProjects();
        $found    = false;

        foreach ($projects as &$project) {
            if ($project['id'] !== $id) continue;

            $found = true;
            $title       = input('title');
            $type        = input('type');
            $description = input('description');

            if ($title !== '')       $project['title']       = $title;
            if ($type !== '')        $project['type']        = $type;
            // description darf leer gesetzt werden
            if (array_key_exists('description', $body) || isset($_GET['description'])) {
                $project['description'] = $description;
            }

            $project['updated_at'] = date('c');
            $updated = $project;
            break;
        }
        unset($project);

        if (!$found) {
            respond(false, null, 'Projekt nicht gefunden.', 404);
        }

        if (!saveProjects($projects)) {
            respond(false, null, 'Projekt konnte nicht gespeichert werden.', 500);
        }

        respond(true, $updated);

    // ------------------------------------------------------------
    // DELETE — Projekt entfernen
    // ------------------------------------------------------------
    case 'delete':
        $id = input('id');
        if ($id === '') {
            respond(false, null, 'Feld "id" ist erforderlich.', 400);
        }

        $projects = loadProjects();
        $filtered = array_filter($projects, fn($p) => $p['id'] !== $id);

        if (count($filtered) === count($projects)) {
            respond(false, null, 'Projekt nicht gefunden.', 404);
        }

        if (!saveProjects($filtered)) {
            respond(false, null, 'Projekt konnte nicht gelöscht werden.', 500);
        }

        respond(true, ['deleted' => $id]);

    // ------------------------------------------------------------
    // Unbekannte Action
    // ------------------------------------------------------------
    default:
        respond(false, null, "Unbekannte action: \"{$action}\".", 400);
}
