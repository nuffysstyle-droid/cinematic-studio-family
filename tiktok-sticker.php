<?php
require_once 'includes/config.php';
require_once 'includes/prompt-engine.php';
$pageTitle = 'TikTok Sticker Studio';
$extraJs   = ['editor.js', 'upload.js'];
require_once 'includes/header.php';
?>

<div class="studio-page">

    <!-- ── Hero ─────────────────────────────────────────────────── -->
    <div class="studio-hero">
        <div>
            <h2 class="studio-hero__title">TikTok Sticker Studio</h2>
            <p class="studio-hero__sub text-muted">
                Individuelle Sticker für TikTok LIVE, Streams und Creator Content.
            </p>
        </div>
        <a href="tiktok-studio.php" class="btn btn-secondary btn-sm">← TikTok Studio</a>
    </div>

    <!-- ── Guidance Bar ──────────────────────────────────────────── -->
    <div class="guidance-bar">
        <div class="guidance-tip">
            <strong>✨ Einfachheit gewinnt:</strong>
            Klare, einfache Formen wirken auf TikTok besser als komplexe Details.
        </div>
        <div class="guidance-tip">
            <strong>💡 Sichtbarkeit:</strong>
            Kontraste und Glow-Effekte sorgen für bessere Sichtbarkeit im Stream-Overlay.
        </div>
        <div class="guidance-tip">
            <strong>✏️ Text-Sticker:</strong>
            Kurz und klar — maximal 2–3 Wörter für beste Lesbarkeit.
        </div>
    </div>

    <!-- ── Kategorie-Auswahl ──────────────────────────────────────── -->
    <section class="sk-section">
        <h3 class="sk-section__title">Sticker Kategorie wählen</h3>
        <div class="sk-categories">

            <button class="sk-cat-card" data-type="emoji" aria-pressed="false">
                <span class="sk-cat-card__icon">😄</span>
                <div class="sk-cat-card__name">Emoji Sticker</div>
                <div class="sk-cat-card__desc text-muted">Animierte Custom-Emojis &amp; Reaktionen</div>
                <span class="sk-cat-card__badge">LIVE</span>
            </button>

            <button class="sk-cat-card" data-type="text" aria-pressed="false">
                <span class="sk-cat-card__icon">✏️</span>
                <div class="sk-cat-card__name">Text Sticker</div>
                <div class="sk-cat-card__desc text-muted">Name, Slogan oder Custom Text</div>
                <span class="sk-cat-card__badge">Creator</span>
            </button>

            <button class="sk-cat-card" data-type="logo" aria-pressed="false">
                <span class="sk-cat-card__icon">🏷️</span>
                <div class="sk-cat-card__name">Logo Sticker</div>
                <div class="sk-cat-card__desc text-muted">Dein Logo als animierter Sticker</div>
                <span class="sk-cat-card__badge">Branding</span>
            </button>

            <button class="sk-cat-card" data-type="reaction" aria-pressed="false">
                <span class="sk-cat-card__icon">⚡</span>
                <div class="sk-cat-card__name">Reaction Sticker</div>
                <div class="sk-cat-card__desc text-muted">Für Likes, Gifts &amp; Live-Events</div>
                <span class="sk-cat-card__badge">Reaction</span>
            </button>

            <button class="sk-cat-card" data-type="custom" aria-pressed="false">
                <span class="sk-cat-card__icon">⚙️</span>
                <div class="sk-cat-card__name">Custom Sticker</div>
                <div class="sk-cat-card__desc text-muted">Individuelle Lösung nach Wunsch</div>
                <span class="sk-cat-card__badge">On Request</span>
            </button>

        </div>
    </section>

    <!-- ── Showroom ───────────────────────────────────────────────── -->
    <section class="sk-section">
        <div class="sk-section__header">
            <h3 class="sk-section__title">Sticker Showroom</h3>
            <span class="text-muted text-sm">Beispiele — dein Sticker wird individuell erstellt</span>
        </div>
        <div class="sk-showroom">

            <div class="sk-demo-card sk-demo--neon">
                <div class="sk-demo-card__preview">
                    <span class="sk-demo-card__symbol">⚡</span>
                </div>
                <div class="sk-demo-card__label">Neon</div>
                <div class="sk-demo-card__style">Emoji Sticker</div>
            </div>

            <div class="sk-demo-card sk-demo--glow">
                <div class="sk-demo-card__preview">
                    <span class="sk-demo-card__symbol">★</span>
                </div>
                <div class="sk-demo-card__label">Glow</div>
                <div class="sk-demo-card__style">Reaction Sticker</div>
            </div>

            <div class="sk-demo-card sk-demo--gold">
                <div class="sk-demo-card__preview">
                    <span class="sk-demo-card__symbol">👑</span>
                </div>
                <div class="sk-demo-card__label">Gold</div>
                <div class="sk-demo-card__style">Logo Sticker</div>
            </div>

            <div class="sk-demo-card sk-demo--fire">
                <div class="sk-demo-card__preview">
                    <span class="sk-demo-card__symbol">🔥</span>
                </div>
                <div class="sk-demo-card__label">Fire</div>
                <div class="sk-demo-card__style">Reaction Sticker</div>
            </div>

            <div class="sk-demo-card sk-demo--minimal">
                <div class="sk-demo-card__preview">
                    <span class="sk-demo-card__symbol">◆</span>
                </div>
                <div class="sk-demo-card__label">Minimal</div>
                <div class="sk-demo-card__style">Text Sticker</div>
            </div>

            <div class="sk-demo-card sk-demo--cartoon">
                <div class="sk-demo-card__preview">
                    <span class="sk-demo-card__symbol">🎨</span>
                </div>
                <div class="sk-demo-card__label">Cartoon</div>
                <div class="sk-demo-card__style">Custom Sticker</div>
            </div>

            <div class="sk-demo-card sk-demo--neon">
                <div class="sk-demo-card__preview">
                    <span class="sk-demo-card__symbol">💜</span>
                </div>
                <div class="sk-demo-card__label">Neon</div>
                <div class="sk-demo-card__style">Emoji Sticker</div>
            </div>

            <div class="sk-demo-card sk-demo--gold">
                <div class="sk-demo-card__preview">
                    <span class="sk-demo-card__symbol">✨</span>
                </div>
                <div class="sk-demo-card__label">Gold</div>
                <div class="sk-demo-card__style">Reaction Sticker</div>
            </div>

        </div><!-- .sk-showroom -->
    </section>

    <!-- ── Studio Grid ───────────────────────────────────────────── -->
    <div class="studio-grid">

        <!-- ── Linke Spalte: Formular ────────────────────────────── -->
        <div class="studio-col-input">

            <!-- Basis-Felder -->
            <div class="card mb-4">
                <div class="card-header">
                    <span class="card-title">Anfrage Details</span>
                    <span id="selected-type-badge" class="sk-type-badge" hidden></span>
                </div>
                <div class="card-body">

                    <div class="form-group">
                        <label for="field-type">
                            Sticker Typ <span class="sk-required">*</span>
                        </label>
                        <select id="field-type">
                            <option value="">— Kategorie oben wählen oder hier —</option>
                            <option value="emoji">😄 Emoji Sticker</option>
                            <option value="text">✏️ Text Sticker</option>
                            <option value="logo">🏷️ Logo Sticker</option>
                            <option value="reaction">⚡ Reaction Sticker</option>
                            <option value="custom">⚙️ Custom Sticker</option>
                        </select>
                        <span id="type-error" class="sk-field-error" hidden>Bitte einen Typ wählen.</span>
                    </div>

                    <div class="form-group">
                        <label for="field-description">
                            Beschreibung / Wunsch <span class="sk-required">*</span>
                        </label>
                        <textarea
                            id="field-description"
                            rows="4"
                            placeholder="z.B. Neon-Herz mit Glitzer-Effekt in lila/pink, animiert, für TikTok LIVE …"
                            maxlength="600"
                        ></textarea>
                        <span id="desc-counter" class="text-sm text-muted" style="text-align:right;margin-top:4px;display:block;"></span>
                        <span id="desc-error" class="sk-field-error" hidden>Bitte eine Beschreibung eingeben.</span>
                    </div>

                    <!-- Text (für Text- / Name-Sticker) -->
                    <div id="text-field-section" class="form-group" hidden>
                        <label for="field-text">
                            Sticker-Text <span class="text-muted" style="font-weight:400;">(optional)</span>
                        </label>
                        <input
                            type="text"
                            id="field-text"
                            placeholder="z.B. Boss · Live · Name …"
                            maxlength="40"
                        >
                        <span class="text-muted text-sm" style="margin-top:4px;display:block;">Kurz halten — max. 40 Zeichen</span>
                    </div>

                    <!-- Logo Upload (nur bei Logo Sticker) -->
                    <div id="logo-upload-section" hidden>
                        <div class="sk-divider"></div>
                        <div class="form-group">
                            <label>Logo hochladen <span class="text-muted" style="font-weight:400;">(optional)</span></label>
                            <div id="logo-dropzone" class="dropzone">
                                <p class="dropzone__label">Logo hierher ziehen oder klicken</p>
                                <p class="text-muted text-sm">PNG mit Transparenz empfohlen · max. 10 MB</p>
                                <input type="file" id="logo-input" accept="image/jpeg,image/png,image/webp" hidden>
                            </div>
                            <div id="logo-preview" class="upload-preview" hidden>
                                <img class="upload-image-preview" src="" alt="Logo Vorschau">
                                <div class="upload-meta">
                                    <span class="upload-filename text-sm font-semibold"></span>
                                    <span class="upload-filesize text-sm text-muted"></span>
                                </div>
                                <button id="btn-remove-logo" class="btn btn-secondary btn-sm">Entfernen</button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Optionen -->
            <div class="card mb-4">
                <div class="card-header">
                    <span class="card-title">Sticker Optionen</span>
                </div>
                <div class="card-body">

                    <!-- Stil -->
                    <div class="form-group">
                        <label>Stil</label>
                        <div class="sk-style-grid">
                            <?php foreach ([
                                'neon'    => ['💜', 'Neon'],
                                'glow'    => ['✨', 'Glow'],
                                'gold'    => ['👑', 'Gold'],
                                'fire'    => ['🔥', 'Fire'],
                                'minimal' => ['◆',  'Minimal'],
                                'cartoon' => ['🎨', 'Cartoon'],
                            ] as $val => [$icon, $label]): ?>
                            <label class="sk-style-option">
                                <input type="radio" name="opt-style" value="<?= $val ?>" <?= $val === 'neon' ? 'checked' : '' ?>>
                                <span class="sk-style-btn">
                                    <span class="sk-style-btn__icon"><?= $icon ?></span>
                                    <span class="sk-style-btn__label"><?= $label ?></span>
                                </span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="sk-opts-row">
                        <!-- Format -->
                        <div class="form-group">
                            <label for="opt-format">Format</label>
                            <select id="opt-format">
                                <option value="png_transparent">🖼 PNG (transparent)</option>
                                <option value="1:1">⬜ 1:1 Square</option>
                                <option value="9:16">📱 9:16 TikTok</option>
                            </select>
                        </div>

                        <!-- Größe -->
                        <div class="form-group">
                            <label for="opt-size">Größe</label>
                            <select id="opt-size">
                                <option value="medium">▪ Medium</option>
                                <option value="small">· Small</option>
                                <option value="large">■ Large</option>
                            </select>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Aktionen -->
            <div class="card">
                <div class="card-header">
                    <span class="card-title">Aktionen</span>
                </div>
                <div class="card-body">
                    <div class="action-buttons">
                        <button id="btn-submit" class="btn btn-primary btn-lg">
                            🏷️ Sticker Anfrage erstellen
                        </button>
                        <button id="btn-gen-prompt" class="btn btn-secondary">
                            ✦ Prompt generieren
                        </button>
                        <button id="btn-improve" class="btn btn-secondary" disabled>
                            ↑ Make it Better
                        </button>
                    </div>
                </div>
            </div>

        </div><!-- .studio-col-input -->

        <!-- ── Rechte Spalte: Ergebnis ────────────────────────── -->
        <div class="studio-col-result">
            <div class="card h-full">
                <div class="card-header">
                    <span class="card-title">Ergebnis</span>
                    <button id="btn-copy-prompt" class="btn btn-secondary btn-sm" hidden>
                        Prompt kopieren
                    </button>
                </div>
                <div class="card-body">

                    <!-- Leer-State -->
                    <div id="result-empty" class="result-empty">
                        <div class="result-empty__icon">🏷️</div>
                        <p>Wähle eine Kategorie, beschreibe deinen Sticker<br>und klicke auf <strong>„Anfrage erstellen"</strong></p>
                    </div>

                    <!-- Anfrage bestätigt -->
                    <div id="result-request" hidden>

                        <div class="sk-success-banner">
                            <span class="sk-success-banner__icon">✅</span>
                            <div>
                                <div class="sk-success-banner__title">Anfrage gespeichert</div>
                                <div id="result-request-id" class="text-muted text-sm"></div>
                            </div>
                        </div>

                        <!-- Sticker Vorschau-Placeholder -->
                        <div id="result-preview" class="sk-preview-area mb-4">
                            <div class="sk-preview-box">
                                <span id="preview-symbol" class="sk-preview-symbol">🏷️</span>
                            </div>
                            <div class="sk-preview-hint text-muted text-sm">Stil-Vorschau</div>
                        </div>

                        <!-- Beschreibung -->
                        <div class="sk-result-block mb-4">
                            <div class="sk-result-block__label">Sticker Beschreibung</div>
                            <div id="result-description" class="sk-result-block__text"></div>
                        </div>

                        <!-- Parameter -->
                        <div class="sk-params-grid mb-4" id="result-params"></div>

                        <!-- Service-Hinweis -->
                        <div class="sk-service-note">
                            <span>🎨</span>
                            <p class="text-sm">
                                Dieser Sticker wird <strong>individuell erstellt</strong> und nicht automatisch generiert.
                                Du erhältst eine fertige Sticker-Datei.
                            </p>
                        </div>

                    </div><!-- #result-request -->

                    <!-- Optionaler Prompt -->
                    <div id="result-prompt-section" hidden>
                        <div class="sk-divider" style="margin: 20px 0;"></div>
                        <div class="prompt-block">
                            <label class="prompt-block__label">Generierter Prompt</label>
                            <div id="result-prompt" class="prompt-block__text"></div>
                        </div>
                    </div>

                </div>
            </div>
        </div><!-- .studio-col-result -->

    </div><!-- .studio-grid -->

