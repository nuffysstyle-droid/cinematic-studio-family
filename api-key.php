<?php
require_once 'includes/config.php';
$pageTitle = 'API verbinden';
$keyActive = !empty($_SESSION['api_key']);
require_once 'includes/header.php';
?>

<div class="apikey-page">

    <!-- ── Headline ───────────────────────────────────────────── -->
    <div class="apikey-headline">
        <h2>API verbinden &amp; loslegen</h2>
        <p class="text-muted">
            Damit du Bilder und Videos generieren kannst, brauchst du einen eigenen API-Key.
            Dieser verbindet dein Konto direkt mit der KI-Plattform.
        </p>
    </div>

    <!-- ── Status-Banner ──────────────────────────────────────── -->
    <div id="key-status" class="key-status <?= $keyActive ? 'key-status--connected' : 'key-status--empty' ?>" aria-live="polite">
        <?php if ($keyActive): ?>
            <span class="key-status__dot"></span>
            <span>API-Key verbunden — diese Session ist aktiv.</span>
        <?php else: ?>
            <span class="key-status__dot"></span>
            <span>Kein API-Key gesetzt. Bitte unten verbinden.</span>
        <?php endif; ?>
    </div>

    <div class="apikey-grid">

        <!-- ── Linke Spalte: Schritte + Buttons ───────────────── -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">So verbindest du deinen Key</span>
                <button class="btn btn-secondary btn-sm" data-modal-open="guide-modal">
                    Anleitung anzeigen
                </button>
            </div>
            <div class="card-body">
                <ol class="apikey-steps">
                    <li>Öffne die Plattform über <strong>„API holen"</strong></li>
                    <li>Registriere dich oder melde dich an</li>
                    <li>Kaufe Credits über <strong>„Credits kaufen"</strong></li>
                    <li>Navigiere zum API-Key Bereich</li>
                    <li>Erstelle einen neuen API-Key</li>
                    <li>Kopiere den Key</li>
                    <li>Füge ihn unten ein</li>
                    <li>Klicke auf <strong>„Verbindung testen"</strong></li>
                </ol>

                <div class="apikey-actions flex gap-3 mt-6">
                    <a href="<?= API_PROVIDER_LINK ?>" target="_blank" rel="noopener" class="btn btn-primary">
                        API holen
                    </a>
                    <a href="<?= API_PROVIDER_CREDITS_LINK ?>" target="_blank" rel="noopener" class="btn btn-secondary">
                        Credits kaufen
                    </a>
                </div>
            </div>
        </div>

        <!-- ── Rechte Spalte: Key-Eingabe ─────────────────────── -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">API-Key eingeben</span>
            </div>
            <div class="card-body">

                <div class="form-group">
                    <label for="api-key-input">Dein API-Key</label>
                    <div class="apikey-input-wrapper">
                        <input
                            type="password"
                            id="api-key-input"
                            placeholder="sk-••••••••••••••••••••"
                            autocomplete="off"
                            spellcheck="false"
                        >
                        <button
                            type="button"
                            id="toggle-visibility"
                            class="apikey-toggle"
                            aria-label="Key anzeigen/verbergen"
                            title="Anzeigen / Verbergen"
                        >👁</button>
                    </div>
                </div>

                <button id="btn-test-key" class="btn btn-primary w-full mt-2">
                    Verbindung testen
                </button>

                <?php if ($keyActive): ?>
                <button id="btn-clear-key" class="btn btn-danger w-full mt-2">
                    Key aus Session entfernen
                </button>
                <?php endif; ?>

                <p class="apikey-security-note mt-4">
                    🔒 <strong>Sicherheitshinweis:</strong>
                    Dein API-Key wird nicht dauerhaft gespeichert und nur temporär
                    für diese Session verwendet. Er wird gelöscht, sobald du den
                    Tab schließt.
                </p>
            </div>
        </div>

    </div><!-- .apikey-grid -->

</div><!-- .apikey-page -->


<!-- ── Anleitung Modal ────────────────────────────────────────── -->
<div id="guide-modal" class="modal" role="dialog" aria-modal="true" aria-labelledby="guide-modal-title">
    <div class="modal__backdrop"></div>
    <div class="modal__box">
        <div class="modal__header">
            <h3 id="guide-modal-title">API-Key Anleitung</h3>
            <button class="modal__close" data-modal-close="guide-modal" aria-label="Schließen">✕</button>
        </div>
        <div class="modal__body">
            <ol class="apikey-steps">
                <li>Klicke auf <strong>„API holen"</strong> — die Plattform öffnet im neuen Tab</li>
                <li>Registriere dich oder melde dich mit deinem Konto an</li>
                <li>Navigiere zu <strong>„Credits"</strong> und lade dein Guthaben auf</li>
                <li>Öffne den Bereich <strong>„API Keys"</strong> in deinen Account-Einstellungen</li>
                <li>Klicke auf <strong>„Create new API Key"</strong></li>
                <li>Kopiere den generierten Key vollständig</li>
                <li>Kehre hierher zurück und füge ihn in das Eingabefeld ein</li>
                <li>Klicke auf <strong>„Verbindung testen"</strong> — fertig!</li>
            </ol>
            <p class="text-muted text-sm mt-4">
                Der Key wird ausschließlich für diese Browser-Session gespeichert.
                Beim Schließen des Tabs wird er automatisch gelöscht.
            </p>
        </div>
        <div class="modal__footer">
            <button class="btn btn-primary" data-modal-close="guide-modal">Verstanden</button>
        </div>
    </div>
</div>


