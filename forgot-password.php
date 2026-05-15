<?php
/**
 * forgot-password.php — Passwort zurücksetzen (UI)
 *
 * Phase 1: Token wird in DB gespeichert, Link wird NICHT gemailt
 *          (kein SMTP in V0.x). Stattdessen: Hinweis "Support kontaktieren".
 * Phase 2 (V0.5+): SMTP/Mailgun E-Mail mit Reset-Link.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

// Bereits eingeloggt → Dashboard
if (csf_auth_user() !== null) {
    header('Location: dashboard.php');
    exit;
}

// Reset-Token-Verarbeitung (wenn ?token=... in URL)
$token     = trim((string)($_GET['token'] ?? ''));
$tokenMode = $token !== '' && preg_match('/^[a-f0-9]{64}$/', $token);
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Passwort zurücksetzen – Cinematic Vision Studio</title>
    <meta name="robots" content="noindex">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        :root{
            --bg:#0a0a0f;--surface:#13131a;--surface2:#1c1c26;
            --border:#2a2a3a;--text:#e8e8f0;--muted:#888899;
            --primary:#7c3aed;--gold:#f59e0b;--ok:#4ade80;--err:#f87171;
            --radius:12px;--radius-sm:8px;
        }
        body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px 16px;}
        .logo{display:flex;align-items:center;gap:10px;text-decoration:none;margin-bottom:32px;}
        .logo-icon{width:40px;height:40px;background:linear-gradient(135deg,var(--primary),#a855f7);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px;}
        .logo-text{font-size:1.1rem;font-weight:700;}
        .logo-text span{color:var(--primary);}
        .card{width:100%;max-width:420px;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:32px;}
        .card-title{font-size:1.3rem;font-weight:800;margin-bottom:8px;}
        .card-sub{font-size:.875rem;color:var(--muted);margin-bottom:24px;line-height:1.6;}
        .field{margin-bottom:16px;}
        .field label{display:block;font-size:.78rem;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px;}
        .field input{width:100%;padding:11px 13px;background:var(--surface2);border:1px solid var(--border);border-radius:var(--radius-sm);color:var(--text);font-size:.9rem;outline:none;transition:border-color .2s,box-shadow .2s;}
        .field input:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(124,58,237,.2);}
        .field input::placeholder{color:var(--muted);}
        .btn-primary{width:100%;padding:13px;background:linear-gradient(135deg,var(--primary),#a855f7);color:#fff;font-size:.95rem;font-weight:700;border:none;border-radius:var(--radius-sm);cursor:pointer;transition:opacity .2s;font-family:inherit;}
        .btn-primary:hover{opacity:.9;}
        .btn-primary:disabled{opacity:.5;cursor:not-allowed;}
        .msg{padding:12px 14px;border-radius:var(--radius-sm);font-size:.875rem;line-height:1.5;margin-bottom:16px;display:none;}
        .msg.error{background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.3);color:var(--err);}
        .msg.success{background:rgba(74,222,128,.1);border:1px solid rgba(74,222,128,.3);color:var(--ok);}
        .msg.info{background:rgba(59,130,246,.1);border:1px solid rgba(59,130,246,.3);color:#93c5fd;}
        .msg.show{display:block;}
        .back-link{display:block;text-align:center;margin-top:20px;font-size:.85rem;color:var(--muted);text-decoration:none;}
        .back-link a{color:var(--primary);}
        .back-link a:hover{text-decoration:underline;}
        /* Pw Strength */
        .pw-strength{margin-top:5px;height:3px;border-radius:99px;background:var(--border);overflow:hidden;}
        .pw-strength-bar{height:100%;width:0;border-radius:99px;transition:width .3s,background .3s;}
    </style>
</head>
<body>

<a href="login.php" class="logo">
    <div class="logo-icon">🎬</div>
    <div class="logo-text">Cinematic <span>Vision</span> Studio</div>
</a>

