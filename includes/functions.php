<?php
/**
 * includes/functions.php — FFmpeg Service Library
 * Cinematic Studio Family
 *
 * Zentrale FFmpeg-Wrapper-Bibliothek für serverseitige Videoverarbeitung.
 * Alle Shell-Aufrufe nutzen escapeshellarg() — Shell-Injection nicht möglich.
 *
 * Öffentliche Funktionen:
 *   checkFfmpegAvailable()                        → FFmpeg-Verfügbarkeit + Version prüfen
 *   getVideoInfo(string $input)                   → Metadaten via ffprobe
 *   generateThumbnail(string $in, string $out)    → Einzelframe aus Video extrahieren
 *   mergeClips(array $clips, string $out)         → Clips zusammenführen (Concat-Demuxer)
 *   exportPreset(string $in, string $out, string) → Video mit Qualitäts-Preset exportieren
 *
 * Interne Helfer (Präfix csf_):
 *   csf_ffmpeg_run()    → FFmpeg ausführen
 *   csf_ffprobe_run()   → FFprobe ausführen
 *   csf_proc_exec()     → Shell-Prozess mit Timeout
 *   csf_validate_path() → Pfad-Validierung + Traversal-Schutz
 *   csf_within_storage()→ Prüft ob Pfad innerhalb storage/ liegt
 *   csf_ensure_dir()    → Verzeichnis rekursiv anlegen
 *   csf_eval_fps()      → FPS aus Bruchstring berechnen
 *
 * Presets: '720p' | '1080p' | '4k' (4k in V1 deaktiviert)
 * Format:  Nur MP4 (H.264 Video + AAC Audio) in V1
 *
 * @version 1.0.0
 * @since   Phase 4 — TODO #27
 */

declare(strict_types=1);

// ── Konfiguration ─────────────────────────────────────────────────────────────

/**
 * FFmpeg-Binary (via Umgebungsvariable FFMPEG_PATH oder Systemstandard).
 *
 * Reihenfolge:
 *   1. ENV FFMPEG_PATH wenn gesetzt
 *   2. /usr/bin/ffmpeg wenn ausführbar (Render/Debian-Standard) — vermeidet
 *      $PATH-Lookups, die unter PHP-FPM/Apache mit reduziertem PATH scheitern
 *      können und intermittierend "FFmpeg nicht verfügbar" auslösen
 *   3. 'ffmpeg' (PATH-Lookup) als finaler Fallback für lokale Dev-Umgebungen
 */
define('CSF_FFMPEG_BIN',    getenv('FFMPEG_PATH')    ?: (is_executable('/usr/bin/ffmpeg')  ? '/usr/bin/ffmpeg'  : 'ffmpeg'));

/** FFprobe-Binary — gleiches Fallback-Schema wie CSF_FFMPEG_BIN. */
define('CSF_FFPROBE_BIN',   getenv('FFPROBE_PATH')   ?: (is_executable('/usr/bin/ffprobe') ? '/usr/bin/ffprobe' : 'ffprobe'));

/** Maximale Ausführungszeit eines FFmpeg-Jobs in Sekunden */
define('CSF_FFMPEG_TIMEOUT', (int)(getenv('FFMPEG_TIMEOUT') ?: 300));

/** Absoluter Pfad zum storage/-Verzeichnis (Basis für Pfad-Validierung) */
define('CSF_STORAGE_ROOT',   realpath(__DIR__ . '/../storage') ?: __DIR__ . '/../storage');

// ── Export-Presets ────────────────────────────────────────────────────────────

/**
 * Verfügbare Qualitäts-Presets für exportPreset().
 *
 * crf:     Constant Rate Factor — 0 = verlustfrei, 51 = schlechteste Qualität
 *          Empfohlene Werte: 18 (visuell verlustfrei) → 28 (akzeptabel)
 * preset:  FFmpeg Speed/Compression-Trade-off
 *          ultrafast → superfast → veryfast → faster → fast → medium → slow → veryslow
 * available: false = in V1 deaktiviert (hohe Ressourcenanforderung)
 */
