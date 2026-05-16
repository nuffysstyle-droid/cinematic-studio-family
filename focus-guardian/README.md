# Cinematic Focus Guardian

> Lokale ADHS/ADS Fokus-Bremse fuer Windows. Cinematic Vision Studio Style.
> Python + PySide6. Kein Internet noetig. Kein Tracking.

Die App startet eine Fokus-Session, gibt stufenweise Warnungen, zeigt Trink- und
Reality-Reminder, erzwingt am Ende eine Pause und kann optional ausgewaehlte
Programme schliessen (Hart-Modus).

---

## Installation

Voraussetzung: **Python 3.10+** (empfohlen 3.11 oder 3.12) unter Windows.

```powershell
cd focus-guardian
python -m venv .venv
.\.venv\Scripts\activate
pip install -r requirements.txt
```

## Starten

```powershell
python app.py
```

Auf macOS/Linux funktioniert die App ebenfalls (Beep-Fallback nutzt `print "\a"`).

## .exe bauen mit PyInstaller

```powershell
pip install pyinstaller
pyinstaller --noconfirm --windowed --name "CinematicFocusGuardian" ^
  --add-data "config.json;." ^
  --add-data "sounds;sounds" ^
  --add-data "assets;assets" ^
  app.py
```

Die fertige EXE liegt unter `dist\CinematicFocusGuardian\CinematicFocusGuardian.exe`.

> Hinweis: Unter PowerShell `;`-Trenner verwenden (wie oben).
> Unter macOS/Linux: `--add-data "config.json:."`

---

## Dateien

| Datei / Ordner | Zweck |
|---|---|
| `app.py` | Einstiegspunkt, orchestriert Timer, UI, Reminder, Audio |
| `config.json` | Alle einstellbaren Werte (Dauer, Texte, Sounds, Blocker) |
| `requirements.txt` | PySide6 + psutil |
| `modules/settings.py` | Laedt/schreibt config.json mit Defaults |
| `modules/audio.py` | WAV-Sounds via QSoundEffect + Windows-Beep-Fallback |
| `modules/timer.py` | Sekundengenaue Session-Logik, Stage- und Warn-Trigger |
| `modules/reminders.py` | Trink- + Reality-Reminder-Scheduler |
| `modules/ui.py` | Hauptfenster: Start / Focus / Pause |
| `modules/overlay.py` | Animierter Countdown-Kreis, Mini-Fenster, Dialoge, End-Screen |
| `modules/blocker.py` | Hart-Modus: definierte Prozesse via psutil terminieren |
| `sounds/` | Optionale WAVs: start, reminder, warning, critical, alarm, finish |
| `assets/` | Optionales `icon.png` |

### Sounds

Lege diese Dateien in `sounds/` ab, wenn du eigene Klaenge willst:

- `start.wav`, `reminder.wav`, `warning.wav`, `critical.wav`, `alarm.wav`, `finish.wav`

Fehlen sie, faellt die App automatisch auf Windows `winsound.Beep` zurueck.

---

## Was du testen solltest

1. **Smoke-Test (kurz):**
   - In `config.json` `default_duration_minutes` auf z.B. `2` setzen (nur zum Testen),
     oder im Startfenster eine eigene Dauer auswaehlen.
   - "FOKUS STARTEN" druecken → Countdown-Kreis erscheint, Stage Blau.
2. **Warnstufen** (Beep oder WAV bei 0%/50%/75%/90%/95%/100%):
   - Bei 90% wird das Mini-Fenster sichtbar (always-on-top).
   - Bei 90% startet die zufaellige Reality-Reminder-Phase.
3. **Trink-Erinnerung:** in `config.json` `water_reminder_interval_minutes` auf `1`
   setzen → nach 1 Minute kommt der Popup mit "Erledigt" / "Spaeter erinnern".
4. **End-Screen:** bei 100% Vollbild "SESSION TERMINATED", Button erst nach 30s
   freigeschaltet (`endscreen_unlock_seconds`).
5. **Pause-Modus:** "Pause starten" → 10-Minuten-Recovery-Bildschirm → danach
   automatisch zurueck zum Startscreen.
6. **Hart-Modus (vorsichtig!):** im Startbildschirm "Hart" waehlen. Vorher
   `chrome.exe` o.ae. offen lassen. Nach Session-Ende wird das Programm
   terminiert (nur nach Sicherheitsabfrage).

---

## Sicherheit / Don'ts

- Kein Windows-Shutdown.
- Kein Force-Kill — `psutil.terminate()` ist SIGTERM-Equivalent, fragt also den Prozess.
- Hart-Modus erfordert vorher explizite Bestaetigung.
- Reines lokales Setup, keine Telemetrie, kein Netzwerk-Call.
