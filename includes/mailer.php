<?php
declare(strict_types=1);

/**
 * includes/mailer.php — E-Mail-Versand via Mailgun REST API
 *
 * Konfiguration (Render Env-Vars):
 *   MAILGUN_API_KEY  — Mailgun Private Key (key-...)
 *   MAILGUN_DOMAIN   — Sending Domain (z.B. mg.cinematic-vision-studio.de)
 *   APP_FROM_EMAIL   — Absender-Adresse (optional, default: noreply@{domain})
 *   APP_URL          — Basis-URL ohne trailing slash
 *
 * Alle Funktionen sind best-effort: Fehler werden geloggt aber nie nach oben
 * geworfen. E-Mail-Ausfall darf Registration/Reset nicht blockieren.
 */

/**
 * Sendet eine E-Mail via Mailgun.
 * Gibt ['ok'=>true] wenn gesendet oder wenn Mailgun nicht konfiguriert (silent skip).
 *
 * @return array{ok:bool, error:string}
 */
function csf_mail_send(
    string $to,
    string $subject,
    string $textBody,
    string $htmlBody = ''
): array {
    $apiKey = (string) (getenv('MAILGUN_API_KEY') ?: ($_SERVER['MAILGUN_API_KEY'] ?? ''));
    $domain = (string) (getenv('MAILGUN_DOMAIN')  ?: ($_SERVER['MAILGUN_DOMAIN']  ?? ''));

    if ($apiKey === '' || $domain === '') {
        // Mailgun nicht konfiguriert — still überspringen, kein Hard-Fail
        return ['ok' => true, 'error' => ''];
    }

    $fromEnv = (string) (getenv('APP_FROM_EMAIL') ?: ($_SERVER['APP_FROM_EMAIL'] ?? ''));
    $from    = $fromEnv !== '' ? $fromEnv : ('Cinematic Vision Studio <noreply@' . $domain . '>');

    $fields = ['from' => $from, 'to' => $to, 'subject' => $subject, 'text' => $textBody];
    if ($htmlBody !== '') {
        $fields['html'] = $htmlBody;
    }

    $bodyParts = [];
    foreach ($fields as $k => $v) {
        $bodyParts[] = rawurlencode($k) . '=' . rawurlencode($v);
    }
    $body = implode('&', $bodyParts);

    $auth = base64_encode('api:' . $apiKey);
    $opts = [
        'http' => [
            'method'        => 'POST',
            'header'        => "Authorization: Basic {$auth}\r\n"
                             . "Content-Type: application/x-www-form-urlencoded\r\n"
                             . 'Content-Length: ' . strlen($body),
            'content'       => $body,
            'timeout'       => 10,
            'ignore_errors' => true,
        ],
    ];

    $url  = 'https://api.mailgun.net/v3/' . rawurlencode($domain) . '/messages';
    $resp = @file_get_contents($url, false, stream_context_create($opts));

    if ($resp === false) {
        error_log('[csf_mailer] Network error sending to ' . $to);
        return ['ok' => false, 'error' => 'network error'];
    }

    $status = 0;
    if (!empty($http_response_header)) {
        preg_match('/\s(\d{3})\s/', $http_response_header[0], $m);
        $status = isset($m[1]) ? (int) $m[1] : 0;
    }

    if ($status >= 200 && $status < 300) {
        return ['ok' => true, 'error' => ''];
    }

    error_log('[csf_mailer] Mailgun HTTP ' . $status . ' for ' . $to . ': ' . substr($resp, 0, 200));
    return ['ok' => false, 'error' => 'mailgun HTTP ' . $status];
}

/**
 * Gibt die App-Basis-URL zurück (kein trailing slash).
 */
function csf_app_url(): string
{
    $url = (string) (getenv('APP_URL') ?: ($_SERVER['APP_URL'] ?? ''));
    return rtrim($url !== '' ? $url : 'https://cinematic-studio-family.onrender.com', '/');
}

/**
 * HTML-Body: Willkommens-E-Mail.
 */
function csf_mail_html_welcome(string $studioUrl): string
{
    return '<!DOCTYPE html><html lang="de"><body style="font-family:Arial,sans-serif;'
         . 'background:#06060f;color:#f0f0ff;padding:32px;max-width:580px;margin:0 auto;">'
         . '<p style="font-size:12px;font-weight:900;letter-spacing:2px;text-transform:uppercase;'
         . 'color:#f5c542;margin-bottom:24px;">Cinematic Vision Studio</p>'
         . '<h1 style="font-size:22px;font-weight:900;margin-bottom:12px;">Willkommen! 🎬</h1>'
         . '<p style="color:#8888aa;line-height:1.6;margin-bottom:16px;">'
         . 'Dein Account ist bereit. Du hast <strong style="color:#f5c542;">50 Kristalle</strong>'
         . ' als Willkommensbonus erhalten — genug für deine ersten KI-Generierungen.</p>'
         . '<a href="' . htmlspecialchars($studioUrl) . '" '
         . 'style="display:inline-block;background:linear-gradient(135deg,#f5c542,#ff8c00);'
         . 'color:#1a0e00;text-decoration:none;padding:14px 28px;border-radius:12px;'
         . 'font-weight:900;font-size:14px;margin-bottom:24px;">Studio öffnen →</a>'
         . '<p style="color:#5a5a7a;font-size:11px;margin-top:24px;">'
         . 'Du erhältst diese E-Mail, weil du dich bei Cinematic Vision Studio registriert hast.</p>'
         . '</body></html>';
}

/**
 * HTML-Body: Passwort-Reset-E-Mail.
 */
function csf_mail_html_reset(string $resetUrl): string
{
    return '<!DOCTYPE html><html lang="de"><body style="font-family:Arial,sans-serif;'
         . 'background:#06060f;color:#f0f0ff;padding:32px;max-width:580px;margin:0 auto;">'
         . '<p style="font-size:12px;font-weight:900;letter-spacing:2px;text-transform:uppercase;'
         . 'color:#f5c542;margin-bottom:24px;">Cinematic Vision Studio</p>'
         . '<h1 style="font-size:22px;font-weight:900;margin-bottom:12px;">Passwort zurücksetzen</h1>'
         . '<p style="color:#8888aa;line-height:1.6;margin-bottom:16px;">'
         . 'Klicke den Button, um dein Passwort zurückzusetzen. '
         . 'Der Link ist <strong style="color:#f0f0ff;">1 Stunde</strong> gültig.</p>'
         . '<a href="' . htmlspecialchars($resetUrl) . '" '
         . 'style="display:inline-block;background:linear-gradient(135deg,#3b82f6,#9333ea);'
         . 'color:#fff;text-decoration:none;padding:14px 28px;border-radius:12px;'
         . 'font-weight:900;font-size:14px;margin-bottom:24px;">Passwort zurücksetzen →</a>'
         . '<p style="color:#8888aa;font-size:12px;">'
         . 'Falls du kein Passwort-Reset angefordert hast, ignoriere diese E-Mail.</p>'
         . '<p style="color:#5a5a7a;font-size:11px;margin-top:12px;word-break:break-all;">'
         . 'Link: ' . htmlspecialchars($resetUrl) . '</p>'
         . '</body></html>';
}
