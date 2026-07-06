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
    <link rel="stylesheet" href="assets/fonts/fonts.css">
    <link rel="stylesheet" href="assets/css/cvs-core.css">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}

        :root{
            --bg:#020205;
            --surface:#0a0e1a;
            --surface2:#0f1526;
            --border:rgba(24,114,255,.18);
            --text:#edf2ff;
            --text-muted:rgba(237,242,255,.55);
            --primary:#1872ff;
            --primary-h:#003ee8;
            --blue-glow:#4da0ff;
            --gold:#e8c355;
            --gold-warm:#d4a93c;
            --ok:#4ade80;
            --err:#f87171;
            --radius:12px;
            --radius-sm:8px;
            --ease:cubic-bezier(.16,1,.3,1);
        }

        body{
            font-family:'DM Sans',sans-serif;
            background:var(--bg);
            color:var(--text);
            min-height:100vh;
            display:flex;
            flex-direction:column;
        }
        /* CVS-Header/Footer-Integration — cvs-core-Globals neutralisieren, Mittelteil unverändert */
        html,body{cursor:auto!important}
        body::before{display:none!important}
        #cursor,#cursor-ring{display:none!important}

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
        .reveal[data-d="1"]{transition-delay:.1s}.reveal[data-d="2"]{transition-delay:.2s}.reveal[data-d="3"]{transition-delay:.3s}
        @media(prefers-reduced-motion:reduce){.reveal{opacity:1;transform:none;transition:none}.cvs-aurora span{animation:none}}

        .auth-main{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;width:100%;padding:116px 16px 56px;position:relative;z-index:1}

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
            background:linear-gradient(135deg,var(--primary),var(--blue-glow));
            border-radius:10px;
            display:flex;align-items:center;justify-content:center;
            font-size:20px;
        }
        .auth-logo-text{
            font-family:'Syne',sans-serif;
            font-size:1.2rem;
            font-weight:800;
            color:var(--text);
        }
        .auth-logo-text span{color:var(--gold);}

        /* ── Card ── */
        .auth-card{
            width:100%;
            max-width:440px;
            background:rgba(9,13,24,.82);
            border:1px solid var(--border);
            border-radius:var(--radius);
            overflow:hidden;
            backdrop-filter:blur(16px);
            -webkit-backdrop-filter:blur(16px);
            box-shadow:0 24px 60px rgba(0,0,0,.5),0 0 44px rgba(24,114,255,.06);
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
            color:var(--gold);
            background:var(--surface2);
            border-bottom:2px solid var(--gold);
        }

        /* ── Form ── */
        .auth-form{
            padding:32px;
        }
        .auth-form-title{
            font-family:'Syne',sans-serif;
            font-size:1.35rem;
            font-weight:800;
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
            box-shadow:0 0 0 3px rgba(24,114,255,.18),0 0 14px rgba(24,114,255,.18);
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

        /* ── Submit Button (CVS Gold) ── */
        .btn-submit{
            width:100%;
            padding:14px;
            background:linear-gradient(180deg,#fff7a8 0%,#ffe15a 18%,#ffc21f 48%,#f59a00 78%,#d97900 100%);
            color:#100700;
            font-family:'Syne',sans-serif;
            font-size:1rem;
            font-weight:900;
            border:none;
            border-radius:var(--radius-sm);
            cursor:pointer;
            transition:transform .25s var(--ease),box-shadow .25s var(--ease),opacity .2s;
            letter-spacing:.02em;
            box-shadow:0 6px 20px rgba(232,169,59,.3),inset 0 1px 0 rgba(255,255,255,.55);
        }
        .btn-submit:hover{opacity:1;transform:translateY(-2px);box-shadow:0 12px 30px rgba(232,169,59,.5),inset 0 1px 0 rgba(255,255,255,.65);}
        .btn-submit:active{transform:scale(.99);}
        .btn-submit:disabled{opacity:.5;cursor:not-allowed;transform:none;}

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
            background:rgba(24,114,255,.08);
            border:1px solid rgba(24,114,255,.22);
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
    <a href="login.php" class="nav-btn-ghost">Login</a>
    <a href="login.php?tab=register" class="nav-btn-gold">Studio starten</a>
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
  <a href="login.php" class="mob-link">🔑 Login</a>
  <a href="login.php?tab=register" class="mob-cta">🎬 Studio starten</a>
</div>

<main class="auth-main">
<div class="auth-card reveal">

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
</main>

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