<style>
.apikey-page          { max-width: 900px; }
.apikey-headline      { margin-bottom: 24px; }
.apikey-headline h2   { font-size: 1.5rem; font-weight: 700; margin-bottom: 8px; }

.key-status {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 16px;
    border-radius: var(--radius);
    border: 1px solid var(--border-color);
    margin-bottom: 24px;
    font-size: 0.875rem;
    font-weight: 500;
}
.key-status--connected { border-color: #4ade80; color: #4ade80; background: rgba(74,222,128,0.06); }
.key-status--empty     { border-color: var(--border-color); color: var(--text-secondary); }
.key-status--checking  { border-color: var(--accent-blue); color: var(--accent-blue); background: var(--accent-blue-glow); }
.key-status--error     { border-color: #f56565; color: #f56565; background: rgba(245,101,101,0.06); }

.key-status__dot {
    width: 8px; height: 8px;
    border-radius: 50%;
    background: currentColor;
    flex-shrink: 0;
}

.apikey-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
}

.apikey-steps {
    list-style: decimal;
    padding-left: 20px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    color: var(--text-secondary);
    font-size: 0.875rem;
    line-height: 1.5;
}
.apikey-steps li strong { color: var(--text-primary); }

.apikey-input-wrapper {
    position: relative;
    display: flex;
}
.apikey-input-wrapper input {
    padding-right: 44px;
    font-family: monospace;
    letter-spacing: 0.05em;
}
.apikey-toggle {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    font-size: 1rem;
    opacity: 0.5;
    transition: opacity var(--transition);
}
.apikey-toggle:hover { opacity: 1; }

.apikey-security-note {
    font-size: 0.8rem;
    color: var(--text-muted);
    border-top: 1px solid var(--border-color);
    padding-top: 12px;
    line-height: 1.5;
}
.apikey-security-note strong { color: var(--text-secondary); }

/* Modal */
.modal { display: none; position: fixed; inset: 0; z-index: 1000; align-items: center; justify-content: center; }
.modal.modal--open { display: flex; }
.modal__backdrop { position: absolute; inset: 0; background: rgba(0,0,0,0.7); }
.modal__box {
    position: relative; z-index: 1;
    background: var(--bg-panel);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    width: 90%; max-width: 520px;
    box-shadow: var(--shadow);
}
.modal__header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 20px 24px 16px;
    border-bottom: 1px solid var(--border-color);
}
.modal__header h3 { font-size: 1rem; font-weight: 600; }
.modal__close { background: none; border: none; color: var(--text-secondary); cursor: pointer; font-size: 1rem; padding: 4px 8px; }
.modal__close:hover { color: var(--text-primary); }
.modal__body  { padding: 20px 24px; }
.modal__footer { padding: 16px 24px 20px; display: flex; justify-content: flex-end; }

@media (max-width: 640px) {
    .apikey-grid { grid-template-columns: 1fr; }
}
</style>

<script>
(function () {
    const input      = document.getElementById('api-key-input');
    const btnTest    = document.getElementById('btn-test-key');
    const btnClear   = document.getElementById('btn-clear-key');
    const btnToggle  = document.getElementById('toggle-visibility');
    const statusEl   = document.getElementById('key-status');
    const statusDot  = statusEl?.querySelector('.key-status__dot');
    const statusText = statusEl?.querySelector('span:last-child');

    // Sichtbarkeit togglen
    btnToggle?.addEventListener('click', () => {
        input.type = input.type === 'password' ? 'text' : 'password';
    });

    function setStatus(state, message) {
        statusEl.className = 'key-status key-status--' + state;
        if (statusText) statusText.textContent = message;
    }

    // Verbindung testen
    btnTest?.addEventListener('click', async () => {
        const key = input.value.trim();
        if (!key) { Toast.warning('Bitte einen API-Key eingeben.'); return; }

        btnTest.disabled = true;
        btnTest.textContent = 'Wird geprüft …';
        setStatus('checking', 'Verbindung wird geprüft …');

        try {
            const res  = await fetch('api/test-key.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ api_key: key }),
            });
            const data = await res.json();

            if (data.success) {
                setStatus('connected', 'API-Key verbunden — diese Session ist aktiv.');
                Toast.success('API-Key erfolgreich gespeichert.');
                input.value = '';
                // Clear-Button einblenden
                if (!btnClear) {
                    const b = document.createElement('button');
                    b.id = 'btn-clear-key';
                    b.className = 'btn btn-danger w-full mt-2';
                    b.textContent = 'Key aus Session entfernen';
                    btnTest.insertAdjacentElement('afterend', b);
                    b.addEventListener('click', clearKey);
                }
            } else {
                setStatus('error', 'Fehler: ' + data.error);
                Toast.error(data.error);
            }
        } catch {
            setStatus('error', 'Netzwerkfehler — bitte versuche es erneut.');
            Toast.error('Verbindungsfehler.');
        } finally {
            btnTest.disabled = false;
            btnTest.textContent = 'Verbindung testen';
        }
    });

    // Key aus Session entfernen
    function clearKey() {
        fetch('api/test-key.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ api_key: '' }),
        });
        setStatus('empty', 'Kein API-Key gesetzt. Bitte unten verbinden.');
        document.getElementById('btn-clear-key')?.remove();
        Toast.info('API-Key aus Session entfernt.');
    }
    btnClear?.addEventListener('click', clearKey);

    // Enter im Input = Test auslösen
    input?.addEventListener('keydown', e => {
        if (e.key === 'Enter') btnTest?.click();
    });
})();
</script>

<?php require_once 'includes/footer.php'; ?>
