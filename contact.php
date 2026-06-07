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
<meta name="theme-color" content="#020205">
<title>Kontakt – Cinematic Vision Studio</title>
<meta name="description" content="Kontaktformular für Cinematic Vision Studio. Fragen, Feedback, Beta-Zugang.">
<meta property="og:title" content="Kontakt – Cinematic Vision Studio">
<meta property="og:description" content="Kontaktformular für Cinematic Vision Studio. Fragen, Feedback, Beta-Zugang.">
<meta property="og:type" content="website">
<link rel="icon" type="image/png" href="assets/cvs-logo-icon.png">
<link rel="stylesheet" href="assets/fonts/fonts.css">
<style>
:root {
  --black:       #020205;
  --black-2:     #06060f;
  --black-3:     #09091a;
  --glass-bg:    rgba(5,8,24,0.78);
  --glass-border:rgba(24,114,255,0.18);
  --blue-core:   #003ee8;
  --blue-bright: #1872ff;
  --blue-glow:   #4da0ff;
  --blue-pale:   rgba(77,160,255,0.10);
  --gold:        #b8942e;
  --gold-warm:   #d4a93c;
  --gold-bright: #e8c355;
  --gold-light:  #f2d878;
  --gold-glow:   rgba(200,160,60,0.4);
  --white:       #edf2ff;
  --white-dim:   rgba(237,242,255,0.52);
  --white-faint: rgba(237,242,255,0.10);
  --radius-sm:   6px;
  --radius-md:   14px;
  --radius-lg:   26px;
  --perspective: 1400px;
}
*{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{background:var(--black);color:var(--white);font-family:'DM Sans',sans-serif;overflow-x:hidden;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale}

/* Film grain */
body::before{
  content:'';position:fixed;inset:0;z-index:0;pointer-events:none;
  background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 512 512' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.75' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.028'/%3E%3C/svg%3E");
  opacity:.28;
}

/* Aurora */
.cvs-aurora{position:fixed;inset:0;z-index:0;pointer-events:none;overflow:hidden}
.orb{position:absolute;border-radius:50%;filter:blur(100px);pointer-events:none}
.orb-1{width:600px;height:600px;background:radial-gradient(circle,rgba(0,42,175,.08),transparent 60%);top:-10%;left:-10%;animation:orbFloat1 20s ease-in-out infinite}
.orb-2{width:500px;height:500px;background:radial-gradient(circle,rgba(160,118,22,.06),transparent 60%);top:20%;right:-15%;animation:orbFloat2 25s ease-in-out infinite}
.orb-3{width:400px;height:400px;background:radial-gradient(circle,rgba(70,22,162,.05),transparent 60%);bottom:10%;left:30%;animation:orbFloat3 18s ease-in-out infinite}
@keyframes orbFloat1{0%,100%{transform:translate(0,0)}50%{transform:translate(30px,20px)}}
@keyframes orbFloat2{0%,100%{transform:translate(0,0)}50%{transform:translate(-20px,30px)}}
@keyframes orbFloat3{0%,100%{transform:translate(0,0)}50%{transform:translate(20px,-25px)}}

/* Scroll progress */
#cvs-progress{position:fixed;top:0;left:0;height:2px;background:linear-gradient(90deg,var(--gold-warm),var(--gold-bright));z-index:10000;width:0%;transition:width .1s}

/* ── NAVIGATION ── */
nav{
  position:fixed;top:0;left:0;right:0;z-index:1000;
  padding:0 clamp(24px,4vw,64px);
  height:76px;display:flex;align-items:center;justify-content:space-between;
  background:linear-gradient(180deg,rgba(2,2,5,.94) 0%,rgba(2,2,5,0) 100%);
  backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);
  border-bottom:1px solid rgba(255,255,255,.03);
  transition:background .4s,border-color .4s;
}
nav.scrolled{background:rgba(2,2,5,.98);border-bottom-color:rgba(24,114,255,.1)}

