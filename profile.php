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
    <link rel="stylesheet" href="assets/fonts/fonts.css">
    <link rel="stylesheet" href="assets/css/cvs-core.css">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        :root{
            --bg:#020205;--surface:#0a0e1a;--surface2:#0f1526;
            --border:rgba(24,114,255,.18);--text:#edf2ff;--muted:rgba(237,242,255,.55);
            --primary:#1872ff;--primary-h:#003ee8;--blue-glow:#4da0ff;
            --gold:#e8c355;--gold-warm:#d4a93c;--ok:#4ade80;--err:#f87171;
            --radius:14px;--radius-sm:8px;--ease:cubic-bezier(.16,1,.3,1);
        }
        body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;}

        /* CVS-Header/Footer-Integration — cvs-core-Globals neutralisieren + fixe Nav freistellen (Mittelteil unverändert) */
        html,body{cursor:auto!important}
        body::before{display:none!important}
        #cursor,#cursor-ring{display:none!important}
        body{padding-top:96px}

        /* ── Cinematic Aurora ── */
        .cvs-aurora{position:fixed;inset:0;z-index:0;pointer-events:none;overflow:hidden}
        .cvs-aurora span{position:absolute;border-radius:50%;filter:blur(120px);will-change:transform}
        .cvs-aurora .a1{width:56vw;height:56vw;left:-14vw;top:-12vw;background:radial-gradient(circle,rgba(24,114,255,.26),transparent 66%);animation:drift1 28s ease-in-out infinite}
        .cvs-aurora .a2{width:50vw;height:50vw;right:-14vw;top:34vh;background:radial-gradient(circle,rgba(232,169,59,.18),transparent 66%);animation:drift2 34s ease-in-out infinite}
        .cvs-aurora .a3{width:46vw;height:46vw;left:28vw;bottom:-16vw;background:radial-gradient(circle,rgba(0,62,232,.18),transparent 68%);animation:drift1 40s ease-in-out infinite reverse}
        .cvs-aurora .a4{width:34vw;height:34vw;right:18vw;bottom:8vh;background:radial-gradient(circle,rgba(212,146,43,.12),transparent 70%);animation:drift2 46s ease-in-out infinite}
        @keyframes drift1{0%,100%{transform:translate(0,0)}50%{transform:translate(7vw,5vh)}}
        @keyframes drift2{0%,100%{transform:translate(0,0)}50%{transform:translate(-7vw,-4vh)}}

        /* ── Scroll-Progress-Bar ── */
        #cvs-progress{position:fixed;top:0;left:0;width:0%;height:2px;z-index:9999;pointer-events:none;background:linear-gradient(90deg,var(--blue-glow),#ecca63,var(--gold-warm));box-shadow:0 0 8px rgba(232,195,85,.6),0 0 2px rgba(77,160,255,.5);transform-origin:left}

        /* ── Reveal-on-scroll ── */
        .reveal{opacity:0;transform:translateY(30px);transition:opacity .9s var(--ease),transform .9s var(--ease)}
        .reveal.in{opacity:1;transform:none}
        .reveal[data-d="1"]{transition-delay:.08s}.reveal[data-d="2"]{transition-delay:.16s}.reveal[data-d="3"]{transition-delay:.24s}
        @media(prefers-reduced-motion:reduce){.reveal{opacity:1;transform:none;transition:none}.cvs-aurora span{animation:none}}

        /* Layout */
        .page{max-width:680px;margin:0 auto;padding:40px 20px 80px;position:relative;z-index:1;}
        .page-header{margin-bottom:32px;}
        .page-header h1{font-family:'Syne',sans-serif;font-size:1.7rem;font-weight:800;margin-bottom:6px;}
        .page-header p{color:var(--muted);font-size:.9rem;}

        /* Cards */
        .card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);margin-bottom:20px;overflow:hidden;}
        .card-header{padding:18px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;}
        .card-title{font-family:'Syne',sans-serif;font-size:.95rem;font-weight:700;}
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
        .field input:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(24,114,255,.18),0 0 14px rgba(24,114,255,.18);}
        .field input::placeholder{color:var(--muted);}

        /* Pw Strength */
        .pw-strength{margin-top:5px;height:3px;border-radius:99px;background:var(--border);overflow:hidden;}
        .pw-strength-bar{height:100%;width:0;border-radius:99px;transition:width .3s,background .3s;}

        /* Buttons */
        .btn-primary{width:100%;padding:12px;background:linear-gradient(180deg,#fff7a8 0%,#ffe15a 18%,#ffc21f 48%,#f59a00 78%,#d97900 100%);color:#100700;font-family:'Syne',sans-serif;font-size:.9rem;font-weight:900;border:none;border-radius:var(--radius-sm);cursor:pointer;transition:transform .25s var(--ease),box-shadow .25s var(--ease),opacity .2s;box-shadow:0 6px 20px rgba(232,169,59,.3),inset 0 1px 0 rgba(255,255,255,.55);}
        .btn-primary:hover{opacity:1;transform:translateY(-2px);box-shadow:0 12px 30px rgba(232,169,59,.5),inset 0 1px 0 rgba(255,255,255,.65);}
        .btn-primary:disabled{opacity:.5;cursor:not-allowed;transform:none;}

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

<div class="cvs-aurora" aria-hidden="true"><span class="a1"></span><span class="a2"></span><span class="a3"></span><span class="a4"></span></div>

<nav class="cvs-nav-simple" id="main-nav">
  <a href="https://cinematic-vision-studio.de/scene-editor-test.html" class="nav-logo"><img src="assets/cvs-logo-icon.png" alt="CVS" class="cvs-nav-img"><span class="cvs-nav-txt"><span class="nav-t1">Cinematic</span> <span class="nav-t2">Vision</span><span class="cvs-nav-sub"><span class="nav-t3">Studio</span></span></span></a>
  <div class="nav-links">
    <a href="https://cinematic-vision-studio.de/scene-editor-test.html" class="nav-link">Home</a>
    <a href="studio-demo.php" class="nav-link">Studio</a>
    <a href="https://cinematic-vision-studio.de/crystals.html" class="nav-link">Kristalle</a>
    <a href="https://cinematic-vision-studio.de/kontakt.html" class="nav-link">Kontakt</a>
    <a href="https://cinematic-vision-studio.de/calendar.html" class="nav-link">Buchung</a>
    <a href="https://cinematic-vision-studio.de/prompt-generator.html" class="nav-link">Prompts</a>
    <a href="https://cinematic-vision-studio.de/portfolio.html" class="nav-link">Portfolio</a>
    <a href="https://cinematic-vision-studio.de/academy.html" class="nav-link">Academy</a>
    <a href="https://cinematic-vision-studio.de/shop.html" class="nav-link">Shop</a>
  </div>
  <div class="nav-actions">
    <a href="https://cinematic-vision-studio.de/crystals.html" class="wallet-pill">💎 <?= htmlspecialchars((string)$user['crystals_balance']) ?></a>
    <form method="POST" action="/api/auth/logout.php" style="display:inline;margin:0">
      <input type="hidden" name="redirect" value="login.php">
      <button type="submit" class="nav-btn-ghost" style="cursor:pointer;font-family:'Syne',sans-serif">Logout</button>
    </form>
  </div>
  <button class="nav-burger" id="mobBurger" aria-label="Menü öffnen" aria-expanded="false"><span></span><span></span><span></span></button>
</nav>
<div class="mob-menu" id="mobMenu" role="navigation" aria-label="Navigation">
  <a href="https://cinematic-vision-studio.de/scene-editor-test.html" class="mob-link">🏠 Home</a>
  <a href="studio-demo.php" class="mob-link">🎬 Studio</a>
  <a href="https://cinematic-vision-studio.de/crystals.html" class="mob-link">💎 Kristalle</a>
  <a href="https://cinematic-vision-studio.de/kontakt.html" class="mob-link">📞 Kontakt</a>
  <a href="https://cinematic-vision-studio.de/calendar.html" class="mob-link">📅 Buchung</a>
  <a href="https://cinematic-vision-studio.de/prompt-generator.html" class="mob-link">✍️ Prompts</a>
  <a href="https://cinematic-vision-studio.de/portfolio.html" class="mob-link">🎞️ Portfolio</a>
  <a href="https://cinematic-vision-studio.de/academy.html" class="mob-link">🎓 Academy</a>
  <a href="https://cinematic-vision-studio.de/shop.html" class="mob-link">🛍️ Shop</a>
  <div class="mob-sep"></div>
  <a href="dashboard.php" class="mob-link">📊 Dashboard</a>
  <form method="POST" action="/api/auth/logout.php" style="margin:0"><input type="hidden" name="redirect" value="login.php"><button type="submit" class="mob-cta" style="width:calc(100% - 40px);cursor:pointer;font-family:'Syne',sans-serif">Logout</button></form>
</div>

<div class="page">

    <div class="page-header">
        <h1>⚙️ Profil</h1>
        <p>Konto-Einstellungen und Plan-Übersicht</p>
    </div>

    <!-- Account Info -->
    <div class="card reveal">
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
                    $planColors = ['free' => '#888899', 'starter' => '#1872ff', 'pro' => '#e8c355'];
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
    <div class="card reveal" style="border-color:rgba(24,114,255,.3);background:rgba(24,114,255,.05);">
        <div class="card-body" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;">
            <div>
                <div style="font-family:'Syne',sans-serif;font-weight:800;margin-bottom:4px;">Auf Starter+ upgraden</div>
                <div style="font-size:.85rem;color:var(--muted)">1080p · 60s · Original-Audio · 90 Tage Speicher</div>
            </div>
            <a href="https://cinematic-vision-studio.de/crystals.html"
               style="display:inline-block;padding:10px 20px;background:linear-gradient(180deg,#fff7a8 0%,#ffe15a 18%,#ffc21f 48%,#f59a00 78%,#d97900 100%);color:#100700;font-family:'Syne',sans-serif;font-size:.875rem;font-weight:900;border-radius:var(--radius-sm);text-decoration:none;white-space:nowrap;box-shadow:0 6px 20px rgba(232,169,59,.28),inset 0 1px 0 rgba(255,255,255,.5);">
                ⬆️ Upgraden →
            </a>
        </div>
    </div>
    <?php endif; ?>

    <!-- Passwort ändern -->
    <div class="card reveal">
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
    <div class="card reveal" style="border-color:rgba(248,113,113,.2);">
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

<footer class="cvs-footer-master">
  <div class="footer-inner">
    <div class="footer-brand">
      <h3>Cinematic Vision Studio</h3>
      <p>Deine Vision. Als cinematic KI-Erlebnis. Premium AI-Cinematic-Produktion für Creator und Marken.</p>
    </div>
    <div class="footer-col">
      <h4>Studio</h4>
      <a href="https://cinematic-vision-studio.de/scene-editor-test.html">Über uns</a>
      <a href="https://cinematic-vision-studio.de/scene-editor-test.html#process">Prozess</a>
      <a href="https://cinematic-vision-studio.de/academy.html">Academy</a>
      <a href="https://cinematic-vision-studio.de/calendar.html">Buchung</a>
    </div>
    <div class="footer-col">
      <h4>Services</h4>
      <a href="https://cinematic-vision-studio.de/portfolio.html">Trailer</a>
      <a href="https://cinematic-vision-studio.de/shop.html">Shop</a>
      <a href="https://cinematic-vision-studio.de/prompt-generator.html">Prompt Studio</a>
      <a href="https://cinematic-vision-studio.de/portfolio.html">Portfolio</a>
      <a href="https://cinematic-vision-studio.de/crystals.html">Kristalle</a>
    </div>
    <div class="footer-col">
      <h4>Kontakt</h4>
      <a href="https://cinematic-vision-studio.de/kontakt.html">Projekt anfragen</a>
      <a href="studio-demo.php">Studio starten</a>
    </div>
  </div>
  <div class="footer-bottom">
    <div class="footer-copy">© 2026 Cinematic Vision Studio · Alle Rechte vorbehalten</div>
    <div class="footer-legal">
      <a href="https://cinematic-vision-studio.de/impressum.html">Impressum</a>
      <a href="https://cinematic-vision-studio.de/datenschutz.html">Datenschutz</a>
      <a href="https://cinematic-vision-studio.de/agb.html">AGB</a>
      <a href="https://cinematic-vision-studio.de/widerruf.html">Widerruf</a>
      <a href="https://cinematic-vision-studio.de/cookies.html">Cookies</a>
      <a href="https://cinematic-vision-studio.de/kontakt.html">Kontakt</a>
    </div>
  </div>
</footer>

<div id="cvs-progress" aria-hidden="true"></div>
<script src="assets/js/nav.js" defer></script>
<script>
/* ── Reveal-on-scroll ── */
(function(){
  var els=document.querySelectorAll('.reveal');
  if(!('IntersectionObserver' in window)||matchMedia('(prefers-reduced-motion:reduce)').matches){
    els.forEach(function(e){e.classList.add('in');});return;
  }
  var io=new IntersectionObserver(function(entries){
    entries.forEach(function(en){if(en.isIntersecting){en.target.classList.add('in');io.unobserve(en.target);}});
  },{threshold:.12,rootMargin:'0px 0px -8% 0px'});
  els.forEach(function(e){io.observe(e);});
})();
/* ── Scroll-Progress-Bar ── */
(function(){
  var bar=document.getElementById('cvs-progress');if(!bar)return;var raf=null;
  function update(){var h=document.documentElement.scrollHeight-window.innerHeight;bar.style.width=(h>0?(window.scrollY/h*100):0)+'%';raf=null;}
  window.addEventListener('scroll',function(){if(!raf)raf=requestAnimationFrame(update);},{passive:true});update();
})();
</script>
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