const CSF_EXPORT_PRESETS = [
    '720p' => [
        'label'     => 'HD 720p',
        'width'     => 1280,
        'height'    => 720,
        'crf'       => 23,
        'preset'    => 'fast',
        'audio_br'  => '128k',
        'available' => true,
    ],
    '1080p' => [
        'label'     => 'Full HD 1080p',
        'width'     => 1920,
        'height'    => 1080,
        'crf'       => 20,
        'preset'    => 'fast',
        'audio_br'  => '192k',
        'available' => true,
    ],
    '4k' => [
        'label'     => '4K UHD (experimentell)',
        'width'     => 3840,
        'height'    => 2160,
        'crf'       => 18,
        'preset'    => 'slow',
        'audio_br'  => '320k',
        'available' => false, // V1: deaktiviert — sehr hohe CPU/RAM-Last
    ],
];

// ═════════════════════════════════════════════════════════════════════════════
//  ÖFFENTLICHE API
// ═════════════════════════════════════════════════════════════════════════════

/**
 * Prüft ob FFmpeg auf dem Server verfügbar und ausführbar ist.
 *
 * @return array{
 *   success:   bool,
 *   available: bool,
 *   version:   string,
 *   bin:       string,
 *   error:     string
 * }
 *
 * @example
 *   $r = checkFfmpegAvailable();
 *   if (!$r['available']) die('FFmpeg fehlt: ' . $r['error']);
 *   echo 'FFmpeg ' . $r['version'];
 */
function checkFfmpegAvailable(): array {
    // Timeout 30 s statt 10 s — Render Free hat shared CPU + Cold-Start,
    // ein simples `ffmpeg -version` kann beim ersten Aufruf nach Idle
    // länger als 10 s brauchen.
    $result = csf_ffmpeg_run(['-version'], timeout: 30);

    if (!$result['success']) {
        $errMsg = 'FFmpeg nicht gefunden oder nicht ausführbar: ' . trim($result['stderr']);
        // Debug-Log für Render-Deploy-Diagnose. Nur im Fehlerfall, sonst wäre
        // die Datei ein Spam-Magnet. LOCK_EX = atomares Append.
        csf_ffmpeg_debug_log($errMsg, [
            'bin'      => CSF_FFMPEG_BIN,
            'env_path' => getenv('PATH') ?: '(unset)',
        ]);
        return [
            'success'        => false,
            'available'      => false,
            'version'        => '',
            'bin'            => CSF_FFMPEG_BIN,
            'error'          => $errMsg,
            // Diagnose-Felder durchreichen — sonst nicht herauszufinden, warum
            // success=false trotz vorhandener und ausführbarer Binary.
            'exit_code'      => (int)($result['exit_code'] ?? -1),
            'timed_out'      => (bool)($result['timed_out'] ?? false),
            'stdout_preview' => substr((string)($result['stdout'] ?? ''), 0, 200),
            'stderr_preview' => substr((string)($result['stderr'] ?? ''), 0, 400),
            'command'        => CSF_FFMPEG_BIN . ' -version',
        ];
    }

    // Versionszeile parsen: "ffmpeg version 6.1.1 Copyright..."
    $version = '';
    if (preg_match('/ffmpeg version ([^\s]+)/i', $result['stdout'], $m)) {
        $version = $m[1];
    }

    return [
        'success'   => true,
        'available' => true,
        'version'   => $version,
        'bin'       => CSF_FFMPEG_BIN,
        'error'     => '',
    ];
}

// ─────────────────────────────────────────────────────────────────────────────

/**
 * Liest Video-Metadaten einer Datei aus (via ffprobe).
 *
 * @param  string $inputPath Absoluter Pfad zur Videodatei (muss in storage/ liegen)
 * @return array{
 *   success: bool,
 *   data: array{
 *     duration:   float,
 *     size_bytes: int,
 *     format:     string,
 *     video: array{codec: string, width: int, height: int, fps: float, bitrate: int}|null,
 *     audio: array{codec: string, channels: int, sample_rate: int, bitrate: int}|null
 *   }|null,
 *   error: string
 * }
 *
 * @example
 *   $info = getVideoInfo('/var/www/html/storage/uploads/videos/abc123.mp4');
 *   echo $info['data']['video']['width'] . 'x' . $info['data']['video']['height'];
 */
