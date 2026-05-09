<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Portfolio – Cinematic Vision Studio</title>
<style>
:root{--bg:#06060f;--s1:#0d0d1a;--ac:#06b6d4;--tx:#e2e8f0;--txm:#94a3b8;--br:#ffffff12}
*{box-sizing:border-box;margin:0;padding:0}
body{background:var(--bg);color:var(--tx);font-family:'Segoe UI',system-ui,sans-serif;min-height:100vh}
.top-nav{display:flex;align-items:center;gap:8px;padding:14px 28px;background:rgba(13,13,26,.95);backdrop-filter:blur(12px);border-bottom:1px solid var(--br);position:sticky;top:0;z-index:99;flex-wrap:wrap}
.nav-brand{font-weight:700;font-size:1.05rem;background:linear-gradient(135deg,#9333ea,#3b82f6);-webkit-background-clip:text;-webkit-text-fill-color:transparent;margin-right:8px;white-space:nowrap}
.nav-links{display:flex;gap:4px;flex-wrap:wrap;flex:1}
.nav-links a{color:var(--txm);text-decoration:none;font-size:.78rem;padding:5px 10px;border-radius:6px;transition:.2s;white-space:nowrap}
.nav-links a:hover,.nav-links a.active{color:var(--tx);background:var(--br)}
.page-wrap{max-width:800px;margin:0 auto;padding:72px 24px 80px;text-align:center}
.page-icon{font-size:3.5rem;margin-bottom:20px;filter:drop-shadow(0 0 24px rgba(6,182,212,.4))}
.page-badge{display:inline-flex;align-items:center;gap:6px;background:rgba(6,182,212,.1);border:1px solid rgba(6,182,212,.3);color:#67e8f9;font-size:.72rem;font-weight:600;letter-spacing:.06em;text-transform:uppercase;padding:4px 12px;border-radius:20px;margin-bottom:18px}
h1{font-size:clamp(2rem,6vw,3rem);font-weight:800;margin-bottom:16px;background:linear-gradient(135deg,#67e8f9,#818cf8);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.page-sub{color:var(--txm);font-size:1.05rem;line-height:1.7;max-width:520px;margin:0 auto 48px}
.coming-card{background:var(--s1);border:1px solid rgba(6,182,212,.2);border-radius:20px;padding:40px;max-width:480px;margin:0 auto 40px}
.coming-card h2{font-size:1.1rem;font-weight:700;margin-bottom:12px;color:#67e8f9}
.coming-card p{color:var(--txm);font-size:.92rem;line-height:1.6}
.back-link{display:inline-flex;align-items:center;gap:8px;color:var(--txm);text-decoration:none;font-size:.88rem;padding:10px 20px;border:1px solid var(--br);border-radius:10px;transition:.2s}
.back-link:hover{color:var(--tx);border-color:rgba(6,182,212,.4);background:rgba(6,182,212,.06)}
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
    <a href="portfolio.php" class="active">Portfolio</a>
    <a href="availability.php">Verfügbarkeit</a>
    <a href="crystals.php">Kristalle</a>
    <a href="contact.php">Kontakt</a>
  </div>
</nav>
<div class="page-wrap">
  <div class="page-icon">🎞️</div>
  <div class="page-badge">Portfolio</div>
  <h1>Unsere<br>Arbeiten</h1>
  <p class="page-sub">Abgeschlossene Projekte, Kundenprojekte und interne Produktionen – von TikTok-Clips bis zu Kurzfilmen.</p>
  <div class="coming-card">
    <h2>📽️ Portfolio wird befüllt</h2>
    <p>Hier erscheinen bald Videogalerien mit Kategoriefilter: KI Videos, TikTok, Sticker, Kurzfilme und mehr. Demo-Version in Vorbereitung.</p>
  </div>
  <a href="scene-editor-test.html" class="back-link">← Zurück zum Hub</a>
</div>
</body>
</html>