</div><!-- .studio-page -->


<style>
/* ── TikTok Sticker Studio spezifisch ──────────────────────── */
.mb-4 { margin-bottom: 16px; }

/* Section */
.sk-section { margin-bottom: 24px; }
.sk-section__header {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 12px;
}
.sk-section__title {
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--text-muted);
    letter-spacing: 0.05em;
    text-transform: uppercase;
    margin-bottom: 12px;
}
.sk-section__header .sk-section__title { margin-bottom: 0; }

/* ── Kategorie-Cards ─────────────────────────────────────────── */
.sk-categories {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 10px;
}
.sk-cat-card {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    padding: 16px 10px 12px;
    background: var(--bg-panel);
    border: 2px solid var(--border-color);
    border-radius: var(--radius);
    text-align: center;
    cursor: pointer;
    color: var(--text-primary);
    transition: background var(--transition), border-color var(--transition), transform var(--transition);
}
.sk-cat-card:hover {
    background: var(--bg-elevated);
    border-color: var(--accent-blue);
    transform: translateY(-2px);
}
.sk-cat-card[aria-pressed="true"] {
    border-color: var(--accent-orange);
    background: rgba(245, 131, 61, 0.07);
}
.sk-cat-card[aria-pressed="true"]::after {
    content: '✓';
    position: absolute;
    top: 6px;
    right: 8px;
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--accent-orange);
}
.sk-cat-card__icon { font-size: 1.8rem; margin-bottom: 4px; }
.sk-cat-card__name { font-size: 0.78rem; font-weight: 600; line-height: 1.3; }
.sk-cat-card__desc { font-size: 0.68rem; color: var(--text-muted); line-height: 1.3; margin-top: 2px; display: none; }
.sk-cat-card__badge {
    margin-top: 8px;
    font-size: 0.6rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    background: var(--bg-elevated);
    border: 1px solid var(--border-color);
    color: var(--text-muted);
    border-radius: var(--radius-sm);
    padding: 2px 6px;
}

