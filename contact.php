<?php
/**
 * contact.php — Kontaktformular
 *
 * Läuft auf IONOS (PHP mail() funktioniert) und auf Render (benötigt SMTP-Setup).
 * Formular-Verarbeitung: POST → same page, kein JS-Fetch nötig.
 * Honeypot-Feld (website) blockt Bots.
 */
declare(strict_types=1);

$sent  = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = mb_substr(trim(strip_tags((string)($_POST['name']    ?? ''))), 0, 100);
    $emailIn = trim((string)($_POST['email']   ?? ''));
    $subject = mb_substr(trim(strip_tags((string)($_POST['subject'] ?? ''))), 0, 150);
    $message = mb_substr(trim(strip_tags((string)($_POST['message'] ?? ''))), 0, 3000);
    $website = (string)($_POST['website'] ?? ''); // Honeypot — bleibt leer bei echten Nutzern

    if ($website !== '') {
        $sent = true; // Bot erkannt — stille Bestätigung
    } elseif ($name === '' || !filter_var($emailIn, FILTER_VALIDATE_EMAIL) || mb_strlen($message) < 10) {
        $error = 'Bitte Name, gültige E-Mail-Adresse und eine Nachricht (mind. 10 Zeichen) eingeben.';
    } else {
        $to      = 'info@cinematic-vision-studio.de';
        $subj    = '=?UTF-8?B?' . base64_encode('Kontakt: ' . ($subject !== '' ? $subject : 'Keine Angabe')) . '?=';
        $body    = "Name: {$name}\r\nE-Mail: {$emailIn}\r\n\r\nNachricht:\r\n{$message}";
        $headers = implode("\r\n", [
            'From: noreply@cinematic-vision-studio.de',
            'Reply-To: ' . $emailIn,
            'Content-Type: text/plain; charset=UTF-8',
            'MIME-Version: 1.0',
            'X-Mailer: CinematicStudio/1.0',
        ]);

        if (@mail($to, $subj, $body, $headers)) {
            $sent = true;
        } else {
            $error = 'E-Mail konnte nicht gesendet werden. Bitte schreib uns direkt: <a href="mailto:info@cinematic-vision-studio.de">info@cinematic-vision-studio.de</a>';
        }
    }
}