<div class="card">
    <?php if ($tokenMode): ?>
        <!-- ── Neues Passwort setzen (Token-Modus) ── -->
        <div class="card-title">Neues Passwort setzen</div>
        <div class="card-sub">Token erkannt. Gib dein neues Passwort ein.</div>

        <div class="msg" id="resetMsg"></div>

        <form id="resetForm" novalidate>
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
            <div class="field">
                <label for="newPw">Neues Passwort <span style="font-weight:400;text-transform:none">(min. 8 Zeichen)</span></label>
                <input type="password" id="newPw" name="new_password"
                       placeholder="••••••••" autocomplete="new-password" required minlength="8">
                <div class="pw-strength"><div class="pw-strength-bar" id="pwBar"></div></div>
            </div>
            <div class="field">
                <label for="newPwConfirm">Bestätigen</label>
                <input type="password" id="newPwConfirm" name="new_password_confirm"
                       placeholder="••••••••" autocomplete="new-password" required>
            </div>
            <button type="submit" class="btn-primary" id="resetBtn">Passwort speichern</button>
        </form>

    <?php else: ?>
        <!-- ── E-Mail-Anfrage-Modus ── -->
        <div class="card-title">Passwort vergessen?</div>
        <div class="card-sub">
            Gib deine E-Mail ein — wir senden dir einen Reset-Link.<br>
            <strong style="color:var(--text)">Hinweis:</strong> E-Mail-Versand ist in der aktuellen Beta-Version noch nicht aktiv.
            Bei Bedarf bitte direkt an
            <a href="mailto:support@cinematic-vision-studio.de" style="color:var(--primary)">support@cinematic-vision-studio.de</a>
            schreiben.
        </div>

        <div class="msg info show">
            📧 E-Mail-Reset kommt in V0.4. Aktuell bitte Support kontaktieren.
        </div>

        <div class="msg" id="forgotMsg"></div>

        <form id="forgotForm" novalidate>
            <div class="field">
                <label for="forgotEmail">E-Mail-Adresse</label>
                <input type="email" id="forgotEmail" name="email"
                       placeholder="deine@email.de" autocomplete="email" required>
            </div>
            <button type="submit" class="btn-primary" id="forgotBtn">Reset-Link anfordern</button>
        </form>

    <?php endif; ?>
</div>

<p class="back-link"><a href="login.php">← Zurück zum Login</a></p>

<script>
// Passwort-Stärke
document.getElementById('newPw')?.addEventListener('input', function() {
    const v = this.value, bar = document.getElementById('pwBar');
    if (!bar) return;
    let s = 0;
    if (v.length >= 8) s++;
    if (v.length >= 12) s++;
    if (/[A-Z]/.test(v)) s++;
    if (/[0-9]/.test(v)) s++;
    if (/[^A-Za-z0-9]/.test(v)) s++;
    bar.style.width = Math.min(s / 4 * 100, 100) + '%';
    bar.style.background = s <= 1 ? '#f87171' : s <= 2 ? '#f59e0b' : '#4ade80';
});

// Forgot-Form
document.getElementById('forgotForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const msg  = document.getElementById('forgotMsg');
    const btn  = document.getElementById('forgotBtn');
    const email = document.getElementById('forgotEmail').value.trim();
    msg.className = 'msg';

    if (!email) {
        msg.className = 'msg error show';
        msg.textContent = 'Bitte E-Mail-Adresse eingeben.';
        return;
    }

    btn.disabled = true;
    btn.textContent = 'Wird gesendet…';

    try {
        const res  = await fetch('/api/auth/forgot-password.php', {
            method:  'POST',
            headers: {'Content-Type': 'application/json'},
            body:    JSON.stringify({email}),
        });
        const data = await res.json();
        // Immer gleiche Meldung (verhindert User-Enumeration)
        msg.className = 'msg success show';
        msg.textContent = 'Falls diese E-Mail registriert ist, hast du gleich eine Nachricht. Prüfe auch deinen Spam-Ordner.';
    } catch {
        msg.className = 'msg error show';
        msg.textContent = 'Netzwerkfehler. Bitte erneut versuchen.';
    } finally {
        btn.disabled = false;
        btn.textContent = 'Reset-Link anfordern';
    }
});

// Reset-Form (Token-Modus)
document.getElementById('resetForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const msg     = document.getElementById('resetMsg');
    const btn     = document.getElementById('resetBtn');
    const token   = this.querySelector('[name=token]').value;
    const newPw   = document.getElementById('newPw').value;
    const confirm = document.getElementById('newPwConfirm').value;
    msg.className = 'msg';

    if (newPw !== confirm) {
        msg.className = 'msg error show';
        msg.textContent = 'Passwörter stimmen nicht überein.';
        return;
    }
    if (newPw.length < 8) {
        msg.className = 'msg error show';
        msg.textContent = 'Passwort muss mindestens 8 Zeichen haben.';
        return;
    }

    btn.disabled = true;
    btn.textContent = 'Wird gespeichert…';

    try {
        const res  = await fetch('/api/auth/forgot-password.php', {
            method:  'POST',
            headers: {'Content-Type': 'application/json'},
            body:    JSON.stringify({token, new_password: newPw, new_password_confirm: confirm}),
        });
        const data = await res.json();
        if (res.ok && data.status === 'ok') {
            msg.className = 'msg success show';
            msg.textContent = '✓ Passwort gespeichert! Weiterleitung…';
            setTimeout(() => { window.location.href = 'login.php'; }, 1500);
        } else {
            msg.className = 'msg error show';
            msg.textContent = data.message || 'Fehler beim Zurücksetzen.';
        }
    } catch {
        msg.className = 'msg error show';
        msg.textContent = 'Netzwerkfehler. Bitte erneut versuchen.';
    } finally {
        btn.disabled = false;
        btn.textContent = 'Passwort speichern';
    }
});
</script>

</body>
</html>