/* ── Showroom ────────────────────────────────────────────────── */
.sk-showroom {
    display: grid;
    grid-template-columns: repeat(8, 1fr);
    gap: 10px;
}
.sk-demo-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    padding: 16px 8px 12px;
    border-radius: var(--radius);
    border: 1px solid var(--border-color);
    background: var(--bg-panel);
    text-align: center;
    transition: transform var(--transition);
    cursor: default;
}
.sk-demo-card:hover { transform: translateY(-3px); }
.sk-demo-card__preview {
    width: 52px;
    height: 52px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.6rem;
    margin-bottom: 2px;
}
.sk-demo-card__symbol { line-height: 1; }
.sk-demo-card__label  { font-size: 0.72rem; font-weight: 600; color: var(--text-primary); }
.sk-demo-card__style  { font-size: 0.65rem; color: var(--text-muted); }

/* Stil-spezifische Vorschau-Hintergründe */
.sk-demo--neon    .sk-demo-card__preview { background: rgba(161,  0, 255, 0.15); box-shadow: 0 0 12px rgba(161,  0, 255, 0.3); }
.sk-demo--glow    .sk-demo-card__preview { background: rgba( 61,142, 245, 0.15); box-shadow: 0 0 12px rgba( 61,142, 245, 0.3); }
.sk-demo--gold    .sk-demo-card__preview { background: rgba(212,175,  55, 0.15); box-shadow: 0 0 12px rgba(212,175,  55, 0.3); }
.sk-demo--fire    .sk-demo-card__preview { background: rgba(245, 83,  61, 0.15); box-shadow: 0 0 12px rgba(245, 83,  61, 0.3); }
.sk-demo--minimal .sk-demo-card__preview { background: var(--bg-elevated); border: 1px solid var(--border-color); box-shadow: none; }
.sk-demo--cartoon .sk-demo-card__preview { background: rgba(245,200,  61, 0.15); box-shadow: 0 0 12px rgba(245,200,  61, 0.3); }

