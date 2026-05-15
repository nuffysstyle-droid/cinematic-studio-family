<?php
/**
 * includes/rate_limit.php — IP-basiertes Rate Limiting (dateibasiert, kein DB)
 *
 * Speichert Counters als JSON-Dateien in storage/rate_limits/.
 * Jede Datei repräsentiert eine IP (SHA-256-Hash) + Aktion + Zeitfenster.
 *
 * Öffentliche Funktionen:
 *   csf_rate_limit_check(string $action, int $maxRequests, int $windowSeconds)
 *     → Prüft Limit und zählt hoch. Gibt Array zurück.
 *   csf_rate_limit_cleanup()
 *     → Löscht abgelaufene Rate-Limit-Dateien (probabilistisch aufrufen).
 *
 * Sicherheit:
 *   - IP wird via SHA-256 gehashed (nie plaintext gespeichert)
 *   - LOCK_EX bei jedem Schreibzugriff (race-condition-sicher)
 *   - Nur Dateien mit Prefix rl_ werden berührt
 *
 * @since V0.2.0 — Rate Limiting
 */

declare(strict_types=1);

// ── Konstante ──────────────────────────────────────────────────────────────────

/** Verzeichnis für Rate-Limit-Dateien */
define('CSF_RATE_LIMIT_DIR', CSF_STORAGE_ROOT . '/rate_limits');

// ── Öffentliche API ────────────────────────────────────────────────────────────

/**
 * Prüft ob die aktuelle IP das Rate Limit für eine Aktion überschritten hat.
 * Zählt den Request hoch wenn erlaubt.
 *
 * @param  string $action        Aktion-Bezeichner (z. B. 'generate_ai', 'upload')
 * @param  int    $maxRequests   Maximale Requests im Zeitfenster
 * @param  int    $windowSeconds Zeitfenster in Sekunden (Standard: 3600 = 1h)
 * @return array{
 *   allowed:    bool,
 *   remaining:  int,
 *   limit:      int,
 *   reset_at:   int,
 *   reset_in:   int
 * }
 */
function csf_rate_limit_check(
    string $action,
    int $maxRequests,
    int $windowSeconds = 3600
): array {
    csf_rate_limit_ensure_dir();

    $ip     = csf_rate_limit_get_ip();
    $ipHash = hash('sha256', $ip . $action); // IP + Aktion kombiniert hashen
    $now    = time();

    // Slot = aktuelles Zeitfenster (floor zu Fenstergröße)
    $slot    = (int) floor($now / $windowSeconds);
    $resetAt = ($slot + 1) * $windowSeconds;

    $filename = CSF_RATE_LIMIT_DIR . '/rl_' . substr($ipHash, 0, 32) . '_' . $slot . '.json';

    // Datei mit LOCK_EX öffnen (atomares Read-Modify-Write)
    $fp = @fopen($filename, 'c+');
    if ($fp === false) {
        // Kann nicht schreiben → Limit als erlaubt annehmen (fail-open)
        return ['allowed' => true, 'remaining' => $maxRequests - 1, 'limit' => $maxRequests,
                'reset_at' => $resetAt, 'reset_in' => $resetAt - $now];
    }

    if (!flock($fp, LOCK_EX)) {
        fclose($fp);
        return ['allowed' => true, 'remaining' => $maxRequests - 1, 'limit' => $maxRequests,
                'reset_at' => $resetAt, 'reset_in' => $resetAt - $now];
    }

    $raw   = stream_get_contents($fp);
    $data  = ($raw !== false && $raw !== '') ? (json_decode($raw, true) ?? []) : [];

    $count = (int)($data['count'] ?? 0);
    $count++;

    $allowed   = $count <= $maxRequests;
    $remaining = max(0, $maxRequests - $count);

    // Nur schreiben wenn erlaubt (verhindert Counter-Inflation durch Blocker)
    if ($allowed) {
        $newData = json_encode(['count' => $count, 'slot' => $slot, 'action' => $action]);
        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, (string)$newData);
        fflush($fp);
    }

    flock($fp, LOCK_UN);
    fclose($fp);

    return [
        'allowed'   => $allowed,
        'remaining' => $remaining,
        'limit'     => $maxRequests,
        'reset_at'  => $resetAt,
        'reset_in'  => $resetAt - $now,
    ];
}

/**
 * Löscht abgelaufene Rate-Limit-Dateien aus CSF_RATE_LIMIT_DIR.
 * Sollte probabilistisch aufgerufen werden (z. B. 1/100).
 *
 * @return int Anzahl gelöschter Dateien
 */
function csf_rate_limit_cleanup(): int {
    if (!is_dir(CSF_RATE_LIMIT_DIR)) {
        return 0;
    }

    $now     = time();
    $deleted = 0;
    $entries = @scandir(CSF_RATE_LIMIT_DIR);

    if (!is_array($entries)) {
        return 0;
    }

    foreach ($entries as $entry) {
        if (!str_starts_with($entry, 'rl_')) {
            continue;
        }

        $filePath = CSF_RATE_LIMIT_DIR . '/' . $entry;
        if (!is_file($filePath)) {
            continue;
        }

        // Datei älter als 2h? Löschen.
        if (($now - (int)filemtime($filePath)) > 7200) {
            @unlink($filePath);
            $deleted++;
        }
    }

    return $deleted;
}

// ── Interne Helfer ─────────────────────────────────────────────────────────────

/**
 * Stellt sicher dass das Rate-Limit-Verzeichnis existiert.
 *
 * @internal
 */
function csf_rate_limit_ensure_dir(): void {
    if (!is_dir(CSF_RATE_LIMIT_DIR)) {
        @mkdir(CSF_RATE_LIMIT_DIR, 0755, true);

        // .htaccess: Verzeichnis vom Web sperren
        $htaccess = CSF_RATE_LIMIT_DIR . '/.htaccess';
        if (!file_exists($htaccess)) {
            @file_put_contents($htaccess, "Deny from all\n");
        }
    }
}

/**
 * Ermittelt die echte Client-IP (Proxy-Headers berücksichtigt).
 * Gibt immer einen validen String zurück.
 *
 * @internal
 * @return string IP-Adresse (oder 'unknown')
 */
function csf_rate_limit_get_ip(): string {
    // Render.com sendet echte IP in X-Forwarded-For
    $headers = [
        'HTTP_CF_CONNECTING_IP',   // Cloudflare (falls später vorgeschaltet)
        'HTTP_X_FORWARDED_FOR',    // Render / Load Balancer
        'HTTP_X_REAL_IP',          // Nginx-Proxy
        'REMOTE_ADDR',             // Direkt (lokale Dev-Umgebung)
    ];

    foreach ($headers as $h) {
        $val = $_SERVER[$h] ?? '';
        if ($val === '') {
            continue;
        }

        // X-Forwarded-For kann kommagetrennte Liste sein → erste IP nehmen
        $ip = trim(explode(',', $val)[0]);

        // Valide IPv4 oder IPv6?
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return $ip;
        }

        // Lokale IPs (Render internal, Dev-Loopback): REMOTE_ADDR direkt verwenden
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }
    }

    return 'unknown';
}