function getVideoInfo(string $inputPath): array {
    if (!csf_validate_path($inputPath, mustExist: true)) {
        return ['success' => false, 'data' => null,
                'error' => 'Eingabepfad ungültig oder außerhalb des erlaubten Bereichs.'];
    }

    $result = csf_ffprobe_run([
        '-v',            'quiet',
        '-print_format', 'json',
        '-show_streams',
        '-show_format',
        $inputPath,
    ]);

    if (!$result['success']) {
        return ['success' => false, 'data' => null,
                'error' => 'ffprobe Fehler: ' . trim($result['stderr'])];
    }

    $info = json_decode($result['stdout'], true);
    if (!is_array($info)) {
        return ['success' => false, 'data' => null,
                'error' => 'ffprobe Ausgabe konnte nicht geparst werden.'];
    }

    $videoStream = null;
    $audioStream = null;

    foreach ($info['streams'] ?? [] as $stream) {
        if ($stream['codec_type'] === 'video' && $videoStream === null) {
            $videoStream = $stream;
        }
        if ($stream['codec_type'] === 'audio' && $audioStream === null) {
            $audioStream = $stream;
        }
    }

    $fmt = $info['format'] ?? [];

    return [
        'success' => true,
        'data'    => [
            'duration'   => (float)($fmt['duration']  ?? 0),
            'size_bytes' => (int)  ($fmt['size']       ?? 0),
            'format'     =>        ($fmt['format_name'] ?? ''),
            'video'      => $videoStream ? [
                'codec'   =>        ($videoStream['codec_name'] ?? ''),
                'width'   => (int)  ($videoStream['width']      ?? 0),
                'height'  => (int)  ($videoStream['height']     ?? 0),
                'fps'     => csf_eval_fps($videoStream['r_frame_rate'] ?? '0/1'),
                'bitrate' => (int)  ($videoStream['bit_rate']   ?? 0),
            ] : null,
            'audio'      => $audioStream ? [
                'codec'       =>        ($audioStream['codec_name']   ?? ''),
                'channels'    => (int)  ($audioStream['channels']     ?? 0),
                'sample_rate' => (int)  ($audioStream['sample_rate']  ?? 0),
                'bitrate'     => (int)  ($audioStream['bit_rate']     ?? 0),
            ] : null,
        ],
        'error'   => '',
    ];
}

// ─────────────────────────────────────────────────────────────────────────────

/**
 * Extrahiert einen einzelnen Frame aus einer Videodatei als Thumbnail-Bild.
 *
 * Ausgabe: JPEG (empfohlen) oder PNG — hängt von der $outputPath-Endung ab.
 * Position: via $timeOffset (Standard: 1 Sekunde vom Anfang)
 *
 * @param  string $inputPath   Absoluter Pfad zur Eingabe-Videodatei
 * @param  string $outputPath  Absoluter Pfad für das Ausgabe-Bild
 * @param  string $timeOffset  Zeitposition (Format: HH:MM:SS oder Sekunden als String)
 * @return array{success: bool, output_path: string, error: string}
 *
 * @example
 *   $r = generateThumbnail(
 *       CSF_STORAGE_ROOT . '/uploads/videos/abc.mp4',
 *       CSF_STORAGE_ROOT . '/thumbnails/abc.jpg',
 *       '00:00:03'
 *   );
 */
function generateThumbnail(
    string $inputPath,
    string $outputPath,
    string $timeOffset = '00:00:01'
): array {
    if (!csf_validate_path($inputPath, mustExist: true)) {
        return ['success' => false, 'output_path' => '',
                'error' => 'Eingabedatei ungültig oder außerhalb des erlaubten Bereichs.'];
    }

    if (!csf_validate_path($outputPath, mustExist: false)) {
        return ['success' => false, 'output_path' => '',
                'error' => 'Ausgabepfad ungültig oder außerhalb des erlaubten Bereichs.'];
    }

    // Zeitoffset bereinigen: nur Ziffern, Doppelpunkte und Punkte erlaubt
    $timeOffset = preg_replace('/[^0-9:.]/', '', $timeOffset) ?: '00:00:01';

    if (!csf_ensure_dir(dirname($outputPath))) {
        return ['success' => false, 'output_path' => '',
                'error' => 'Ausgabeverzeichnis konnte nicht erstellt werden.'];
    }

    // -ss vor -i: schnelles Seeking (keyframe-basiert, minimal ungenau)
    // -frames:v 1: nur einen Frame ausgeben
    // -q:v 2: JPEG-Qualität (2 = sehr gut; 1 = max; 31 = min)
    $result = csf_ffmpeg_run([
        '-ss',      $timeOffset,
        '-i',       $inputPath,
        '-frames:v', '1',
        '-q:v',     '2',
        '-y',                    // Ausgabedatei überschreiben ohne Nachfrage
        $outputPath,
    ]);

    if (!$result['success']) {
        return [
            'success'     => false,
            'output_path' => '',
            'error'       => 'Thumbnail-Generierung fehlgeschlagen: ' . trim($result['stderr']),
        ];
    }

    return [
        'success'     => true,
        'output_path' => $outputPath,
        'error'       => '',
    ];
}