$renderBase = 'https://cinematic-studio-family.onrender.com';
?>
<!doctype html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Kontakt – Cinematic Vision Studio</title>
<meta name="description" content="Kontaktformular für Cinematic Vision Studio. Fragen, Feedback, Beta-Zugang.">
<style>
:root{
  --bg:#06060f;--card:#0f0f1e;--card2:#161628;--text:#f0f0ff;--muted:#8888aa;--muted2:#5a5a7a;
  --accent:#f5c542;--accent2:#ff8c00;--blue:#3b82f6;--purple:#9333ea;--ok:#4ade80;--danger:#ff5c7a;
  --border:rgba(255,255,255,.09);--gold:linear-gradient(135deg,#f5c542,#ff8c00);--radius:18px;
}
*{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{font-family:'Segoe UI',Arial,sans-serif;background:var(--bg);color:var(--text);min-height:100vh}

/* ── Nav ── */
.nav{position:fixed;top:0;left:0;right:0;z-index:100;display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px 28px;background:rgba(6,6,15,.88);backdrop-filter:blur(20px);border-bottom:1px solid var(--border);}
.nav-logo{font-size:13px;font-weight:900;letter-spacing:2px;background:var(--gold);-webkit-background-clip:text;-webkit-text-fill-color:transparent;text-transform:uppercase;flex-shrink:0;text-decoration:none;}
.nav-logo span{font-size:10px;display:block;letter-spacing:4px;font-weight:400;opacity:.6;margin-top:1px;}
.nav-links{display:flex;align-items:center;gap:2px;flex:1;justify-content:center;}
.nav-link{color:var(--accent);text-decoration:none;font-size:13px;font-weight:600;padding:7px 10px;border-radius:9px;transition:color .15s,background .15s;white-space:nowrap;}
.nav-link:hover,.nav-link.active{color:var(--text);background:rgba(245,197,66,.12);}
.nav-actions{display:flex;align-items:center;gap:8px;flex-shrink:0;}
.nav-btn-ghost{color:var(--accent);text-decoration:none;font-size:13px;font-weight:700;padding:8px 13px;border-radius:10px;border:1px solid rgba(245,197,66,.35);transition:color .15s,border-color .15s;}
.nav-btn-ghost:hover{color:var(--text);border-color:rgba(245,197,66,.7);}
.nav-btn-gold{background:var(--gold);color:#1a0e00;text-decoration:none;font-size:13px;font-weight:800;padding:9px 16px;border-radius:10px;transition:opacity .15s;}
.nav-btn-gold:hover{opacity:.88;}
@media(max-width:900px){.nav-links{display:none;}}
@media(max-width:640px){.nav{padding:12px 16px;gap:8px;}.nav-logo span{display:none;}.nav-btn-ghost{display:none;}.nav-btn-gold{display:none;}}
.mob-burger{display:none;flex-direction:column;justify-content:center;gap:5px;width:40px;height:40px;padding:8px;background:rgba(255,255,255,.06);border:1px solid var(--border);border-radius:10px;cursor:pointer;flex-shrink:0;}
.mob-burger span{display:block;height:2px;border-radius:2px;background:var(--text);transition:transform .22s,opacity .22s;}
.mob-burger.open span:nth-child(1){transform:translateY(7px) rotate(45deg);}
.mob-burger.open span:nth-child(2){opacity:0;}
.mob-burger.open span:nth-child(3){transform:translateY(-7px) rotate(-45deg);}
.mob-menu{position:fixed;left:0;right:0;z-index:99;background:rgba(6,6,15,.97);backdrop-filter:blur(24px);border-bottom:1px solid var(--border);max-height:0;overflow:hidden;opacity:0;pointer-events:none;transition:max-height .3s cubic-bezier(.4,0,.2,1),opacity .22s;display:none;}
.mob-menu.open{max-height:820px;opacity:1;pointer-events:auto;}
.mob-link{display:block;padding:14px 24px;color:var(--accent);text-decoration:none;font-size:15px;font-weight:700;border-left:3px solid transparent;transition:color .15s,border-color .15s,background .15s;}
.mob-link.active{color:var(--text);border-left-color:var(--accent);background:rgba(245,197,66,.08);}
.mob-sep{height:1px;background:var(--border);margin:10px 20px;}
.mob-cta{display:block;margin:12px 20px 8px;padding:15px;background:var(--gold);color:#1a0e00;text-decoration:none;font-size:15px;font-weight:900;border-radius:13px;text-align:center;}
@media(max-width:900px){.mob-burger{display:flex;}.mob-menu{display:block;}}

/* ── Page ── */
.page{padding:100px 0 80px;position:relative;}
.page::before{content:"";position:absolute;top:0;left:0;right:0;height:420px;background:radial-gradient(ellipse 70% 120% at 15% 0%,rgba(59,130,246,.07) 0%,transparent 60%),radial-gradient(ellipse 50% 80% at 85% 20%,rgba(147,51,234,.06) 0%,transparent 55%);pointer-events:none;z-index:0;}
.wrap{width:min(680px,calc(100% - 48px));margin:0 auto;position:relative;z-index:1;}

/* ── Header ── */
.eyebrow{display:inline-flex;align-items:center;gap:8px;font-size:11px;font-weight:800;letter-spacing:3px;text-transform:uppercase;color:var(--accent);background:rgba(245,197,66,.1);border:1px solid rgba(245,197,66,.28);border-radius:999px;padding:6px 14px;margin-bottom:18px;}
.page-h1{font-size:clamp(28px,5vw,48px);font-weight:900;letter-spacing:-1.5px;margin-bottom:12px;line-height:1.1;}
.page-h1 span{background:var(--gold);-webkit-background-clip:text;-webkit-text-fill-color:transparent;}
.page-sub{color:var(--accent);font-size:15px;line-height:1.65;margin-bottom:40px;}

/* ── Form ── */
.contact-form{background:var(--card);border:1px solid var(--border);border-radius:var(--radius);padding:36px;margin-bottom:28px;}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
@media(max-width:560px){.form-row{grid-template-columns:1fr;}}
.field{margin-bottom:18px;}
.field:last-child{margin-bottom:0;}
.field label{display:block;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--accent);margin-bottom:6px;}
.field input,.field textarea,.field select{width:100%;padding:12px 14px;background:var(--card2);border:1px solid var(--border);border-radius:10px;color:var(--text);font-size:14px;font-family:inherit;outline:none;transition:border-color .2s,box-shadow .2s;resize:none;}
.field input:focus,.field textarea:focus{border-color:rgba(245,197,66,.5);box-shadow:0 0 0 3px rgba(245,197,66,.08);}
.field input::placeholder,.field textarea::placeholder{color:var(--muted2);}
.field textarea{min-height:120px;line-height:1.5;}
.honeypot{display:none;}

/* ── Submit ── */
.btn-send{display:inline-flex;align-items:center;gap:8px;background:var(--gold);color:#1a0e00;font-size:15px;font-weight:900;padding:14px 28px;border-radius:12px;border:none;cursor:pointer;font-family:inherit;transition:opacity .15s,transform .1s;width:100%;justify-content:center;}
.btn-send:hover{opacity:.88;}
.btn-send:active{transform:scale(.98);}

/* ── Messages ── */
.msg-error{background:rgba(255,92,122,.1);border:1px solid rgba(255,92,122,.3);border-radius:12px;padding:14px 18px;color:var(--danger);font-size:14px;line-height:1.6;margin-bottom:20px;}
.msg-error a{color:var(--danger);font-weight:700;}
.msg-success{background:var(--card);border:1px solid rgba(74,222,128,.3);border-radius:var(--radius);padding:36px;text-align:center;margin-bottom:28px;}
.msg-success .success-icon{font-size:3rem;margin-bottom:12px;}
.msg-success h2{font-size:1.4rem;font-weight:800;margin-bottom:8px;color:var(--ok);}
.msg-success p{color:var(--accent);font-size:15px;line-height:1.65;}

/* ── Direct contact card ── */
.direct-card{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:22px 26px;display:flex;align-items:center;gap:16px;margin-bottom:28px;text-decoration:none;transition:border-color .2s;}
.direct-card:hover{border-color:rgba(245,197,66,.3);}
.direct-icon{font-size:1.8rem;flex-shrink:0;}
.direct-text{}
.direct-label{font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--accent);margin-bottom:4px;}
.direct-value{font-size:14px;font-weight:700;color:var(--accent);}
.direct-hint{font-size:12px;color:var(--accent);margin-top:2px;opacity:.75;}

/* ── Back ── */
.back-link{display:inline-flex;align-items:center;gap:8px;color:var(--accent);text-decoration:none;font-size:14px;padding:10px 18px;border:1px solid rgba(245,197,66,.3);border-radius:10px;transition:color .15s,border-color .15s;}
.back-link:hover{color:var(--text);border-color:rgba(245,197,66,.7);}

/* ── Footer ── */
.footer{border-top:1px solid var(--border);padding:32px 0;margin-top:60px;}
.footer-inner{width:min(960px,calc(100% - 48px));margin:0 auto;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;}
.footer-logo{font-size:13px;font-weight:900;letter-spacing:2px;background:var(--gold);-webkit-background-clip:text;-webkit-text-fill-color:transparent;}
.footer-nav{display:flex;gap:16px;flex-wrap:wrap;}
.footer-nav a{color:var(--accent);text-decoration:none;font-size:12px;font-weight:600;transition:color .15s;}
.footer-nav a:hover{color:var(--text);}
.footer-copy{font-size:12px;color:var(--accent);opacity:.5;}
</style>
</head>
<body>

<!-- NAV -->
<nav class="nav">
  <a href="scene-editor-test.html" class="nav-logo">Cinematic Vision Studio<span>Premium KI-Filmstudio</span></a>
  <div class="nav-links">
    <a href="scene-editor-test.html" class="nav-link">Home</a>
    <a href="<?= $renderBase ?>/studio-demo.php" class="nav-link">Studio</a>
    <a href="shop.html" class="nav-link">Shop</a>
    <a href="academy.html" class="nav-link">Academy</a>
    <a href="portfolio.html" class="nav-link">Portfolio</a>
    <a href="ki-videos.html" class="nav-link">KI Videos</a>
    <a href="prompt-generator.html" class="nav-link">Prompts</a>
    <a href="calendar.html" class="nav-link">Kalender</a>
    <a href="crystals.html" class="nav-link">Kristalle</a>
    <a href="contact.php" class="nav-link active">Kontakt</a>
  </div>
  <div class="nav-actions">
    <a href="<?= $renderBase ?>/login.php" class="nav-btn-ghost">Login</a>
    <a href="<?= $renderBase ?>/studio-demo.php" class="nav-btn-gold">Studio starten</a>
  </div>
  <button class="mob-burger" id="mobBurger" aria-label="Menü öffnen" aria-expanded="false"><span></span><span></span><span></span></button>
</nav>

<!-- MOBILE MENU -->
<div class="mob-menu" id="mobMenu" role="navigation" aria-label="Navigation">
  <a href="scene-editor-test.html" class="mob-link">🏠 Home</a>
  <a href="<?= $renderBase ?>/studio-demo.php" class="mob-link">🎬 Studio</a>
  <a href="shop.html" class="mob-link">🛍️ Shop</a>
  <a href="academy.html" class="mob-link">🎓 Academy</a>
  <a href="portfolio.html" class="mob-link">🎞️ Portfolio</a>
  <a href="ki-videos.html" class="mob-link">🎥 KI Videos</a>
  <a href="prompt-generator.html" class="mob-link">✍️ Prompts</a>
  <a href="calendar.html" class="mob-link">📅 Kalender</a>
  <a href="crystals.html" class="mob-link">💎 Kristalle</a>
  <a href="contact.php" class="mob-link active">📞 Kontakt</a>
  <div class="mob-sep"></div>
  <a href="<?= $renderBase ?>/login.php" class="mob-link">🔑 Login / Registrieren</a>
  <a href="<?= $renderBase ?>/studio-demo.php" class="mob-cta">🎬 Studio starten</a>
</div>

<!-- PAGE -->
<div class="page">
  <div class="wrap">
    <div class="eyebrow">📬 Kontakt</div>
    <h1 class="page-h1">Schreib uns<br><span>direkt an.</span></h1>
    <p class="page-sub">Fragen, Feedback oder Kooperationsanfragen — wir antworten in der Regel innerhalb von 24 Stunden.</p>

    <?php if ($sent): ?>
      <!-- Erfolgsmeldung -->
      <div class="msg-success">
        <div class="success-icon">✓</div>
        <h2>Nachricht gesendet!</h2>
        <p>Danke — wir melden uns bald bei dir.</p>
      </div>
    <?php else: ?>
      <?php if ($error !== ''): ?>
        <div class="msg-error"><?= $error ?></div>
      <?php endif; ?>

      <!-- Kontaktformular -->
      <form class="contact-form" method="post" action="contact.php">
        <div class="form-row">
          <div class="field">
            <label for="cname">Dein Name *</label>
            <input type="text" id="cname" name="name" placeholder="Max Mustermann" required maxlength="100"
                   value="<?= htmlspecialchars((string)($_POST['name'] ?? '')) ?>">
          </div>
          <div class="field">
            <label for="cemail">E-Mail-Adresse *</label>
            <input type="email" id="cemail" name="email" placeholder="deine@email.de" required
                   value="<?= htmlspecialchars((string)($_POST['email'] ?? '')) ?>">
          </div>
        </div>
        <div class="field">
          <label for="csubject">Betreff</label>
          <input type="text" id="csubject" name="subject" placeholder="Worum geht es?" maxlength="150"
                 value="<?= htmlspecialchars((string)($_POST['subject'] ?? '')) ?>">
        </div>
        <div class="field">
          <label for="cmessage">Nachricht *</label>
          <textarea id="cmessage" name="message" placeholder="Deine Nachricht..." required minlength="10"><?= htmlspecialchars((string)($_POST['message'] ?? '')) ?></textarea>
        </div>
        <!-- Honeypot: bleibt leer -->
        <div class="honeypot" aria-hidden="true">
          <label>Website</label>
          <input type="text" name="website" tabindex="-1" autocomplete="off">
        </div>
        <button type="submit" class="btn-send">📬 Nachricht senden</button>
      </form>
    <?php endif; ?>

    <!-- Direktkontakt -->
    <div class="direct-card">
      <div class="direct-icon">✉️</div>
      <div class="direct-text">
        <div class="direct-label">Direkt per E-Mail</div>
        <div class="direct-value">info@cinematic-vision-studio.de</div>
        <div class="direct-hint">Nutze das Formular oben — oder schreib uns direkt an.</div>
      </div>
    </div>

    <a href="scene-editor-test.html" class="back-link">← Zurück zum Hub</a>
  </div>
</div>

<!-- FOOTER -->
<footer class="footer">
  <div class="footer-inner">
    <div class="footer-logo">Cinematic Vision Studio</div>
    <div class="footer-copy">© 2026 Cinematic Vision Studio · Alle Rechte vorbehalten</div>
    <nav class="footer-nav">
      <a href="impressum.html">Impressum</a>
      <a href="datenschutz.html">Datenschutz</a>
      <a href="agb.html">AGB</a>
      <a href="widerruf.html">Widerruf</a>
      <a href="cookies.html">Cookies</a>
    </nav>
  </div>
</footer>

<script>
(function(){
  var b=document.getElementById('mobBurger'),m=document.getElementById('mobMenu');
  if(!b||!m)return;
  function t(){m.style.top=document.querySelector('.nav').offsetHeight+'px';}
  t();window.addEventListener('resize',t);
  b.addEventListener('click',function(e){
    e.stopPropagation();
    var o=m.classList.toggle('open');
    b.classList.toggle('open',o);
    b.setAttribute('aria-expanded',o?'true':'false');
    b.setAttribute('aria-label',o?'Menü schließen':'Menü öffnen');
  });
  m.querySelectorAll('.mob-link,.mob-cta').forEach(function(l){
    l.addEventListener('click',function(){
      m.classList.remove('open');b.classList.remove('open');
      b.setAttribute('aria-expanded','false');b.setAttribute('aria-label','Menü öffnen');
    });
  });
  document.addEventListener('click',function(e){
    if(!b.contains(e.target)&&!m.contains(e.target)){
      m.classList.remove('open');b.classList.remove('open');
      b.setAttribute('aria-expanded','false');
    }
  });
  document.addEventListener('keydown',function(e){
    if(e.key==='Escape'){m.classList.remove('open');b.classList.remove('open');b.setAttribute('aria-expanded','false');}
  });
})();
</script>
</body>
</html>
