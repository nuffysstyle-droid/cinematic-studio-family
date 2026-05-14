# memory/ffmpeg.md — FFmpeg Integration

> Letzte Aktualisierung: 2026-05-14

---

## Setup

| Parameter | Wert |
|---|---|
| **Version** | 7.1.3-0+deb13u1 (Debian-Paket) |
| **Installation** | `apt-get install -y ffmpeg` im Dockerfile |
| **Binary** | `/usr/bin/ffmpeg` |
| **ffprobe** | `/usr/bin/ffprobe` |
| **Fonts** | `/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf` |
| **Font-Paket** | `fonts-liberation` (apt-get) |

---

## Service-Library: `includes/functions.php`

### Öffentliche Funktionen

| Funktion | Beschreibung |
|---|---|
| `checkFfmpegAvailable()` | FFmpeg-Verfügbarkeit + Version prüfen |
| `getVideoInfo(string $input)` | Metadaten via ffprobe (Dauer, Auflösung, FPS) |
| `generateThumbnail(string $in, string $out)` | Einzelframe aus Video |
| `mergeClips(array $clips, string $out)` | Clips zusammenführen (Concat-Demuxer) |
| `exportPreset(string $in, string $out, string $preset)` | Video mit Qualitäts-Preset |

### Binary-Auflösung (in `functions.php`)
```php
define('CSF_FFMPEG_BIN', getenv('FFMPEG_PATH') ?: (is_executable('/usr/bin/ffmpeg') ? '/usr/bin/ffmpeg' : 'ffmpeg'));
define('CSF_FFPROBE_BIN', getenv('FFPROBE_PATH') ?: (is_executable('/usr/bin/ffprobe') ? '/usr/bin/ffprobe' : 'ffprobe'));
```

---

## Render-Pipeline (api/render-final.php)

### MVP-Konstanten (Free-Plan)
```php
const RENDER_OUT_W      = 1280;   // 720p
const RENDER_OUT_H      = 720;
const RENDER_OUT_FPS    = 30;
const RENDER_CRF        = 20;
const RENDER_PRESET     = 'ultrafast';   // RAM-sparend
const RENDER_SLOT_TO    = 180;           // Timeout pro Slot (Sekunden)
const RENDER_CONCAT_TO  = 180;           // Timeout Concat
const RENDER_FONT_PATH  = '/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf';
```

### Slot-Typen und FFmpeg-Kommandos

#### Typ A: Text-Titelkarte (schwarzer Hintergrund + weißer Text)
```
ffmpeg -f lavfi -i color=c=black:size=1280x720:rate=30
  -t <duration>
  -vf "scale=1280:720,drawtext=text='<escaped_text>':fontfile=<path>:fontsize=54:fontcolor=white:shadowcolor=black@0.7:shadowx=2:shadowy=2:x=(w-text_w)/2:y=(h-text_h)/2"
  -c:v libx264 -crf 20 -preset ultrafast -pix_fmt yuv420p -an -y <out>
```

#### Typ B: Bild-Replacement (Standbild für Slot-Dauer)
```
ffmpeg -loop 1 -i <image>
  -t <duration>
  -vf "scale=1280:720:force_original_aspect_ratio=decrease,pad=1280:720:..."
  -c:v libx264 -crf 20 -preset ultrafast -pix_fmt yuv420p -an -y <out>
```

#### Typ C: Video-Replacement (Clip auf Slot-Dauer trimmen)
```
ffmpeg -i <video> -t <duration>
  -vf "scale=1280:720:..."
  -c:v libx264 -crf 20 -preset ultrafast -pix_fmt yuv420p -an -y <out>
```

#### Typ D: Original-Szene (Cut aus Originalvideo)
```
ffmpeg -ss <start> -t <duration> -i <original>
  -vf "scale=1280:720"
  -c:v libx264 -crf 20 -preset ultrafast -pix_fmt yuv420p -an -y <out>
```

### Concat (Finale Zusammenführung)
```
# concat_list.txt:
file '/absolute/path/to/slot_01.mp4'
file '/absolute/path/to/slot_02.mp4'
...

ffmpeg -f concat -safe 0 -i concat_list.txt
  -c copy -movflags +faststart -y <final_out.mp4>
```

---

## Text-Overlay: csf_drawtext_escape()

FFmpeg filter-level erfordert spezifisches Escaping:

```php
function csf_drawtext_escape(string $text): string {
    $text = str_replace("\r", '',     $text);
    $text = str_replace("\n", ' ',    $text);
    $text = str_replace('\\', '\\\\', $text);  // Backslash zuerst!
    $text = str_replace("'",  "\\'",  $text);
    $text = str_replace(':',  '\\:',  $text);
    $text = str_replace('=',  '\\=',  $text);
    // Emoji entfernen (Liberation Sans hat keine Emoji-Glyphen)
    $text = preg_replace('/[\x{1F000}-\x{1FFFF}]/u', '', $text);
    $text = preg_replace('/[\x{2600}-\x{27BF}]/u',   '', $text);
    return trim($text);
}
// Vor Nutzung: mb_substr($text, 0, 80) für max. 80 Zeichen
```

---

## Bekannte Limitierungen

| Limit | Ursache | Status |
|---|---|---|
| **Kein Audio** | Concat mit gemischten Slot-Typen → Homogenitätsproblem | V2 Backlog |
| **Max 15s** | Free-Plan RAM-Schutz | accept in V1 |
| **Max 3 Slots** | Free-Plan Schutz | accept in V1 |
| **720p statt 1080p** | 512 MB RAM reicht nicht für 1080p ultrafast | Starter+ Plan |
| **Keine Emoji in Titeln** | Liberation Sans hat keine Emoji-Glyphen | known limitation |
| **Font-Path Linux-only** | `/usr/share/fonts/...` existiert nur im Docker-Container | kein lokaler Test möglich |

---

## Export-Presets (CSF_EXPORT_PRESETS)

| Preset | Auflösung | CRF | Preset | Status |
|---|---|---|---|---|
| `720p` | 1280×720 | 23 | fast | ✅ aktiv |
| `1080p` | 1920×1080 | 20 | fast | ⬜ V2 (RAM-Limit) |
| `4k` | 3840×2160 | 18 | slow | ❌ deaktiviert |

---

## Shell-Sicherheit

Alle FFmpeg-Parameter die aus User-Input stammen werden durch `escapeshellarg()` geschützt.
Kein direktes Durchreichen von Request-Parametern an Shell-Commands.
`csf_validate_path()` + `realpath()` + `CSF_STORAGE_ROOT`-Prefix-Check vor jedem File-Zugriff.
