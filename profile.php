<?php
/**
 * profile.php — User-Profil & Einstellungen
 *
 * Zeigt: E-Mail, Plan, Passwort ändern, Account-Info.
 * Erfordert Login.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$user = csf_auth_user();
if ($user === null) {
    header('Location: login.php?redirect=profile.php');
    exit;
}

$planLabels = ['free' => 'Free', 'starter' => 'Starter+', 'pro' => 'Pro/Ultra'];
$planLabel  = $planLabels[$user['plan']] ?? ucfirst($user['plan']);
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil – Cinematic Vision Studio</title>
    <meta name="robots" content="noindex">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        :root{
            --bg:#0a0a0f;--surface:#13131a;--surface2:#1c1c26;
            --border:#2a2a3a;--text:#e8e8f0;--muted:#888899;
            --primary:#7c3aed;--gold:#f59e0b;--ok:#4ade80;--err:#f87171;
            --radius:14px;--radius-sm:8px;
        }
        body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;}

        /* Nav */
        .nav{position:sticky;top:0;z-index:50;display:flex;align-items:center;justify-content:space-between;padding:14px 28px;background:rgba(10,10,15,.93);backdrop-filter:blur(20px);border-bottom:1px solid var(--border);}
        .nav-logo{text-decoration:none;font-size:1rem;font-weight:900;background:linear-gradient(135deg,#f59e0b,#ff8c00);-webkit-background-clip:text;-webkit-text-fill-color:transparent;}
        .nav-links{display:flex;align-items:center;gap:4px;}
        .nav-link{color:var(--muted);text-decoration:none;font-size:13px;font-weight:600;padding:7px 12px;border-radius:8px;transition:color .15s,background .15s;}
        .nav-link:hover,.nav-link.active{color:var(--text);background:rgba(255,255,255,.07);}
        .nav-right{display:flex;align-items:center;gap:10px;}
        .wallet-pill{display:flex;align-items:center;gap:6px;background:rgba(245,158,11,.1);border:1px solid rgba(245,158,11,.3);border-radius:999px;padding:6px 14px;font-size:13px;font-weight:800;color:var(--gold);text-decoration:none;}
        .btn-nav{background:none;border:1px solid var(--border);color:var(--muted);font-size:13px;font-weight:600;padding:7px 13px;border-radius:8px;cursor:pointer;font-family:inherit;transition:color .15s,border-color .15s;}
        .btn-nav:hover{color:var(--err);border-color:rgba(248,113,113,.4);}
        @media(max-width:640px){.nav{padding:12px 16px;}.nav-links{display:none;}}

        /* Layout */
        .page{max-width:680px;margin:0 auto;padding:40px 20px 80px;}
        .page-header{margin-bottom:32px;}
        .page-header h1{font-size:1.7rem;font-weight:800;margin-bottom:6px;}
        .page-header p{color:var(--muted);font-size:.9rem;}

        /* Cards */
        .card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);margin-bottom:20px;overflow:hidden;}
        .card-header{padding:18px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;}
        .card-title{font-size:.95rem;font-weight:700;}
        .card-body{padding:22px;}

        /* Info Row */
        .info-row{display:flex;align-items:center;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--border);}
        .info-row:last-child{border-bottom:none;}
        .info-label{font-size:.8rem;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;}
        .info-value{font-size:.9rem;font-weight:600;}

        /* Badge */
        .plan-badge{display:inline-block;padding:3px 12px;border-radius:999px;font-size:.75rem;font-weight:800;text-transform:uppercase;letter-spacing:.05em;}

        /* Form */
        .field{margin-bottom:16px;}
        .field label{display:block;font-size:.78rem;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px;}
        .field input{width:100%;padding:11px 13px;background:var(--surface2);border:1px solid var(--border);border-radius:var(--radius-sm);color:var(--text);font-size:.9rem;outline:none;transition:border-color .2s,box-shadow .2s;}
        .field input:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(124,58,237,.2);}
        .field input::placeholder{color:var(--muted);}

        /* Pw Strength */
        .pw-strength{margin-top:5px;height:3px;border-radius:99px;background:var(--border);overflow:hidden;}
        .pw-strength-bar{height:100%;width:0;border-radius:99px;transition:width .3s,background .3s;}

        /* Buttons */
        .btn-primary{width:100%;padding:12px;background:linear-gradient(135deg,var(--primary),#a855f7);color:#fff;font-size:.9rem;font-weight:700;border:none;border-radius:var(--radius-sm);cursor:pointer;transition:opacity .2s;font-family:inherit;}
        .btn-primary:hover{opacity:.9;}
        .btn-primary:disabled{opacity:.5;cursor:not-allowed;}

        /* Msg */
        .msg{padding:11px 14px;border-radius:var(--radius-sm);font-size:.85rem;line-height:1.5;margin-bottom:14px;display:none;}
        .msg.error{background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.3);color:var(--err);}
        .msg.success{background:rgba(74,222,128,.1);border:1px solid rgba(74,222,128,.3);color:var(--ok);}
        .msg.show{display:block;}

        /* Danger Zone */
        .danger-note{font-size:.82rem;color:var(--muted);margin-bottom:14px;line-height:1.6;}
        .btn-danger-outline{width:100%;padding:11px;background:none;border:1px solid rgba(248,113,113,.3);color:var(--err);font-size:.875rem;font-weight:700;border-radius:var(--radius-sm);cursor:pointer;font-family:inherit;transition:all .2s;}
        .btn-danger-outline:hover{background:rgba(248,113,113,.1);border-color:var(--err);}
    </style>
</head>
<body>

<nav class="nav">
    <a href="studio-demo.php" class="nav-logo">🎬 CVS</a>
    <div class="nav-links">
        <a href="studio-demo.php" class="nav-link">Studio</a>
        <a href="dashboard.php" class="nav-link">Dashboard</a>
        <a href="profile.php" class="nav-link active">Profil</a>
        <a href="https://cinematic-vision-studio.de/crystals.html" class="nav-link">Pläne</a>
    </div>
    <div class="nav-right">
        <a href="https://cinematic-vision-studio.de/crystals.html" class="wallet-pill">💎 <?= htmlspecialchars((string)$user['crystals_balance']) ?></a>
        <form method="POST" action="/api/auth/logout.php" style="display:inline">
            <input type="hidden" name="redirect" value="login.php">
            <button type="submit" class="btn-nav">Logout</button>
        </form>
    </div>
</nav>

<div class="page">

    <div class="page-header">
        <h1>⚙️ Profil</h1>
        <p>Konto-Einstellungen und Plan-Übersicht</p>
    </div>

    <!-- Account Info -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">Account-Info</div>
        </div>
        <div class="card-body">
            <div class="info-row">
                <span class="info-label">E-Mail</span>
                <span class="info-value"><?= htmlspecialchars($user['email']) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Plan</span>
                <span class="info-value">
                    <?php
                    $planColors = ['free' => '#888899', 'starter' => '#3b82f6', 'pro' => '#9333ea'];
                    $pc = $planColors[$user['plan']] ?? '#888899';
                    ?>
                    <span class="plan-badge" style="background:<?= $pc ?>22;color:<?= $pc ?>;border:1px solid <?= $pc ?>44">
                        <?= htmlspecialchars($planLabel) ?>
                    </span>
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Kristalle</span>
                <span class="info-value" style="color:var(--gold)">💎 <?= number_format($user['crystals_balance']) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">User-ID</span>
                <span class="info-value" style="font-family:monospace;font-size:.85rem;color:var(--muted)">#<?= $user['id'] ?></span>
            </div>
        </div>
    </div>

    <?php if ($user['plan'] === 'free'): ?>
    <!-- Upgrade CTA -->
    <div class="card" style="border-color:rgba(124,58,237,.3);background:rgba(124,58,237,.05);">
        <div class="card-body" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;">
            <div>
                <div style="font-weight:700;margin-bottom:4px;">Auf Starter+ upgraden</div>
                <div style="font-size:.85rem;color:var(--muted)">1080p · 60s · Original-Audio · 90 Tage Speicher</div>
            </div>
            <a href="https://cinematic-vision-studio.de/crystals.html"
               style="display:inline-block;padding:10px 20px;background:linear-gradient(135deg,var(--primary),#a855f7);color:#fff;font-size:.875rem;font-weight:700;border-radius:var(--radius-sm);text-decoration:none;white-space:nowrap;">
                ⬆️ Upgraden →
            </a>
        </div>
    </div>
    <?php endif; ?>

    <!-- Passwort ändern -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">🔐 Passwort ändern</div>
        </div>
        <div class="card-body">
            <div class="msg" id="pwMsg"></div>
            <form id="pwForm" novalidate>
                <div class="field">
                    <label for="pwCurrent">Aktuelles Passwort</label>
                    <input type="password" id="pwCurrent" name="current_password"
                           placeholder="••••••••" autocomplete="current-password" required>
                </div>
                <div class="field">
                    <label for="pwNew">Neues Passwort <span style="font-weight:400;text-transform:none">(min. 8 Zeichen)</span></label>
                    <input type="password" id="pwNew" name="new_password"
                           placeholder="••••••••" autocomplete="new-password" required minlength="8">
                    <div class="pw-strength"><div class="pw-strength-bar" id="pwBar"></div></div>
                </div>
                <div class="field">
                    <label for="pwConfirm">Neues Passwort bestätigen</label>
                    <input type="password" id="pwConfirm" name="new_password_confirm"
                           placeholder="••••••••" autocomplete="new-password" required>
                </div>
                <button type="submit" class="btn-primary" id="pwBtn">Passwort ändern</button>
            </form>
        </div>
    </div>

    <!-- Danger Zone -->
    <div class="card" style="border-color:rgba(248,113,113,.2);">
        <div class="card-header" style="border-bottom-color:rgba(248,113,113,.2);">
            <div class="card-title" style="color:var(--err)">Danger Zone</div>
        </div>
        <div class="card-body">
            <p class="danger-note">
                Account-Löschung entfernt alle deine Daten dauerhaft. Diese Aktion kann nicht rückgängig gemacht werden.
                Schreib uns unter <strong>support@cinematic-vision-studio.de</strong> für Account-Löschung.
            </p>
            <a href="mailto:support@cinematic-vision-studio.de?subject=Account%20l%C3%B6schen&body=Bitte%20l%C3%B6sche%20meinen%20Account%3A%20<?= urlencode($user['email']) ?>"
               class="btn-danger-outline" style="display:block;text-decoration:none;text-align:center;padding:11px;">
                ✉️ Account löschen anfragen
            </a>
        </div>
    </div>

</div>

<script>
// Passwort-Stärke
document.getElementById('pwNew')?.addEventListener('input', function() {
    const v = this.value, bar = document.getElementById('pwBar');
    let s = 0;
    if (v.length >= 8) s++;
    if (v.length >= 12) s++;
    if (/[A-Z]/.test(v)) s++;
    if (/[0-9]/.test(v)) s++;
    if (/[^A-Za-z0-9]/.test(v)) s++;
    bar.style.width = Math.min(s / 4 * 100, 100) + '%';
    bar.style.background = s <= 1 ? '#f87171' : s <= 2 ? '#f59e0b' : '#4ade80';
});

// Passwort-Form
document.getElementById('pwForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const msg = document.getElementById('pwMsg');
    const btn = document.getElementById('pwBtn');
    msg.className = 'msg'; msg.textContent = '';

    const current = document.getElementById('pwCurrent').value;
    const newPw   = document.getElementById('pwNew').value;
    const confirm = document.getElementById('pwConfirm').value;

    if (!current || !newPw || !confirm) {
        msg.className = 'msg error show';
        msg.textContent = 'Bitte alle Felder ausfüllen.';
        return;
    }
    if (newPw !== confirm) {
        msg.className = 'msg error show';
        msg.textContent = 'Neue Passwörter stimmen nicht überein.';
        return;
    }
    if (newPw.length < 8) {
        msg.className = 'msg error show';
        msg.textContent = 'Neues Passwort muss mindestens 8 Zeichen haben.';
        return;
    }

    btn.disabled = true;
    btn.textContent = 'Wird gespeichert…';

    try {
        const res  = await fetch('/api/auth/change-password.php', {
            method:  'POST',
            headers: {'Content-Type': 'application/json'},
            body:    JSON.stringify({current_password: current, new_password: newPw, new_password_confirm: confirm}),
        });
        const data = await res.json();

        if (res.ok && data.status === 'ok') {
            msg.className = 'msg success show';
            msg.textContent = '✓ Passwort erfolgreich geändert.';
            document.getElementById('pwForm').reset();
            document.getElementById('pwBar').style.width = '0';
        } else {
            msg.className = 'msg error show';
            msg.textContent = data.message || 'Passwort konnte nicht geändert werden.';
        }
    } catch {
        msg.className = 'msg error show';
        msg.textContent = 'Netzwerkfehler. Bitte erneut versuchen.';
    } finally {
        btn.disabled = false;
        btn.textContent = 'Passwort ändern';
    }
});
</script>

</body>
</html>
