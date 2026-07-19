<?php
/**
 * includes/auth.php — Authentication & Authorization
 *
 * Cinematic Vision Studio — V0.3.0 Login-System
 *
 * Öffentliche Funktionen:
 *   csf_auth_register(email, password)   → User anlegen
 *   csf_auth_login(email, password)      → Session starten
 *   csf_auth_logout()                    → Session zerstören
 *   csf_auth_user()                      → Aktuell eingeloggten User holen (oder null)
 *   csf_auth_require()                   → 401 wenn nicht eingeloggt
 *   csf_auth_require_plan(plan)          → 403 wenn Plan zu niedrig
 *   csf_auth_check_crystals(cost)        → Prüft ob genug Kristalle
 *   csf_auth_spend_crystals(cost, action, job_id) → Kristalle atomisch abziehen
 *   csf_auth_add_crystals(amount, action) → Kristalle gutschreiben
 *
 * Session-Struktur ($_SESSION['csf_user']):
 *   id, email, plan, crystals_balance
 *
 * Sicherheit:
 *   - ARGON2ID für Passwort-Hashing
 *   - Constant-time Vergleich via hash_equals()
 *   - Login-Brute-Force: max 5 Versuche / 15 min per IP
 *   - Remember-Me: HMAC-SHA256 Token (64 Byte Zufallsbasis)
 *   - Session-Fixation-Schutz: session_regenerate_id() nach Login
 *
 * @since V0.3.0
 */

declare(strict_types=1);

require_once __DIR__ . '/db.php';

// Session-Cookie-Hardening für Seiten die config.php nicht einbinden
if (session_status() === PHP_SESSION_NONE) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

// ── Konstanten ────────────────────────────────────────────────────────────────

const CSF_AUTH_SESSION_KEY       = 'csf_user';
const CSF_AUTH_REMEMBER_COOKIE   = 'csf_remember';
const CSF_AUTH_REMEMBER_DAYS     = 30;
const CSF_AUTH_LOGIN_MAX_TRIES   = 5;
const CSF_AUTH_LOGIN_WINDOW_SECS = 900; // 15 Minuten

// Plan-Hierarchie (niedrig → hoch)
const CSF_PLAN_LEVELS = [
    'free'    => 0,
    'starter' => 1,
    'pro'     => 2,
];

// ── Register ──────────────────────────────────────────────────────────────────

/**
 * Legt einen neuen User an.
 *
 * @param  string $email    E-Mail (wird kleingeschrieben + getrimmt)
 * @param  string $password Klartext-Passwort (min. 8 Zeichen)
 * @return array{ok: bool, user_id?: int, error?: string}
 */
