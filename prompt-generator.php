<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Prompt Generator – Cinematic Vision Studio</title>
<style>
:root{--bg:#06060f;--s1:#0d0d1a;--s2:#12121f;--ac:#9333ea;--ac2:#3b82f6;--tx:#e2e8f0;--txm:#94a3b8;--br:#ffffff12;--glow:rgba(147,51,234,.18)}
*{box-sizing:border-box;margin:0;padding:0}
body{background:var(--bg);color:var(--tx);font-family:'Segoe UI',system-ui,sans-serif;min-height:100vh}
/* NAV */
.top-nav{display:flex;align-items:center;gap:8px;padding:14px 28px;background:rgba(13,13,26,.95);backdrop-filter:blur(12px);border-bottom:1px solid var(--br);position:sticky;top:0;z-index:99;flex-wrap:wrap}
.nav-brand{font-weight:700;font-size:1.05rem;background:linear-gradient(135deg,#9333ea,#3b82f6);-webkit-background-clip:text;-webkit-text-fill-color:transparent;margin-right:8px;white-space:nowrap}
.nav-links{display:flex;gap:4px;flex-wrap:wrap;flex:1}
.nav-links a{color:var(--txm);text-decoration:none;font-size:.78rem;padding:5px 10px;border-radius:6px;transition:.2s;white-space:nowrap}
.nav-links a:hover,.nav-links a.active{color:var(--tx);background:var(--br)}
/* PAGE */
.page-wrap{max-width:860px;margin:0 auto;padding:48px 24px 80px}
.page-badge{display:inline-flex;align-items:center;gap:6px;background:rgba(147,51,234,.12);border:1px solid rgba(147,51,234,.3);color:#c084fc;font-size:.72rem;font-weight:600;letter-spacing:.06em;text-transform:uppercase;padding:4px 12px;border-radius:20px;margin-bottom:18px}
.page-badge::before{content:'✦';font-size:.7rem}
h1{font-size:clamp(1.8rem,5vw,2.8rem);font-weight:800;line-height:1.2;margin-bottom:12px;background:linear-gradient(135deg,#c084fc,#60a5fa,#e2e8f0);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.page-sub{color:var(--txm);font-size:1.05rem;line-height:1.6;margin-bottom:40px;max-width:600px}
/* GENERATOR UI */
.gen-card{background:var(--s1);border:1px solid var(--br);border-radius:16px;padding:28px;margin-bottom:20px}
.gen-card h2{font-size:.9rem;font-weight:700;color:var(--txm);text-transform:uppercase;letter-spacing:.08em;margin-bottom:18px}
.field-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.field-group{display:flex;flex-direction:column;gap:6px}
.field-group.full{grid-column:1/-1}
.field-group label{font-size:.78rem;font-weight:600;color:var(--txm);text-transform:uppercase;letter-spacing:.05em}
.field-group select,.field-group input,.field-group textarea{
  background:var(--s2);border:1px solid var(--br);border-radius:8px;color:var(--tx);
  font-size:.88rem;padding:10px 12px;outline:none;transition:.2s;font-family:inherit;width:100%
}
.field-group select:focus,.field-group input:focus,.field-group textarea:focus{border-color:rgba(147,51,234,.5);box-shadow:0 0 0 3px rgba(147,51,234,.08)}
.field-group textarea{resize:vertical;min-height:80px}
.btn-row{display:flex;gap:10px;margin-top:20px;flex-wrap:wrap}
.btn{padding:11px 22px;border-radius:10px;font-size:.88rem;font-weight:600;cursor:pointer;border:none;transition:.2s;font-family:inherit}
.btn-primary{background:linear-gradient(135deg,#7c3aed,#3b82f6);color:#fff}
.btn-primary:hover{opacity:.88;transform:translateY(-1px)}
.btn-ghost{background:transparent;border:1px solid var(--br);color:var(--txm)}
.btn-ghost:hover{border-color:rgba(147,51,234,.4);color:var(--tx)}
.btn-copy{background:rgba(59,130,246,.15);border:1px solid rgba(59,130,246,.3);color:#93c5fd}
.btn-copy:hover{background:rgba(59,130,246,.25)}
/* OUTPUT */
.output-area{position:relative}
.output-box{
  background:var(--s2);border:1px solid var(--br);border-radius:12px;padding:20px;
  min-height:140px;font-size:.88rem;line-height:1.7;color:var(--tx);white-space:pre-wrap;word-break:break-word;
  transition:.3s
}
.output-box.empty{color:var(--txm);font-style:italic}
.copy-toast{position:fixed;bottom:32px;right:32px;background:rgba(16,185,129,.9);color:#fff;padding:10px 20px;border-radius:10px;font-size:.85rem;font-weight:600;opacity:0;transition:.3s;pointer-events:none;z-index:200}
.copy-toast.show{opacity:1}
/* BACK */
.back-link{display:inline-flex;align-items:center;gap:8px;color:var(--txm);text-decoration:none;font-size:.88rem;padding:10px 16px;border:1px solid var(--br);border-radius:10px;transition:.2s;margin-top:32px}
.back-link:hover{color:var(--tx);border-color:rgba(147,51,234,.4);background:rgba(147,51,234,.06)}
@media(max-width:600px){.field-grid{grid-template-columns:1fr}.field-group.full{grid-column:1}}
</style>
</head>
<body>

<nav class="top-nav">
  <span class="nav-brand">🎬 Cinematic Studio</span>
  <div class="nav-links">
    <a href="scene-editor-test.html">Hub</a>
    <a href="studio-demo.php">Demo</a>
    <a href="prompt-generator.php" class="active">Prompts</a>
    <a href="ki-videos.php">KI Videos</a>
    <a href="shop.php">Shop</a>
    <a href="portfolio.php">Portfolio</a>
    <a href="availability.php">Verfügbarkeit</a>
    <a href="crystals.php">Kristalle</a>
    <a href="contact.php">Kontakt</a>
  </div>
</nav>

<div class="page-wrap">
  <div class="page-badge">Prompt Generator</div>
  <h1>Cinematic Prompt<br>Generator</h1>
  <p class="page-sub">Erstelle professionelle KI-Video-Prompts in Sekunden. Wähle Genre, Stil und Details – der Generator baut den perfekten Prompt für deine Szene.</p>

  <!-- INPUTS -->
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
        <textarea id="scene" placeholder="z.B. Ein Detektiv betritt eine verlassene Lagerhalle im Regen ..."></textarea>
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
      <button class="btn btn-primary" onclick="generatePrompt()">✦ Prompt generieren</button>
      <button class="btn btn-ghost" onclick="clearPrompt()">Leeren</button>
    </div>
  </div>

  <!-- OUTPUT -->
  <div class="gen-card">
    <h2>Generierter Prompt</h2>
    <div class="output-area">
      <div class="output-box empty" id="output">Prompt erscheint hier nach der Generierung ...</div>
    </div>
    <div class="btn-row">
      <button class="btn btn-copy" onclick="copyPrompt()">📋 Prompt kopieren</button>
    </div>
  </div>

  <a href="scene-editor-test.html" class="back-link">← Zurück zum Hub</a>
</div>

<div class="copy-toast" id="toast">✓ Prompt kopiert!</div>

<script>
function generatePrompt(){
  const genre   = document.getElementById('genre').value;
  const cam     = document.getElementById('camstyle').value;
  const light   = document.getElementById('lighting').value;
  const color   = document.getElementById('color').value;
  const scene   = document.getElementById('scene').value.trim();
  const quality = document.getElementById('quality').value;
  const extra   = document.getElementById('extra').value.trim();

  const parts = [];
  if(genre)   parts.push(genre + ' film scene');
  if(scene)   parts.push(scene);
  if(cam)     parts.push(cam);
  if(light)   parts.push(light);
  if(color)   parts.push(color);
  if(quality) parts.push(quality);
  if(extra)   parts.push(extra);

  if(parts.length < 2){
    alert('Bitte mindestens 2 Parameter auswählen.');
    return;
  }

  const prompt = parts.join(', ') + ', award-winning visual effects, professional post-production';
  const el = document.getElementById('output');
  el.textContent = prompt;
  el.classList.remove('empty');
}

function copyPrompt(){
  const txt = document.getElementById('output').textContent;
  if(txt === 'Prompt erscheint hier nach der Generierung ...') return;
  navigator.clipboard.writeText(txt).then(()=>{
    const t = document.getElementById('toast');
    t.classList.add('show');
    setTimeout(()=>t.classList.remove('show'),2200);
  });
}

function clearPrompt(){
  ['genre','camstyle','lighting','color','quality'].forEach(id=>{
    document.getElementById(id).selectedIndex=0;
  });
  document.getElementById('scene').value='';
  document.getElementById('extra').value='';
  const el=document.getElementById('output');
  el.textContent='Prompt erscheint hier nach der Generierung ...';
  el.classList.add('empty');
}
</script>
</body>
</html>
