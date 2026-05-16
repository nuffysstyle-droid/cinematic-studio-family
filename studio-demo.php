<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
$authUser = csf_auth_user();
?>
<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <title>Studio Demo – Cinematic Vision Studio</title>
  <style>
    :root{--bg:#06060f;--card:#0f0f1e;--card2:#161628;--text:#f0f0ff;--muted:#8888aa;--muted2:#5a5a7a;--accent:#f5c542;--accent2:#ff8c00;--blue:#3b82f6;--purple:#9333ea;--ok:#4ade80;--danger:#ff5c7a;--border:rgba(255,255,255,.09);--gold:linear-gradient(135deg,#f5c542,#ff8c00);--radius:18px;}
    *{box-sizing:border-box;margin:0;padding:0;}
    html{scroll-behavior:smooth;}
    body{font-family:'Segoe UI',Arial,sans-serif;background:var(--bg);color:var(--text);min-height:100vh;}
    /* Nav */
    .nav{position:fixed;top:0;left:0;right:0;z-index:100;display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px 28px;background:rgba(6,6,15,.88);backdrop-filter:blur(20px);border-bottom:1px solid var(--border);}
    .nav-logo{font-size:13px;font-weight:900;letter-spacing:2px;background:var(--gold);-webkit-background-clip:text;-webkit-text-fill-color:transparent;text-transform:uppercase;flex-shrink:0;text-decoration:none;}
    .nav-logo span{font-size:10px;display:block;letter-spacing:4px;font-weight:400;opacity:.6;margin-top:1px;}
    .nav-links{display:flex;align-items:center;gap:2px;flex:1;justify-content:center;}
    .nav-link{color:var(--muted);text-decoration:none;font-size:13px;font-weight:600;padding:7px 10px;border-radius:9px;transition:color .15s,background .15s;}
    .nav-link:hover,.nav-link.active{color:var(--text);background:rgba(255,255,255,.06);}
    .nav-actions{display:flex;align-items:center;gap:8px;flex-shrink:0;}
    .nav-btn-ghost{color:var(--muted);text-decoration:none;font-size:13px;font-weight:700;padding:8px 13px;border-radius:10px;border:1px solid var(--border);transition:color .15s,border-color .15s;}
    .nav-btn-ghost:hover{color:var(--text);border-color:rgba(255,255,255,.25);}
    .nav-btn-gold{background:var(--gold);color:#1a0e00;text-decoration:none;font-size:13px;font-weight:800;padding:9px 16px;border-radius:10px;}
    .wallet-pill{display:flex;align-items:center;gap:6px;background:rgba(245,197,66,.1);border:1px solid rgba(245,197,66,.3);border-radius:999px;padding:7px 14px;font-size:13px;font-weight:800;color:var(--accent);cursor:pointer;text-decoration:none;}
    @media(max-width:900px){.nav-links{display:none;}}
    @media(max-width:640px){.nav{padding:12px 16px;gap:8px;}.nav-logo span{display:none;}.nav-btn-ghost{display:none;}.nav-btn-gold{display:none;}.wallet-pill{display:none;}}
    .mob-burger{display:none;flex-direction:column;justify-content:center;gap:5px;width:40px;height:40px;padding:8px;background:rgba(255,255,255,.06);border:1px solid var(--border);border-radius:10px;cursor:pointer;flex-shrink:0;}
    .mob-burger span{display:block;height:2px;border-radius:2px;background:var(--text);transition:transform .22s,opacity .22s;}
    .mob-burger.open span:nth-child(1){transform:translateY(7px) rotate(45deg);}
    .mob-burger.open span:nth-child(2){opacity:0;}
    .mob-burger.open span:nth-child(3){transform:translateY(-7px) rotate(-45deg);}
    .mob-menu{position:fixed;left:0;right:0;z-index:99;background:rgba(6,6,15,.97);backdrop-filter:blur(24px);border-bottom:1px solid var(--border);max-height:0;overflow:hidden;opacity:0;pointer-events:none;transition:max-height .3s cubic-bezier(.4,0,.2,1),opacity .22s;display:none;}
    .mob-menu.open{max-height:560px;opacity:1;pointer-events:auto;}
    .mob-link{display:block;padding:14px 24px;color:var(--muted);text-decoration:none;font-size:15px;font-weight:700;border-left:3px solid transparent;transition:color .15s,border-color .15s,background .15s;}
    .mob-link.active{color:var(--accent);border-left-color:var(--accent);}
    .mob-sep{height:1px;background:var(--border);margin:10px 20px;}
    .mob-cta{display:block;margin:12px 20px 8px;padding:15px;background:var(--gold);color:#1a0e00;text-decoration:none;font-size:15px;font-weight:900;border-radius:13px;text-align:center;}
    @media(max-width:900px){.mob-burger{display:flex;}.mob-menu{display:block;}}
    /* Page */
    .page{padding:100px 0 60px;}
    .wrap{width:min(1160px,calc(100% - 48px));margin:0 auto;}
    /* Demo header */
    .demo-header{margin-bottom:32px;}
    .demo-eyebrow{font-size:11px;font-weight:800;letter-spacing:3px;text-transform:uppercase;color:var(--blue);background:rgba(59,130,246,.1);border:1px solid rgba(59,130,246,.25);border-radius:999px;padding:6px 14px;display:inline-flex;align-items:center;gap:8px;margin-bottom:14px;}
    .demo-eyebrow::before{content:"";width:7px;height:7px;border-radius:50%;background:var(--ok);box-shadow:0 0 8px var(--ok);animation:rblink 2s ease-in-out infinite;}
    @keyframes rblink{0%,100%{opacity:1}50%{opacity:.2}}
    .demo-header h1{font-size:clamp(28px,4vw,48px);font-weight:900;letter-spacing:-1.5px;margin-bottom:8px;}
    .demo-header h1 span{background:linear-gradient(135deg,#3b82f6,#9333ea);-webkit-background-clip:text;-webkit-text-fill-color:transparent;}
    .demo-header p{color:var(--muted);font-size:15px;line-height:1.6;max-width:600px;}
    /* Demo module */
    .demo-module{background:linear-gradient(145deg,rgba(59,130,246,.05),rgba(147,51,234,.03));border:1px solid rgba(59,130,246,.18);border-radius:22px;padding:32px;}
    .demo-note{margin-top:20px;padding:12px 16px;background:rgba(245,197,66,.05);border:1px solid rgba(245,197,66,.15);border-radius:12px;color:var(--muted);font-size:13px;line-height:1.5;}
    /* Panel, row, meta */
    .panel{margin-top:0;background:rgba(0,0,0,.28);border:1px solid var(--border);border-radius:16px;padding:18px;}
    .row{display:flex;flex-wrap:wrap;gap:12px;align-items:center;}
    input[type=file]{flex:1;min-width:180px;padding:13px;border-radius:12px;border:1px solid var(--border);background:rgba(255,255,255,.04);color:var(--text);font-size:13px;}
    button{border:0;border-radius:12px;padding:13px 18px;font-weight:800;cursor:pointer;background:var(--accent);color:#171000;transition:transform .15s,opacity .15s,box-shadow .15s;font-size:13px;}
    button:hover{transform:translateY(-1px);box-shadow:0 6px 18px rgba(245,197,66,.28);}
    button:disabled{opacity:.45;cursor:not-allowed;transform:none;box-shadow:none;}
    .status{margin-top:12px;color:var(--muted);white-space:pre-wrap;font-size:13px;}
    .status.ok{color:var(--ok);}
    .status.err{color:var(--danger);}
    .meta{display:none;margin-top:18px;grid-template-columns:repeat(4,1fr);gap:10px;}
    .meta div{background:rgba(255,255,255,.04);border:1px solid var(--border);border-radius:12px;padding:13px;}
    .meta strong{display:block;font-size:11px;color:var(--muted);margin-bottom:4px;letter-spacing:.4px;}
    .slots{margin-top:22px;display:grid;grid-template-columns:repeat(5,1fr);gap:12px;}
    .slot{background:linear-gradient(160deg,var(--card),var(--card2));border:1px solid var(--border);border-radius:16px;overflow:hidden;box-shadow:0 8px 22px rgba(0,0,0,.22);transition:border-color .2s,box-shadow .2s;}
    .slot.is-replaced{border-color:var(--ok);box-shadow:0 0 0 1px var(--ok),0 8px 22px rgba(0,0,0,.22);}
    .thumb{position:relative;aspect-ratio:9/16;background:#030308;display:flex;align-items:center;justify-content:center;color:var(--muted);font-size:12px;overflow:hidden;}
    .thumb img{width:100%;height:100%;object-fit:cover;display:block;}
    .badge{display:none;position:absolute;top:8px;left:8px;font-size:10px;font-weight:800;color:#0a2b14;background:var(--ok);padding:3px 7px;border-radius:999px;box-shadow:0 4px 10px rgba(0,0,0,.4);}
    .slot.is-replaced .badge{display:inline-block;}
    .slot-body{padding:11px;}
    .slot-title{font-weight:800;margin-bottom:5px;font-size:13px;}
    .time{color:var(--muted);font-size:12px;margin-bottom:9px;}
    .field{width:100%;margin-top:7px;padding:9px 11px;border-radius:9px;border:1px solid var(--border);background:rgba(0,0,0,.28);color:var(--text);outline:none;font-family:inherit;font-size:12px;}
    .replace{margin-top:7px;width:100%;font-size:12px;color:var(--muted);}
    .save-btn{margin-top:8px;width:100%;min-height:38px;padding:9px 12px;font-size:12px;font-weight:800;border-radius:9px;background:var(--accent);color:#171000;border:0;cursor:pointer;transition:transform .15s,opacity .15s;}
    .save-btn:hover:not(:disabled){transform:translateY(-1px);}
    .save-btn:disabled{opacity:.45;cursor:not-allowed;transform:none;}
    .slot-status{margin-top:7px;font-size:11px;color:var(--muted);min-height:1.2em;word-break:break-word;}
    .slot-status.ok{color:var(--ok);}
    .slot-status.err{color:var(--danger);}
    .note{margin-top:18px;padding:12px 14px;background:rgba(245,197,66,.05);border:1px solid rgba(245,197,66,.14);border-radius:11px;color:var(--muted);font-size:12px;line-height:1.5;}
    .reset-btn{background:transparent;color:var(--muted);border:1px solid var(--border);padding:11px 14px;border-radius:11px;font-weight:700;cursor:pointer;min-height:42px;font-size:12px;}
    .reset-btn:hover{color:var(--text);border-color:rgba(255,255,255,.28);}
    .restored-hint{margin-top:11px;padding:11px 13px;background:rgba(74,222,128,.06);border:1px solid rgba(74,222,128,.22);border-radius:11px;color:var(--ok);font-size:12px;line-height:1.4;}
    .final-section{margin-top:26px;padding:22px;background:rgba(245,197,66,.04);border:1px solid rgba(245,197,66,.18);border-radius:14px;}
    .final-section h2{font-size:19px;font-weight:900;margin-bottom:7px;}
    .final-section p{color:var(--muted);font-size:12px;line-height:1.5;margin-bottom:14px;}
    .render-btn{background:var(--accent);color:#171000;border:0;border-radius:12px;padding:13px 22px;font-size:14px;font-weight:800;cursor:pointer;min-height:46px;transition:transform .15s,opacity .15s,box-shadow .15s;}
    .render-btn:hover:not(:disabled){transform:translateY(-1px);box-shadow:0 8px 22px rgba(245,197,66,.28);}
    .render-btn:disabled{opacity:.45;cursor:not-allowed;transform:none;}
    .render-status{margin-top:12px;padding:11px 13px;background:rgba(255,255,255,.04);border:1px solid var(--border);border-radius:11px;color:var(--muted);font-size:12px;line-height:1.5;display:flex;align-items:center;gap:9px;}
    .render-status::before{content:"";width:13px;height:13px;flex-shrink:0;border:2px solid var(--accent);border-top-color:transparent;border-radius:50%;animation:csf-spin 1s linear infinite;}
    @keyframes csf-spin{to{transform:rotate(360deg);}}
    .render-result{margin-top:12px;padding:13px;background:rgba(74,222,128,.06);border:1px solid rgba(74,222,128,.28);border-radius:11px;}
    .render-result__label{display:block;font-size:11px;color:var(--ok);font-weight:800;margin-bottom:5px;letter-spacing:.4px;}
    .render-result__meta{display:block;font-size:11px;color:var(--muted);margin-bottom:9px;word-break:break-all;}
    .render-result__download{display:inline-block;background:var(--ok);color:#0a2b14;text-decoration:none;padding:9px 14px;border-radius:9px;font-weight:800;font-size:12px;min-height:40px;line-height:1.6;}
    .render-error{margin-top:12px;padding:13px;background:rgba(255,92,122,.06);border-left:3px solid var(--danger);border-radius:8px;color:var(--text);font-size:12px;line-height:1.4;}
    .render-error strong{color:var(--danger);display:block;margin-bottom:3px;}
    .check-btn{background:transparent;color:var(--accent);border:1px solid var(--accent);border-radius:11px;padding:11px 16px;font-weight:800;cursor:pointer;min-height:42px;font-size:13px;margin-top:9px;}
    .check-btn:hover:not(:disabled){background:rgba(245,197,66,.07);}
    .check-btn:disabled{opacity:.45;cursor:not-allowed;}
    /* KI-Bild Button */
    .ai-prompt{width:100%;margin-top:8px;padding:8px 10px;border-radius:8px;border:1px solid rgba(147,51,234,.3);background:rgba(147,51,234,.05);color:var(--text);font-family:inherit;font-size:11px;resize:none;outline:none;}
    .ai-prompt:focus{border-color:rgba(147,51,234,.6);background:rgba(147,51,234,.08);}
    .ai-btn{margin-top:6px;width:100%;min-height:36px;padding:7px 10px;font-size:11px;font-weight:800;border-radius:8px;background:linear-gradient(135deg,#7c3aed,#2563eb);color:#fff;border:0;cursor:pointer;transition:opacity .15s,transform .15s;}
    .ai-btn:hover:not(:disabled){opacity:.88;transform:translateY(-1px);}
    .ai-btn:disabled{opacity:.45;cursor:not-allowed;transform:none;}
    .ai-status{margin-top:5px;font-size:10px;color:var(--muted);min-height:1em;word-break:break-word;}
    .ai-status.ai-ok{color:#a78bfa;}
    .ai-status.ai-err{color:var(--danger);}
    .slot.ai-pending{box-shadow:0 0 0 1px rgba(147,51,234,.5),0 8px 22px rgba(0,0,0,.22);}
    /* Back link */
    .back-link{display:inline-flex;align-items:center;gap:6px;color:var(--muted);text-decoration:none;font-size:13px;font-weight:600;margin-bottom:28px;transition:color .15s;}
    .back-link:hover{color:var(--text);}
    /* Footer */
    .footer{border-top:1px solid var(--border);padding:32px 0;margin-top:60px;}
    .footer-inner{width:min(1160px,calc(100% - 48px));margin:0 auto;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;}
    .footer-logo{font-size:13px;font-weight:900;letter-spacing:2px;background:var(--gold);-webkit-background-clip:text;-webkit-text-fill-color:transparent;}
    .footer-copy{font-size:12px;color:var(--muted2);}
    @media(max-width:900px){.slots{grid-template-columns:repeat(3,1fr);}.meta{grid-template-columns:repeat(2,1fr);}}
    @media(max-width:560px){.slots{grid-template-columns:repeat(2,1fr);}.meta{grid-template-columns:1fr;}}
    /* Prompt Generator */
    .prompt-gen{margin-top:7px;}
    .prompt-gen-toggle{background:transparent;border:1px dashed rgba(147,51,234,.35);color:rgba(167,139,250,.75);font-size:10px;font-weight:700;padding:5px 9px;border-radius:7px;cursor:pointer;width:100%;text-align:left;transition:border-color .15s,color .15s;}
    .prompt-gen-toggle:hover{border-color:rgba(147,51,234,.6);color:#a78bfa;}
    .prompt-gen-panel{margin-top:6px;background:rgba(147,51,234,.04);border:1px solid rgba(147,51,234,.15);border-radius:10px;padding:9px;}
    .prompt-cats{display:flex;flex-wrap:wrap;gap:5px;margin-bottom:7px;}
    .pcat{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);color:var(--muted);font-size:10px;font-weight:700;padding:4px 8px;border-radius:6px;cursor:pointer;transition:all .12s;}
    .pcat.active,.pcat:hover{background:rgba(147,51,234,.18);border-color:rgba(147,51,234,.45);color:#a78bfa;}
    .prompt-items{display:flex;flex-direction:column;gap:4px;}
    .ptpl{background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07);border-radius:7px;padding:6px 8px;cursor:pointer;font-size:10px;color:var(--muted);text-align:left;transition:all .12s;line-height:1.4;}
    .ptpl:hover{background:rgba(147,51,234,.1);border-color:rgba(147,51,234,.3);color:#d8b4fe;}
    /* Cinematic transitions toggle */
    .trans-toggle-row{display:flex;align-items:center;gap:10px;margin-bottom:12px;flex-wrap:wrap;background:rgba(59,130,246,.04);border:1px solid rgba(59,130,246,.12);border-radius:10px;padding:10px 14px;}
    .trans-toggle-row label{display:flex;align-items:center;gap:8px;cursor:pointer;font-size:12px;font-weight:700;color:var(--text);}
    .trans-toggle-row input[type=checkbox]{width:15px;height:15px;accent-color:#7c3aed;cursor:pointer;}
    .trans-info{font-size:11px;color:var(--muted);}
  </style>
</head>
<body>

  <nav class="nav">
    <a href="https://cinematic-vision-studio.de/scene-editor-test.html" class="nav-logo">Cinematic Vision Studio<span>Premium KI-Filmstudio</span></a>
    <div class="nav-links">
      <a href="https://cinematic-vision-studio.de/scene-editor-test.html" class="nav-link">Home</a>
      <a href="studio-demo.php" class="nav-link active">Studio</a>
      <a href="https://cinematic-vision-studio.de/shop.html" class="nav-link">Shop Beta</a>
      <a href="https://cinematic-vision-studio.de/academy.html" class="nav-link">Academy</a>
      <a href="https://cinematic-vision-studio.de/crystals.html" class="nav-link">Kristalle</a>
    </div>
    <div class="nav-actions">
      <?php if ($authUser): ?>
        <a href="https://cinematic-vision-studio.de/crystals.html" class="wallet-pill">💎 <?= htmlspecialchars((string)($authUser['crystals_balance'] ?? 0)) ?></a>
        <form method="POST" action="/api/auth/logout.php" style="display:inline" id="logoutForm">
          <input type="hidden" name="redirect" value="studio-demo.php">
          <button type="submit" class="nav-btn-ghost" style="cursor:pointer;font-family:inherit">Logout</button>
        </form>
      <?php else: ?>
        <a href="login.php?redirect=studio-demo.php" class="nav-btn-ghost">Login</a>
        <a href="login.php?tab=register&redirect=studio-demo.php" class="nav-btn-gold">Kostenlos starten</a>
        <a href="https://cinematic-vision-studio.de/crystals.html" class="wallet-pill">💎 Free</a>
      <?php endif; ?>
    </div>
    <button class="mob-burger" id="mobBurger" aria-label="Menü öffnen" aria-expanded="false"><span></span><span></span><span></span></button>
  </nav>
  <div class="mob-menu" id="mobMenu">
    <a href="https://cinematic-vision-studio.de/scene-editor-test.html" class="mob-link">🏠 Home</a>
    <a href="studio-demo.php" class="mob-link active">🎬 Studio</a>
    <a href="https://cinematic-vision-studio.de/shop.html" class="mob-link">🛍️ Shop Beta</a>
    <a href="https://cinematic-vision-studio.de/academy.html" class="mob-link">🎓 Academy</a>
    <a href="https://cinematic-vision-studio.de/crystals.html" class="mob-link">💎 Kristalle</a>
    <div class="mob-sep"></div>
    <?php if ($authUser): ?>
      <span class="mob-link" style="color:var(--accent)">💎 <?= htmlspecialchars((string)($authUser['crystals_balance'] ?? 0)) ?> Kristalle · <?= htmlspecialchars((string)($authUser['email'] ?? '')) ?></span>
      <form method="POST" action="/api/auth/logout.php" style="margin:4px 0" id="logoutFormMob">
        <input type="hidden" name="redirect" value="studio-demo.php">
        <button type="submit" class="mob-cta" style="width:100%;cursor:pointer;font-family:inherit;background:rgba(255,92,122,.15);color:#ff5c7a">🚪 Logout</button>
      </form>
    <?php else: ?>
      <a href="login.php?redirect=studio-demo.php" class="mob-link">🔑 Login</a>
      <a href="login.php?tab=register&redirect=studio-demo.php" class="mob-cta">✨ Kostenlos starten</a>
    <?php endif; ?>
  </div>

  <div class="page">
    <div class="wrap">

      <a href="https://cinematic-vision-studio.de/scene-editor-test.html" class="back-link">← Zurück zur Startseite</a>

      <div class="demo-header">
        <div class="demo-eyebrow">Demo aktiv</div>
        <h1>Scene <span>Replacement Editor</span></h1>
        <p>Lade ein Referenzvideo hoch — das Backend analysiert es, erzeugt Szenen-Slots und zeigt Thumbnails. Dann kannst du pro Slot Bilder, Videos oder Text ersetzen.</p>
      </div>

      <div class="demo-module">

        <div class="panel">
          <div class="row">
            <input id="videoInput" type="file" accept="video/mp4,video/webm,video/quicktime,video/x-matroska">
            <button id="analyzeBtn">Video analysieren</button>
            <button id="resetBtn" class="reset-btn" type="button" hidden>↻ Job zurücksetzen</button>
          </div>
          <div id="restoredHint" class="restored-hint" hidden></div>
          <div id="status" class="status">Bereit. Wähle ein kurzes MP4-Testvideo.</div>
        </div>

        <div id="meta" class="meta">
          <div><strong>Job ID</strong><span id="jobId">-</span></div>
          <div><strong>Dauer</strong><span id="duration">-</span></div>
          <div><strong>Auflösung</strong><span id="resolution">-</span></div>
          <div><strong>Slots</strong><span id="slotCount">-</span></div>
        </div>

        <div id="slots" class="slots"></div>

        <section id="finalSection" class="final-section" hidden>
          <h2>Finales Video erstellen</h2>
          <p>Alle nicht ersetzten Slots werden aus dem Originalvideo geschnitten. Original-Audio wird beibehalten wenn vorhanden.</p>

          <?php if ($authUser && in_array($authUser['plan'], ['starter','pro'], true)): ?>
          <!-- Qualitäts-Umschalter nur für Starter+ / Pro -->
          <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;flex-wrap:wrap;">
            <span style="font-size:.82rem;color:var(--muted);font-weight:700;text-transform:uppercase;letter-spacing:.05em">Export-Qualität:</span>
            <div style="display:flex;gap:6px;">
              <button id="q720Btn" onclick="setQuality('720p')"
                style="padding:6px 14px;border-radius:8px;border:1px solid var(--border);background:var(--card2);color:var(--muted);font-size:.82rem;font-weight:700;cursor:pointer;transition:all .15s"
                class="q-btn q-active">720p</button>
              <button id="q1080Btn" onclick="setQuality('1080p')"
                style="padding:6px 14px;border-radius:8px;border:1px solid var(--border);background:var(--card2);color:var(--muted);font-size:.82rem;font-weight:700;cursor:pointer;transition:all .15s"
                class="q-btn">1080p ✨</button>
            </div>
            <span id="qualityLabel" style="font-size:.78rem;color:var(--muted)">· 720p aktiv</span>
          </div>
          <?php else: ?>
          <div style="font-size:.8rem;color:var(--muted);margin-bottom:12px;display:flex;align-items:center;gap:6px;">
            <span>📺 720p Export</span>
            <?php if (!$authUser): ?>
              · <a href="login.php?tab=register&redirect=studio-demo.php" style="color:var(--purple)">Registrieren für 1080p</a>
            <?php else: ?>
              · <a href="https://cinematic-vision-studio.de/crystals.html" style="color:var(--purple)">Starter+ für 1080p</a>
            <?php endif; ?>
          </div>
          <?php endif; ?>

          <div class="trans-toggle-row">
            <label><input type="checkbox" id="transitionsToggle"> ✨ Cinematic Übergänge</label>
            <span class="trans-info">Weiches Crossfade (0.3s) zwischen Szenen</span>
          </div>
          <button id="renderBtn" class="render-btn" type="button">🎬 Finales Video erstellen</button>
          <div id="renderStatus" class="render-status" hidden></div>
          <div id="renderError" class="render-error" hidden></div>
          <button id="checkStatusBtn" class="check-btn" type="button" hidden>🔍 Status prüfen</button>
          <div id="renderResult" class="render-result" hidden>
            <span class="render-result__label">✓ Render abgeschlossen</span>
            <span id="renderResultMeta" class="render-result__meta"></span>
            <a id="renderDownload" class="render-result__download" download>⬇ MP4 herunterladen</a>
            <video id="renderPreview" controls hidden style="width:100%;max-height:280px;border-radius:9px;margin-top:11px;"></video>
          </div>
        </section>

        <p class="note">Hinweis: Ein Klick auf „Finales Video erstellen" rendert blockierend (Schätzwert: 30–120 Sekunden je nach Slots). Original-Audio wird beibehalten wenn vorhanden. KI-Bilder erfordern einen gesetzten API-Key auf dem Server.</p>
      </div>

    </div>
  </div>

  <footer class="footer">
    <div class="footer-inner">
      <div class="footer-logo">Cinematic Vision Studio</div>
      <div class="footer-copy">© 2026 · Demo-Version · <a href="scene-editor-test.html" style="color:var(--accent);text-decoration:none;">Startseite</a></div>
    </div>
  </footer>

  <script>
    "use strict";
    const BASE_URL       = "https://cinematic-studio-family.onrender.com";
    const ANALYZE_API    = BASE_URL + "/api/analyze.php";
    const REPLACE_API    = BASE_URL + "/api/replace-slot.php";
    const GETJOB_API     = BASE_URL + "/api/get-job.php";
    const RENDER_API     = BASE_URL + "/api/render-final.php";
    const AI_GEN_API     = BASE_URL + "/api/generate-ai.php";
    const AI_STATUS_API  = BASE_URL + "/api/ai-status.php";
    const LS_KEY      = "csf_last_job_id";
    const MAX_IMG_BYTES = 10  * 1024 * 1024;
    const MAX_VID_BYTES = 100 * 1024 * 1024;
    const MAX_TEXT_LEN  = 500;

    const videoInput    = document.getElementById("videoInput");
    const analyzeBtn    = document.getElementById("analyzeBtn");
    const resetBtn      = document.getElementById("resetBtn");
    const restoredHint  = document.getElementById("restoredHint");
    const statusBox     = document.getElementById("status");
    const metaBox       = document.getElementById("meta");
    const slotsBox      = document.getElementById("slots");
    const jobIdEl       = document.getElementById("jobId");
    const durationEl    = document.getElementById("duration");
    const resolutionEl  = document.getElementById("resolution");
    const slotCountEl   = document.getElementById("slotCount");
    const finalSection  = document.getElementById("finalSection");
    const renderBtn     = document.getElementById("renderBtn");
    const renderStatus  = document.getElementById("renderStatus");
    const renderError   = document.getElementById("renderError");
    const renderResult  = document.getElementById("renderResult");
    const renderResMeta  = document.getElementById("renderResultMeta");
    const renderDownload = document.getElementById("renderDownload");
    const checkStatusBtn = document.getElementById("checkStatusBtn");
    const renderPreview  = document.getElementById("renderPreview");

    function setStatus(text, type) { statusBox.className = "status" + (type ? " " + type : ""); statusBox.textContent = text; }
    function formatSeconds(value) { return Number(value).toFixed(2) + "s"; }
    function clearChildren(el) { while (el.firstChild) el.removeChild(el.firstChild); }

    // ── Qualitäts-Toggle (Starter+ / Pro) ────────────────────────────────────
    let currentQuality = '720p';
    async function setQuality(q) {
        try {
            const res  = await fetch('/api/settings/quality.php', {
                method:  'POST',
                headers: {'Content-Type':'application/json'},
                body:    JSON.stringify({quality: q}),
            });
            const data = await res.json();
            if (data.status === 'ok') {
                currentQuality = data.quality;
                const lbl = document.getElementById('qualityLabel');
                const b720 = document.getElementById('q720Btn');
                const b1080 = document.getElementById('q1080Btn');
                if (lbl)  lbl.textContent = '· ' + currentQuality + ' aktiv' + (data.capped ? ' (Plan: Free → max 720p)' : '');
                if (b720)  { b720.style.color  = currentQuality==='720p'  ? '#f0f0ff' : ''; b720.style.borderColor  = currentQuality==='720p'  ? 'rgba(124,58,237,.6)' : ''; }
                if (b1080) { b1080.style.color = currentQuality==='1080p' ? '#f0f0ff' : ''; b1080.style.borderColor = currentQuality==='1080p' ? 'rgba(124,58,237,.6)' : ''; }
                if (data.capped && q==='1080p') {
                    alert('1080p ist nur für Starter+ und Pro verfügbar. Upgrade unter cinematic-vision-studio.de/crystals.html');
                }
            }
        } catch(e) { console.warn('Quality toggle failed', e); }
    }
    function setSlotStatus(el, text, type) { el.textContent = text; el.classList.remove("ok","err"); if (type) el.classList.add(type); }
    function dbg(label, data) { try { if (data === undefined) console.log("[csf] " + label); else console.log("[csf] " + label, data); } catch(_){} }
    function networkErrorMessage(err, endpoint) {
      const msg = err && err.message ? err.message : String(err);
      const isNetwork = err && (err.name === "TypeError" || /failed to fetch|networkerror|load failed/i.test(msg));
      if (isNetwork) return "Backend nicht erreichbar (" + endpoint + ").\nMögliche Ursachen: Render schläft, DNS-Hiccup, Verbindung weg.\nBitte 20–30 Sekunden warten.";
      return msg;
    }
    const COLD_MS = 35000; const COLD_RETRIES = 2;
    async function fetchWithRetry(url, options, onRetry) {
      for (var attempt = 0; attempt <= COLD_RETRIES; attempt++) {
        try { return await fetch(url, options); }
        catch (err) {
          var isNet = err instanceof TypeError || /failed to fetch|networkerror|load failed/i.test(err && err.message ? err.message : "");
          if (isNet && attempt < COLD_RETRIES) { if (onRetry) onRetry(attempt + 1); await new Promise(function(res){setTimeout(res,COLD_MS);}); continue; }
          throw err;
        }
      }
    }
    // ── Prompt-Generator ─────────────────────────────────────────────────────
    const PROMPT_TEMPLATES = {
      urlaub:      { label: '✈️ Urlaub',      prompts: [
        'cinematic beach sunset, turquoise water, golden hour light, film grain',
        'mountain landscape at dawn, dramatic clouds, epic scale, 4K cinematic',
        'tropical rainforest waterfall, lush green, mist, magical atmosphere',
        'aerial travel city view, vibrant streets, warm bokeh, golden hour',
      ]},
      geburtstag:  { label: '🎂 Geburtstag',  prompts: [
        'birthday celebration, colorful confetti falling, warm bokeh lights',
        'birthday cake with glowing candles, cinematic close-up, warm tones',
        'happy family gathering, smiling faces, cozy home, golden light',
        'children birthday party, balloons floating, joyful cheerful atmosphere',
      ]},
      natur:       { label: '🌿 Natur',        prompts: [
        'misty forest morning, sunbeams through trees, ethereal cinematic light',
        'wildflower meadow, aerial drone view, golden hour, cinematic',
        'stormy ocean waves on coastal rocks, dramatic sky, raw power',
        'cherry blossom trees, soft pink petals falling, springtime magic',
      ]},
      cinematic:   { label: '🎬 Cinematic',    prompts: [
        'cinematic wide shot, dramatic lens flare, film grain, anamorphic look',
        'noir city rain-soaked street at night, neon reflections on pavement',
        'slow motion smoke rising, dark studio background, dramatic lighting',
        'aerial cityscape at twilight, city lights sparkling, cinematic blue hour',
      ]},
      winter:      { label: '❄️ Winter',       prompts: [
        'cozy christmas living room, fireplace glow, snow outside window, warm',
        'snowy village at night, christmas lights twinkling, magical atmosphere',
        'christmas market scene, warm lights and steam, festive bokeh',
        'family gathering by christmas tree, warm candlelight, joyful smiles',
      ]},
    };
    function renderPromptItems(catKey, container, targetTextarea) {
      while (container.firstChild) container.removeChild(container.firstChild);
      var cat = PROMPT_TEMPLATES[catKey]; if (!cat) return;
      cat.prompts.forEach(function(p) {
        var btn = document.createElement('button'); btn.type = 'button'; btn.className = 'ptpl';
        btn.textContent = p;
        btn.addEventListener('click', function() {
          targetTextarea.value = p;
          targetTextarea.dispatchEvent(new Event('input'));
        });
        container.appendChild(btn);
      });
    }

    function createSlotCard(jobId, slot) {
      const card = document.createElement("div"); card.className = "slot"; card.dataset.jobId = jobId; card.dataset.slotNumber = String(slot.slot);
      if (slot.replaced) card.classList.add("is-replaced");
      const thumb = document.createElement("div"); thumb.className = "thumb";
      const img = document.createElement("img"); img.src = BASE_URL + slot.thumbnail; img.alt = "Slot " + slot.slot; thumb.appendChild(img);
      const badge = document.createElement("span"); badge.className = "badge"; badge.textContent = "✓ ersetzt"; thumb.appendChild(badge);
      card.appendChild(thumb);
      const body = document.createElement("div"); body.className = "slot-body";
      const title = document.createElement("div"); title.className = "slot-title"; title.textContent = "Slot " + slot.slot; body.appendChild(title);
      const time = document.createElement("div"); time.className = "time"; time.textContent = formatSeconds(slot.start_seconds) + " – " + formatSeconds(slot.end_seconds); body.appendChild(time);
      const textField = document.createElement("input"); textField.type = "text"; textField.className = "field"; textField.placeholder = "Text für diese Szene..."; textField.maxLength = MAX_TEXT_LEN;
      if (slot.text) textField.value = String(slot.text); body.appendChild(textField);
      const fileInput = document.createElement("input"); fileInput.type = "file"; fileInput.className = "replace"; fileInput.accept = "image/*,video/*"; body.appendChild(fileInput);
      const saveBtn = document.createElement("button"); saveBtn.type = "button"; saveBtn.className = "save-btn"; saveBtn.textContent = "Slot speichern"; body.appendChild(saveBtn);
      const slotStatus = document.createElement("div"); slotStatus.className = "slot-status";
      if (slot.replaced && slot.replacement_file) { const parts = String(slot.replacement_file).split("/"); slotStatus.textContent = "Aktuelle Datei: " + parts[parts.length-1]; }
      body.appendChild(slotStatus);

      // ── KI-Bild-Bereich ─────────────────────────────────────────
      const aiPrompt = document.createElement("textarea"); aiPrompt.className = "ai-prompt"; aiPrompt.rows = 2; aiPrompt.placeholder = "KI-Prompt: z.B. cinematic forest scene, golden hour…"; aiPrompt.maxLength = 500;
      if (slot.ai_prompt) aiPrompt.value = String(slot.ai_prompt);
      body.appendChild(aiPrompt);
      // Prompt Generator (Kategorie-Chips → Template-Prompts)
      const promptGen = document.createElement("div"); promptGen.className = "prompt-gen";
      const pgToggle = document.createElement("button"); pgToggle.type = "button"; pgToggle.className = "prompt-gen-toggle"; pgToggle.textContent = "💡 Prompt-Vorschläge";
      const pgPanel = document.createElement("div"); pgPanel.className = "prompt-gen-panel"; pgPanel.hidden = true;
      const pgCats = document.createElement("div"); pgCats.className = "prompt-cats";
      const pgItems = document.createElement("div"); pgItems.className = "prompt-items";
      Object.entries(PROMPT_TEMPLATES).forEach(function(entry, idx) {
        var key = entry[0]; var cat = entry[1];
        var cb = document.createElement("button"); cb.type = "button";
        cb.className = "pcat" + (idx === 0 ? " active" : ""); cb.textContent = cat.label; cb.dataset.cat = key;
        cb.addEventListener("click", function() {
          pgCats.querySelectorAll(".pcat").forEach(function(b){ b.classList.remove("active"); });
          cb.classList.add("active"); renderPromptItems(key, pgItems, aiPrompt);
        });
        pgCats.appendChild(cb);
      });
      renderPromptItems(Object.keys(PROMPT_TEMPLATES)[0], pgItems, aiPrompt);
      pgPanel.appendChild(pgCats); pgPanel.appendChild(pgItems);
      promptGen.appendChild(pgToggle); promptGen.appendChild(pgPanel);
      body.appendChild(promptGen);
      pgToggle.addEventListener("click", function() {
        pgPanel.hidden = !pgPanel.hidden;
        pgToggle.textContent = pgPanel.hidden ? "💡 Prompt-Vorschläge" : "▲ Schließen";
      });
      const aiBtn = document.createElement("button"); aiBtn.type = "button"; aiBtn.className = "ai-btn"; aiBtn.textContent = "✨ KI-Bild generieren"; body.appendChild(aiBtn);
      const aiStatusEl = document.createElement("div"); aiStatusEl.className = "ai-status"; body.appendChild(aiStatusEl);

      card.appendChild(body);

      saveBtn.addEventListener("click", function(){ saveSlot({card,fileInput,textField,slotStatus,saveBtn}); });

      // Auto-resume: Wenn Slot beim Laden bereits 'pending' ist → sofort pollen
      if (slot.ai_status === "pending" && slot.ai_task_id) {
        card.classList.add("ai-pending");
        aiBtn.disabled = true; aiBtn.textContent = "⏳ Generiert…";
        aiStatusEl.textContent = "Generierung läuft (wiederhergestellt)…"; aiStatusEl.className = "ai-status";
        pollAiStatus(jobId, slot.slot, card, aiBtn, aiStatusEl);
      }

      aiBtn.addEventListener("click", function(){
        const p = aiPrompt.value.trim();
        if (!p) { aiStatusEl.textContent = "Bitte Prompt eingeben."; aiStatusEl.className = "ai-status ai-err"; return; }
        generateAiImage(jobId, slot.slot, p, card, aiBtn, aiStatusEl);
      });

      return card;
    }
    function renderSlots(data) { clearChildren(slotsBox); data.slots.forEach(function(slot){ slotsBox.appendChild(createSlotCard(data.job_id, slot)); }); }
    async function saveSlot(refs) {
      const {card,fileInput,textField,slotStatus,saveBtn} = refs;
      const file = fileInput.files[0] || null; const text = textField.value.trim();
      if (!file && !text) { setSlotStatus(slotStatus,"Bitte Datei oder Text angeben.","err"); return; }
      if (text.length > MAX_TEXT_LEN) { setSlotStatus(slotStatus,"Text zu lang (max. "+MAX_TEXT_LEN+" Zeichen).","err"); return; }
      if (file) {
        const isImage = file.type.indexOf("image/") === 0; const isVideo = file.type.indexOf("video/") === 0;
        if (!isImage && !isVideo) { setSlotStatus(slotStatus,"Nur Bild oder Video erlaubt.","err"); return; }
        const maxBytes = isImage ? MAX_IMG_BYTES : MAX_VID_BYTES;
        if (file.size > maxBytes) { setSlotStatus(slotStatus,"Datei zu groß. Limit: "+Math.round(maxBytes/1024/1024)+" MB.","err"); return; }
      }
      saveBtn.disabled = true; setSlotStatus(slotStatus,"Speichere…");
      try {
        const formData = new FormData(); formData.append("job_id",card.dataset.jobId); formData.append("slot_number",card.dataset.slotNumber); formData.append("text",text);
        if (file) formData.append("replacement_file",file);
        const response = await fetchWithRetry(REPLACE_API,{method:"POST",body:formData},function(n){setSlotStatus(slotStatus,"Render schläft — Versuch "+n+" in 35s…");});
        const responseText = await response.text();
        let data; try { data = JSON.parse(responseText); } catch(e){ throw new Error("Antwort war kein JSON:\n"+responseText); }
        if (!response.ok || data.status !== "ok") throw new Error(data.message || "Speichern fehlgeschlagen");
        card.classList.add("is-replaced");
        let infoText = "Gespeichert"; if (data.replacement_file) { const parts = String(data.replacement_file).split("/"); infoText += " · " + parts[parts.length-1]; }
        setSlotStatus(slotStatus,infoText,"ok"); fileInput.value = "";
      } catch(err) { setSlotStatus(slotStatus,"Fehler: "+(err&&err.message?err.message:err),"err"); }
      finally { saveBtn.disabled = false; }
    }
    analyzeBtn.addEventListener("click", async function(){
      const file = videoInput.files[0];
      if (!file) { setStatus("Bitte zuerst ein Video auswählen.","err"); return; }
      analyzeBtn.disabled = true; clearChildren(slotsBox); metaBox.style.display = "none";
      setStatus("Upload läuft... Render kann beim Free Plan kurz aufwachen. Bitte warten.");
      dbg("analyze: POST",{endpoint:ANALYZE_API,name:file.name,size:file.size});
      try {
        const formData = new FormData(); formData.append("video",file);
        const response = await fetchWithRetry(ANALYZE_API,{method:"POST",body:formData},function(n){setStatus("Render schläft (Cold Start) — Versuch "+n+" in 35s…");});
        const text = await response.text();
        let data; try { data = JSON.parse(text); } catch(e){ throw new Error("Antwort war kein JSON:\n"+text); }
        if (!response.ok || data.status !== "ok") throw new Error(data.message || "Analyse fehlgeschlagen");
        jobIdEl.textContent = data.job_id; durationEl.textContent = data.video.duration_seconds+"s";
        resolutionEl.textContent = data.video.width+" × "+data.video.height; slotCountEl.textContent = String(data.slot_count);
        metaBox.style.display = "grid"; renderSlots(data);
        saveJobToStorage(data.job_id); updateUrlHash(data.job_id); hideRestoredHint(); hideRenderState();
        finalSection.hidden = false; resetBtn.hidden = false;
        setStatus("Analyse erfolgreich. Slots wurden erzeugt.","ok");
        dbg("analyze: ok",{job_id:data.job_id,slots:data.slot_count});
      } catch(err) { dbg("analyze: error",err); setStatus("Fehler:\n"+networkErrorMessage(err,ANALYZE_API),"err"); }
      finally { analyzeBtn.disabled = false; }
    });
    function isValidJobId(id) { return /^job_\d{8}_\d{6}_[a-f0-9]{8}$/.test(String(id||"")); }
    function saveJobToStorage(jobId) { try { localStorage.setItem(LS_KEY,String(jobId)); } catch(_){} }
    function loadJobFromStorage() { try { return localStorage.getItem(LS_KEY)||null; } catch(_){ return null; } }
    function clearJobStorage() { try { localStorage.removeItem(LS_KEY); } catch(_){} }
    function updateUrlHash(jobId) { try { if(jobId) history.replaceState(null,"","#job="+encodeURIComponent(jobId)); else history.replaceState(null,"",location.pathname+location.search); } catch(_){} }
    function readJobIdFromHash() { const h=(location.hash||"").replace(/^#/,""); const m=h.match(/(?:^|&)job=([^&]+)/); return m?decodeURIComponent(m[1]):null; }
    function showRestoredHint(jobId) { restoredHint.textContent="Wiederhergestellt: "+jobId+" — du kannst weiter Slots ersetzen oder unten ein finales Video erstellen."; restoredHint.hidden=false; }
    function hideRestoredHint() { restoredHint.hidden=true; restoredHint.textContent=""; }
    function hideRenderState() {
      renderStatus.hidden=true; renderStatus.textContent=""; renderError.hidden=true;
      while(renderError.firstChild) renderError.removeChild(renderError.firstChild);
      renderResult.hidden=true; renderResMeta.textContent=""; renderDownload.removeAttribute("href");
      checkStatusBtn.hidden=true; renderPreview.hidden=true; renderPreview.removeAttribute("src");
    }
    function showRenderError(title,detail) {
      while(renderError.firstChild) renderError.removeChild(renderError.firstChild);
      const t=document.createElement("strong"); t.textContent=title||"Render fehlgeschlagen"; renderError.appendChild(t);
      if(detail){ const d=document.createElement("span"); d.textContent=String(detail); renderError.appendChild(d); }
      renderError.hidden=false;
    }
    async function restoreJob(jobId) {
      if(!isValidJobId(jobId)){ clearJobStorage(); updateUrlHash(null); return; }
      setStatus("Job "+jobId+" wird geladen…");
      try {
        const url=GETJOB_API+"?job_id="+encodeURIComponent(jobId);
        const response=await fetchWithRetry(url,{method:"GET"},function(n){setStatus("Render schläft (Cold Start) — Versuch "+n+" in 35s…");});
        const text=await response.text();
        let data; try{data=JSON.parse(text);}catch(e){throw new Error("Antwort war kein JSON:\n"+text);}
        if(response.status===404){ clearJobStorage(); updateUrlHash(null); setStatus("Gespeicherter Job nicht mehr verfügbar — bitte ein neues Video analysieren.","err"); return; }
        if(!response.ok||data.status!=="ok"||!data.job) throw new Error(data.message||"Job konnte nicht geladen werden.");
        const job=data.job;
        jobIdEl.textContent=job.job_id||"-"; durationEl.textContent=(job.video&&job.video.duration_seconds!=null?job.video.duration_seconds:"-")+"s";
        resolutionEl.textContent=((job.video&&job.video.width)||"-")+" × "+((job.video&&job.video.height)||"-");
        slotCountEl.textContent=String(job.slot_count||(job.slots?job.slots.length:0));
        metaBox.style.display="grid"; renderSlots({job_id:job.job_id,slots:job.slots||[]});
        finalSection.hidden=false; resetBtn.hidden=false; hideRenderState(); showRestoredHint(jobId);
        setStatus("Job wiederhergestellt.","ok");
      } catch(err) { setStatus("Job-Restore fehlgeschlagen:\n"+networkErrorMessage(err,GETJOB_API),"err"); }
    }
    resetBtn.addEventListener("click", function(){
      clearJobStorage(); updateUrlHash(null); hideRestoredHint(); hideRenderState();
      finalSection.hidden=true; clearChildren(slotsBox); metaBox.style.display="none";
      jobIdEl.textContent="-"; durationEl.textContent="-"; resolutionEl.textContent="-"; slotCountEl.textContent="-";
      videoInput.value=""; resetBtn.hidden=true; setStatus("Bereit. Wähle ein neues Video.","");
    });
    function showFinalResult(data) {
      var url=data.download_url||data.final_video||""; var fname=data.filename||data.final_filename||"final.mp4";
      var size=data.size_bytes||data.final_size_bytes||0; var sizeMb=size>0?(size/1024/1024).toFixed(1)+" MB":"";
      var absUrl=url.indexOf("http")===0?url:(BASE_URL+url);
      renderDownload.href=absUrl; renderDownload.download=fname;
      var parts=[fname]; if(data.duration_seconds) parts.push(Number(data.duration_seconds).toFixed(2)+"s"); if(data.slot_count) parts.push(data.slot_count+" Slots"); if(sizeMb) parts.push(sizeMb);
      renderResMeta.textContent=parts.join(" · "); renderPreview.src=absUrl; renderPreview.hidden=false; renderResult.hidden=false;
    }
    async function reAnalyzeAndRender(videoFile) {
      try {
        renderStatus.textContent="Server neu gestartet — analysiere Video erneut…"; renderStatus.hidden=false;
        var fd=new FormData(); fd.append("video",videoFile);
        var aResp=await fetchWithRetry(ANALYZE_API,{method:"POST",body:fd},function(n){renderStatus.textContent="Analyse: Cold Start — Versuch "+n+" in 35s…";});
        var aText=await aResp.text(); var aData; try{aData=JSON.parse(aText);}catch(e){throw new Error("Analyse-Antwort kein JSON:\n"+aText);}
        if(!aResp.ok||aData.status!=="ok") throw new Error(aData.message||"Re-Analyse fehlgeschlagen");
        var newJobId=aData.job_id; jobIdEl.textContent=newJobId; saveJobToStorage(newJobId); updateUrlHash(newJobId);
        renderSlots({job_id:newJobId,slots:aData.slots||[]});
        renderStatus.textContent="Analyse fertig. Starte Render ("+aData.slot_count+" Slots)…";
        var rf=new FormData(); rf.append("job_id",newJobId);
        var _transRe=document.getElementById('transitionsToggle'); if(_transRe&&_transRe.checked) rf.append('transitions','1');
        var rResp=await fetchWithRetry(RENDER_API,{method:"POST",body:rf},function(n){renderStatus.textContent="Render: Versuch "+n+" in 35s…";});
        var rText=await rResp.text(); var rData; try{rData=JSON.parse(rText);}catch(e){throw new Error("Render-Antwort kein JSON:\n"+rText);}
        if(!rResp.ok||rData.status!=="ok") throw new Error(rData.message||"Render fehlgeschlagen");
        renderStatus.hidden=true; showFinalResult(rData);
      } catch(err) {
        renderStatus.hidden=true;
        var isNet=err instanceof TypeError||/failed to fetch|networkerror|load failed/i.test(err&&err.message?err.message:"");
        if(isNet){showRenderError("Verbindung unterbrochen",'"Status prüfen" nach 60s.');checkStatusBtn.hidden=false;}
        else showRenderError("Render fehlgeschlagen",err&&err.message?err.message:String(err));
      } finally { renderBtn.disabled=false; }
    }
    renderBtn.addEventListener("click", async function(){
      const jobId=jobIdEl.textContent.trim();
      if(!isValidJobId(jobId)){showRenderError("Kein aktiver Job","Bitte zuerst ein Video analysieren.");return;}
      hideRenderState(); renderBtn.disabled=true; renderStatus.textContent="Verbinde mit Server…"; renderStatus.hidden=false;
      try {
        var preUrl=GETJOB_API+"?job_id="+encodeURIComponent(jobId);
        var preResp=await fetchWithRetry(preUrl,{method:"GET"},function(n){renderStatus.textContent="Server wacht auf (Cold Start) — Versuch "+n+" in 35s…";});
        var preText=await preResp.text(); var preData; try{preData=JSON.parse(preText);}catch(e){throw new Error("Server-Antwort kein JSON:\n"+preText);}
        if(preResp.ok&&preData.status==="ok"&&preData.job&&preData.job.final_video){renderStatus.hidden=true;showFinalResult(preData.job);return;}
        if(preResp.status===404||!preResp.ok||preData.status!=="ok"){
          var videoFile=videoInput.files[0];
          if(!videoFile){clearJobStorage();updateUrlHash(null);renderStatus.hidden=true;showRenderError("Server neu gestartet — bitte Video erneut hochladen","Wähle das Video oben erneut aus.");finalSection.hidden=true;resetBtn.hidden=true;clearChildren(slotsBox);metaBox.style.display="none";return;}
          await reAnalyzeAndRender(videoFile);return;
        }
        renderStatus.textContent="Rendere … 30–120 Sekunden. Bitte Tab offen lassen.";
        var formData=new FormData(); formData.append("job_id",jobId);
        var _trans=document.getElementById('transitionsToggle'); if(_trans&&_trans.checked) formData.append('transitions','1');
        var response=await fetchWithRetry(RENDER_API,{method:"POST",body:formData},function(n){renderStatus.textContent="Render: Cold Start — Versuch "+n+" in 35s…";});
        var text=await response.text(); var data; try{data=JSON.parse(text);}catch(e){throw new Error("Antwort war kein JSON:\n"+text);}
        if(!response.ok||data.status!=="ok") throw new Error(data.message||"Render fehlgeschlagen");
        renderStatus.hidden=true; showFinalResult(data);
      } catch(err) {
        renderStatus.hidden=true;
        var isNetErr=err&&(err.name==="TypeError"||/failed to fetch|networkerror|load failed/i.test(err.message?err.message:String(err)));
        if(isNetErr){showRenderError("Verbindung unterbrochen — Render kann noch laufen","Warte 60–120 Sekunden, dann klicke \"Status prüfen\".");checkStatusBtn.hidden=false;}
        else {
          var missingFiles=err&&err.message&&(err.message.indexOf("meta.json")!==-1||err.message.indexOf("Originalvideo")!==-1||(err.message.indexOf("nicht gefunden")!==-1&&err.message.indexOf("Job")!==-1));
          if(missingFiles){var vfFallback=videoInput&&videoInput.files&&videoInput.files[0];if(vfFallback){hideRenderState();await reAnalyzeAndRender(vfFallback);return;}else{clearJobStorage();updateUrlHash(null);showRenderError("Server neu gestartet — bitte Video neu auswählen","Wähle dein Video oben erneut aus.");return;}}
          showRenderError("Render fehlgeschlagen",err&&err.message?err.message:String(err));
        }
      } finally { renderBtn.disabled=false; }
    });
    checkStatusBtn.addEventListener("click", async function(){
      const jobId=jobIdEl.textContent.trim();
      if(!isValidJobId(jobId)){showRenderError("Kein aktiver Job","Bitte zuerst ein Video analysieren.");return;}
      checkStatusBtn.disabled=true; checkStatusBtn.textContent="Prüfe …";
      try {
        const url=GETJOB_API+"?job_id="+encodeURIComponent(jobId);
        const response=await fetchWithRetry(url,{method:"GET"},function(n){checkStatusBtn.textContent="Cold Start — Versuch "+n+" in 35s…";});
        const text=await response.text(); let data; try{data=JSON.parse(text);}catch(e){throw new Error("Antwort war kein JSON:\n"+text);}
        if(!response.ok||data.status!=="ok"||!data.job) throw new Error(data.message||"Status konnte nicht geladen werden.");
        const job=data.job; const finalUrl=job.final_video||job.final_video_url||job.result_url||null;
        if(finalUrl){
          const absUrl=finalUrl.indexOf("http")===0?finalUrl:(BASE_URL+finalUrl); const fname=job.final_filename||"final.mp4";
          const size=job.final_size_bytes||0; const sizeMb=size>0?(size/1024/1024).toFixed(1)+" MB":"—";
          renderDownload.href=absUrl; renderDownload.download=fname;
          const parts=[fname]; if(sizeMb!=="—") parts.push(sizeMb);
          renderResMeta.textContent=parts.join(" · "); renderPreview.src=absUrl; renderPreview.hidden=false;
          renderResult.hidden=false; renderError.hidden=true; checkStatusBtn.hidden=true;
        } else { showRenderError("Render noch nicht fertig","Noch kein fertiges Video gefunden. Bitte in 30 Sekunden erneut prüfen."); checkStatusBtn.hidden=false; }
      } catch(err) { showRenderError("Status-Prüfung fehlgeschlagen",networkErrorMessage(err,GETJOB_API)); checkStatusBtn.hidden=false; }
      finally { checkStatusBtn.disabled=false; checkStatusBtn.textContent="🔍 Status prüfen"; }
    });
    // ── KI-Bild-Generierung ────────────────────────────────────────────────
    async function generateAiImage(jobId, slotNumber, prompt, card, aiBtn, aiStatusEl) {
      aiBtn.disabled = true; aiBtn.textContent = "⏳ Starte KI…";
      aiStatusEl.textContent = "Sende Anfrage an Kie.ai…"; aiStatusEl.className = "ai-status";
      card.classList.remove("ai-pending");
      try {
        const resp = await fetchWithRetry(AI_GEN_API, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ job_id: jobId, slot_number: slotNumber, prompt: prompt }),
        }, function(n){ aiStatusEl.textContent = "Verbindung … Versuch " + n; });
        const txt = await resp.text();
        let data; try { data = JSON.parse(txt); } catch(e) { throw new Error("Antwort kein JSON:\n" + txt); }
        if (!resp.ok || (data.status !== "pending")) {
          throw new Error(data.message || "KI-Start fehlgeschlagen (HTTP " + resp.status + ").");
        }
        card.classList.add("ai-pending");
        aiBtn.textContent = "⏳ Generiert…";
        aiStatusEl.textContent = "Task gestartet. Polling alle 5s (max. 3 Min.)…"; aiStatusEl.className = "ai-status";
        pollAiStatus(jobId, slotNumber, card, aiBtn, aiStatusEl);
      } catch(err) {
        aiBtn.disabled = false; aiBtn.textContent = "✨ KI-Bild generieren";
        aiStatusEl.textContent = "Fehler: " + (err && err.message ? err.message : String(err));
        aiStatusEl.className = "ai-status ai-err";
      }
    }

    function pollAiStatus(jobId, slotNumber, card, aiBtn, aiStatusEl) {
      const maxAttempts = 36; // 36 × 5s = 3 Min.
      let attempts = 0;
      const interval = setInterval(async function() {
        attempts++;
        if (attempts > maxAttempts) {
          clearInterval(interval);
          aiBtn.disabled = false; aiBtn.textContent = "✨ KI-Bild generieren";
          aiStatusEl.textContent = "Timeout — Generierung dauert länger als 3 Min. Seite neu laden.";
          aiStatusEl.className = "ai-status ai-err";
          card.classList.remove("ai-pending");
          return;
        }
        try {
          const url = AI_STATUS_API + "?job_id=" + encodeURIComponent(jobId) + "&slot_number=" + encodeURIComponent(slotNumber);
          const resp = await fetch(url);
          const txt  = await resp.text();
          let data; try { data = JSON.parse(txt); } catch(e) { return; } // transient parse error → retry
          if (data.status === "generating" || data.status === "pending") {
            aiStatusEl.textContent = "Generiert … (" + attempts + "/" + maxAttempts + ")";
            return;
          }
          if (data.status === "done") {
            clearInterval(interval);
            card.classList.remove("ai-pending");
            card.classList.add("is-replaced");
            aiBtn.disabled = false; aiBtn.textContent = "✨ KI-Bild generieren";
            aiStatusEl.textContent = "✓ KI-Bild gespeichert!"; aiStatusEl.className = "ai-status ai-ok";
            // Thumbnail aktualisieren
            if (data.replacement_file) {
              const thumbImg = card.querySelector(".thumb img");
              if (thumbImg) {
                thumbImg.src = BASE_URL + data.replacement_file + "?t=" + Date.now();
                thumbImg.alt = "KI-generiertes Bild";
              }
              const badge = card.querySelector(".badge");
              if (badge) badge.style.display = "inline-block";
            }
            return;
          }
          if (data.status === "failed" || data.status === "error") {
            clearInterval(interval);
            card.classList.remove("ai-pending");
            aiBtn.disabled = false; aiBtn.textContent = "✨ KI-Bild generieren";
            aiStatusEl.textContent = "KI-Generierung fehlgeschlagen: " + (data.message || data.status);
            aiStatusEl.className = "ai-status ai-err";
          }
        } catch(_) { /* Netzwerkfehler → nächste Iteration */ }
      }, 5000);
    }

    (function boot(){
      // Priorität: ?job_id= URL-Parameter (von dashboard.php) → #job= Hash → localStorage
      const urlParams  = new URLSearchParams(location.search);
      const fromUrl    = urlParams.get('job_id') || null;
      const fromHash   = readJobIdFromHash();
      const fromStorage = loadJobFromStorage();
      const jobId = (isValidJobId(fromUrl)    ? fromUrl    :
                    (isValidJobId(fromHash)   ? fromHash   :
                    (isValidJobId(fromStorage)? fromStorage: null)));
      if (jobId) {
        // URL sauber halten — Query-Param zu Hash konvertieren
        if (fromUrl && isValidJobId(fromUrl)) {
          history.replaceState(null, '', location.pathname + '#job=' + encodeURIComponent(fromUrl));
        }
        restoreJob(jobId);
      }
    })();
  </script>
  <script>(function(){var b=document.getElementById('mobBurger'),m=document.getElementById('mobMenu');if(!b||!m)return;function t(){m.style.top=document.querySelector('.nav').offsetHeight+'px';}t();window.addEventListener('resize',t);b.addEventListener('click',function(e){e.stopPropagation();var o=m.classList.toggle('open');b.classList.toggle('open',o);b.setAttribute('aria-expanded',o?'true':'false');b.setAttribute('aria-label',o?'Menü schließen':'Menü öffnen');});m.querySelectorAll('.mob-link,.mob-cta').forEach(function(l){l.addEventListener('click',function(){m.classList.remove('open');b.classList.remove('open');b.setAttribute('aria-expanded','false');b.setAttribute('aria-label','Menü öffnen');});});document.addEventListener('click',function(e){if(!b.contains(e.target)&&!m.contains(e.target)){m.classList.remove('open');b.classList.remove('open');b.setAttribute('aria-expanded','false');}});document.addEventListener('keydown',function(e){if(e.key==='Escape'){m.classList.remove('open');b.classList.remove('open');b.setAttribute('aria-expanded','false');}});})();</script>
</body>
</html>