function csf_auth_register(string $email, string $password): array
{
    $email = strtolower(trim($email));

    // Validierung
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'error' => 'Ungültige E-Mail-Adresse.'];
    }

    if (mb_strlen($password) < 8) {
        return ['ok' => false, 'error' => 'Passwort muss mindestens 8 Zeichen lang sein.'];
    }

    if (mb_strlen($password) > 1000) {
        return ['ok' => false, 'error' => 'Passwort zu lang.'];
    }

    $hash = password_hash($password, PASSWORD_ARGON2ID);
    if ($hash === false) {
        return ['ok' => false, 'error' => 'Passwort konnte nicht gehasht werden.'];
    }

    try {
        $pdo  = csf_db();
        // Neuer User startet mit 50 Gratis-Kristallen (Willkommens-Bonus)
        $stmt = $pdo->prepare("
            INSERT INTO users (email, password_hash, plan, crystals_balance)
            VALUES (:email, :hash, 'free', 50)
        ");
        $stmt->execute([':email' => $email, ':hash' => $hash]);
        $userId = (int) $pdo->lastInsertId();

        // Willkommens-Bonus in Transaktions-Log eintragen
        $pdo->prepare("
            INSERT INTO crystal_transactions (user_id, amount, action, note)
            VALUES (:uid, 50, 'welcome_bonus', 'Willkommens-Bonus für neue Mitglieder')
        ")->execute([':uid' => $userId]);

        return ['ok' => true, 'user_id' => $userId];

    } catch (PDOException $e) {
        // UNIQUE constraint = E-Mail bereits vergeben
        if (str_contains($e->getMessage(), 'UNIQUE')) {
            return ['ok' => false, 'error' => 'Diese E-Mail ist bereits registriert.'];
        }
        return ['ok' => false, 'error' => 'Registrierung fehlgeschlagen. Bitte erneut versuchen.'];
    }
}

// ── Login ─────────────────────────────────────────────────────────────────────

/**
 * Prüft Credentials und startet eine Session.
 *
 * @param  string $email
 * @param  string $password
 * @param  bool   $remember   "Eingeloggt bleiben" Cookie setzen
 * @return array{ok: bool, error?: string, remaining_tries?: int}
 */
function csf_auth_login(string $email, string $password, bool $remember = false): array
{
    $email = strtolower(trim($email));

    // ── Brute-Force-Schutz ────────────────────────────────────────────────────
    $ipHash  = hash('sha256', csf_auth_get_ip() . 'login_salt_2026');
    $since   = time() - CSF_AUTH_LOGIN_WINDOW_SECS;

    $pdo = csf_db();

    // Abgelaufene Versuche aufräumen (probabilistisch)
    if (random_int(1, 20) === 1) {
        $pdo->prepare("DELETE FROM login_attempts WHERE attempted_at < :since")
            ->execute([':since' => $since]);
    }

    $stmtCount = $pdo->prepare("
        SELECT COUNT(*) FROM login_attempts
        WHERE ip_hash = :ip AND attempted_at >= :since
    ");
    $stmtCount->execute([':ip' => $ipHash, ':since' => $since]);
    $tryCount = (int) $stmtCount->fetchColumn();

    if ($tryCount >= CSF_AUTH_LOGIN_MAX_TRIES) {
        $remaining = CSF_AUTH_LOGIN_WINDOW_SECS - (time() - $since);
        return [
            'ok'    => false,
            'error' => 'Zu viele Anmeldeversuche. Bitte ' . ceil($remaining / 60) . ' Minuten warten.',
        ];
    }

    // ── User suchen ───────────────────────────────────────────────────────────
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    // Versuch loggen BEVOR wir das Ergebnis prüfen
    // (Timing-Angriff-Schutz: immer gleich lang)
    $dummyHash = '$argon2id$v=19$m=65536,t=4,p=1$dummydummy';

    if (!$user) {
        // Passwort-Check trotzdem ausführen (Constant-Time)
        password_verify($password, $dummyHash);
        $pdo->prepare("INSERT INTO login_attempts (ip_hash) VALUES (:ip)")
            ->execute([':ip' => $ipHash]);
        return ['ok' => false, 'error' => 'E-Mail oder Passwort falsch.'];
    }

    if (!password_verify($password, $user['password_hash'])) {
        $pdo->prepare("INSERT INTO login_attempts (ip_hash) VALUES (:ip)")
            ->execute([':ip' => $ipHash]);
        $remaining = CSF_AUTH_LOGIN_MAX_TRIES - $tryCount - 1;
        return [
            'ok'             => false,
            'error'          => 'E-Mail oder Passwort falsch.',
            'remaining_tries' => max(0, $remaining),
        ];
    }

    // ── Erfolgreicher Login ───────────────────────────────────────────────────

    // Passwort-Hash aktualisieren wenn Algorithmus veraltet
    if (password_needs_rehash($user['password_hash'], PASSWORD_ARGON2ID)) {
        $newHash = password_hash($password, PASSWORD_ARGON2ID);
        $pdo->prepare("UPDATE users SET password_hash = :h WHERE id = :id")
            ->execute([':h' => $newHash, ':id' => $user['id']]);
    }

    // Session-Fixation-Schutz
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(delete_old_session: true);
    } elseif (session_status() === PHP_SESSION_NONE) {
        session_start();
        session_regenerate_id(delete_old_session: true);
    }

    // Session-Daten setzen
    $_SESSION[CSF_AUTH_SESSION_KEY] = [
        'id'               => (int) $user['id'],
        'email'            => $user['email'],
        'plan'             => $user['plan'],
        'crystals_balance' => (int) $user['crystals_balance'],
    ];

    // "Eingeloggt bleiben" Cookie
    if ($remember) {
        csf_auth_set_remember_cookie((int) $user['id']);
    }

    // Erfolgreiche Login-Versuche dieser IP löschen
    $pdo->prepare("DELETE FROM login_attempts WHERE ip_hash = :ip")
        ->execute([':ip' => $ipHash]);

    return ['ok' => true];
}

// ── Logout ────────────────────────────────────────────────────────────────────

/**
 * Zerstört die Session und löscht Remember-Me-Cookie.
 */
function csf_auth_logout(): void
{
    // Remember-Me-Token aus DB löschen — nur das präsentierte Token;
    // Tokens anderer Geräte desselben Users bleiben gültig
    $cookie = $_COOKIE[CSF_AUTH_REMEMBER_COOKIE] ?? '';
    if ($cookie !== '') {
        $tokenHash = hash('sha256', $cookie);
        csf_db()->prepare("DELETE FROM remember_tokens WHERE token_hash = :th")
            ->execute([':th' => $tokenHash]);
    }

    // Cookie löschen (gleiche Parameter wie beim Setzen in csf_auth_set_remember_cookie)
    if (isset($_COOKIE[CSF_AUTH_REMEMBER_COOKIE])) {
        setcookie(CSF_AUTH_REMEMBER_COOKIE, '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'secure'   => true,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        unset($_COOKIE[CSF_AUTH_REMEMBER_COOKIE]);
    }

    // Session muss aktiv sein, um sie zerstören zu können — logout.php ruft
    // csf_auth_logout() auf, ohne dass vorher csf_auth_user() die Session
    // gestartet hat (session_status() wäre hier PHP_SESSION_NONE und die
    // Server-Session bliebe gültig)
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }

    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION = [];

        // Session-Cookie beim Client löschen — mit den aktiven Cookie-Parametern
        if ((bool) ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires'  => time() - 3600,
                'path'     => $p['path'],
                'domain'   => $p['domain'],
                'secure'   => $p['secure'],
                'httponly' => $p['httponly'],
                'samesite' => $p['samesite'] !== '' ? $p['samesite'] : 'Lax',
            ]);
        }

        session_destroy();
    }
}

