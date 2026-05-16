"""Audio service with Windows-Beep fallback."""

from __future__ import annotations

import sys
from pathlib import Path
from typing import Dict, Optional

from PySide6.QtCore import QObject, QUrl
from PySide6.QtMultimedia import QSoundEffect


class AudioService(QObject):
    """Plays WAV sounds via QSoundEffect; falls back to winsound.Beep on Windows."""

    BEEP_PROFILES: Dict[str, tuple[int, int]] = {
        "start": (660, 220),
        "reminder": (520, 180),
        "warning": (440, 260),
        "critical": (300, 320),
        "alarm": (220, 700),
        "finish": (180, 900),
    }

    def __init__(self, settings) -> None:
        super().__init__()
        self.settings = settings
        self._effects: Dict[str, QSoundEffect] = {}
        self._preload()

    def _preload(self) -> None:
        for name in self.BEEP_PROFILES.keys():
            path = self.settings.sound_path(name)
            if path:
                effect = QSoundEffect()
                effect.setSource(QUrl.fromLocalFile(str(path)))
                effect.setVolume(0.9)
                self._effects[name] = effect

    def play(self, name: str) -> None:
        effect = self._effects.get(name)
        if effect is not None:
            try:
                effect.play()
                return
            except Exception:
                pass
        self._fallback(name)

    def _fallback(self, name: str) -> None:
        freq, dur = self.BEEP_PROFILES.get(name, (500, 200))
        if sys.platform.startswith("win"):
            try:
                import winsound

                winsound.Beep(freq, dur)
                return
            except Exception:
                pass
        # Non-Windows / failure: silent fallback.
        try:
            print("\a", end="", flush=True)
        except Exception:
            pass
