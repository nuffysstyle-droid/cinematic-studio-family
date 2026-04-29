/* ============================================================
   progress.js — Cinematic Studio Family
   Wiederverwendbares Polling-Modul für Export-Jobs (Phase 4 — TODO #33)

   Hinweis V1:
   Aktuell laufen api/merge-clips.php und api/export.php SYNCHRON —
   das heißt: Der HTTP-Request blockiert bis FFmpeg fertig ist und
   liefert direkt das Ergebnis. Echtes Polling über api/progress.php
   ist daher NICHT nötig (der Job ist bereits "done" wenn die Response
   ankommt). Dieses Modul ist trotzdem komplett implementiert, damit
   ein späterer Wechsel auf einen Async-Worker (Background-Queue, SSE)
   keine Frontend-Änderung mehr braucht.

   Verwendung:
     csfTrackJob(jobId, {
         onProgress: (percent, status, job) => {...},
         onDone:     (job) => {...},
         onError:    (err, job) => {...},
         intervalMs: 2000,   // optional, default 2000
         maxTicks:   120,    // optional, default 120 (= 4 Minuten)
     });

   Alternativ: csfIndeterminate(barFillEl, statusTextEl, label) für
   den synchronen Fall — animiert die Bar während eines fetch().
   ============================================================ */