// ─────────────────────────────────────────────────────────────────────────────

/**
 * Fügt mehrere Video-Clips zu einer Datei zusammen.
 *
 * Methode: FFmpeg Concat-Demuxer (-f concat -c copy)
 *   → Kein Re-encode → schnell, verlustfrei
 *   → Voraussetzung: alle Clips müssen denselben Codec, dieselbe Auflösung
 *     und Framerate haben. Bei gemischten Quellen zuerst exportPreset() aufrufen.
 *
 * @param  string[] $clipPaths  Liste absoluter Pfade zu den Eingabe-Clips (min. 2)
 * @param  string   $outputPath Absoluter Pfad für die Ausgabedatei (.mp4)
 * @return array{success: bool, output_path: string, clip_count: int, error: string}
 *
 * @example
 *   $r = mergeClips([
 *       CSF_STORAGE_ROOT . '/uploads/videos/clip1.mp4',
 *       CSF_STORAGE_ROOT . '/uploads/videos/clip2.mp4',
 *   ], CSF_STORAGE_ROOT . '/exports/merged_abc.mp4');
 */
function mergeClips(array $clipPaths, string $outputPath): array {
    $count = count($clipPaths);

    if ($count < 2) {
        return ['success' => false, 'output_path' => '', 'clip_count' => 0,
                'error' => 'Mindestens 2 Clips erforderlich (übergeben: ' . $count . ').'];
    }

    // Alle Eingabepfade validieren
    foreach ($clipPaths as $i => $path) {
        if (!csf_validate_path($path, mustExist: true)) {
            return [
                'success'     => false,
                'output_path' => '',
                'clip_count'  => 0,
                'error'       => "Clip #{$i} ungültig oder nicht gefunden: " . basename((string)$path),
            ];
        }
    }

    if (!csf_validate_path($outputPath, mustExist: false)) {
        return ['success' => false, 'output_path' => '', 'clip_count' => 0,
                'error' => 'Ausgabepfad ungültig oder außerhalb des erlaubten Bereichs.'];
    }

    if (!csf_ensure_dir(dirname($outputPath))) {
        return ['success' => false, 'output_path' => '', 'clip_count' => 0,
                'error' => 'Ausgabeverzeichnis konnte nicht erstellt werden.'];
    }

    // Temporäre Concat-Dateiliste erstellen
    // Format pro Zeile: file '/absoluter/pfad/zum/clip.mp4'
    $tempDir  = CSF_STORAGE_ROOT . '/temp';
    if (!csf_ensure_dir($tempDir)) {
        return ['success' => false, 'output_path' => '', 'clip_count' => 0,
                'error' => 'Temp-Verzeichnis konnte nicht erstellt werden.'];
    }

    $listFile = $tempDir . '/concat_' . bin2hex(random_bytes(8)) . '.txt';
    $lines    = [];

    foreach ($clipPaths as $path) {
        // Einfache Anführungszeichen im Pfad escapen (FFmpeg filelist-Format)
        $escaped = str_replace("'", "'\\''", (string)$path);
        $lines[] = "file '" . $escaped . "'";
    }

    if (file_put_contents($listFile, implode("\n", $lines)) === false) {
        return ['success' => false, 'output_path' => '', 'clip_count' => 0,
                'error' => 'Concat-Liste konnte nicht geschrieben werden.'];
    }

    $result = csf_ffmpeg_run([
        '-f',    'concat',
        '-safe', '0',       // Absolute Pfade erlauben
        '-i',    $listFile,
        '-c',    'copy',    // Kein Re-encode — schnell + verlustfrei
        '-y',
        $outputPath,
    ]);

    @unlink($listFile); // Temporäre Liste aufräumen

    if (!$result['success']) {
        return [
            'success'     => false,
            'output_path' => '',
            'clip_count'  => 0,
            'error'       => 'Clip-Merge fehlgeschlagen: ' . trim($result['stderr']),
        ];
    }

    return [
        'success'     => true,
        'output_path' => $outputPath,
        'clip_count'  => $count,
        'error'       => '',
    ];
}