/* ── Formular ────────────────────────────────────────────────── */
.sk-required { color: var(--accent-orange); margin-left: 2px; }
.sk-field-error { font-size: 0.78rem; color: #f56565; margin-top: 4px; display: block; }
.sk-divider { height: 1px; background: var(--border-color); margin: 16px 0; }
.sk-type-badge {
    font-size: 0.72rem; font-weight: 700; letter-spacing: 0.05em;
    text-transform: uppercase; background: rgba(245,131,61,0.15);
    color: var(--accent-orange); border: 1px solid rgba(245,131,61,0.3);
    border-radius: var(--radius-sm); padding: 3px 8px;
}

/* Stil-Auswahl Grid */
.sk-style-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
}
.sk-style-option { display: flex; cursor: pointer; }
.sk-style-option input[type="radio"] { display: none; }
.sk-style-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    padding: 10px 8px;
    width: 100%;
    background: var(--bg-elevated);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-sm);
    transition: background var(--transition), border-color var(--transition);
    text-align: center;
}
.sk-style-option input[type="radio"]:checked + .sk-style-btn {
    background: var(--accent-blue-glow);
    border-color: var(--accent-blue);
}
.sk-style-btn__icon  { font-size: 1.1rem; }
.sk-style-btn__label { font-size: 0.72rem; font-weight: 600; color: var(--text-secondary); }
.sk-style-option input[type="radio"]:checked + .sk-style-btn .sk-style-btn__label {
    color: var(--accent-blue);
}

