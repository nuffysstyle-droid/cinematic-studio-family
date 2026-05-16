"""Settings / config.json loader + writer."""

from __future__ import annotations

import json
import os
from pathlib import Path
from typing import Any, Dict


DEFAULT_CONFIG: Dict[str, Any] = {
    "default_duration_minutes": 60,
    "default_mode": "Normal",
    "available_durations": [30, 60, 90, 120],
    "available_modes": ["Soft", "Normal", "Hart"],
    "pause_duration_minutes": 10,
    "water_reminder_interval_minutes": 30,
    "endscreen_unlock_seconds": 30,
    "warning_thresholds": [0.0, 0.5, 0.75, 0.9, 0.95, 1.0],
    "warning_texts": {
        "start": "Mission gestartet. Kein neues Thema. Nur dieses Projekt.",
        "50": "Fokus-Check. Bist du noch bei deinem Ziel?",
        "75": "Keine neuen Baustellen. Nur noch Hauptaufgabe.",
        "90": "Endspurt. Speichern, testen, abschliessen.",
        "95": "Letzte Minuten. Keine neue Idee mehr anfangen.",
        "100": "Session beendet. Pause ist jetzt Pflicht.",
    },
    "reality_reminders": [
        "Hast du genug getrunken?",
        "Hast du heute Wasser getrunken?",
        "Hast du schon Waesche gemacht?",
        "Hast du aufgeraeumt?",
        "Was waren deine Aufgaben heute?",
        "Was wolltest du ausserhalb vom PC erledigen?",
        "Hast du Zeit mit den Kindern verbracht?",
        "Hast du deiner Frau geholfen?",
        "Gibt es noch Haushalt?",
        "Hast du gegessen?",
        "Hast du dich bewegt?",
        "Brauchst du frische Luft?",
        "Was liegt noch wirklich an?",
        "PC ist nicht alles. Realitaet zuerst.",
    ],
    "hard_mode_enabled": False,
    "blocked_processes": ["chrome.exe", "msedge.exe", "discord.exe"],
    "sounds": {
        "start": "sounds/start.wav",
        "reminder": "sounds/reminder.wav",
        "warning": "sounds/warning.wav",
        "critical": "sounds/critical.wav",
        "alarm": "sounds/alarm.wav",
        "finish": "sounds/finish.wav",
    },
    "theme": {
        "background": "#050816",
        "panel": "#0b1224",
        "border": "#16233d",
        "primary": "#1f6fff",
        "glow": "#3ea6ff",
        "gold": "#ffd84d",
        "warning": "#ff9f1c",
        "critical": "#ff3b3b",
        "text": "#eef4ff",
    },
}


class Settings:
    """Loads config.json with safe defaults; missing keys are filled in."""

    def __init__(self, base_dir: Path | None = None) -> None:
        self.base_dir = Path(base_dir) if base_dir else Path(__file__).resolve().parent.parent
        self.config_path = self.base_dir / "config.json"
        self.data: Dict[str, Any] = {}
        self.load()

    def load(self) -> None:
        if not self.config_path.exists():
            self.data = json.loads(json.dumps(DEFAULT_CONFIG))
            self.save()
            return
        try:
            with self.config_path.open("r", encoding="utf-8") as fh:
                loaded = json.load(fh)
        except (json.JSONDecodeError, OSError):
            loaded = {}
        self.data = self._merge_defaults(loaded)

    def _merge_defaults(self, loaded: Dict[str, Any]) -> Dict[str, Any]:
        merged = json.loads(json.dumps(DEFAULT_CONFIG))
        for key, value in loaded.items():
            if isinstance(value, dict) and isinstance(merged.get(key), dict):
                merged[key].update(value)
            else:
                merged[key] = value
        return merged

    def save(self) -> None:
        try:
            with self.config_path.open("w", encoding="utf-8") as fh:
                json.dump(self.data, fh, indent=2, ensure_ascii=False)
        except OSError:
            pass

    def get(self, key: str, default: Any = None) -> Any:
        return self.data.get(key, default)

    def set(self, key: str, value: Any) -> None:
        self.data[key] = value
        self.save()

    @property
    def theme(self) -> Dict[str, str]:
        return self.data.get("theme", DEFAULT_CONFIG["theme"])

    def sound_path(self, name: str) -> Path | None:
        rel = self.data.get("sounds", {}).get(name)
        if not rel:
            return None
        path = self.base_dir / rel
        return path if path.exists() else None
