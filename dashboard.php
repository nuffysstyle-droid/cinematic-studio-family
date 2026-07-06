<?php
/**
 * dashboard.php — User Dashboard
 *
 * Zeigt: Plan-Info, Kristall-Balance, letzte Jobs, Transaktions-Verlauf.
 * Erfordert Login — Redirect zu login.php wenn nicht eingeloggt.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

$user = csf_auth_user();
if ($user === null) {
    header('Location: login.php?redirect=dashboard.php');
    exit;
}

// ── Kristall-Transaktionen (letzte 10) ───────────────────────────────────────
$txStmt = csf_db()->prepare("
    SELECT amount, action, job_id, note, created_at
    FROM   crystal_transactions
    WHERE  user_id = :uid
    ORDER  BY created_at DESC
    LIMIT  10
");
$txStmt->execute([':uid' => $user['id']]);
$transactions = $txStmt->fetchAll();

// ── Letzte Jobs aus dem Storage lesen ────────────────────────────────────────
$jobsDir  = CSF_STORAGE_ROOT . '/jobs';
$userJobs = [];
if (is_dir($jobsDir)) {
    $entries = glob($jobsDir . '/job_*/meta.json') ?: [];
    usort($entries, fn($a, $b) => filemtime($b) <=> filemtime($a));
    foreach (array_slice($entries, 0, 9) as $metaFile) {
        $meta = @json_decode((string)file_get_contents($metaFile), true);
        if (!is_array($meta)) continue;
        $userJobs[] = [
            'job_id'      => basename(dirname($metaFile)),
            'slot_count'  => count($meta['slots'] ?? []),
            'final_video' => $meta['final_video'] ?? null,
            'rendered_at' => $meta['rendered_at'] ?? null,
            'has_ai'      => !empty(array_filter($meta['slots'] ?? [], fn($s) => !empty($s['ai_generated']))),
        ];
    }
}

