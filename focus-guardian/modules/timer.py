"""Focus session timer with warning-stage detection."""

from __future__ import annotations

from dataclasses import dataclass
from typing import List, Optional

from PySide6.QtCore import QObject, QTimer, Signal


@dataclass
class SessionConfig:
    project: str
    goal: str
    duration_seconds: int
    mode: str  # "Soft" | "Normal" | "Hart"


class FocusTimer(QObject):
    tick = Signal(int, int)            # remaining_seconds, total_seconds
    stage_changed = Signal(str)        # "blue" | "yellow" | "orange" | "red"
    warning_triggered = Signal(str)    # warning key: "start", "50", "75", "90", "95", "100"
    finished = Signal()

    STAGE_THRESHOLDS = [
        (0.75, "yellow"),
        (0.90, "orange"),
        (0.95, "red"),
    ]

    def __init__(self, config: SessionConfig, warning_thresholds: List[float], parent: Optional[QObject] = None) -> None:
        super().__init__(parent)
        self.config = config
        self.total_seconds = int(config.duration_seconds)
        self.remaining_seconds = self.total_seconds
        self.warning_thresholds = sorted(warning_thresholds)
        self._triggered: set[str] = set()
        self._stage = "blue"

        self._qtimer = QTimer(self)
        self._qtimer.setInterval(1000)
        self._qtimer.timeout.connect(self._on_tick)

    @staticmethod
    def threshold_key(threshold: float) -> str:
        if threshold <= 0.0:
            return "start"
        return str(int(round(threshold * 100)))

    def start(self) -> None:
        self.remaining_seconds = self.total_seconds
        self._triggered.clear()
        self._stage = "blue"
        self.stage_changed.emit(self._stage)
        self._maybe_trigger_warnings(elapsed_ratio=0.0)
        self.tick.emit(self.remaining_seconds, self.total_seconds)
        self._qtimer.start()

    def stop(self) -> None:
        self._qtimer.stop()

    def _on_tick(self) -> None:
        self.remaining_seconds = max(0, self.remaining_seconds - 1)
        elapsed_ratio = 1.0 - (self.remaining_seconds / self.total_seconds) if self.total_seconds else 1.0

        new_stage = "blue"
        for threshold, stage in self.STAGE_THRESHOLDS:
            if elapsed_ratio >= threshold:
                new_stage = stage
        if new_stage != self._stage:
            self._stage = new_stage
            self.stage_changed.emit(self._stage)

        self._maybe_trigger_warnings(elapsed_ratio)
        self.tick.emit(self.remaining_seconds, self.total_seconds)

        if self.remaining_seconds <= 0:
            self._qtimer.stop()
            self.finished.emit()

    def _maybe_trigger_warnings(self, elapsed_ratio: float) -> None:
        for threshold in self.warning_thresholds:
            key = self.threshold_key(threshold)
            if key in self._triggered:
                continue
            if elapsed_ratio + 1e-6 >= threshold:
                self._triggered.add(key)
                self.warning_triggered.emit(key)