// ─────────────────────────────────────────────────────────────────────────────

/**
 * Exportiert ein Video mit einem Qualitäts-Preset (re-encode).
 *
 * Ausgabe: MP4 / H.264 (libx264) / AAC Audio / web-optimiert (-movflags +faststart)
 * Seitenverhältnis: wird beibehalten — schwarze Balken (Letterbox/Pillarbox) bei Bedarf.
 *
 * Verfügbare Presets: '720p' | '1080p'
 * Vorbereitet aber deaktiviert: '4k' (available: false)
 *
 * @param  string $inputPath   Absoluter Pfad zur Eingabedatei
 * @param  string $outputPath  Absoluter Pfad für die Ausgabedatei (.mp4)
 * @param  string $preset      Preset-Schlüssel aus CSF_EXPORT_PRESETS
 * @return array{success: bool, output_path: string, preset: string, label: string, error: string}
 *
 * @example
 *   $r = exportPreset(
 *       CSF_STORAGE_ROOT . '/uploads/videos/raw.mp4',
 *       CSF_STORAGE_ROOT . '/exports/final_1080p.mp4',
 *       '1080p'
 *   );
 */
function exportPreset(string $inputPath, string $outputPath, string $preset = '1080p'): array {
    // Preset-Validierung
    if (!array_key_exists($preset, CSF_EXPORT_PRESETS)) {
        return [
            'success'     => false,
            'output_path' => '',
            'preset'      => $preset,
            'label'       => '',
            'error'       => 'Unbekanntes Preset "' . $preset . '". Erlaubt: '
                           . implode(', ', array_keys(CSF_EXPORT_PRESETS)),
        ];
    }

    $p = CSF_EXPORT_PRESETS[$preset];

    if (!$p['available']) {
        return [
            'success'     => false,
            'output_path' => '',
            'preset'      => $preset,
            'label'       => $p['label'],
            'error'       => 'Preset "' . $p['label'] . '" ist in V1 deaktiviert '
                           . '(hohe Ressourcenanforderung).',
        ];
    }

    if (!csf_validate_path($inputPath, mustExist: true)) {
        return ['success' => false, 'output_path' => '', 'preset' => $preset,
                'label' => $p['label'], 'error' => 'Eingabedatei ungültig oder außerhalb des erlaubten Bereichs.'];
    }

    if (!csf_validate_path($outputPath, mustExist: false)) {
        return ['success' => false, 'output_path' => '', 'preset' => $preset,
                'label' => $p['label'], 'error' => 'Ausgabepfad ungültig oder außerhalb des erlaubten Bereichs.'];
    }

    if (!csf_ensure_dir(dirname($outputPath))) {
        return ['success' => false, 'output_path' => '', 'preset' => $preset,
                'label' => $p['label'], 'error' => 'Ausgabeverzeichnis konnte nicht erstellt werden.'];
    }

    $w = $p['width'];
    $h = $p['height'];

    // Scale-Filter: Seitenverhältnis beibehalten + schwarze Balken bei Bedarf
    // force_original_aspect_ratio=decrease → kleiner skalieren wenn nötig
    // pad → fehlende Pixel mit Schwarz auffüllen
    // setsar=1 → Sample Aspect Ratio auf 1:1 normalisieren
    $scaleFilter = "scale={$w}:{$h}:force_original_aspect_ratio=decrease,"
                 . "pad={$w}:{$h}:(ow-iw)/2:(oh-ih)/2:color=black,"
                 . "setsar=1";

    $result = csf_ffmpeg_run([
        '-i',        $inputPath,
        '-vf',       $scaleFilter,
        '-c:v',      'libx264',
        '-crf',      (string)$p['crf'],
        '-preset',   $p['preset'],
        '-c:a',      'aac',
        '-b:a',      $p['audio_br'],
        '-movflags', '+faststart',  // Metadata an Dateianfang — besser für Web-Streaming
        '-y',
        $outputPath,
    ]);

    if (!$result['success']) {
        return [
            'success'     => false,
            'output_path' => '',
            'preset'      => $preset,
            'label'       => $p['label'],
            'error'       => 'Export fehlgeschlagen: ' . trim($result['stderr']),
        ];
    }

    return [
        'success'     => true,
        'output_path' => $outputPath,
        'preset'      => $preset,
        'label'       => $p['label'],
        'error'       => '',
    ];
}