// Plan Meta
$planMeta = [
    'free'    => ['label' => 'Free',      'color' => '#888899', 'features' => ['720p Export · max. 15s', '3 Szenen-Slots', '3 KI-Bilder/Job', '48h Speicher']],
    'starter' => ['label' => 'Starter+',  'color' => '#1872ff', 'features' => ['1080p Export · max. 60s', 'Unbegrenzte Slots', 'Original-Audio', '90 Tage Speicher · +50 💎/Monat']],
    'pro'     => ['label' => 'Pro/Ultra', 'color' => '#e8c355', 'features' => ['4K Export · unbegrenzte Länge', 'KI-Video-Generierung', 'Auto Story Engine', '500 💎/Monat · API-Zugang']],
];
$plan = $planMeta[$user['plan']] ?? $planMeta['free'];
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard – Cinematic Vision Studio</title>
    <meta name="robots" content="noindex">
    <link rel="stylesheet" href="assets/fonts/fonts.css">
    <link rel="stylesheet" href="assets/css/cvs-core.css">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        :root{
            --bg:#020205;--surface:#0a0e1a;--surface2:#0f1526;
            --border:rgba(24,114,255,.18);--text:#edf2ff;--muted:rgba(237,242,255,.55);
            --primary:#1872ff;--primary-h:#003ee8;--blue-glow:#4da0ff;
            --gold:#e8c355;--gold-warm:#d4a93c;--ok:#4ade80;--err:#f87171;
            --radius:14px;--radius-sm:8px;--ease:cubic-bezier(.16,1,.3,1);
        }
        body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;}

        /* CVS-Header/Footer-Integration — cvs-core-Globals neutralisieren + fixe Nav freistellen (Mittelteil unverändert) */
        html,body{cursor:auto!important}
        body::before{display:none!important}
        #cursor,#cursor-ring{display:none!important}
        body{padding-top:96px}

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
        .reveal[data-d="1"]{transition-delay:.08s}.reveal[data-d="2"]{transition-delay:.16s}.reveal[data-d="3"]{transition-delay:.24s}
        @media(prefers-reduced-motion:reduce){.reveal{opacity:1;transform:none;transition:none}.cvs-aurora span{animation:none}}

        /* Layout */
        .page{max-width:1100px;margin:0 auto;padding:36px 20px 80px;position:relative;z-index:1;}
        .page-header{margin-bottom:28px;}
        .page-header h1{font-family:'Syne',sans-serif;font-size:1.7rem;font-weight:800;margin-bottom:6px;}
        .page-header p{color:var(--muted);font-size:.9rem;}

        /* Stats Row */
        .stats-row{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;margin-bottom:32px;}
        .stat-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:20px 22px;}
        .stat-label{font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--muted);margin-bottom:10px;}
        .stat-value{font-family:'Syne',sans-serif;font-size:1.9rem;font-weight:900;line-height:1;}
        .stat-sub{font-size:.78rem;color:var(--muted);margin-top:6px;}

        /* Section */
        .section{margin-bottom:36px;}
        .section-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;}
        .section-title{font-family:'Syne',sans-serif;font-size:1rem;font-weight:700;}
        .section-link{font-size:.82rem;color:var(--primary);text-decoration:none;font-weight:600;}
        .section-link:hover{text-decoration:underline;}

        /* Plan Box */
        .plan-box{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:22px 24px;display:flex;align-items:flex-start;justify-content:space-between;gap:20px;flex-wrap:wrap;}
        .plan-badge{display:inline-block;padding:3px 12px;border-radius:999px;font-size:.75rem;font-weight:800;text-transform:uppercase;letter-spacing:.05em;margin-bottom:12px;}
        .plan-features{list-style:none;color:var(--muted);font-size:.875rem;line-height:2;}
        .plan-features li::before{content:'✓  ';color:var(--ok);}
        .plan-actions{display:flex;flex-direction:column;gap:10px;min-width:160px;}
        .btn-upgrade{display:block;padding:11px 20px;background:linear-gradient(180deg,#fff7a8 0%,#ffe15a 18%,#ffc21f 48%,#f59a00 78%,#d97900 100%);color:#100700;font-family:'Syne',sans-serif;font-size:.875rem;font-weight:900;border-radius:var(--radius-sm);text-decoration:none;text-align:center;box-shadow:0 6px 20px rgba(232,169,59,.28),inset 0 1px 0 rgba(255,255,255,.5);transition:transform .25s var(--ease),box-shadow .25s var(--ease);}
        .btn-upgrade:hover{transform:translateY(-2px);box-shadow:0 12px 30px rgba(232,169,59,.45),inset 0 1px 0 rgba(255,255,255,.6);}
        .btn-secondary-sm{display:block;padding:10px 20px;border:1px solid var(--border);color:var(--muted);font-size:.82rem;font-weight:600;border-radius:var(--radius-sm);text-decoration:none;text-align:center;transition:all .15s;}
        .btn-secondary-sm:hover{border-color:rgba(255,255,255,.3);color:var(--text);}

        /* Jobs Grid */
        .jobs-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:14px;}
        .job-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:18px;transition:border-color .2s;}
        .job-card:hover{border-color:rgba(24,114,255,.4);}
        .job-id{font-size:.72rem;font-family:monospace;color:var(--muted);margin-bottom:10px;word-break:break-all;}
        .job-tags{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:14px;}
        .tag{font-size:.7rem;font-weight:700;padding:2px 8px;border-radius:999px;background:var(--surface2);color:var(--muted);border:1px solid var(--border);}
        .tag.ai{background:rgba(232,195,85,.14);color:var(--gold);border-color:rgba(232,195,85,.32);}
        .tag.done{background:rgba(74,222,128,.1);color:var(--ok);border-color:rgba(74,222,128,.3);}
        .job-actions{display:flex;gap:8px;}
        .job-btn{flex:1;padding:8px;text-align:center;text-decoration:none;border-radius:var(--radius-sm);font-size:.78rem;font-weight:700;border:1px solid var(--border);color:var(--muted);background:var(--surface2);transition:all .15s;}
        .job-btn:hover{border-color:var(--primary);color:var(--blue-glow);}
        .job-btn.primary{background:linear-gradient(180deg,#fff7a8 0%,#ffe15a 18%,#ffc21f 48%,#f59a00 78%,#d97900 100%);color:#100700;font-weight:900;border-color:transparent;box-shadow:0 4px 14px rgba(232,169,59,.28);}
        .job-btn.primary:hover{transform:translateY(-1px);color:#100700;box-shadow:0 8px 20px rgba(232,169,59,.42);}
        .no-jobs{text-align:center;padding:44px 20px;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);color:var(--muted);}
        .no-jobs-icon{font-size:2.5rem;margin-bottom:14px;}
        .no-jobs a{color:var(--primary);text-decoration:none;font-weight:700;}

        /* Transactions */
        .tx-list{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;}
        .tx-item{display:flex;align-items:center;justify-content:space-between;padding:13px 20px;border-bottom:1px solid var(--border);}
        .tx-item:last-child{border-bottom:none;}
        .tx-action{font-size:.875rem;font-weight:600;}
        .tx-date{font-size:.72rem;color:var(--muted);margin-top:2px;}
        .tx-amt{font-size:1rem;font-weight:800;}
        .tx-amt.pos{color:var(--ok);}
        .tx-amt.neg{color:var(--err);}
        .tx-empty{padding:24px;text-align:center;color:var(--muted);font-size:.875rem;}

        /* Quick links */
        .quick-links{display:flex;flex-wrap:wrap;gap:10px;margin-bottom:32px;}
        .quick-link{display:flex;align-items:center;gap:8px;padding:10px 16px;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-sm);text-decoration:none;color:var(--muted);font-size:.875rem;font-weight:600;transition:all .15s;}
        .quick-link:hover{border-color:rgba(24,114,255,.4);color:var(--text);}

        @media(max-width:600px){
            .stats-row{grid-template-columns:1fr 1fr;}
            .plan-actions{min-width:100%;flex-direction:row;flex-wrap:wrap;}
            .page{padding:20px 14px 60px;}
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
    <a href="https://cinematic-vision-studio.de/crystals.html" class="wallet-pill">💎 <?= htmlspecialchars((string)$user['crystals_balance']) ?></a>
    <form method="POST" action="/api/auth/logout.php" style="display:inline;margin:0">
      <input type="hidden" name="redirect" value="login.php">
      <button type="submit" class="nav-btn-ghost" style="cursor:pointer;font-family:'Syne',sans-serif">Logout</button>
    </form>
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
  <a href="profile.php" class="mob-link">⚙️ Profil</a>
  <form method="POST" action="/api/auth/logout.php" style="margin:0"><input type="hidden" name="redirect" value="login.php"><button type="submit" class="mob-cta" style="width:calc(100% - 40px);cursor:pointer;font-family:'Syne',sans-serif">Logout</button></form>
</div>

<div class="page">

    <div class="page-header">
        <h1>Dashboard</h1>
        <p><?= htmlspecialchars($user['email']) ?> · <?= htmlspecialchars($plan['label']) ?>-Plan</p>
    </div>

    <!-- Quick Links -->
    <div class="quick-links reveal">
        <a href="studio-demo.php" class="quick-link">🎬 Studio öffnen</a>
        <a href="profile.php" class="quick-link">⚙️ Profil & Passwort</a>
        <a href="https://cinematic-vision-studio.de/crystals.html" class="quick-link">💎 Kristalle kaufen</a>
        <a href="https://cinematic-vision-studio.de/academy.html" class="quick-link">🎓 Academy</a>
    </div>

    <!-- Stats -->
    <div class="stats-row reveal">
        <div class="stat-card">
            <div class="stat-label">Kristalle</div>
            <div class="stat-value" style="color:var(--gold)">💎 <?= number_format($user['crystals_balance']) ?></div>
            <div class="stat-sub">Verfügbares Guthaben</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Plan</div>
            <div class="stat-value" style="font-size:1.3rem;padding-top:4px;">
                <span class="plan-badge" style="background:<?= htmlspecialchars($plan['color']) ?>22;color:<?= htmlspecialchars($plan['color']) ?>;border:1px solid <?= htmlspecialchars($plan['color']) ?>44">
                    <?= htmlspecialchars($plan['label']) ?>
                </span>
            </div>
            <div class="stat-sub"><?= $user['plan'] === 'free' ? '720p · 15s · Free' : ($user['plan'] === 'starter' ? '1080p · 60s · Audio' : '4K · KI-Video · API') ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Server-Jobs</div>
            <div class="stat-value"><?= count($userJobs) ?></div>
            <div class="stat-sub">Letzte Projekte gefunden</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Transaktionen</div>
            <div class="stat-value"><?= count($transactions) ?></div>
            <div class="stat-sub">Letzte Kristall-Bewegungen</div>
        </div>
    </div>

    <!-- Plan Info -->
    <div class="section reveal">
        <div class="section-header"><div class="section-title">Dein Plan</div></div>
        <div class="plan-box">
            <div>
                <span class="plan-badge" style="background:<?= htmlspecialchars($plan['color']) ?>22;color:<?= htmlspecialchars($plan['color']) ?>;border:1px solid <?= htmlspecialchars($plan['color']) ?>44">
                    <?= htmlspecialchars($plan['label']) ?>
                </span>
                <ul class="plan-features">
                    <?php foreach ($plan['features'] as $f): ?>
                        <li><?= htmlspecialchars($f) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="plan-actions">
                <?php if ($user['plan'] === 'free'): ?>
                    <a href="https://cinematic-vision-studio.de/crystals.html" class="btn-upgrade">⬆️ Auf Starter+ upgraden</a>
                <?php elseif ($user['plan'] === 'starter'): ?>
                    <a href="https://cinematic-vision-studio.de/crystals.html" class="btn-upgrade">⬆️ Auf Pro upgraden</a>
                <?php else: ?>
                    <span style="color:var(--ok);font-weight:700;font-size:.9rem">✓ Pro aktiv — Bester Plan</span>
                <?php endif; ?>
                <a href="profile.php" class="btn-secondary-sm">⚙️ Profil & Einstellungen</a>
            </div>
        </div>
    </div>

    <!-- Jobs -->
    <div class="section reveal">
        <div class="section-header">
            <div class="section-title">Letzte Server-Projekte</div>
            <a href="studio-demo.php" class="section-link">+ Neues Projekt</a>
        </div>
        <?php if (empty($userJobs)): ?>
            <div class="no-jobs">
                <div class="no-jobs-icon">🎬</div>
                <p style="margin-bottom:8px;font-weight:600">Noch keine Projekte auf dem Server</p>
                <p><a href="studio-demo.php">Erstes Video hochladen →</a></p>
            </div>
        <?php else: ?>
            <div class="jobs-grid">
                <?php foreach ($userJobs as $job): ?>
                    <div class="job-card">
                        <div class="job-id"><?= htmlspecialchars($job['job_id']) ?></div>
                        <div class="job-tags">
                            <span class="tag"><?= $job['slot_count'] ?> Slots</span>
                            <?php if ($job['has_ai']): ?><span class="tag ai">✨ KI</span><?php endif; ?>
                            <?php if ($job['final_video']): ?><span class="tag done">✓ Gerendert</span><?php endif; ?>
                        </div>
                        <div class="job-actions">
                            <a href="studio-demo.php?job_id=<?= urlencode($job['job_id']) ?>" class="job-btn">Öffnen</a>
                            <?php if ($job['final_video']): ?>
                                <a href="/storage/exports/<?= urlencode(basename($job['final_video'])) ?>" class="job-btn primary" download>↓ Download</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Transaktionen -->
    <div class="section reveal">
        <div class="section-header">
            <div class="section-title">Kristall-Verlauf</div>
            <a href="https://cinematic-vision-studio.de/crystals.html" class="section-link">💎 Kristalle kaufen</a>
        </div>
        <div class="tx-list">
            <?php if (empty($transactions)): ?>
                <div class="tx-empty">Noch keine Kristall-Transaktionen.</div>
            <?php else: ?>
                <?php foreach ($transactions as $tx): ?>
                    <div class="tx-item">
                        <div>
                            <div class="tx-action"><?= htmlspecialchars($tx['action']) ?></div>
                            <div class="tx-date"><?= htmlspecialchars(substr($tx['created_at'], 0, 16)) ?></div>
                        </div>
                        <div class="tx-amt <?= (int)$tx['amount'] >= 0 ? 'pos' : 'neg' ?>">
                            <?= (int)$tx['amount'] > 0 ? '+' : '' ?><?= (int)$tx['amount'] ?> 💎
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

</div>

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
</body>
</html>
