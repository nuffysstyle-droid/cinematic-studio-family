<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Kristalle & Pakete – Cinematic Vision Studio</title>
<style>
:root{--bg:#06060f;--s1:#0d0d1a;--s2:#12121f;--ac:#a855f7;--tx:#e2e8f0;--txm:#94a3b8;--br:#ffffff12}
*{box-sizing:border-box;margin:0;padding:0}
body{background:var(--bg);color:var(--tx);font-family:'Segoe UI',system-ui,sans-serif;min-height:100vh}
.top-nav{display:flex;align-items:center;gap:8px;padding:14px 28px;background:rgba(13,13,26,.95);backdrop-filter:blur(12px);border-bottom:1px solid var(--br);position:sticky;top:0;z-index:99;flex-wrap:wrap}
.nav-brand{font-weight:700;font-size:1.05rem;background:linear-gradient(135deg,#9333ea,#3b82f6);-webkit-background-clip:text;-webkit-text-fill-color:transparent;margin-right:8px;white-space:nowrap}
.nav-links{display:flex;gap:4px;flex-wrap:wrap;flex:1}
.nav-links a{color:var(--txm);text-decoration:none;font-size:.78rem;padding:5px 10px;border-radius:6px;transition:.2s;white-space:nowrap}
.nav-links a:hover,.nav-links a.active{color:var(--tx);background:var(--br)}
.page-wrap{max-width:900px;margin:0 auto;padding:60px 24px 80px;text-align:center}
.page-icon{font-size:3.5rem;margin-bottom:20px;filter:drop-shadow(0 0 32px rgba(168,85,247,.5));animation:gfloat 3s ease-in-out infinite}
@keyframes gfloat{0%,100%{transform:translateY(0)}50%{transform:translateY(-8px)}}
.page-badge{display:inline-flex;align-items:center;gap:6px;background:rgba(168,85,247,.1);border:1px solid rgba(168,85,247,.3);color:#d8b4fe;font-size:.72rem;font-weight:600;letter-spacing:.06em;text-transform:uppercase;padding:4px 12px;border-radius:20px;margin-bottom:18px}
h1{font-size:clamp(2rem,6vw,3rem);font-weight:800;margin-bottom:16px;background:linear-gradient(135deg,#d8b4fe,#818cf8,#60a5fa);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.page-sub{color:var(--txm);font-size:1.05rem;line-height:1.7;max-width:540px;margin:0 auto 48px}
/* CRYSTAL PACKS */
.packs-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:40px}
.pack{background:var(--s1);border:1px solid var(--br);border-radius:16px;padding:28px 20px;transition:.3s;cursor:default}
.pack:hover{border-color:rgba(168,85,247,.4);transform:translateY(-4px);box-shadow:0 12px 40px rgba(168,85,247,.12)}
.pack.featured{border-color:rgba(168,85,247,.5);background:linear-gradient(145deg,rgba(168,85,247,.08),var(--s1))}
.pack-gem{font-size:2rem;margin-bottom:12px}
.pack-name{font-weight:700;font-size:.95rem;margin-bottom:4px}
.pack-crystals{font-size:1.6rem;font-weight:800;color:#d8b4fe;margin-bottom:8px}
.pack-price{color:var(--txm);font-size:.85rem;margin-bottom:14px}
.pack-price strong{color:var(--tx);font-size:1.1rem}
.pack-tag{display:inline-block;background:rgba(168,85,247,.15);color:#d8b4fe;font-size:.7rem;font-weight:700;padding:3px 10px;border-radius:20px;text-transform:uppercase;letter-spacing:.05em}
.pack-btn{margin-top:16px;width:100%;padding:10px;border-radius:8px;background:rgba(168,85,247,.15);border:1px solid rgba(168,85,247,.3);color:#d8b4fe;font-size:.85rem;font-weight:600;cursor:not-allowed;opacity:.7}
.info-note{background:var(--s1);border:1px solid var(--br);border-radius:14px;padding:24px;max-width:500px;margin:0 auto 36px;color:var(--txm);font-size:.88rem;line-height:1.6}
.info-note strong{color:var(--tx)}
.back-link{display:inline-flex;align-items:center;gap:8px;color:var(--txm);text-decoration:none;font-size:.88rem;padding:10px 20px;border:1px solid var(--br);border-radius:10px;transition:.2s}
.back-link:hover{color:var(--tx);border-color:rgba(168,85,247,.4);background:rgba(168,85,247,.06)}
@media(max-width:640px){.packs-grid{grid-template-columns:1fr}}
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
    <a href="crystals.php" class="active">Kristalle</a>
    <a href="contact.php">Kontakt</a>
  </div>
</nav>
<div class="page-wrap">
  <div class="page-icon">💎</div>
  <div class="page-badge">Kristalle & Pakete</div>
  <h1>Kristalle –<br>Deine Studio-Währung</h1>
  <p class="page-sub">Kristalle sind die interne Währung des Cinematic Vision Studios. Kaufe Pakete und setze sie für KI-Videos, Prompts, Sticker und mehr ein.</p>

  <div class="packs-grid">
    <div class="pack">
      <div class="pack-gem">💎</div>
      <div class="pack-name">Starter</div>
      <div class="pack-crystals">100 K</div>
      <div class="pack-price"><strong>5 €</strong></div>
      <div class="pack-tag">Einstieg</div>
      <button class="pack-btn">Bald kaufbar</button>
    </div>
    <div class="pack featured">
      <div class="pack-gem">💎💎</div>
      <div class="pack-name">Creator</div>
      <div class="pack-crystals">500 K</div>
      <div class="pack-price"><strong>19 €</strong> <span>statt 25 €</span></div>
      <div class="pack-tag">⭐ Beliebt</div>
      <button class="pack-btn">Bald kaufbar</button>
    </div>
    <div class="pack">
      <div class="pack-gem">💎💎💎</div>
      <div class="pack-name">Pro Studio</div>
      <div class="pack-crystals">1500 K</div>
      <div class="pack-price"><strong>49 €</strong> <span>statt 75 €</span></div>
      <div class="pack-tag">Profi</div>
      <button class="pack-btn">Bald kaufbar</button>
    </div>
  </div>

  <div class="info-note">
    <strong>💡 Demo-Version:</strong> Das Kristalle-System befindet sich im Aufbau. Der Kauf wird bald per Stripe & PayPal aktiviert. Für Early-Access-Anfragen: <a href="contact.php" style="color:#d8b4fe;text-decoration:none">Kontakt aufnehmen</a>.
  </div>

  <a href="scene-editor-test.html" class="back-link">← Zurück zum Hub</a>
</div>
</body>
</html>