.nav-logo{
  font-family:'Syne',sans-serif;font-size:1rem;font-weight:800;letter-spacing:.1em;
  background:linear-gradient(130deg,var(--white) 10%,var(--blue-glow) 85%);
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;
  text-decoration:none;flex-shrink:0;
}
.nav-logo span{-webkit-text-fill-color:var(--gold-bright)}

.nav-links{display:flex;gap:0;list-style:none;align-items:center}
.nav-links a{color:rgba(237,242,255,.4);text-decoration:none;font-size:.72rem;font-weight:500;letter-spacing:.07em;padding:7px 10px;transition:color .22s;text-transform:uppercase;white-space:nowrap}
.nav-links a:hover{color:rgba(237,242,255,.88)}
.nav-links .nav-cta{margin-left:10px;background:rgba(0,62,232,.15);border:1px solid rgba(24,114,255,.35);color:var(--blue-glow);padding:8px 22px;border-radius:50px;font-weight:600;-webkit-text-fill-color:var(--blue-glow);transition:all .25s}
.nav-links .nav-cta:hover{background:rgba(0,62,232,.28);border-color:rgba(24,114,255,.6);color:var(--white);-webkit-text-fill-color:var(--white)}

.nav-actions{display:flex;align-items:center;gap:8px;flex-shrink:0;}
.nav-btn-ghost{color:var(--white-dim);text-decoration:none;font-size:.72rem;font-weight:600;letter-spacing:.07em;text-transform:uppercase;padding:7px 10px;transition:color .22s;white-space:nowrap;}
.nav-btn-ghost:hover{color:var(--white);}

.nav-btn-gold{background:linear-gradient(135deg,var(--gold-warm),var(--gold-bright));color:#1a0e00;text-decoration:none;font-size:.72rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;padding:8px 16px;border-radius:50px;transition:opacity .15s;}
.nav-btn-gold:hover{opacity:.88;}

.nav-burger{display:none;flex-direction:column;gap:5px;cursor:pointer;padding:8px;background:none;border:none}
.nav-burger span{display:block;width:24px;height:2px;background:var(--blue-bright);border-radius:2px;transition:transform .3s,opacity .3s}

/* Mobile nav */
.mobile-nav{
  display:none;position:fixed;inset:0;z-index:999;
  background:rgba(2,2,5,.98);backdrop-filter:blur(24px);-webkit-backdrop-filter:blur(24px);
  flex-direction:column;align-items:center;justify-content:center;gap:2px;padding:76px 40px 40px;
  overflow-y:auto;
}
.mobile-nav.open{display:flex}
.mobile-nav a{
  font-family:'Syne',sans-serif;font-size:1.4rem;font-weight:700;
  color:var(--white);text-decoration:none;
  padding:11px 0;width:100%;text-align:center;
  border-bottom:1px solid rgba(24,114,255,.06);
  transition:color .22s;letter-spacing:-.01em;
}
.mobile-nav a:hover{color:var(--blue-bright)}
.mobile-nav a:last-child{border-bottom:none;margin-top:8px}
.mobile-nav .nav-cta-mobile{
  margin-top:12px;background:rgba(0,62,232,.15);border:1px solid rgba(24,114,255,.35);color:var(--blue-glow);
  padding:12px 28px;border-radius:50px;font-family:'Syne',sans-serif;font-size:1rem;font-weight:700;
  text-decoration:none;transition:all .25s;
}
.mobile-nav .nav-cta-mobile:hover{background:rgba(0,62,232,.28);border-color:rgba(24,114,255,.6);color:var(--white);}

/* ── PAGE ── */
.page{padding:120px 0 80px;position:relative;z-index:1;}
.page{
  background:
    radial-gradient(ellipse 80% 50% at 50% 0%,rgba(0,38,148,.08) 0%,transparent 55%),
    radial-gradient(ellipse 60% 40% at 85% 20%,rgba(170,130,30,.05) 0%,transparent 50%),
    var(--black);
}
.wrap{width:min(680px,calc(100% - 48px));margin:0 auto;position:relative;z-index:1;}

/* ── HEADER ── */
.section-label{
  display:inline-flex;align-items:center;gap:12px;
  font-size:.62rem;font-weight:600;letter-spacing:.26em;text-transform:uppercase;
  color:rgba(77,160,255,.6);margin-bottom:22px;
}
.section-label::before{content:'';width:24px;height:1px;background:linear-gradient(90deg,var(--gold-warm),transparent);opacity:.72}

.page-h1{font-size:clamp(28px,5vw,48px);font-weight:900;letter-spacing:-1.5px;margin-bottom:12px;line-height:1.1;font-family:'Syne',sans-serif;}
.page-h1 span{background:linear-gradient(110deg,var(--blue-bright) 0%,var(--blue-glow) 45%,var(--gold-warm) 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}

.page-sub{color:var(--white-dim);font-size:15px;line-height:1.65;margin-bottom:40px;}

/* ── Form ── */
.contact-form{background:var(--glass-bg);border:1px solid var(--glass-border);border-radius:var(--radius-lg);backdrop-filter:blur(22px);-webkit-backdrop-filter:blur(22px);padding:42px;margin-bottom:28px;}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
@media(max-width:560px){.form-row{grid-template-columns:1fr;}}
.field{margin-bottom:18px;}
.field:last-child{margin-bottom:0;}
.field label{display:block;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--gold-warm);margin-bottom:6px;}
.field input,.field textarea,.field select{width:100%;padding:12px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.09);border-radius:var(--radius-md);color:var(--white);font-size:14px;font-family:'DM Sans',sans-serif;outline:none;transition:border-color .2s,box-shadow .2s;resize:none;}
.field input:focus,.field textarea:focus{border-color:rgba(24,114,255,.5);box-shadow:0 0 0 3px rgba(24,114,255,.08);}
.field input::placeholder,.field textarea::placeholder{color:var(--white-dim);}
.field textarea{min-height:120px;line-height:1.5;}
.honeypot{display:none;}