/* Format + Größe nebeneinander */
.sk-opts-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-top: 4px;
}

/* ── Ergebnis ────────────────────────────────────────────────── */
.sk-success-banner {
    display: flex;
    align-items: center;
    gap: 14px;
    background: rgba(72,199,116,0.08);
    border: 1px solid rgba(72,199,116,0.3);
    border-left: 3px solid #48c774;
    border-radius: var(--radius);
    padding: 14px 16px;
    margin-bottom: 16px;
}
.sk-success-banner__icon  { font-size: 1.4rem; }
.sk-success-banner__title { font-size: 0.9rem; font-weight: 600; color: #48c774; margin-bottom: 2px; }

/* Sticker Vorschau */
.sk-preview-area {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}
.sk-preview-box {
    width: 90px;
    height: 90px;
    border-radius: 50%;
    background: var(--bg-elevated);
    border: 2px dashed var(--border-color);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background var(--transition), box-shadow var(--transition);
}
.sk-preview-symbol { font-size: 2.5rem; line-height: 1; }
.sk-preview-hint   { margin-top: 4px; }

/* Stil-spezifische Vorschau-Farben (dynamisch via JS-Klasse) */
.sk-preview-box.style-neon    { background: rgba(161,  0,255,0.12); box-shadow: 0 0 20px rgba(161,  0,255,0.25); border-color: rgba(161,  0,255,0.3); }
.sk-preview-box.style-glow    { background: rgba( 61,142,245,0.12); box-shadow: 0 0 20px rgba( 61,142,245,0.25); border-color: rgba( 61,142,245,0.3); }
.sk-preview-box.style-gold    { background: rgba(212,175, 55,0.12); box-shadow: 0 0 20px rgba(212,175, 55,0.25); border-color: rgba(212,175, 55,0.3); }
.sk-preview-box.style-fire    { background: rgba(245, 83, 61,0.12); box-shadow: 0 0 20px rgba(245, 83, 61,0.25); border-color: rgba(245, 83, 61,0.3); }
.sk-preview-box.style-minimal { background: var(--bg-elevated); box-shadow: none; border-color: var(--border-color); }
.sk-preview-box.style-cartoon { background: rgba(245,200, 61,0.12); box-shadow: 0 0 20px rgba(245,200, 61,0.25); border-color: rgba(245,200, 61,0.3); }

.sk-result-block {
    background: var(--bg-elevated);
    border: 1px solid var(--border-color);
    border-radius: var(--radius);
    padding: 14px 16px;
}
.sk-result-block__label {
    font-size: 0.72rem; font-weight: 600; letter-spacing: 0.06em;
    text-transform: uppercase; color: var(--text-muted); margin-bottom: 8px;
}
.sk-result-block__text {
    font-size: 0.9rem; color: var(--text-primary); line-height: 1.6;
}

.sk-params-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 8px;
}
.sk-param-item {
    background: var(--bg-elevated);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-sm);
    padding: 10px 12px;
}
.sk-param-item__key {
    font-size: 0.7rem; font-weight: 600; letter-spacing: 0.06em;
    text-transform: uppercase; color: var(--text-muted); margin-bottom: 4px;
}
.sk-param-item__val { font-size: 0.875rem; color: var(--text-primary); font-weight: 500; }

