<?php
/**
 * login.php — Login & Registrierung
 *
 * Zeigt Login-Formular (default) oder Registrierungs-Formular (?tab=register).
 * Auth-State: Wenn User bereits eingeloggt → Redirect zu studio-demo.php
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

// Bereits eingeloggt? → weiterleiten
$user = csf_auth_user();
if ($user !== null) {
    header('Location: studio-demo.php');
    exit;
}

$tab = ($_GET['tab'] ?? 'login') === 'register' ? 'register' : 'login';
$redirect = htmlspecialchars(strip_tags((string)($_GET['redirect'] ?? 'studio-demo.php')));
// Nur relative Pfade erlauben
if (str_starts_with($redirect, '//') || str_contains($redirect, ':')) {
    $redirect = 'studio-demo.php';
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login – Cinematic Vision Studio</title>
    <meta name="description" content="Einloggen oder Konto erstellen für Cinematic Vision Studio.">
    <meta name="robots" content="noindex">
    <link rel="icon" type="image/png" href="assets/cvs-logo.png">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}

        :root{
            --bg:#0a0a0f;
            --surface:#13131a;
            --surface2:#1c1c26;
            --border:#2a2a3a;
            --text:#e8e8f0;
            --text-muted:#888899;
            --primary:#7c3aed;
            --primary-h:#6d28d9;
            --gold:#f59e0b;
            --ok:#4ade80;
            --err:#f87171;
            --radius:12px;
            --radius-sm:8px;
        }

        body{
            font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;
            background:var(--bg);
            color:var(--text);
            min-height:100vh;
            display:flex;
            flex-direction:column;
            align-items:center;
            justify-content:center;
            padding:24px 16px;
        }

        /* ── Logo / Header ── */
        .auth-logo{
            display:flex;
            align-items:center;
            gap:10px;
            text-decoration:none;
            margin-bottom:32px;
        }
        .auth-logo-icon{
            width:40px;height:40px;
            background:linear-gradient(135deg,var(--primary),#a855f7);
            border-radius:10px;
            display:flex;align-items:center;justify-content:center;
            font-size:20px;
        }
        .auth-logo-text{
            font-size:1.2rem;
            font-weight:700;
            color:var(--text);
        }
        .auth-logo-text span{color:var(--primary);}

        /* ── Card ── */
        .auth-card{
            width:100%;
            max-width:440px;
            background:var(--surface);
            border:1px solid var(--border);
            border-radius:var(--radius);
            overflow:hidden;
        }

        /* ── Tabs ── */
        .auth-tabs{
            display:grid;
            grid-template-columns:1fr 1fr;
            border-bottom:1px solid var(--border);
        }
        .auth-tab{
            padding:16px;
            text-align:center;
            font-size:.9rem;
            font-weight:600;
            color:var(--text-muted);
            text-decoration:none;
            transition:color .2s,background .2s;
        }
        .auth-tab:hover{color:var(--text);background:var(--surface2);}
        .auth-tab.active{
            color:var(--primary);
            background:var(--surface2);
            border-bottom:2px solid var(--primary);
        }

        /* ── Form ── */
        .auth-form{
            padding:32px;
        }
        .auth-form-title{
            font-size:1.35rem;
            font-weight:700;
            margin-bottom:8px;
        }
        .auth-form-sub{
            font-size:.875rem;
            color:var(--text-muted);
            margin-bottom:28px;
            line-height:1.5;
        }

        .field{margin-bottom:18px;}
        .field label{
            display:block;
            font-size:.8rem;
            font-weight:600;
            color:var(--text-muted);
            text-transform:uppercase;
            letter-spacing:.05em;
            margin-bottom:6px;
        }
        .field input{
            width:100%;
            padding:12px 14px;
            background:var(--surface2);
            border:1px solid var(--border);
            border-radius:var(--radius-sm);
            color:var(--text);
            font-size:.95rem;
            outline:none;
            transition:border-color .2s,box-shadow .2s;
        }
        .field input:focus{
            border-color:var(--primary);
            box-shadow:0 0 0 3px rgba(124,58,237,.2);
        }
        .field input::placeholder{color:var(--text-muted);}

        /* Remember-Me */
        .remember-row{
            display:flex;
            align-items:center;
            gap:8px;
            margin-bottom:24px;
        }
        .remember-row input[type=checkbox]{
            width:16px;height:16px;
            accent-color:var(--primary);
            cursor:pointer;
        }
        .remember-row label{
            font-size:.875rem;
            color:var(--text-muted);
            cursor:pointer;
            user-select:none;
        }

        /* ── Submit Button ── */
        .btn-submit{
            width:100%;
            padding:14px;
            background:linear-gradient(135deg,var(--primary),#a855f7);
            color:#fff;
            font-size:1rem;
            font-weight:700;
            border:none;
            border-radius:var(--radius-sm);
            cursor:pointer;
            transition:opacity .2s,transform .1s;
            letter-spacing:.02em;
        }
        .btn-submit:hover{opacity:.9;}
        .btn-submit:active{transform:scale(.98);}
        .btn-submit:disabled{opacity:.5;cursor:not-allowed;}

        /* ── Error / Success Box ── */
        .msg-box{
            padding:12px 14px;
            border-radius:var(--radius-sm);
            font-size:.875rem;
            line-height:1.5;
            margin-bottom:20px;
            display:none;
        }
        .msg-box.error{
            background:rgba(248,113,113,.1);
            border:1px solid rgba(248,113,113,.3);
            color:var(--err);
        }
        .msg-box.success{
            background:rgba(74,222,128,.1);
            border:1px solid rgba(74,222,128,.3);
            color:var(--ok);
        }
        .msg-box.show{display:block;}

        /* ── Passwort-Stärke ── */
        .pw-strength{
            margin-top:6px;
            height:3px;
            border-radius:99px;
            background:var(--border);
            overflow:hidden;
        }
        .pw-strength-bar{
            height:100%;
            width:0;
            border-radius:99px;
            transition:width .3s,background .3s;
        }

        /* ── Footer ── */
        .auth-footer{
            text-align:center;
            margin-top:24px;
            font-size:.8rem;
            color:var(--text-muted);
        }
        .auth-footer a{color:var(--primary);text-decoration:none;}
        .auth-footer a:hover{text-decoration:underline;}

        /* ── Plan-Teaser (nur bei Register) ── */
        .plan-teaser{
            display:flex;
            align-items:center;
            gap:10px;
            padding:14px;
            background:rgba(124,58,237,.08);
            border:1px solid rgba(124,58,237,.25);
            border-radius:var(--radius-sm);
            margin-bottom:24px;
        }
        .plan-teaser-icon{font-size:1.5rem;}
        .plan-teaser-text{font-size:.85rem;color:var(--text-muted);line-height:1.5;}
        .plan-teaser-text strong{color:var(--text);}

        @media(max-width:480px){
            .auth-form{padding:24px 20px;}
        }
    </style>
</head>
<body>

<a href="https://cinematic-vision-studio.de/scene-editor-test.html" class="auth-logo">
    <div class="auth-logo-icon">🎬</div>
    <div class="auth-logo-text">Cinematic <span>Vision</span> Studio</div>
</a>

<div class="auth-card">

    <!-- Tabs -->
    <div class="auth-tabs">
        <a href="login.php?tab=login&redirect=<?= urlencode($redirect) ?>"
           class="auth-tab <?= $tab === 'login' ? 'active' : '' ?>">
            Einloggen
        </a>
        <a href="login.php?tab=register&redirect=<?= urlencode($redirect) ?>"
           class="auth-tab <?= $tab === 'register' ? 'active' : '' ?>">
            Registrieren
        </a>
    </div>

    <!-- ── LOGIN FORM ── -->
    <?php if ($tab === 'login'): ?>
    <div class="auth-form">
        <div class="auth-form-title">Willkommen zurück 👋</div>
        <div class="auth-form-sub">Logge dich ein, um deine Projekte zu öffnen.</div>

        <div class="msg-box" id="loginMsg"></div>

        <form id="loginForm" novalidate>
            <div class="field">
                <label for="loginEmail">E-Mail</label>
                <input type="email" id="loginEmail" name="email"
                       placeholder="deine@email.de" autocomplete="email" required>
            </div>
            <div class="field">
                <label for="loginPw">Passwort</label>
                <input type="password" id="loginPw" name="password"
                       placeholder="••••••••" autocomplete="current-password" required>
            </div>
            <div class="remember-row" style="justify-content:space-between">
                <div style="display:flex;align-items:center;gap:8px">
                    <input type="checkbox" id="loginRemember" name="remember">
                    <label for="loginRemember">Eingeloggt bleiben (30 Tage)</label>
                </div>
                <a href="forgot-password.php" style="font-size:.8rem;color:var(--primary);text-decoration:none">Passwort vergessen?</a>
            </div>
            <button type="submit" class="btn-submit" id="loginBtn">Einloggen</button>
        </form>
    </div>

    <!-- ── REGISTER FORM ── -->
    <?php else: ?>
    <div class="auth-form">
        <div class="auth-form-title">Konto erstellen</div>
        <div class="auth-form-sub">Kostenlos starten — kein Abo nötig.</div>

        <div class="plan-teaser">
            <div class="plan-teaser-icon">🎬</div>
            <div class="plan-teaser-text">
                <strong>Free-Plan inklusive:</strong><br>
                720p · 15s Videos · 3 KI-Bilder/Job · Sofort loslegen
            </div>
        </div>

        <div class="msg-box" id="regMsg"></div>

        <form id="regForm" novalidate>
            <div class="field">
                <label for="regEmail">E-Mail</label>
                <input type="email" id="regEmail" name="email"
                       placeholder="deine@email.de" autocomplete="email" required>
            </div>
            <div class="field">
                <label for="regPw">Passwort <span style="font-weight:400;text-transform:none">(min. 8 Zeichen)</span></label>
                <input type="password" id="regPw" name="password"
                       placeholder="••••••••" autocomplete="new-password" required minlength="8">
                <div class="pw-strength"><div class="pw-strength-bar" id="pwBar"></div></div>
            </div>
            <div class="field">
                <label for="regPwConfirm">Passwort bestätigen</label>
                <input type="password" id="regPwConfirm" name="password_confirm"
                       placeholder="••••••••" autocomplete="new-password" required>
            </div>
            <button type="submit" class="btn-submit" id="regBtn">Konto erstellen</button>
        </form>
    </div>
    <?php endif; ?>

</div>

<div class="auth-footer">
    <a href="https://cinematic-vision-studio.de/crystals.html">Pläne & Preise</a> ·
    <a href="https://cinematic-vision-studio.de/kontakt.html">Support</a> ·
    <a href="https://cinematic-vision-studio.de/scene-editor-test.html">Ohne Login testen</a>
</div>

<script>
const REDIRECT = <?= json_encode($redirect) ?>;

/* ── Hilfsfunktionen ── */
function showMsg(el, type, text) {
    el.className = 'msg-box ' + type + ' show';
    el.textContent = text;
}
function hideMsg(el) {
    el.className = 'msg-box';
    el.textContent = '';
}
function setLoading(btn, loading) {
    btn.disabled = loading;
    btn.textContent = loading
        ? (btn.id === 'loginBtn' ? 'Einloggen…' : 'Konto erstellen…')
        : (btn.id === 'loginBtn' ? 'Einloggen' : 'Konto erstellen');
}

/* ── Login ── */
const loginForm = document.getElementById('loginForm');
if (loginForm) {
    loginForm.addEventListener('submit', async e => {
        e.preventDefault();
        const msg = document.getElementById('loginMsg');
        const btn = document.getElementById('loginBtn');
        hideMsg(msg);

        const email    = document.getElementById('loginEmail').value.trim();
        const password = document.getElementById('loginPw').value;
        const remember = document.getElementById('loginRemember').checked;

        if (!email || !password) {
            showMsg(msg, 'error', 'Bitte E-Mail und Passwort eingeben.');
            return;
        }

        setLoading(btn, true);
        try {
            const res = await fetch('/api/auth/login.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({email, password, remember}),
            });
            const data = await res.json();

            if (res.ok && data.status === 'ok') {
                showMsg(msg, 'success', 'Willkommen! Weiterleitung…');
                setTimeout(() => { window.location.href = REDIRECT; }, 800);
            } else if (res.status === 429) {
                showMsg(msg, 'error', data.message || 'Zu viele Versuche. Kurz warten.');
            } else {
                let text = data.message || 'Login fehlgeschlagen.';
                if (data.remaining_tries !== undefined) {
                    text += ' (' + data.remaining_tries + ' Versuche verbleibend)';
                }
                showMsg(msg, 'error', text);
            }
        } catch {
            showMsg(msg, 'error', 'Netzwerkfehler. Bitte erneut versuchen.');
        } finally {
            setLoading(btn, false);
        }
    });
}

/* ── Register ── */
const regForm = document.getElementById('regForm');
if (regForm) {
    // Passwort-Stärke-Anzeige
    const pwInput = document.getElementById('regPw');
    const pwBar   = document.getElementById('pwBar');
    if (pwInput && pwBar) {
        pwInput.addEventListener('input', () => {
            const v = pwInput.value;
            let score = 0;
            if (v.length >= 8)  score++;
            if (v.length >= 12) score++;
            if (/[A-Z]/.test(v)) score++;
            if (/[0-9]/.test(v)) score++;
            if (/[^A-Za-z0-9]/.test(v)) score++;
            const pct   = Math.min(score / 4 * 100, 100);
            const color = score <= 1 ? '#f87171' : score <= 2 ? '#f59e0b' : '#4ade80';
            pwBar.style.width = pct + '%';
            pwBar.style.background = color;
        });
    }

    regForm.addEventListener('submit', async e => {
        e.preventDefault();
        const msg = document.getElementById('regMsg');
        const btn = document.getElementById('regBtn');
        hideMsg(msg);

        const email           = document.getElementById('regEmail').value.trim();
        const password        = document.getElementById('regPw').value;
        const password_confirm = document.getElementById('regPwConfirm').value;

        if (!email || !password || !password_confirm) {
            showMsg(msg, 'error', 'Bitte alle Felder ausfüllen.');
            return;
        }
        if (password !== password_confirm) {
            showMsg(msg, 'error', 'Passwörter stimmen nicht überein.');
            return;
        }
        if (password.length < 8) {
            showMsg(msg, 'error', 'Passwort muss mindestens 8 Zeichen haben.');
            return;
        }

        setLoading(btn, true);
        try {
            const res = await fetch('/api/auth/register.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({email, password, password_confirm}),
            });
            const data = await res.json();

            if (res.status === 201 && data.status === 'ok') {
                showMsg(msg, 'success', 'Konto erstellt! Jetzt einloggen…');
                setTimeout(() => {
                    window.location.href = 'login.php?tab=login&redirect=' + encodeURIComponent(REDIRECT);
                }, 1500);
            } else if (res.status === 429) {
                showMsg(msg, 'error', data.message || 'Zu viele Versuche. Kurz warten.');
            } else {
                showMsg(msg, 'error', data.message || 'Registrierung fehlgeschlagen.');
            }
        } catch {
            showMsg(msg, 'error', 'Netzwerkfehler. Bitte erneut versuchen.');
        } finally {
            setLoading(btn, false);
        }
    });
}
</script>

</body>
</html>