/* ── Buttons ── */
.btn-cvs--gold{
  display:inline-flex;align-items:center;gap:10px;justify-content:center;width:100%;
  background:linear-gradient(140deg,#c4a03a 0%,#d4af37 55%,#b8941f 100%);
  color:#1a0e00;text-decoration:none;
  font-family:'Syne',sans-serif;font-size:.88rem;font-weight:700;
  letter-spacing:.13em;text-transform:uppercase;
  padding:16px 32px;border-radius:50px;border:1px solid rgba(200,160,60,.42);
  box-shadow:0 0 32px rgba(200,160,60,.25),0 5px 22px rgba(150,110,28,.22);
  transition:all .38s;position:relative;overflow:hidden;cursor:pointer;border:none;
}
.btn-cvs--gold::before{
  content:'';position:absolute;top:-50%;left:-80%;width:60%;height:200%;
  background:linear-gradient(90deg,transparent,rgba(255,255,255,.15),transparent);
  transform:skewX(-20deg);transition:left .55s;
}
.btn-cvs--gold:hover::before{left:160%}
.btn-cvs--gold:hover{box-shadow:0 0 55px rgba(200,160,60,.45),0 10px 32px rgba(150,110,28,.32);transform:translateY(-2px)}

.btn-cvs--ghost{
  display:inline-flex;align-items:center;gap:8px;
  border:1px solid rgba(237,242,255,.09);color:rgba(237,242,255,.5);text-decoration:none;
  font-family:'Syne',sans-serif;font-size:.8rem;font-weight:600;letter-spacing:.09em;text-transform:uppercase;
  padding:15px 32px;border-radius:50px;
  background:rgba(237,242,255,.02);backdrop-filter:blur(10px);
  transition:all .35s cubic-bezier(.23,1,.32,1);
}
.btn-cvs--ghost:hover{border-color:rgba(237,242,255,.2);color:rgba(237,242,255,.88);transform:translateY(-2px);background:rgba(237,242,255,.04)}

/* ── Messages ── */
.msg-error{background:rgba(255,92,122,.08);border:1px solid rgba(255,92,122,.25);border-radius:var(--radius-md);padding:14px 18px;color:var(--white);font-size:14px;line-height:1.6;margin-bottom:20px;}
.msg-error a{color:#ff5c7a;font-weight:700;}
.msg-success{background:var(--glass-bg);border:1px solid rgba(74,222,128,.25);border-radius:var(--radius-lg);backdrop-filter:blur(16px);padding:42px;text-align:center;margin-bottom:28px;}
.msg-success .success-icon{font-size:3rem;margin-bottom:12px;color:#4ade80;}
.msg-success h2{font-size:1.4rem;font-weight:800;margin-bottom:8px;color:#4ade80;font-family:'Syne',sans-serif;}
.msg-success p{color:var(--white-dim);font-size:15px;line-height:1.65;}

/* ── Direct contact card ── */
.direct-card{background:var(--glass-bg);border:1px solid var(--glass-border);border-radius:var(--radius-lg);backdrop-filter:blur(22px);padding:22px 26px;display:flex;align-items:center;gap:16px;margin-bottom:28px;text-decoration:none;transition:border-color .2s,box-shadow .2s;}
.direct-card:hover{border-color:rgba(200,160,60,.3);box-shadow:0 0 30px rgba(200,160,60,.08)}
.direct-icon{font-size:1.8rem;flex-shrink:0;}
.direct-label{font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--gold-warm);margin-bottom:4px;}
.direct-value{font-size:14px;font-weight:700;color:var(--white);}
.direct-hint{font-size:12px;color:var(--white-dim);margin-top:2px;opacity:.75;}

/* ── Lightbar ── */
.lightbar{height:1px;background:linear-gradient(90deg,transparent,rgba(24,114,255,.18),rgba(200,160,60,.14),transparent);margin:0 auto;max-width:800px;width:90%}

/* ── Reveal ── */
.reveal{opacity:0;transform:translateY(36px);transition:opacity .9s cubic-bezier(.23,1,.32,1),transform .9s cubic-bezier(.23,1,.32,1)}
.reveal.visible{opacity:1;transform:translateY(0)}
.reveal-delay-1{transition-delay:.12s}
.reveal-delay-2{transition-delay:.22s}
.reveal-delay-3{transition-delay:.32s}

/* ── Footer ── */
footer{background:rgba(2,2,5,.98);border-top:1px solid rgba(24,114,255,.09);padding:68px clamp(20px,6vw,80px) 34px;position:relative;z-index:1;}
.footer-grid{display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:44px;margin-bottom:52px}
.footer-brand h3{font-family:'Syne',sans-serif;font-size:1.3rem;font-weight:800;margin-bottom:14px;background:linear-gradient(130deg,var(--blue-bright),var(--gold-bright));-webkit-background-clip:text;-webkit-text-fill-color:transparent;letter-spacing:.02em}
.footer-brand p{font-size:.86rem;color:var(--white-dim);line-height:1.78;max-width:280px}
.footer-col h4{font-family:'Syne',sans-serif;font-size:.72rem;font-weight:700;letter-spacing:.16em;text-transform:uppercase;color:var(--blue-glow);margin-bottom:18px;opacity:.75}
.footer-col a{display:block;color:var(--white-dim);text-decoration:none;font-size:.86rem;padding:5px 0;transition:color .22s;opacity:.7}
.footer-col a:hover{color:var(--blue-bright);opacity:1}
.footer-bottom{border-top:1px solid rgba(255,255,255,.05);padding-top:26px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px}
.footer-bottom p{font-size:.76rem;color:rgba(237,242,255,.25)}
.footer-socials{display:flex;gap:10px}
.social-btn{width:36px;height:36px;background:rgba(24,114,255,.07);border:1px solid rgba(24,114,255,.18);border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;text-decoration:none;color:var(--blue-glow);font-size:.8rem;font-weight:700;transition:all .3s;opacity:.7}
.social-btn:hover{background:rgba(24,114,255,.18);border-color:var(--blue-bright);opacity:1}

/* ── Responsive ── */
@media(max-width:1100px){
  .footer-grid{grid-template-columns:1fr 1fr}
}
@media(max-width:768px){
  .nav-links{display:none}
  .nav-burger{display:flex}
  .footer-grid{grid-template-columns:1fr}
}
@media(max-width:560px){
  .form-row{grid-template-columns:1fr;}
}
</style>
</head>
<body>

<div id="cvs-progress"></div>

<div class="cvs-aurora">
  <div class="orb orb-1"></div>
  <div class="orb orb-2"></div>
  <div class="orb orb-3"></div>
</div>

<!-- NAV -->
<nav>
  <a href="scene-editor-test.html" class="nav-logo">Cinematic Vision <span>Studio</span></a>
  <div class="nav-links">
    <a href="scene-editor-test.html">Home</a>
    <a href="<?= $renderBase ?>/studio-demo.php">Studio</a>
    <a href="prompt-generator.html">Prompts</a>
    <a href="portfolio.html">Portfolio</a>
    <a href="crystals.html">Kristalle</a>
    <a href="shop.html">Shop</a>
    <a href="academy.html">Academy</a>
    <a href="contact.php" class="nav-cta">Kontakt</a>
  </div>
  <div class="nav-actions">
    <a href="<?= $renderBase ?>/login.php" class="nav-btn-ghost">Login</a>
    <a href="<?= $renderBase ?>/studio-demo.php" class="nav-btn-gold">Studio starten</a>
  </div>
  <button class="nav-burger" id="navBurger" aria-label="Menü öffnen" aria-expanded="false"><span></span><span></span><span></span></button>
</nav>

<!-- MOBILE NAV -->
<div class="mobile-nav" id="mobileNav" role="navigation" aria-label="Navigation">
  <a href="scene-editor-test.html">Home</a>
  <a href="<?= $renderBase ?>/studio-demo.php">Studio</a>
  <a href="prompt-generator.html">Prompts</a>
  <a href="portfolio.html">Portfolio</a>
  <a href="crystals.html">Kristalle</a>
  <a href="shop.html">Shop</a>
  <a href="academy.html">Academy</a>
  <a href="contact.php">Kontakt</a>
  <a href="<?= $renderBase ?>/login.php" class="nav-cta-mobile">Login</a>
  <a href="<?= $renderBase ?>/studio-demo.php" class="nav-cta-mobile">Studio starten</a>
</div>

<!-- PAGE -->
<div class="page">
  <div class="wrap">

    <div class="reveal reveal-delay-1">
      <div class="section-label">Kontakt</div>
      <h1 class="page-h1">Schreib uns<br><span>direkt an.</span></h1>
      <p class="page-sub">Fragen, Feedback oder Kooperationsanfragen — wir antworten in der Regel innerhalb von 24 Stunden.</p>
    </div>

    <?php if ($sent): ?>
      <!-- Erfolgsmeldung -->
      <div class="msg-success reveal reveal-delay-2">
        <div class="success-icon">&#10003;</div>
        <h2>Nachricht gesendet!</h2>
        <p>Danke — wir melden uns bald bei dir.</p>
      </div>
    <?php else: ?>
      <?php if ($error !== ''): ?>
        <div class="msg-error reveal reveal-delay-2"><?= $error ?></div>
      <?php endif; ?>

      <!-- Kontaktformular -->
      <form class="contact-form reveal reveal-delay-2" method="post" action="contact.php">
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
          <textarea id="cmessage" name="message" placeholder="Deine Nachricht…" required minlength="10"><?= htmlspecialchars((string)($_POST['message'] ?? '')) ?></textarea>
        </div>
        <!-- Honeypot: bleibt leer -->
        <div class="honeypot" aria-hidden="true">
          <label>Website</label>
          <input type="text" name="website" tabindex="-1" autocomplete="off">
        </div>
        <button type="submit" class="btn-cvs--gold">Nachricht senden</button>
      </form>
    <?php endif; ?>

    <div class="lightbar"></div>

    <!-- Direktkontakt -->
    <div class="direct-card reveal reveal-delay-3">
      <div class="direct-icon">&#9993;</div>
      <div class="direct-text">
        <div class="direct-label">Direkt per E-Mail</div>
        <div class="direct-value">info@cinematic-vision-studio.de</div>
        <div class="direct-hint">Nutze das Formular oben — oder schreib uns direkt an.</div>
      </div>
    </div>

    <a href="scene-editor-test.html" class="btn-cvs--ghost reveal reveal-delay-3">&larr; Zurück zum Hub</a>
  </div>
</div>

<!-- FOOTER -->
<footer>
  <div class="footer-grid">
    <div class="footer-brand">
      <h3>Cinematic Vision Studio</h3>
      <p>Deine Vision. Als cinematic KI-Erlebnis. Premium AI-Cinematic-Produktion für Creator und Marken.</p>
    </div>
    <div class="footer-col">
      <h4>Produkt</h4>
      <a href="scene-editor-test.html">Home</a>
      <a href="portfolio.html">Portfolio</a>
      <a href="shop.html">Shop</a>
      <a href="crystals.html">Kristalle</a>
    </div>
    <div class="footer-col">
      <h4>Ressourcen</h4>
      <a href="academy.html">Academy</a>
      <a href="prompt-generator.html">Prompts</a>
      <a href="contact.php">Kontakt</a>
    </div>
    <div class="footer-col">
      <h4>Rechtliches</h4>
      <a href="impressum.html">Impressum</a>
      <a href="datenschutz.html">Datenschutz</a>
      <a href="agb.html">AGB</a>
      <a href="cookies.html">Cookies</a>
    </div>
  </div>
  <div class="footer-bottom">
    <p>&copy; 2026 Cinematic Vision Studio &middot; Alle Rechte vorbehalten</p>
    <div class="footer-socials">
      <a href="#" class="social-btn" aria-label="Instagram">IG</a>
      <a href="#" class="social-btn" aria-label="YouTube">YT</a>
      <a href="#" class="social-btn" aria-label="TikTok">TT</a>
    </div>
  </div>
</footer>

<script>
(function(){
  "use strict";

  // ── Mobile nav ──
  var burger=document.getElementById('navBurger'),mnav=document.getElementById('mobileNav');
  if(burger&&mnav){
    burger.addEventListener('click',function(e){
      e.stopPropagation();
      var o=mnav.classList.toggle('open');
      burger.classList.toggle('open',o);
      burger.setAttribute('aria-expanded',o?'true':'false');
      burger.setAttribute('aria-label',o?'Menü schließen':'Menü öffnen');
      document.body.style.overflow=o?'hidden':'';
    });
    mnav.querySelectorAll('a').forEach(function(l){
      l.addEventListener('click',function(){
        mnav.classList.remove('open');burger.classList.remove('open');
        burger.setAttribute('aria-expanded','false');burger.setAttribute('aria-label','Menü öffnen');
        document.body.style.overflow='';
      });
    });
    document.addEventListener('click',function(e){
      if(!burger.contains(e.target)&&!mnav.contains(e.target)){
        mnav.classList.remove('open');burger.classList.remove('open');
        burger.setAttribute('aria-expanded','false');document.body.style.overflow='';
      }
    });
    document.addEventListener('keydown',function(e){
      if(e.key==='Escape'){
        mnav.classList.remove('open');burger.classList.remove('open');
        burger.setAttribute('aria-expanded','false');document.body.style.overflow='';
      }
    });
  }

  // ── Scroll progress ──
  var prog=document.getElementById('cvs-progress');
  if(prog){
    window.addEventListener('scroll',function(){
      var h=document.documentElement;
      var pct=h.scrollTop/(h.scrollHeight-h.clientHeight)*100;
      prog.style.width=pct+'%';
    });
  }

  // ── Nav scrolled ──
  var nav=document.querySelector('nav');
  if(nav){
    window.addEventListener('scroll',function(){
      nav.classList.toggle('scrolled',window.scrollY>20);
    });
  }

  // ── Reveal animations ──
  if('IntersectionObserver' in window){
    var ro=new IntersectionObserver(function(es){
      es.forEach(function(e){if(e.isIntersecting)e.target.classList.add('visible');});
    },{threshold:.1});
    document.querySelectorAll('.reveal').forEach(function(el){ro.observe(el);});
  }else{
    document.querySelectorAll('.reveal').forEach(function(el){el.classList.add('visible');});
  }
})();
</script>

</body>
</html>