// ═════════════════════════════════════════════════════════════════════════════
//  INTERNE HELFER  (csf_-Präfix — nicht direkt aufrufen)
// ═════════════════════════════════════════════════════════════════════════════

/**
 * Führt einen FFmpeg-Befehl mit den angegebenen Argumenten aus.
 * Alle Argumente werden via escapeshellarg() gesichert.
 *
 * @internal
 * @param  string[] $args    FFmpeg-Argumente (ohne Binary-Name)
 * @param  int      $timeout Timeout in Sekunden
 * @return array{success: bool, stdout: string, stderr: string, exit_code: int, timed_out: bool}
 */
function csf_ffmpeg_run(array $args, int $timeout = CSF_FFMPEG_TIMEOUT): array {
    $parts = [escapeshellarg(CSF_FFMPEG_BIN)];
    foreach ($args as $arg) {
        $parts[] = escapeshellarg((string)$arg);
    }
    return csf_proc_exec(implode(' ', $parts), $timeout);
}

/**
 * Führt einen FFprobe-Befehl mit den angegebenen Argumenten aus.
 *
 * @internal
 * @param  string[] $args FFprobe-Argumente
 * @return array{success: bool, stdout: string, stderr: string, exit_code: int, timed_out: bool}
 */
function csf_ffprobe_run(array $args): array {
    $parts = [escapeshellarg(CSF_FFPROBE_BIN)];
    foreach ($args as $arg) {
        $parts[] = escapeshellarg((string)$arg);
    }
    return csf_proc_exec(implode(' ', $parts), timeout: 30);
}

/**
 * Führt einen Shell-Befehl aus und gibt Stdout/Stderr + Exit-Code zurück.
 *
 * Verwendet proc_open() für vollständige Kontrolle über I/O und Timeout.
 * Bei Timeout-Überschreitung wird der Prozess via SIGKILL (Signal 9) beendet.
 *
 * @internal
 * @param  string $cmd     Vollständig zusammengebauter Shell-Befehl (bereits escaped)
 * @param  int    $timeout Timeout in Sekunden
 * @return array{success: bool, stdout: string, stderr: string, exit_code: int, timed_out: bool}
 */
function csf_proc_exec(string $cmd, int $timeout = 60): array {
    $descriptors = [
        0 => ['pipe', 'r'],  // stdin  → Prozess (wird sofort geschlossen)
        1 => ['pipe', 'w'],  // stdout ← Prozess
        2 => ['pipe', 'w'],  // stderr ← Prozess
    ];

    $process = proc_open($cmd, $descriptors, $pipes);

    if (!is_resource($process)) {
        return [
            'success'   => false,
            'stdout'    => '',
            'stderr'    => 'Prozess konnte nicht gestartet werden.',
            'exit_code' => -1,
            'timed_out' => false,
        ];
    }

    fclose($pipes[0]);                        // stdin nicht benötigt
    stream_set_blocking($pipes[1], false);    // Non-blocking lesen
    stream_set_blocking($pipes[2], false);

    $stdout    = '';
    $stderr    = '';
    $startTime = microtime(true);
    $timedOut  = false;

    while (true) {
        $status  = proc_get_status($process);
        $stdout .= (string)stream_get_contents($pipes[1]);
        $stderr .= (string)stream_get_contents($pipes[2]);

        if (!$status['running']) {
            break;
        }

        if ((microtime(true) - $startTime) > $timeout) {
            proc_terminate($process, 9); // SIGKILL
            $timedOut = true;
            break;
        }

        usleep(200_000); // 200ms Polling-Intervall
    }

    // Restliche Ausgabe nach Prozessende lesen
    $stdout .= (string)stream_get_contents($pipes[1]);
    $stderr .= (string)stream_get_contents($pipes[2]);

    fclose($pipes[1]);
    fclose($pipes[2]);
    $exitCode = proc_close($process);

    return [
        'success'   => !$timedOut && $exitCode === 0,
        'stdout'    => $stdout,
        'stderr'    => $stderr,
        'exit_code' => $timedOut ? -1 : $exitCode,
        'timed_out' => $timedOut,
    ];
}