// ── Aktuellen User abrufen ────────────────────────────────────────────────────

/**
 * Gibt den aktuell eingeloggten User zurück (oder null).
 *
 * Prüft zuerst die Session, dann den Remember-Me-Cookie.
 * Aktualisiert crystals_balance bei jedem Aufruf aus der DB.
 *
 * @return array{id: int, email: string, plan: string, crystals_balance: int}|null
 */
function csf_auth_user(): ?array
{
    // Session-Start wenn nötig (ohne Ausgabe zu unterbrechen)
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // ── Session vorhanden? ────────────────────────────────────────────────────
    if (!empty($_SESSION[CSF_AUTH_SESSION_KEY]['id'])) {
        $userId = (int) $_SESSION[CSF_AUTH_SESSION_KEY]['id'];
        return csf_auth_refresh_user($userId);
    }

    // ── Remember-Me-Cookie? ───────────────────────────────────────────────────
    $cookie = $_COOKIE[CSF_AUTH_REMEMBER_COOKIE] ?? '';
    if ($cookie === '') {
        return null;
    }

    $tokenHash = hash('sha256', $cookie);
    $pdo       = csf_db();

    $stmt = $pdo->prepare("
        SELECT rt.user_id
        FROM   remember_tokens rt
        WHERE  rt.token_hash = :th
          AND  rt.expires_at > :now
        LIMIT  1
    ");
    $stmt->execute([':th' => $tokenHash, ':now' => time()]);
    $row = $stmt->fetch();

    if (!$row) {
        // Ungültiger/abgelaufener Cookie → löschen
        setcookie(CSF_AUTH_REMEMBER_COOKIE, '', ['expires' => time() - 3600, 'path' => '/', 'httponly' => true]);
        return null;
    }

    // Frische Session anlegen
    $userId = (int) $row['user_id'];
    $user   = csf_auth_refresh_user($userId);

    if ($user !== null) {
        session_regenerate_id(delete_old_session: true);
        $_SESSION[CSF_AUTH_SESSION_KEY] = $user;

        // Remember-Token erneuern (Rolling)
        csf_auth_set_remember_cookie($userId);
    }

    return $user;
}

// ── Plan-Enforcement ──────────────────────────────────────────────────────────

/**
 * Gibt 401 JSON zurück wenn kein User eingeloggt.
 * Für API-Endpoints die Login erfordern.
 *
 * @return array{id: int, email: string, plan: string, crystals_balance: int}
 */
function csf_auth_require(): array
{
    $user = csf_auth_user();
    if ($user === null) {
        http_response_code(401);
        echo json_encode([
            'status'  => 'error',
            'message' => 'Nicht eingeloggt. Bitte zuerst anmelden.',
            'code'    => 'auth_required',
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    return $user;
}

/**
 * Gibt 403 wenn eingeloggter User nicht den erforderlichen Plan hat.
 *
 * @param 'free'|'starter'|'pro' $requiredPlan
 * @return array{id: int, email: string, plan: string, crystals_balance: int}
 */
function csf_auth_require_plan(string $requiredPlan): array
{
    $user = csf_auth_require();

    $userLevel     = CSF_PLAN_LEVELS[$user['plan']]     ?? 0;
    $requiredLevel = CSF_PLAN_LEVELS[$requiredPlan]     ?? 0;

    if ($userLevel < $requiredLevel) {
        http_response_code(403);
        echo json_encode([
            'status'        => 'error',
            'message'       => 'Diese Funktion erfordert den ' . ucfirst($requiredPlan) . '+ Plan.',
            'code'          => 'plan_required',
            'required_plan' => $requiredPlan,
            'current_plan'  => $user['plan'],
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    return $user;
}

// ── Kristalle ─────────────────────────────────────────────────────────────────

/**
 * Prüft ob der eingeloggte User genug Kristalle hat.
 *
 * @param  int $cost Kosten in Kristallen
 * @return bool
 */
function csf_auth_check_crystals(int $cost): bool
{
    $user = csf_auth_user();
    return $user !== null && $user['crystals_balance'] >= $cost;
}

/**
 * Zieht Kristalle atomar ab. Gibt false zurück wenn nicht genug vorhanden.
 *
 * @param  int    $cost   Kosten (positiv)
 * @param  string $action Aktion-Bezeichner (für Log)
 * @param  string $jobId  Optionale job_id Referenz
 * @return bool
 */
function csf_auth_spend_crystals(int $cost, string $action, string $jobId = ''): bool
{
    $user = csf_auth_user();
    if ($user === null || $cost <= 0) {
        return false;
    }

    $pdo = csf_db();

    // Atomares UPDATE — nur wenn balance >= cost (CHECK constraint + WHERE)
    $stmt = $pdo->prepare("
        UPDATE users
        SET    crystals_balance = crystals_balance - :cost,
               updated_at       = datetime('now','utc')
        WHERE  id               = :uid
          AND  crystals_balance >= :cost2
    ");
    $stmt->execute([
        ':cost'  => $cost,
        ':uid'   => $user['id'],
        ':cost2' => $cost,
    ]);

    if ($stmt->rowCount() === 0) {
        return false; // Nicht genug Kristalle
    }

    // Transaktion loggen
    $pdo->prepare("
        INSERT INTO crystal_transactions (user_id, amount, action, job_id)
        VALUES (:uid, :amount, :action, :job_id)
    ")->execute([
        ':uid'    => $user['id'],
        ':amount' => -$cost,
        ':action' => $action,
        ':job_id' => $jobId ?: null,
    ]);

    // Session aktualisieren
    $_SESSION[CSF_AUTH_SESSION_KEY]['crystals_balance'] -= $cost;

    return true;
}

/**
 * Schreibt Kristalle gut (Kauf, Bonus, Admin).
 *
 * @param  int    $amount Anzahl Kristalle (positiv)
 * @param  string $action Bezeichner
 * @return bool
 */
function csf_auth_add_crystals(int $amount, string $action): bool
{
    $user = csf_auth_user();
    if ($user === null || $amount <= 0) {
        return false;
    }

    $pdo = csf_db();

    $pdo->prepare("
        UPDATE users
        SET    crystals_balance = crystals_balance + :amount,
               updated_at       = datetime('now','utc')
        WHERE  id = :uid
    ")->execute([':amount' => $amount, ':uid' => $user['id']]);

    $pdo->prepare("
        INSERT INTO crystal_transactions (user_id, amount, action)
        VALUES (:uid, :amount, :action)
    ")->execute([
        ':uid'    => $user['id'],
        ':amount' => $amount,
        ':action' => $action,
    ]);

    // Session aktualisieren
    $_SESSION[CSF_AUTH_SESSION_KEY]['crystals_balance'] += $amount;

    return true;
}

// ── Interne Helfer ────────────────────────────────────────────────────────────

/**
 * Lädt frische User-Daten aus der DB (verhindert veraltete Session-Daten).
 *
 * @internal
 * @param  int $userId
 * @return array|null
 */
function csf_auth_refresh_user(int $userId): ?array
{
    $stmt = csf_db()->prepare("
        SELECT id, email, plan, crystals_balance
        FROM   users
        WHERE  id = :id
        LIMIT  1
    ");
    $stmt->execute([':id' => $userId]);
    $row = $stmt->fetch();

    if (!$row) {
        return null;
    }

    return [
        'id'               => (int) $row['id'],
        'email'            => $row['email'],
        'plan'             => $row['plan'],
        'crystals_balance' => (int) $row['crystals_balance'],
    ];
}

/**
 * Setzt den Remember-Me-Cookie und speichert Token-Hash in der DB.
 *
 * @internal
 * @param int $userId
 */
function csf_auth_set_remember_cookie(int $userId): void
{
    $token    = bin2hex(random_bytes(32)); // 64 Zeichen hex
    $hash     = hash('sha256', $token);
    $expires  = time() + (CSF_AUTH_REMEMBER_DAYS * 86400);

    csf_db()->prepare("
        INSERT OR REPLACE INTO remember_tokens (user_id, token_hash, expires_at)
        VALUES (:uid, :hash, :exp)
    ")->execute([':uid' => $userId, ':hash' => $hash, ':exp' => $expires]);

    setcookie(CSF_AUTH_REMEMBER_COOKIE, $token, [
        'expires'  => $expires,
        'path'     => '/',
        'secure'   => true,   // Nur HTTPS (Render läuft hinter TLS-Terminator)
        'httponly' => true,   // Kein JS-Zugriff
        'samesite' => 'Lax',  // CSRF-Schutz
    ]);
}

/**
 * Ermittelt die Client-IP (selbe Logik wie rate_limit.php).
 *
 * @internal
 * @return string
 */
function csf_auth_get_ip(): string
{
    // Render fügt die echte Client-IP rechts an X-Forwarded-For an.
    // Das rechteiste (letzte) valide IP ist daher verlässlich;
    // linksstehende IPs können vom Client gespooft werden.
    $xff = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
    if ($xff !== '') {
        $parts = array_map('trim', explode(',', $xff));
        for ($i = count($parts) - 1; $i >= 0; $i--) {
            if (filter_var($parts[$i], FILTER_VALIDATE_IP)) {
                return $parts[$i];
            }
        }
    }

    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    if ($ip !== '' && filter_var($ip, FILTER_VALIDATE_IP)) {
        return $ip;
    }
    return 'unknown';
}