(function (global) {
    'use strict';

    /**
     * Pollt api/progress.php?job_id=<id> alle intervalMs bis status === 'done' || 'failed'.
     *
     * @param {string} jobId
     * @param {object} cb
     * @param {(percent:number, status:string, job:object) => void} [cb.onProgress]
     * @param {(job:object) => void} [cb.onDone]
     * @param {(error:string, job?:object) => void} [cb.onError]
     * @param {number} [cb.intervalMs=2000]
     * @param {number} [cb.maxTicks=120]   Sicherheits-Stop nach N Ticks
     * @returns {{stop: () => void}}
     */
    function csfTrackJob(jobId, cb) {
        cb = cb || {};
        const interval = (typeof cb.intervalMs === 'number' && cb.intervalMs >= 500) ? cb.intervalMs : 2000;
        const maxTicks = (typeof cb.maxTicks   === 'number' && cb.maxTicks   > 0)    ? cb.maxTicks   : 120;

        let ticks   = 0;
        let stopped = false;
        let timer   = null;

        async function tick() {
            if (stopped) return;
            ticks++;

            if (ticks > maxTicks) {
                stop();
                if (typeof cb.onError === 'function') {
                    cb.onError('Zeitlimit erreicht — Job-Status konnte nicht ermittelt werden.');
                }
                return;
            }

            try {
                const url  = 'api/progress.php?job_id=' + encodeURIComponent(jobId);
                const resp = await fetch(url, { method: 'GET' });
                const data = await resp.json().catch(function () { return null; });

                if (!data || !data.success || !data.job) {
                    // Job evtl. noch nicht geloggt — weiter pollen, aber Fehler reporten
                    if (resp.status === 404 && ticks < 3) {
                        scheduleNext();
                        return;
                    }
                    stop();
                    if (typeof cb.onError === 'function') {
                        cb.onError((data && data.error) || 'Job-Status konnte nicht gelesen werden.');
                    }
                    return;
                }

                const job     = data.job;
                const status  = String(job.status || 'pending');
                const percent = Math.max(0, Math.min(100, Number(job.progress) || 0));

                if (typeof cb.onProgress === 'function') {
                    cb.onProgress(percent, status, job);
                }

                if (status === 'done') {
                    stop();
                    if (typeof cb.onDone === 'function') cb.onDone(job);
                    return;
                }
                if (status === 'failed') {
                    stop();
                    if (typeof cb.onError === 'function') {
                        cb.onError(job.error || 'Job fehlgeschlagen.', job);
                    }
                    return;
                }

                scheduleNext();
            } catch (_) {
                // Netzwerkfehler — kurz weiter versuchen, dann abbrechen
                if (ticks > 5) {
                    stop();
                    if (typeof cb.onError === 'function') {
                        cb.onError('Netzwerkfehler beim Status-Abruf.');
                    }
                    return;
                }
                scheduleNext();
            }
        }

        function scheduleNext() {
            if (stopped) return;
            timer = setTimeout(tick, interval);
        }

        function stop() {
            stopped = true;
            if (timer) { clearTimeout(timer); timer = null; }
        }

        // Erster Tick sofort
        tick();

        return { stop: stop };
    }

    /**
     * Indeterminate-Animation für den synchronen Fall.
     * Lässt eine Fortschrittsleiste pendelnd zwischen 5% und 95%
     * laufen — ohne echte Daten.
     *
     * @param {HTMLElement} fillEl     Das innere "Fill"-Element der Bar
     * @param {HTMLElement} [textEl]   Optionales Status-Text Element
     * @param {string}      [label]    Status-Text während Animation
     * @returns {{stop: (finalPercent?:number, finalLabel?:string) => void}}
     */
    function csfIndeterminate(fillEl, textEl, label) {
        if (!fillEl) return { stop: function () {} };

        let percent   = 5;
        let direction = 1;
        const step    = 4;
        const everyMs = 250;

        if (textEl && label) textEl.textContent = label;
        fillEl.style.width = percent + '%';

        const id = setInterval(function () {
            percent += direction * step;
            if (percent >= 95) { percent = 95; direction = -1; }
            if (percent <= 10) { percent = 10; direction = 1;  }
            fillEl.style.width = percent + '%';
        }, everyMs);

        return {
            stop: function (finalPercent, finalLabel) {
                clearInterval(id);
                if (typeof finalPercent === 'number') {
                    fillEl.style.width = Math.max(0, Math.min(100, finalPercent)) + '%';
                }
                if (textEl && typeof finalLabel === 'string') {
                    textEl.textContent = finalLabel;
                }
            },
        };
    }

    /**
     * Default-Status-Mapping für Anzeige-Texte.
     * @param {string} status
     * @returns {string}
     */
    function csfStatusLabel(status) {
        switch (String(status)) {
            case 'pending':  return 'Verarbeitung wird vorbereitet…';
            case 'running':  return 'Rendering…';
            case 'done':     return 'Fertig.';
            case 'failed':   return 'Fehlgeschlagen.';
            default:         return 'Verarbeitung läuft…';
        }
    }

    /**
     * Mappt API-Fehlertexte auf benutzerfreundliche Meldungen.
     * Wird auch von der Fehlerbox in merge-clips.php / video-studio.php genutzt.
     *
     * @param {number|null} httpStatus
     * @param {string}      rawError
     * @returns {{message: string, details: string}}
     */
    function csfMapError(httpStatus, rawError) {
        const raw = String(rawError || '').toLowerCase();

        if (httpStatus === 503 || raw.indexOf('ffmpeg') !== -1 && raw.indexOf('not available') !== -1
            || raw.indexOf('ffmpeg nicht verfügbar') !== -1) {
            return {
                message: 'Video-Verarbeitung ist gerade nicht verfügbar. Bitte später erneut versuchen.',
                details: rawError || '',
            };
        }
        if (raw.indexOf('different codec') !== -1 || raw.indexOf('concat failed') !== -1
            || raw.indexOf('unterschiedliche codec') !== -1) {
            return {
                message: 'Clips haben unterschiedliche Codecs/Auflösungen — Multi-Scene benötigt einheitliches Format.',
                details: rawError || '',
            };
        }
        if (raw.indexOf('format not supported') !== -1 || raw.indexOf('format nicht unterstützt') !== -1
            || raw.indexOf('ungültiges format') !== -1) {
            return {
                message: 'Format nicht unterstützt — verwende MP4, WebM oder MOV.',
                details: rawError || '',
            };
        }

        return {
            message: 'Video konnte nicht verarbeitet werden.',
            details: rawError || '',
        };
    }

    // Globale Exports
    global.csfTrackJob       = csfTrackJob;
    global.csfIndeterminate  = csfIndeterminate;
    global.csfStatusLabel    = csfStatusLabel;
    global.csfMapError       = csfMapError;

})(window);
