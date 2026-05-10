<!doctype html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Kontakt & Beta – Cinematic Vision Studio</title>
<style>
:root{--bg:#06060f;--card:#0f0f1e;--card2:#161628;--text:#f0f0ff;--muted:#8888aa;--muted2:#5a5a7a;--accent:#f5c542;--accent2:#ff8c00;--blue:#3b82f6;--purple:#9333ea;--ok:#4ade80;--danger:#ff5c7a;--border:rgba(255,255,255,.09);--gold:linear-gradient(135deg,#f5c542,#ff8c00);--radius:18px;}
*{box-sizing:border-box;margin:0;padding:0}html{scroll-behavior:smooth}
body{font-family:'Segoe UI',Arial,sans-serif;background:var(--bg);color:var(--text);min-height:100vh}
.nav{position:fixed;top:0;left:0;right:0;z-index:100;display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px 28px;background:rgba(6,6,15,.88);backdrop-filter:blur(20px);border-bottom:1px solid var(--border);}
.nav-logo{font-size:13px;font-weight:900;letter-spacing:2px;background:var(--gold);-webkit-background-clip:text;-webkit-text-fill-color:transparent;text-transform:uppercase;flex-shrink:0;text-decoration:none;}
.nav-logo span{font-size:10px;display:block;letter-spacing:4px;font-weight:400;opacity:.6;margin-top:1px;}
.nav-links{display:flex;align-items:center;gap:2px;flex:1;justify-content:center;}
.nav-link{color:var(--muted);text-decoration:none;font-size:13px;font-weight:600;padding:7px 10px;border-radius:9px;transition:color .15s,background .15s;white-space:nowrap;}
.nav-link:hover,.nav-link.active{color:var(--text);background:rgba(255,255,255,.06);}
.nav-actions{display:flex;align-items:center;gap:8px;flex-shrink:0;}
.nav-btn-ghost{color:var(--muted);text-decoration:none;font-size:13px;font-weight:700;padding:8px 13px;border-radius:10px;border:1px solid var(--border);transition:color .15s,border-color .15s;}
.nav-btn-ghost:hover{color:var(--text);border-color:rgba(255,255,255,.25);}
.nav-btn-gold{background:var(--gold);color:#1a0e00;text-decoration:none;font-size:13px;font-weight:800;padding:9px 16px;border-radius:10px;transition:opacity .15s;}
.nav-btn-gold:hover{opacity:.88;}
.wallet-pill{display:flex;align-items:center;gap:6px;background:rgba(245,197,66,.1);border:1px solid rgba(245,197,66,.3);border-radius:999px;padding:7px 14px;font-size:13px;font-weight:800;color:var(--accent);cursor:pointer;text-decoration:none;}
@media(max-width:900px){.nav-links{display:none;}}
@media(max-width:640px){.nav{padding:12px 16px;gap:8px;}.nav-logo{font-size:11px;letter-spacing:1.5px;min-width:0;flex-shrink:1;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;}.nav-logo span{display:none;}.nav-btn-ghost{display:none;}.nav-btn-gold{display:none;}.wallet-pill{display:none;}}
.mob-burger{display:none;flex-direction:column;justify-content:center;gap:5px;width:40px;height:40px;padding:8px;background:rgba(255,255,255,.06);border:1px solid var(--border);border-radius:10px;cursor:pointer;flex-shrink:0;}
.mob-burger span{display:block;height:2px;border-radius:2px;background:var(--text);transition:transform .22s,opacity .22s;}
.mob-burger.open span:nth-child(1){transform:translateY(7px) rotate(45deg);}
.mob-burger.open span:nth-child(2){opacity:0;}
.mob-burger.open span:nth-child(3){transform:translateY(-7px) rotate(-45deg);}
.mob-menu{position:fixed;left:0;right:0;z-index:99;background:rgba(6,6,15,.97);backdrop-filter:blur(24px);border-bottom:1px solid var(--border);box-shadow:0 24px 60px rgba(0,0,0,.6),0 0 50px rgba(59,130,246,.07);max-height:0;overflow:hidden;opacity:0;pointer-events:none;transition:max-height .3s cubic-bezier(.4,0,.2,1),opacity .22s;display:none;}
.mob-menu.open{max-height:560px;opacity:1;pointer-events:auto;}
.mob-link{display:block;padding:14px 24px;color:var(--muted);text-decoration:none;font-size:15px;font-weight:700;border-left:3px solid transparent;transition:color .15s,border-color .15s,background .15s;}
.mob-link:active{background:rgba(255,255,255,.06);}
.mob-link.active{color:var(--accent);border-left-color:var(--accent);}
.mob-sep{height:1px;background:var(--border);margin:10px 20px;}
.mob-cta{display:block;margin:12px 20px 8px;padding:15px;background:var(--gold);color:#1a0e00;text-decoration:none;font-size:15px;font-weight:900;border-radius:13px;text-align:center;}
@media(max-width:900px){.mob-burger{display:flex;}.mob-menu{display:block;}}
.page{padding:90px 0 60px;position:relative;}.page::before{content:"";position:absolute;top:0;left:0;right:0;height:380px;background:radial-gradient(ellipse 70% 120% at 15% 0%,rgba(59,130,246,.07) 0%,transparent 60%),radial-gradient(ellipse 50% 80% at 85% 20%,rgba(147,51,234,.05) 0%,transparent 55%);pointer-events:none;z-index:0;}.wrap{width:min(760px,calc(100% - 48px));margin:0 auto;position:relative;z-index:1;}
.page-eyebrow{display:inline-flex;align-items:center;gap:8px;font-size:11px;font-weight:800;letter-spacing:3px;text-transform:uppercase;color:var(--accent);background:rgba(245,197,66,.1);border:1px solid rgba(245,197,66,.28);border-radius:999px;padding:6px 14px;margin-bottom:18px;}
.page-h1{font-size:clamp(28px,5vw,48px);font-weight:900;letter-spacing:-1.5px;margin-bottom:10px;line-height:1.1;}
.page-h1 span{background:var(--gold);-webkit-background-clip:text;-webkit-text-fill-color:transparent;}
.page-sub{color:var(--muted);font-size:15px;line-height:1.6;max-width:500px;margin-bottom:44px;}
/* Contact cards */
.contact-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:24px;}
.contact-card{background:var(--card);border:1px solid var(--border);border-radius:var(--radius);padding:24px;text-decoration:none;color:var(--text);transition:transform .2s,border-color .2s,box-shadow .2s;display:block;}
.contact-card:hover{transform:translateY(-3px);border-color:rgba(245,197,66,.3);box-shadow:0 12px 36px rgba(0,0,0,.4),0 0 30px rgba(245,197,66,.07);}
.cc-icon{font-size:1.8rem;margin-bottom:12px;display:block;}
.cc-label{font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:var(--muted2);margin-bottom:5px;}
.cc-value{font-size:14px;font-weight:700;color:var(--accent);word-break:break-all;}
.cc-hint{font-size:12px;color:var(--muted);margin-top:4px;}
/* Beta card */
.beta-card{background:linear-gradient(145deg,rgba(245,197,66,.06),rgba(255,140,0,.03));border:1px solid rgba(245,197,66,.28);border-radius:var(--radius);padding:30px;margin-bottom:28px;}
.beta-card h2{font-size:16px;font-weight:900;margin-bottom:10px;}
.beta-card p{color:var(--muted);font-size:13px;line-height:1.6;margin-bottom:16px;}
.beta-perks{list-style:none;display:flex;flex-direction:column;gap:7px;margin-bottom:22px;}
.beta-perks li{color:var(--muted);font-size:13px;display:flex;align-items:center;gap:9px;}
.beta-perks li::before{content:"✦";color:var(--accent);font-size:10px;}
.btn-gold{background:var(--gold);color:#1a0e00;border:0;border-radius:12px;padding:13px 22px;font-size:14px;font-weight:900;cursor:pointer;transition:transform .15s,box-shadow .15s;text-decoration:none;display:inline-flex;align-items:center;gap:8px;box-shadow:0 6px 22px rgba(245,197,66,.22);}
.btn-gold:hover{transform:translateY(-2px);box-shadow:0 12px 30px rgba(245,197,66,.36);}
.btn-ghost{background:transparent;color:var(--text);border:1px solid var(--border);border-radius:12px;padding:13px 22px;font-size:14px;font-weight:700;cursor:pointer;transition:border-color .15s,background .15s;text-decoration:none;display:inline-flex;align-items:center;gap:8px;}
.btn-ghost:hover{border-color:rgba(255,255,255,.28);background:rgba(255,255,255,.05);}
.footer{border-top:1px solid var(--border);padding:36px 0;margin-top:60px;}
.footer-inner{width:min(960px,calc(100% - 48px));margin:0 auto;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;}
.footer-logo{font-size:13px;font-weight:900;letter-spacing:2px;background:var(--gold);-webkit-background-clip:text;-webkit-text-fill-color:transparent;}
.footer-nav{display:flex;gap:18px;flex-wrap:wrap;}
.footer-nav a{color:var(--muted);text-decoration:none;font-size:12px;font-weight:600;transition:color .15s;}
.footer-nav a:hover{color:var(--text);}
.footer-copy{font-size:12px;color:var(--muted2);}
@media(max-width:560px){.contact-grid{grid-template-columns:1fr;}}
</style>
</head>
<body>
<nav class="nav">
  <a href="scene-editor-test.html" class="nav-logo">Cinematic Vision Studio<span>Premium KI-Filmstudio</span></a>
  <div class="nav-links">
    <a href="scene-editor-test.html" class="nav-link">Home</a>
    <a href="studio-demo.php" class="nav-link">Studio</a>
    <a href="prompt-generator.php" class="nav-link">Prompts</a>
    <a href="shop.php" class="nav-link">Shop Beta</a>
    <a href="portfolio.php" class="nav-link">Portfolio</a>
    <a href="availability.php" class="nav-link">Verfügbarkeit</a>
    <a href="academy.php" class="nav-link">Academy</a>
    <a href="crystals.php" class="nav-link">Kristalle</a>
    <a href="contact.php" class="nav-link active">Kontakt</a>
  </div>
  <div class="nav-actions">
    <a href="contact.php" class="nav-btn-ghost">Beta-Zugang</a>
    <a href="studio-demo.php" class="nav-btn-gold">Studio starten</a>
    <a href="crystals.php" class="wallet-pill">💎 500</a>
  </div>
  <button class="mob-burger" id="mobBurger" aria-label="Menü öffnen" aria-expanded="false"><span></span><span></span><span></span></button>
</nav>
<div class="mob-menu" id="mobMenu">
  <a href="scene-editor-test.html" class="mob-link">🏠 Home</a>
  <a href="studio-demo.php" class="mob-link">🎬 Studio</a>
  <a href="prompt-generator.php" class="mob-link">✍️ Prompts</a>
  <a href="shop.php" class="mob-link">🛍️ Shop Beta</a>
  <a href="portfolio.php" class="mob-link">🎞️ Portfolio</a>
  <a href="availability.php" class="mob-link">📅 Verfügbarkeit</a>
  <a href="academy.php" class="mob-link">🎓 Academy</a>
  <a href="crystals.php" class="mob-link">💎 Kristalle</a>
  <a href="contact.php" class="mob-link active">✉️ Kontakt</a>
  <div class="mob-sep"></div>
  <a href="studio-demo.php" class="mob-cta">🎬 Studio starten</a>
</div>
<div class="page">
  <div class="wrap">
    <div class="page-eyebrow">✦ Beta · Limitierte Plätze</div>
    <h1 class="page-h1">Werde Teil der<br><span>ersten Runde.</span></h1>
    <p class="page-sub">Sichere dir einen der limitierten Beta-Plätze für Cinematic Vision Studio — oder schick uns deine Projektanfrage direkt.</p>

    <div class="contact-grid">
      <a href="mailto:nuffysstyle@gmail.com?subject=Studio-Anfrage" class="contact-card">
        <span class="cc-icon">📧</span>
        <div class="cc-label">E-Mail</div>
        <div class="cc-value">nuffysstyle@gmail.com</div>
        <div class="cc-hint">Antwort in der Regel innerhalb 24h</div>
      </a>
      <a href="mailto:nuffysstyle@gmail.com?subject=Beta-Zugang Cinematic Studio" class="contact-card">
        <span class="cc-icon">🚀</span>
        <div class="cc-label">Beta-Zugang</div>
        <div class="cc-value">Beta-Slot sichern →</div>
        <div class="cc-hint">Limitierte Plätze · Per E-Mail anfragen</div>
      </a>
    </div>

    <div class="beta-card">
      <h2>✦ Beta-Programm · Limitierte Plätze</h2>
      <p>Werde einer der ersten Nutzer des Cinematic Vision Studios und erhalte exklusiven Zugang zu allen Features.</p>
      <ul class="beta-perks">
        <li>Kostenlose Kristalle zum Start</li>
        <li>Direkter Kanal zum Entwicklerteam</li>
        <li>Feature-Requests mit hoher Priorität</li>
        <li>Früher Zugang zu KI Video, Shop &amp; Portfolio</li>
      </ul>
      <a href="mailto:nuffysstyle@gmail.com?subject=Beta-Bewerbung Cinematic Vision Studio&body=Hallo,%0A%0Aich möchte mich für das Beta-Programm bewerben.%0A%0AMein Use-Case:%0A" class="btn-gold">🎬 Für Beta bewerben</a>
    </div>

  </div>
</div>
<footer class="footer">
  <div class="footer-inner">
    <div class="footer-logo">Cinematic Vision Studio</div>
    <nav class="footer-nav">
      <a href="studio-demo.php">Studio</a>
      <a href="shop.php">Shop</a>
      <a href="portfolio.php">Portfolio</a>
      <a href="crystals.php">Kristalle</a>
      <a href="contact.php">Kontakt</a>
    </nav>
    <div class="footer-copy">© 2025 · Premium KI-Filmproduktion</div>
  </div>
</footer>
<script>(function(){var b=document.getElementById('mobBurger'),m=document.getElementById('mobMenu');if(!b||!m)return;function t(){m.style.top=document.querySelector('.nav').offsetHeight+'px';}t();window.addEventListener('resize',t);b.addEventListener('click',function(e){e.stopPropagation();var o=m.classList.toggle('open');b.classList.toggle('open',o);b.setAttribute('aria-expanded',o?'true':'false');b.setAttribute('aria-label',o?'Menü schließen':'Menü öffnen');});m.querySelectorAll('.mob-link,.mob-cta').forEach(function(l){l.addEventListener('click',function(){m.classList.remove('open');b.classList.remove('open');b.setAttribute('aria-expanded','false');b.setAttribute('aria-label','Menü öffnen');});});document.addEventListener('click',function(e){if(!b.contains(e.target)&&!m.contains(e.target)){m.classList.remove('open');b.classList.remove('open');b.setAttribute('aria-expanded','false');}});document.addEventListener('keydown',function(e){if(e.key==='Escape'){m.classList.remove('open');b.classList.remove('open');b.setAttribute('aria-expanded','false');}});})();</script>
</body>
</html>