.sk-service-note {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    background: rgba(61,142,245,0.06);
    border: 1px solid rgba(61,142,245,0.2);
    border-radius: var(--radius);
    padding: 12px 14px;
    font-size: 1.2rem;
}
.sk-service-note p   { color: var(--text-secondary); line-height: 1.5; font-size: 0.85rem; }
.sk-service-note strong { color: var(--text-primary); }

/* ── Responsive ──────────────────────────────────────────────── */
@media (max-width: 1100px) {
    .sk-showroom { grid-template-columns: repeat(4, 1fr); }
}
@media (max-width: 900px) {
    .sk-categories { grid-template-columns: repeat(3, 1fr); }
}
@media (max-width: 768px) {
    .sk-categories    { grid-template-columns: repeat(2, 1fr); }
    .sk-cat-card__desc { display: block; }
    .sk-showroom      { grid-template-columns: repeat(3, 1fr); }
    .sk-style-grid    { grid-template-columns: repeat(2, 1fr); }
    .sk-opts-row      { grid-template-columns: 1fr; }
    .sk-params-grid   { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 480px) {
    .sk-categories { grid-template-columns: 1fr 1fr; }
    .sk-showroom   { grid-template-columns: repeat(2, 1fr); }
}
</style>


<script>
(function () {

    // ── DOM ───────────────────────────────────────────────────────
    const catCards      = document.querySelectorAll('.sk-cat-card');
    const fieldType     = document.getElementById('field-type');
    const fieldDesc     = document.getElementById('field-description');
    const fieldText     = document.getElementById('field-text');
    const descCounter   = document.getElementById('desc-counter');
    const typeError     = document.getElementById('type-error');
    const descError     = document.getElementById('desc-error');
    const selectedBadge = document.getElementById('selected-type-badge');

    const textSection   = document.getElementById('text-field-section');
    const logoSection   = document.getElementById('logo-upload-section');
    const logoInput     = document.getElementById('logo-input');
    const logoPreview   = document.getElementById('logo-preview');
    const btnRemoveLogo = document.getElementById('btn-remove-logo');

    const btnSubmit     = document.getElementById('btn-submit');
    const btnGenPrompt  = document.getElementById('btn-gen-prompt');
    const btnImprove    = document.getElementById('btn-improve');
    const btnCopyPrompt = document.getElementById('btn-copy-prompt');

    const resultEmpty         = document.getElementById('result-empty');
    const resultRequest       = document.getElementById('result-request');
    const resultRequestId     = document.getElementById('result-request-id');
    const resultDescription   = document.getElementById('result-description');
    const resultParams        = document.getElementById('result-params');
    const resultPromptSection = document.getElementById('result-prompt-section');
    const resultPromptEl      = document.getElementById('result-prompt');
    const previewBox          = document.querySelector('.sk-preview-box');
    const previewSymbol       = document.getElementById('preview-symbol');

    // ── State ─────────────────────────────────────────────────────
    let currentPrompt = '';

    // ── Typ-Labels & Symbole ──────────────────────────────────────
    const TYPE_LABELS  = { emoji:'😄 Emoji', text:'✏️ Text', logo:'🏷️ Logo', reaction:'⚡ Reaction', custom:'⚙️ Custom' };
    const STYLE_LABELS = { neon:'💜 Neon', glow:'✨ Glow', gold:'👑 Gold', fire:'🔥 Fire', minimal:'◆ Minimal', cartoon:'🎨 Cartoon' };
    const TYPE_SYMBOLS = { emoji:'😄', text:'✏️', logo:'🏷️', reaction:'⚡', custom:'⚙️' };

    // ── Kategorie-Auswahl ─────────────────────────────────────────
    function selectCategory(type) {
        catCards.forEach(c => c.setAttribute('aria-pressed', c.dataset.type === type ? 'true' : 'false'));
        fieldType.value = type;
        typeError.hidden = true;
        fieldType.style.borderColor = '';
        selectedBadge.textContent = TYPE_LABELS[type] ?? type;
        selectedBadge.hidden = false;
        // Konditionale Felder
        textSection.hidden  = (type !== 'text');
        logoSection.hidden  = (type !== 'logo');
    }

    catCards.forEach(c => c.addEventListener('click', () => selectCategory(c.dataset.type)));
    fieldType.addEventListener('change', () => {
        const v = fieldType.value;
        if (v) selectCategory(v);
        else {
            catCards.forEach(c => c.setAttribute('aria-pressed', 'false'));
            selectedBadge.hidden = true;
            textSection.hidden = true;
            logoSection.hidden = true;
        }
    });

    // ── Zeichenzähler ─────────────────────────────────────────────
    fieldDesc.addEventListener('input', () => {
        const len = fieldDesc.value.length;
        const max = parseInt(fieldDesc.getAttribute('maxlength') || '600', 10);
        descCounter.textContent = len + ' / ' + max;
        descCounter.style.color = len > max * 0.9 ? 'var(--accent-orange)' : '';
    });

    // ── Logo Upload ───────────────────────────────────────────────
    UploadPreview.init('#logo-input', '#logo-preview', 'image');
    DropZone.init('#logo-dropzone', '#logo-input', 'image');
    btnRemoveLogo?.addEventListener('click', () => {
        UploadPreview.reset(logoPreview);
        logoInput.value = '';
    });

    // ── Validierung ───────────────────────────────────────────────
    function validate() {
        let ok = true;
        if (!fieldType.value) {
            typeError.hidden = false;
            fieldType.style.borderColor = '#f56565';
            ok = false;
        } else {
            typeError.hidden = true;
            fieldType.style.borderColor = '';
        }
        if (!fieldDesc.value.trim()) {
            descError.hidden = false;
            fieldDesc.style.borderColor = '#f56565';
            ok = false;
        } else {
            descError.hidden = true;
            fieldDesc.style.borderColor = '';
        }
        return ok;
    }
    fieldType.addEventListener('change', () => { typeError.hidden = true; fieldType.style.borderColor = ''; });
    fieldDesc.addEventListener('input',  () => { descError.hidden = true; fieldDesc.style.borderColor = ''; });

    // ── Werte sammeln ─────────────────────────────────────────────
    function getStyle() {
        return document.querySelector('input[name="opt-style"]:checked')?.value ?? 'neon';
    }
    function getValues() {
        return {
            type:        fieldType.value,
            description: fieldDesc.value.trim(),
            text:        fieldText?.value.trim() ?? '',
            style:       getStyle(),
            format:      document.getElementById('opt-format').value,
            size:        document.getElementById('opt-size').value,
        };
    }

    // ── Vorschau-Box aktualisieren ────────────────────────────────
    function updatePreviewBox(type, style) {
        if (!previewBox || !previewSymbol) return;
        previewSymbol.textContent = TYPE_SYMBOLS[type] ?? '🏷️';
        // Stil-Klassen tauschen
        previewBox.className = 'sk-preview-box style-' + (style || 'neon');
    }

    // ── Anfrage erstellen ─────────────────────────────────────────
    btnSubmit.addEventListener('click', async () => {
        if (!validate()) return;

        const origText     = btnSubmit.textContent;
        btnSubmit.disabled = true;
        btnSubmit.textContent = '…';

        try {
            const vals = getValues();
            const res  = await fetch('api/sticker-request.php', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify(vals),
            });
            const data = await res.json();

            if (!data.success) {
                Toast.error(data.error ?? 'Anfrage fehlgeschlagen.');
                return;
            }

            Toast.success('Sticker-Anfrage erstellt!');
            showRequestResult(data);

        } catch {
            Toast.error('Netzwerkfehler — bitte erneut versuchen.');
        } finally {
            btnSubmit.disabled    = false;
            btnSubmit.textContent = origText;
        }
    });

    // ── Prompt generieren ─────────────────────────────────────────
    btnGenPrompt.addEventListener('click', async () => {
        const desc = fieldDesc.value.trim();
        if (!desc) { Toast.warning('Bitte zuerst eine Beschreibung eingeben.'); return; }

        const origText        = btnGenPrompt.textContent;
        btnGenPrompt.disabled = true;
        btnGenPrompt.textContent = '…';

        try {
            const res  = await fetch('api/generate-tiktok.php', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify({
                    action: 'build', input: desc,
                    template: 'tiktok_shop', style: 'cinematic', cta: 'none',
                }),
            });
            const data = await res.json();

            if (!data.success) { Toast.error(data.error ?? 'Fehler.'); return; }

            currentPrompt = data.positive;
            showPromptResult(data.positive);
            btnImprove.disabled  = false;
            btnCopyPrompt.hidden = false;

        } catch {
            Toast.error('Netzwerkfehler.');
        } finally {
            btnGenPrompt.disabled    = false;
            btnGenPrompt.textContent = origText;
        }
    });

    // ── Make it Better ────────────────────────────────────────────
    btnImprove.addEventListener('click', async () => {
        if (!currentPrompt) { Toast.warning('Bitte zuerst einen Prompt generieren.'); return; }

        const origText     = btnImprove.textContent;
        btnImprove.disabled = true;
        btnImprove.textContent = '…';

        try {
            const res  = await fetch('api/generate-tiktok.php', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify({
                    action: 'improve', input: currentPrompt,
                    template: 'tiktok_shop', style: 'cinematic', cta: 'none',
                }),
            });
            const data = await res.json();

            if (!data.success) { Toast.error(data.error ?? 'Fehler.'); return; }

            currentPrompt = data.positive;
            showPromptResult(data.positive);
            Toast.success('Prompt verbessert!');

        } catch {
            Toast.error('Netzwerkfehler.');
        } finally {
            btnImprove.disabled    = false;
            btnImprove.textContent = origText;
        }
    });

    // ── Copy ──────────────────────────────────────────────────────
    btnCopyPrompt.addEventListener('click', () => {
        navigator.clipboard.writeText(currentPrompt)
            .then(() => Toast.success('Prompt kopiert!'))
            .catch(() => Toast.error('Kopieren fehlgeschlagen.'));
    });

    // ── Ergebnis: Anfrage ─────────────────────────────────────────
    function showRequestResult(data) {
        const req = data.request;

        resultRequestId.textContent  = 'Referenz-ID: ' + (data.id ?? '—');
        resultDescription.textContent = req.description ?? '';

        // Vorschau-Box
        updatePreviewBox(req.type, req.style);

        // Parameter-Grid
        resultParams.innerHTML = '';
        const FORMAT_LABELS = { png_transparent: '🖼 PNG transparent', '1:1': '⬜ 1:1', '9:16': '📱 9:16' };
        const params = [
            { key: 'Typ',    val: TYPE_LABELS[req.type]           ?? req.type   },
            { key: 'Stil',   val: STYLE_LABELS[req.style]         ?? req.style  },
            { key: 'Format', val: FORMAT_LABELS[req.format]       ?? req.format },
            { key: 'Größe',  val: req.size ? req.size.charAt(0).toUpperCase() + req.size.slice(1) : '—' },
            { key: 'Text',   val: req.text  || '—'   },
            { key: 'Status', val: '⏳ Pending' },
        ];

        params.forEach(p => {
            const item = document.createElement('div');
            item.className = 'sk-param-item';
            const k = document.createElement('div');
            k.className = 'sk-param-item__key';
            k.textContent = p.key;
            const v = document.createElement('div');
            v.className = 'sk-param-item__val';
            v.textContent = p.val;
            item.appendChild(k);
            item.appendChild(v);
            resultParams.appendChild(item);
        });

        resultEmpty.hidden   = true;
        resultRequest.hidden = false;
    }

    // ── Ergebnis: Prompt ──────────────────────────────────────────
    function showPromptResult(prompt) {
        resultPromptEl.textContent   = prompt;
        resultPromptSection.hidden   = false;
        if (resultRequest.hidden) resultEmpty.hidden = true;
    }

})();
</script>

<?php require_once 'includes/footer.php'; ?>
