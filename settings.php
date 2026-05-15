<?php
declare(strict_types=1);

require_once 'includes/config.php';
$pageTitle = 'Einstellungen';
require_once 'includes/header.php';

// ── Session-Einstellungen lesen ────────────────────────────────────
$exportQuality = $_SESSION['export_quality'] ?? '720p';
$kieKeySet     = (getenv('KIE_AI_API_KEY') ?: ($_SERVER['KIE_AI_API_KEY'] ?? '')) !== '';

// ── POST: Einstellungen speichern ──────────────────────────────────
$saved   = false;
$saveErr = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $q = trim((string)($_POST['export_quality'] ?? '720p'));
    if (in_array($q, ['720p', '1080p'], true)) {
        $_SESSION['export_quality'] = $q;
        $exportQuality = $q;
        $saved = true;
    } else {
        $saveErr = 'Ungültige Qualitätsstufe.';
    }
}
?>

<div class="studio-page">

    <div class="studio-hero">
        <div>
            <h2 class="studio-hero__title">Einstellungen</h2>
            <p class="studio-hero__sub text-muted">
                Export-Qualität, API-Key-Status und Systeminfos.
            </p>
        </div>
    </div>

    <?php if ($saved): ?>
    <div class="alert alert-success mb-4">✓ Einstellungen gespeichert.</div>
    <?php elseif ($saveErr !== ''): ?>
    <div class="alert alert-danger mb-4">✗ <?= htmlspecialchars($saveErr) ?></div>
    <?php endif; ?>

    <!-- ── Export-Qualität ───────────────────────────────────────── -->
    <div class="card mb-6">
        <div class="card-header">
            <span class="card-title">🎬 Export-Qualität</span>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="form-group">
                    <label for="export_quality">Standard-Auflösung</label>
                    <select name="export_quality" id="export_quality">
                        <option value="720p"  <?= $exportQuality === '720p'  ? 'selected' : '' ?>>HD 720p — schnell · für Demo + Vorschau</option>
                        <option value="1080p" <?= $exportQuality === '1080p' ? 'selected' : '' ?>>Full HD 1080p — scharf · für finale Ausgabe</option>
                    </select>
                    <p class="text-muted text-sm mt-2">Diese Einstellung gilt für alle Exports in dieser Session.</p>
                </div>
                <button type="submit" class="btn btn-primary">Speichern</button>
            </form>
        </div>
    </div>

    <!-- ── KI / API-Key-Status ──────────────────────────────────── -->
    <div class="card mb-6">
        <div class="card-header">
            <span class="card-title">🔑 KI-Integration (Kie.ai)</span>
        </div>
        <div class="card-body">
            <div class="settings-row">
                <div>
                    <div class="settings-label">KIE_AI_API_KEY</div>
                    <div class="text-muted text-sm">Benötigt für KI-Bildgenerierung (Flux Kontext Pro/Max)</div>
                </div>
                <span class="badge <?= $kieKeySet ? 'badge-ok' : 'badge-warn' ?>">
                    <?= $kieKeySet ? '✓ Konfiguriert' : '✗ Nicht gesetzt' ?>
                </span>
            </div>
            <?php if (!$kieKeySet): ?>
            <div class="info-note mt-4">
                <strong>Key noch nicht gesetzt.</strong> Gehe zu
                <a href="api-key.php">API Key → Einstellungen</a> oder trage den Key direkt
                im Render-Dashboard unter <em>Environment Variables</em> als
                <code>KIE_AI_API_KEY</code> ein.
            </div>
            <?php endif; ?>
            <div class="mt-4">
                <a href="api-key.php" class="btn btn-secondary btn-sm">API-Key verwalten →</a>
            </div>
        </div>
    </div>

    <!-- ── System-Info ───────────────────────────────────────────── -->
    <div class="card mb-6">
        <div class="card-header">
            <span class="card-title">ℹ️ System</span>
        </div>
        <div class="card-body">
            <table class="settings-table">
                <tr><td>App-Version</td><td><code><?= htmlspecialchars(APP_VERSION) ?></code></td></tr>
                <tr><td>PHP-Version</td><td><code><?= phpversion() ?></code></td></tr>
                <tr><td>Session-ID</td><td><code><?= substr(session_id(), 0, 8) ?>…</code></td></tr>
                <tr><td>Export-Qualität (aktiv)</td><td><code><?= htmlspecialchars($exportQuality) ?></code></td></tr>
                <tr><td>Health-Check</td><td><a href="api/health.php" target="_blank" class="text-link">api/health.php →</a></td></tr>
            </table>
        </div>
    </div>

</div><!-- .studio-page -->

<style>
.mb-4 { margin-bottom: 16px; }
.mb-6 { margin-bottom: 24px; }
.mt-2 { margin-top: 8px; }
.mt-4 { margin-top: 16px; }
.alert { padding: 12px 16px; border-radius: var(--radius); font-size: 0.875rem; font-weight: 600; }
.alert-success { background: rgba(72,199,116,.15); color: #48c774; border: 1px solid rgba(72,199,116,.3); }
.alert-danger  { background: rgba(245,85,85,.12);  color: var(--text-danger, #ff5c7a); border: 1px solid rgba(245,85,85,.28); }
.info-note { background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: var(--radius); padding: 12px 16px; font-size: 0.85rem; color: var(--text-secondary); }
.info-note strong { color: var(--text-primary); }
.info-note a { color: var(--accent-blue); text-decoration: none; }
.info-note a:hover { text-decoration: underline; }
.info-note code { font-family: monospace; font-size: 0.8rem; color: var(--accent-orange, #f5843d); }
.settings-row { display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
.settings-label { font-size: 0.9rem; font-weight: 600; color: var(--text-primary); }
.badge { display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 999px; font-size: 0.75rem; font-weight: 700; }
.badge-ok   { background: rgba(72,199,116,.15); color: #48c774; border: 1px solid rgba(72,199,116,.3); }
.badge-warn { background: rgba(245,197,66,.12); color: var(--accent-yellow, #f5c542); border: 1px solid rgba(245,197,66,.3); }
.settings-table { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
.settings-table tr { border-bottom: 1px solid var(--border-color); }
.settings-table tr:last-child { border-bottom: none; }
.settings-table td { padding: 9px 4px; color: var(--text-secondary); }
.settings-table td:first-child { font-weight: 500; color: var(--text-primary); width: 180px; }
.settings-table code { font-family: monospace; font-size: 0.78rem; color: var(--accent-blue); background: var(--bg-elevated); padding: 2px 5px; border-radius: 4px; }
.text-link { color: var(--accent-blue); text-decoration: none; font-size: 0.875rem; }
.text-link:hover { text-decoration: underline; }
</style>

<?php require_once 'includes/footer.php'; ?>
