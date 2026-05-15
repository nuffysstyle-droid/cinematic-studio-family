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
    'starter' => ['label' => 'Starter+',  'color' => '#3b82f6', 'features' => ['1080p Export · max. 60s', 'Unbegrenzte Slots', 'Original-Audio', '90 Tage Speicher · +50 💎/Monat']],
    'pro'     => ['label' => 'Pro/Ultra', 'color' => '#9333ea', 'features' => ['4K Export · unbegrenzte Länge', 'KI-Video-Generierung', 'Auto Story Engine', '500 💎/Monat · API-Zugang']],
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
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        :root{
            --bg:#0a0a0f;--surface:#13131a;--surface2:#1c1c26;
            --border:#2a2a3a;--text:#e8e8f0;--muted:#888899;
            --primary:#7c3aed;--gold:#f59e0b;--ok:#4ade80;--err:#f87171;
            --radius:14px;--radius-sm:8px;
        }
        body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;}

        /* Nav */
        .nav{position:sticky;top:0;z-index:50;display:flex;align-items:center;justify-content:space-between;padding:14px 28px;background:rgba(10,10,15,.93);backdrop-filter:blur(20px);border-bottom:1px solid var(--border);}
        .nav-logo{text-decoration:none;font-size:1rem;font-weight:900;background:linear-gradient(135deg,#f59e0b,#ff8c00);-webkit-background-clip:text;-webkit-text-fill-color:transparent;}
        .nav-links{display:flex;align-items:center;gap:4px;}
        .nav-link{color:var(--muted);text-decoration:none;font-size:13px;font-weight:600;padding:7px 12px;border-radius:8px;transition:color .15s,background .15s;}
        .nav-link:hover,.nav-link.active{color:var(--text);background:rgba(255,255,255,.07);}
        .nav-right{display:flex;align-items:center;gap:10px;}
        .wallet-pill{display:flex;align-items:center;gap:6px;background:rgba(245,158,11,.1);border:1px solid rgba(245,158,11,.3);border-radius:999px;padding:6px 14px;font-size:13px;font-weight:800;color:var(--gold);text-decoration:none;}
        .btn-nav{background:none;border:1px solid var(--border);color:var(--muted);font-size:13px;font-weight:600;padding:7px 13px;border-radius:8px;cursor:pointer;font-family:inherit;text-decoration:none;display:inline-block;transition:color .15s,border-color .15s;}
        .btn-nav:hover{color:var(--err);border-color:rgba(248,113,113,.4);}
        @media(max-width:640px){.nav{padding:12px 16px;}.nav-links{display:none;}}

        /* Layout */
        .page{max-width:1100px;margin:0 auto;padding:36px 20px 80px;}
        .page-header{margin-bottom:28px;}
        .page-header h1{font-size:1.7rem;font-weight:800;margin-bottom:6px;}
        .page-header p{color:var(--muted);font-size:.9rem;}

        /* Stats Row */
        .stats-row{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;margin-bottom:32px;}
        .stat-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:20px 22px;}
        .stat-label{font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--muted);margin-bottom:10px;}
        .stat-value{font-size:1.9rem;font-weight:900;line-height:1;}
        .stat-sub{font-size:.78rem;color:var(--muted);margin-top:6px;}

        /* Section */
        .section{margin-bottom:36px;}
        .section-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;}
        .section-title{font-size:1rem;font-weight:700;}
        .section-link{font-size:.82rem;color:var(--primary);text-decoration:none;font-weight:600;}
        .section-link:hover{text-decoration:underline;}

        /* Plan Box */
        .plan-box{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:22px 24px;display:flex;align-items:flex-start;justify-content:space-between;gap:20px;flex-wrap:wrap;}
        .plan-badge{display:inline-block;padding:3px 12px;border-radius:999px;font-size:.75rem;font-weight:800;text-transform:uppercase;letter-spacing:.05em;margin-bottom:12px;}
        .plan-features{list-style:none;color:var(--muted);font-size:.875rem;line-height:2;}
        .plan-features li::before{content:'✓  ';color:var(--ok);}
        .plan-actions{display:flex;flex-direction:column;gap:10px;min-width:160px;}
        .btn-upgrade{display:block;padding:11px 20px;background:linear-gradient(135deg,var(--primary),#a855f7);color:#fff;font-size:.875rem;font-weight:700;border-radius:var(--radius-sm);text-decoration:none;text-align:center;}
        .btn-upgrade:hover{opacity:.9;}
        .btn-secondary-sm{display:block;padding:10px 20px;border:1px solid var(--border);color:var(--muted);font-size:.82rem;font-weight:600;border-radius:var(--radius-sm);text-decoration:none;text-align:center;transition:all .15s;}
        .btn-secondary-sm:hover{border-color:rgba(255,255,255,.3);color:var(--text);}

        /* Jobs Grid */
        .jobs-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:14px;}
        .job-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:18px;transition:border-color .2s;}
        .job-card:hover{border-color:rgba(124,58,237,.4);}
        .job-id{font-size:.72rem;font-family:monospace;color:var(--muted);margin-bottom:10px;word-break:break-all;}
        .job-tags{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:14px;}
        .tag{font-size:.7rem;font-weight:700;padding:2px 8px;border-radius:999px;background:var(--surface2);color:var(--muted);border:1px solid var(--border);}
        .tag.ai{background:rgba(147,51,234,.15);color:#c084fc;border-color:rgba(147,51,234,.3);}
        .tag.done{background:rgba(74,222,128,.1);color:var(--ok);border-color:rgba(74,222,128,.3);}
        .job-actions{display:flex;gap:8px;}
        .job-btn{flex:1;padding:8px;text-align:center;text-decoration:none;border-radius:var(--radius-sm);font-size:.78rem;font-weight:700;border:1px solid var(--border);color:var(--muted);background:var(--surface2);transition:all .15s;}
        .job-btn:hover{border-color:var(--primary);color:var(--primary);}
        .job-btn.primary{background:linear-gradient(135deg,var(--primary),#a855f7);color:#fff;border-color:transparent;}
        .job-btn.primary:hover{opacity:.9;}
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
        .quick-link:hover{border-color:rgba(124,58,237,.4);color:var(--text);}

        @media(max-width:600px){
            .stats-row{grid-template-columns:1fr 1fr;}
            .plan-actions{min-width:100%;flex-direction:row;flex-wrap:wrap;}
            .page{padding:20px 14px 60px;}
        }
    </style>
</head>
<body>

<nav class="nav">
    <a href="studio-demo.php" class="nav-logo">🎬 CVS</a>
    <div class="nav-links">
        <a href="studio-demo.php" class="nav-link">Studio</a>
        <a href="dashboard.php" class="nav-link active">Dashboard</a>
        <a href="profile.php" class="nav-link">Profil</a>
        <a href="https://cinematic-vision-studio.de/crystals.html" class="nav-link">Pläne & Preise</a>
    </div>
    <div class="nav-right">
        <a href="https://cinematic-vision-studio.de/crystals.html" class="wallet-pill">💎 <?= htmlspecialchars((string)$user['crystals_balance']) ?></a>
        <form method="POST" action="/api/auth/logout.php" style="display:inline">
            <input type="hidden" name="redirect" value="login.php">
            <button type="submit" class="btn-nav">Logout</button>
        </form>
    </div>
</nav>

<div class="page">

    <div class="page-header">
        <h1>Dashboard</h1>
        <p><?= htmlspecialchars($user['email']) ?> · <?= htmlspecialchars($plan['label']) ?>-Plan</p>
    </div>

    <!-- Quick Links -->
    <div class="quick-links">
        <a href="studio-demo.php" class="quick-link">🎬 Studio öffnen</a>
        <a href="profile.php" class="quick-link">⚙️ Profil & Passwort</a>
        <a href="https://cinematic-vision-studio.de/crystals.html" class="quick-link">💎 Kristalle kaufen</a>
        <a href="https://cinematic-vision-studio.de/academy.html" class="quick-link">🎓 Academy</a>
    </div>

    <!-- Stats -->
    <div class="stats-row">
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
    <div class="section">
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
    <div class="section">
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
    <div class="section">
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
</body>
</html>