/**
 * Validiert einen Dateipfad gegen Directory-Traversal und Shell-Injection.
 *
 * Erlaubt sind ausschließlich Pfade innerhalb von CSF_STORAGE_ROOT (storage/).
 *
 * @internal
 * @param  string $path      Zu prüfender Pfad
 * @param  bool   $mustExist true = Datei muss bereits existieren
 * @return bool
 */
function csf_validate_path(string $path, bool $mustExist = true): bool {
    if (trim($path) === '') {
        return false;
    }

    if ($mustExist) {
        $real = realpath($path);
        return $real !== false && csf_within_storage($real);
    }

    // Für Ausgabepfade (Datei existiert noch nicht):
    // Elternverzeichnis mit realpath prüfen wenn vorhanden
    $parent = dirname($path);

    if (is_dir($parent)) {
        $realParent = realpath($parent);
        return $realParent !== false && csf_within_storage($realParent);
    }

    // Elternverzeichnis existiert noch nicht:
    // String-Vergleich nach Normalisierung der Trennzeichen
    $normPath    = rtrim(str_replace('\\', '/', $path), '/');
    $normStorage = rtrim(str_replace('\\', '/', CSF_STORAGE_ROOT), '/');

    return str_starts_with($normPath, $normStorage . '/');
}

/**
 * Prüft ob ein (bereits aufgelöster) Pfad innerhalb von CSF_STORAGE_ROOT liegt.
 *
 * @internal
 * @param  string $realPath Aufgelöster Pfad (z.B. Ergebnis von realpath())
 * @return bool
 */
function csf_within_storage(string $realPath): bool {
    $root = rtrim(str_replace('\\', '/', CSF_STORAGE_ROOT), '/');
    $path = rtrim(str_replace('\\', '/', $realPath), '/');
    return $path === $root || str_starts_with($path, $root . '/');
}

/**
 * Erstellt ein Verzeichnis rekursiv, wenn es noch nicht existiert.
 *
 * @internal
 * @param  string $dir Zu erstellendes Verzeichnis
 * @return bool        true = Verzeichnis existiert oder wurde erfolgreich erstellt
 */
function csf_ensure_dir(string $dir): bool {
    if (is_dir($dir)) {
        return true;
    }
    return mkdir($dir, 0755, recursive: true);
}

/**
 * Berechnet FPS als Dezimalzahl aus einem FFprobe-Bruchstring.
 *
 * Beispiele: "30/1" → 30.0 | "30000/1001" → 29.97 | "25/1" → 25.0
 *
 * @internal
 * @param  string $fraction Bruchstring aus ffprobe r_frame_rate
 * @return float
 */
function csf_eval_fps(string $fraction): float {
    if (!str_contains($fraction, '/')) {
        return (float)$fraction;
    }
    [$num, $den] = explode('/', $fraction, 2);
    $den = (int)$den;
    return $den > 0 ? round((int)$num / $den, 3) : 0.0;
}

// ─────────────────────────────────────────────────────────────────────────────

/**
 * Schreibt eine FFmpeg-Fehlerzeile in data/ffmpeg-debug.log.
 *
 * Wird ausschließlich bei FFmpeg-Fehlern aufgerufen (siehe checkFfmpegAvailable).
 * Format: [ISO-Datum] message | key=value key=value …
 *
 * Ausfälle beim Logging werden bewusst geschluckt — der Aufrufer darf nicht
 * blockieren, nur weil das Log-Verzeichnis (noch) nicht beschreibbar ist.
 *
 * @internal
 * @param string               $message Kurze Beschreibung
 * @param array<string,string> $context Zusatz-Felder (bin, env_path, …)
 * @return void
 */
function csf_ffmpeg_debug_log(string $message, array $context = []): void {
    $logDir  = __DIR__ . '/../data';
    $logFile = $logDir . '/ffmpeg-debug.log';

    if (!is_dir($logDir)) {
        // Bei symlinked data/ (Render-Disk) sollte der Ordner existieren.
        // Fallback: Versuche anzulegen, ohne Hard-Fail.
        @mkdir($logDir, 0755, true);
    }
    if (!is_dir($logDir) || !is_writable($logDir)) {
        return; // Stillschweigend abbrechen — kein Hard-Fail im Hot Path
    }

    $line = '[' . date('c') . '] ' . $message;
    foreach ($context as $k => $v) {
        $line .= ' | ' . $k . '=' . str_replace(["\r", "\n"], ' ', (string)$v);
    }
    $line .= "\n";

    @file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
}
