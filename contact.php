<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Kontakt & Beta – Cinematic Vision Studio</title>
<style>
:root{--bg:#06060f;--s1:#0d0d1a;--s2:#12121f;--ac:#ec4899;--tx:#e2e8f0;--txm:#94a3b8;--br:#ffffff12}
*{box-sizing:border-box;margin:0;padding:0}
body{background:var(--bg);color:var(--tx);font-family:'Segoe UI',system-ui,sans-serif;min-height:100vh}
.top-nav{display:flex;align-items:center;gap:8px;padding:14px 28px;background:rgba(13,13,26,.95);backdrop-filter:blur(12px);border-bottom:1px solid var(--br);position:sticky;top:0;z-index:99;flex-wrap:wrap}
.nav-brand{font-weight:700;font-size:1.05rem;background:linear-gradient(135deg,#9333ea,#3b82f6);-webkit-background-clip:text;-webkit-text-fill-color:transparent;margin-right:8px;white-space:nowrap}
.nav-links{display:flex;gap:4px;flex-wrap:wrap;flex:1}
.nav-links a{color:var(--txm);text-decoration:none;font-size:.78rem;padding:5px 10px;border-radius:6px;transition:.2s;white-space:nowrap}
.nav-links a:hover,.nav-links a.active{color:var(--tx);background:var(--br)}
.page-wrap{max-width:720px;margin:0 auto;padding:60px 24px 80px;text-align:center}
.page-icon{font-size:3.5rem;margin-bottom:20px;filter:drop-shadow(0 0 24px rgba(236,72,153,.4))}
.page-badge{display:inline-flex;align-items:center;gap:6px;background:rgba(236,72,153,.1);border:1px solid rgba(236,72,153,.3);color:#f9a8d4;font-size:.72rem;font-weight:600;letter-spacing:.06em;text-transform:uppercase;padding:4px 12px;border-radius:20px;margin-bottom:18px}
h1{font-size:clamp(2rem,6vw,3rem);font-weight:800;margin-bottom:16px;background:linear-gradient(135deg,#f9a8d4,#c084fc);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.page-sub{color:var(--txm);font-size:1.05rem;line-height:1.7;max-width:480px;margin:0 auto 48px}
/* CONTACT CARDS */
.contact-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:36px}
.contact-card{background:var(--s1);border:1px solid var(--br);border-radius:14px;padding:24px;text-align:left;text-decoration:none;color:var(--tx);transition:.3s;display:block}
.contact-card:hover{border-color:rgba(236,72,153,.4);transform:translateY(-3px);box-shadow:0 8px 30px rgba(236,72,153,.1)}
.cc-icon{font-size:1.8rem;margin-bottom:10px}
.cc-label{font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txm);margin-bottom:4px}
.cc-value{font-size:.95rem;font-weight:600;color:#f9a8d4;word-break:break-all}
.cc-hint{font-size:.78rem;color:var(--txm);margin-top:4px}
/* BETA */
.beta-card{background:var(--s1);border:1px solid rgba(236,72,153,.25);border-radius:18px;padding:32px;margin-bottom:36px;text-align:left}
.beta-card h2{font-size:1.05rem;font-weight:700;color:#f9a8d4;margin-bottom:10px}
.beta-card p{color:var(--txm);font-size:.9rem;line-height:1.65;margin-bottom:16px}
.beta-perks{list-style:none;display:flex;flex-direction:column;gap:6px;margin-bottom:20px}
.beta-perks li{color:var(--txm);font-size:.88rem;display:flex;align-items:center;gap:8px}
.beta-perks li::before{content:'✦';color:#f9a8d4;font-size:.7rem}
.beta-btn{display:inline-flex;align-items:center;gap:8px;background:linear-gradient(135deg,#9d174d,#7c3aed);color:#fff;text-decoration:none;padding:12px 24px;border-radius:10px;font-size:.9rem;font-weight:700;transition:.2s}
.beta-btn:hover{opacity:.88;transform:translateY(-1px)}
.back-link{display:inline-flex;align-items:center;gap:8px;color:var(--txm);text-decoration:none;font-size:.88rem;padding:10px 20px;border:1px solid var(--br);border-radius:10px;transition:.2s}
.back-link:hover{color:var(--tx);border-color:rgba(236,72,153,.4);background:rgba(236,72,153,.06)}
@media(max-width:560px){.contact-grid{grid-template-columns:1fr}}
</style>
</head>
<body>
<nav class="top-nav">
  <span class="nav-brand">🎬 Cinematic Studio</span>
  <div class="nav-links">
    <a href="scene-editor-test.html">Hub</a>
    <a href="studio-demo.php">Demo</a>
    <a href="prompt-generator.php">Prompts</a>
    <a href="ki-videos.php">KI Videos</a>
    <a href="shop.php">Shop</a>
    <a href="portfolio.php">Portfolio</a>
    <a href="availability.php">Verfügbarkeit</a>
    <a href="crystals.php">Kristalle</a>
    <a href="contact.php" class="active">Kontakt</a>
  </div>
</nav>
<div class="page-wrap">
  <div class="page-icon">✉️</div>
  <div class="page-badge">Kontakt & Beta</div>
  <h1>Schreib uns<br>direkt an</h1>
  <p class="page-sub">Projektanfragen, Kooperationen, Beta-Zugang oder einfach Hallo – wir antworten schnell.</p>

  <div class="contact-grid">
    <a href="mailto:nuffysstyle@gmail.com?subject=Studio-Anfrage" class="contact-card">
      <div class="cc-icon">📧</div>
      <div class="cc-label">E-Mail</div>
      <div class="cc-value">nuffysstyle@gmail.com</div>
      <div class="cc-hint">Antwort in der Regel innerhalb 24h</div>
    </a>
    <a href="mailto:nuffysstyle@gmail.com?subject=Beta-Zugang Cinematic Studio" class="contact-card">
      <div class="cc-icon">🚀</div>
      <div class="cc-label">Beta-Zugang</div>
      <div class="cc-value">Early Access</div>
      <div class="cc-hint">Anfrage per E-Mail für Beta-Slot</div>
    </a>
  </div>

  <div class="beta-card">
    <h2>🔥 Beta-Programm – Jetzt bewerben</h2>
    <p>Werde einer der ersten Nutzer des Cinematic Vision Studios und erhalte exklusiven Zugang zu allen Features bevor sie öffentlich gehen.</p>
    <ul class="beta-perks">
      <li>Kostenlose Kristalle zum Start</li>
      <li>Direkter Kanal zum Entwicklerteam</li>
      <li>Feature-Requests mit hoher Priorität</li>
      <li>Früher Zugang zu KI Video, Shop & Portfolio</li>
    </ul>
    <a href="mailto:nuffysstyle@gmail.com?subject=Beta-Bewerbung Cinematic Vision Studio&body=Hallo,%0A%0Aich möchte mich für das Beta-Programm des Cinematic Vision Studios bewerben.%0A%0AMein Use-Case:%0A" class="beta-btn">
      🎬 Für Beta bewerben
    </a>
  </div>

  <a href="scene-editor-test.html" class="back-link">← Zurück zum Hub</a>
</div>
</body>
</html>
