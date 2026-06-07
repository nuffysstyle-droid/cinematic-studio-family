<?php
/**
 * includes/mailer.php — E-Mail-Versand Utility
 *
 * Verwendet PHP mail() mit msmtp als Sendmail-Backend (Dockerfile).
 * SMTP-Credentials werden via Render Env-Vars zur Laufzeit konfiguriert:
 *   SMTP_HOST, SMTP_PORT, SMTP_USER, SMTP_PASS, MAIL_FROM
 *
 * Auf IONOS läuft mail() direkt über den lokalen MTA (keine Config nötig).
 * Auf Render: msmtp muss via entrypoint.sh konfiguriert sein.
 *
 * @since V0.4.0
 */

declare(strict_types=1);

/**
 * Prüft ob E-Mail-Versand konfiguriert ist.
 *
 * @return bool true wenn SMTP_HOST gesetzt ist
 */
function csf_mail_is_configured(): bool
{
    return !empty(getenv('SMTP_HOST'));
}

/**
 * Sendet eine E-Mail.
 *
 * @param string $to       Empfänger (validierte E-Mail-Adresse)
 * @param string $subject  Betreff (wird UTF-8 encoded)
 * @param string $body     Plaintext-Body
 * @param string $replyTo  Optionale Reply-To Adresse
 * @return bool            true wenn mail() erfolgreich, false bei Fehler
 */
function csf_mail_send(string $to, string $subject, string $body, string $replyTo = ''): bool
{
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $from = getenv('MAIL_FROM') ?: 'noreply@cinematic-vision-studio.de';

    // UTF-8 encoded Subject (RFC 2047)
    $encodedSubject = '=?UTF-8?B?' . base64_encode(mb_substr($subject, 0, 200)) . '?=';

    $headers = [
        'From: ' . $from,
        'Content-Type: text/plain; charset=UTF-8',
        'MIME-Version: 1.0',
        'X-Mailer: CinematicStudio/1.0',
    ];

    if ($replyTo !== '' && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
        $headers[] = 'Reply-To: ' . $replyTo;
    }

    return @mail($to, $encodedSubject, $body, implode("\r\n", $headers));
}

/**
 * Sendet den Passwort-Reset-Link.
 *
 * @param string $email    Empfänger
 * @param string $resetUrl Vollständige Reset-URL mit Token
 * @return bool
 */
function csf_mail_password_reset(string $email, string $resetUrl): bool
{
    $subject = 'Passwort zurücksetzen – Cinematic Vision Studio';
    $body    = implode("\r\n", [
        'Hallo,',
        '',
        'du hast einen Passwort-Reset für deinen Cinematic Vision Studio Account angefordert.',
        '',
        'Reset-Link (gültig 1 Stunde):',
        $resetUrl,
        '',
        'Falls du diesen Reset nicht angefordert hast, ignoriere diese E-Mail.',
        'Dein Passwort bleibt unverändert.',
        '',
        '— Cinematic Vision Studio Team',
        'https://cinematic-vision-studio.de',
    ]);

    return csf_mail_send($email, $subject, $body);
}

/**
 * Sendet die Willkommens-E-Mail nach Registrierung.
 *
 * @param string $email Empfänger
 * @return bool
 */
function csf_mail_welcome(string $email): bool
{
    $subject = 'Willkommen im Cinematic Vision Studio!';
    $studioUrl = 'https://cinematic-studio-family.onrender.com/studio-demo.php';
    $body    = implode("\r\n", [
        'Willkommen!',
        '',
        'Dein Account im Cinematic Vision Studio ist jetzt aktiv.',
        'Du hast 50 kostenlose Kristalle als Willkommens-Bonus erhalten.',
        '',
        'Jetzt loslegen:',
        $studioUrl,
        '',
        '— Cinematic Vision Studio Team',
        'https://cinematic-vision-studio.de',
    ]);

    return csf_mail_send($email, $subject, $body);
}
