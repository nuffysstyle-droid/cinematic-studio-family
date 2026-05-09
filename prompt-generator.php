<!doctype html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Prompt Generator – Cinematic Vision Studio</title>
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
.page{padding:90px 0 60px;}.wrap{width:min(860px,calc(100% - 48px));margin:0 auto;}
.page-eyebrow{display:inline-flex;align-items:center;gap:8px;font-size:11px;font-weight:800;letter-spacing:3px;text-transform:uppercase;color:var(--accent);background:rgba(245,197,66,.1);border:1px solid rgba(245,197,66,.28);border-radius:999px;padding:6px 14px;margin-bottom:18px;}
.page-h1{font-size:clamp(28px,5vw,48px);font-weight:900;letter-spacing:-1.5px;margin-bottom:10px;line-height:1.1;}
.page-h1 span{background:var(--gold);-webkit-background-clip:text;-webkit-text-fill-color:transparent;}
.page-sub{color:var(--muted);font-size:15px;line-height:1.6;max-width:560px;margin-bottom:36px;}
/* Generator cards */
.gen-card{background:var(--card);border:1px solid var(--border);border-radius:var(--radius);padding:28px;margin-bottom:16px;}
.gen-card h2{font-size:11px;font-weight:800;color:var(--muted2);text-transform:uppercase;letter-spacing:.08em;margin-bottom:20px;}
.field-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;}
.field-group{display:flex;flex-direction:column;gap:6px;}
.field-group.full{grid-column:1/-1;}
.field-group label{font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;}
.field-group select,.field-group input,.field-group textarea{background:var(--card2);border:1px solid var(--border);border-radius:9px;color:var(--text);font-size:13px;padding:10px 12px;outline:none;transition:border-color .15s,box-shadow .15s;font-family:inherit;width:100%;}
.field-group select:focus,.field-group input:focus,.field-group textarea:focus{border-color:rgba(245,197,66,.4);box-shadow:0 0 0 3px rgba(245,197,66,.06);}
.field-group textarea{resize:vertical;min-height:80px;}
.btn-row{display:flex;gap:10px;margin-top:22px;flex-wrap:wrap;}
/* Buttons – unified */
.btn-gold{background:var(--gold);color:#1a0e00;border:0;border-radius:12px;padding:13px 22px;font-size:14px;font-weight:900;cursor:pointer;transition:transform .15s,box-shadow .15s;text-decoration:none;display:inline-flex;align-items:center;gap:8px;box-shadow:0 6px 22px rgba(245,197,66,.22);}
.btn-gold:hover{transform:translateY(-2px);box-shadow:0 12px 30px rgba(245,197,66,.36);}
.btn-ghost{background:transparent;color:var(--text);border:1px solid var(--border);border-radius:12px;padding:13px 22px;font-size:14px;font-weight:700;cursor:pointer;transition:border-color .15s,background .15s;text-decoration:none;display:inline-flex;align-items:center;gap:8px;}
.btn-ghost:hover{border-color:rgba(255,255,255,.28);background:rgba(255,255,255,.05);}
.btn-blue{background:rgba(59,130,246,.15);border:1px solid rgba(59,130,246,.3);color:#93c5fd;border-radius:12px;padding:13px 22px;font-size:14px;font-weight:700;cursor:pointer;transition:.15s;display:inline-flex;align-items:center;gap:8px;}
.btn-blue:hover{background:rgba(59,130,246,.25);}
/* Output */
.output-box{background:var(--card2);border:1px solid var(--border);border-radius:12px;padding:20px;min-height:130px;font-size:13px;line-height:1.7;color:var(--text);white-space:pre-wrap;word-break:break-word;transition:.2s;}
.output-box.empty{color:var(--muted);font-style:italic;}
/* Toast */
.copy-toast{position:fixed;bottom:32px;right:32px;background:rgba(74,222,128,.9);color:#0a2b14;padding:10px 20px;border-radius:10px;font-size:13px;font-weight:800;opacity:0;transition:.3s;pointer-events:none;z-index:200;}
.copy-toast.show{opacity:1;}
.footer{border-top:1px solid var(--border);padding:36px 0;margin-top:60px;}
.footer-inner{width:min(960px,calc(100% - 48px));margin:0 auto;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;}
.footer-logo{font-size:13px;font-weight:900;letter-spacing:2px;background:var(--gold);-webkit-background-clip:text;-webkit-text-fill-color:transparent;}
.footer-nav{display:flex;gap:18px;flex-wrap:wrap;}
.footer-nav a{color:var(--muted);text-decoration:none;font-size:12px;font-weight:600;transition:color .15s;}
.footer-nav a:hover{color:var(--text);}
.footer-copy{font-size:12px;color:var(--muted2);}
@media(max-width:600px){.field-grid{grid-template-columns:1fr;}.field-group.full{grid-column:1;}}
</style>
</head>
<body>
<nav class="nav">
  <a href="scene-editor-test.html" class="nav-logo">Cinematic Vision Studio<span>Premium KI-Filmstudio</span></a>
  <div class="nav-links">
    <a href="scene-editor-test.html" class="nav-link">Home</a>
    <a href="studio-demo.php" class="nav-link">Studio</a>
    <a href="shop.php" class="nav-link">Shop</a>
    <a href="portfolio.php" class="nav-link">Portfolio</a>
    <a href="availability.php" class="nav-link">Verfügbarkeit</a>
    <a href="academy.php" class="nav-link">Academy</a>
    <a href="crystals.php" class="nav-link">Kristalle</a>
    <a href="contact.php" class="nav-link">Kontakt</a>
  </div>
  <div class="nav-actions">
    <a href="contact.php" class="nav-btn-ghost">Anmelden</a>
    <a href="studio-demo.php" class="nav-btn-gold">Studio starten</a>
    <a href="crystals.php" class="wallet-pill">💎 500</a>
  </div>
</nav>

<div class="page">
  <div class="wrap">
    <div class="page-eyebrow">✍️ Prompt Generator</div>
    <h1 class="page-h1">Cinematic<br><span>Prompt Generator</span></h1>
    <p class="page-sub">Erstelle professionelle KI-Video-Prompts in Sekunden. Wähle Genre, Stil und Details – der Generator baut den perfekten Prompt.</p>

    <div class="gen-card">
      <h2>Szenen-Parameter</h2>
      <div class="field-grid">
        <div class="field-group">
          <label>Genre</label>
          <select id="genre">
            <option value="">– wählen –</option>
            <option value="Thriller">Thriller</option>
            <option value="Science Fiction">Science Fiction</option>
            <option value="Horror">Horror</option>
            <option value="Drama">Drama</option>
            <option value="Action">Action</option>
            <option value="Romance">Romance</option>
            <option value="Documentary">Documentary</option>
            <option value="Fantasy">Fantasy</option>
          </select>
        </div>
        <div class="field-group">
          <label>Kamerastil</label>
          <select id="camstyle">
            <option value="">– wählen –</option>
            <option value="slow-motion dolly shot">Slow Motion Dolly</option>
            <option value="handheld chase cam">Handheld Chase</option>
            <option value="overhead drone shot">Overhead Drone</option>
            <option value="extreme close-up">Extreme Close-up</option>
            <option value="wide establishing shot">Wide Establishing</option>
            <option value="point of view shot">Point of View</option>
            <option value="tracking shot">Tracking Shot</option>
          </select>
        </div>
        <div class="field-group">
          <label>Licht & Atmosphäre</label>
          <select id="lighting">
            <option value="">– wählen –</option>
            <option value="golden hour cinematic lighting">Golden Hour</option>
            <option value="neon-lit night scene">Neon Night</option>
            <option value="dark moody shadows">Dark Moody</option>
            <option value="foggy morning light">Foggy Morning</option>
            <option value="harsh midday sun">Harsh Midday</option>
            <option value="warm firelight">Warm Firelight</option>
            <option value="blue hour twilight">Blue Hour</option>
            <option value="overcast diffused light">Overcast Diffused</option>
          </select>
        </div>
        <div class="field-group">
          <label>Farbgrading</label>
          <select id="color">
            <option value="">– wählen –</option>
            <option value="teal and orange grade">Teal & Orange</option>
            <option value="desaturated film grain">Desaturated Film</option>
            <option value="vibrant saturated colors">Vibrant Saturated</option>
            <option value="cold blue steel tone">Cold Blue Steel</option>
            <option value="warm vintage look">Warm Vintage</option>
            <option value="high contrast monochrome">High Contrast B&W</option>
            <option value="bleach bypass look">Bleach Bypass</option>
          </select>
        </div>
        <div class="field-group full">
          <label>Szene beschreiben (optional)</label>
          <textarea id="scene" placeholder="z.B. Ein Detektiv betritt eine verlassene Lagerhalle im Regen …"></textarea>
        </div>
        <div class="field-group">
          <label>Technische Qualität</label>
          <select id="quality">
            <option value="8K ultra-detailed, professional cinematography">8K Ultra HD</option>
            <option value="4K cinematic, film look">4K Cinematic</option>
            <option value="anamorphic lens flares, 35mm film">Anamorphic 35mm</option>
            <option value="IMAX quality, razor sharp">IMAX Quality</option>
          </select>
        </div>
        <div class="field-group">
          <label>Zusatz-Tags</label>
          <input type="text" id="extra" placeholder="z.B. slow zoom, rain, fog machine">
        </div>
      </div>
      <div class="btn-row">
        <button class="btn-gold" onclick="generatePrompt()">✦ Prompt generieren</button>
        <button class="btn-ghost" onclick="clearPrompt()">Leeren</button>
      </div>
    </div>

    <div class="gen-card">
      <h2>Generierter Prompt</h2>
      <div class="output-box empty" id="output">Prompt erscheint hier nach der Generierung …</div>
      <div class="btn-row">
        <button class="btn-blue" onclick="copyPrompt()">📋 Prompt kopieren</button>
      </div>
    </div>

    <a href="scene-editor-test.html" class="btn-ghost">← Zurück zum Hub</a>
  </div>
</div>

<div class="copy-toast" id="toast">✓ Prompt kopiert!</div>

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

<script>
function generatePrompt(){
  const genre=document.getElementById('genre').value;
  const cam=document.getElementById('camstyle').value;
  const light=document.getElementById('lighting').value;
  const color=document.getElementById('color').value;
  const scene=document.getElementById('scene').value.trim();
  const quality=document.getElementById('quality').value;
  const extra=document.getElementById('extra').value.trim();
  const parts=[];
  if(genre) parts.push(genre+' film scene');
  if(scene) parts.push(scene);
  if(cam) parts.push(cam);
  if(light) parts.push(light);
  if(color) parts.push(color);
  if(quality) parts.push(quality);
  if(extra) parts.push(extra);
  if(parts.length<2){alert('Bitte mindestens 2 Parameter auswählen.');return;}
  const prompt=parts.join(', ')+', award-winning visual effects, professional post-production';
  const el=document.getElementById('output');
  el.textContent=prompt;
  el.classList.remove('empty');
}
function copyPrompt(){
  const txt=document.getElementById('output').textContent;
  if(txt.startsWith('Prompt erscheint')) return;
  navigator.clipboard.writeText(txt).then(()=>{
    const t=document.getElementById('toast');
    t.classList.add('show');
    setTimeout(()=>t.classList.remove('show'),2200);
  });
}
function clearPrompt(){
  ['genre','camstyle','lighting','color','quality'].forEach(id=>{document.getElementById(id).selectedIndex=0;});
  document.getElementById('scene').value='';
  document.getElementById('extra').value='';
  const el=document.getElementById('output');
  el.textContent='Prompt erscheint hier nach der Generierung …';
  el.classList.add('empty');
}
</script>
</body>
</html>
