"""Water + reality reminder scheduler."""

from __future__ import annotations

import random
from typing import List, Optional

from PySide6.QtCore import QObject, QTimer, Signal


class ReminderScheduler(QObject):
    water_reminder = Signal()
    reality_reminder = Signal(str)

    def __init__(self, reality_pool: List[str], water_interval_seconds: int, parent: Optional[QObject] = None) -> None:
        super().__init__(parent)
        self._reality_pool = list(reality_pool) or ["PC ist nicht alles. Realitaet zuerst."]
        self._used: list[str] = []
        self._water_interval = max(60, int(water_interval_seconds))

        self._water_timer = QTimer(self)
        self._water_timer.setInterval(self._water_interval * 1000)
        self._water_timer.timeout.connect(self.water_reminder.emit)

        self._reality_timer = QTimer(self)
        self._reality_timer.setSingleShot(True)
        self._reality_timer.timeout.connect(self._fire_reality)

        self._reality_active = False

    def start_water(self) -> None:
        self._water_timer.start()

    def stop_water(self) -> None:
        self._water_timer.stop()

    def snooze_water(self, minutes: int = 5) -> None:
        self._water_timer.stop()
        QTimer.singleShot(minutes * 60 * 1000, self._restart_water)

    def _restart_water(self) -> None:
        self.water_reminder.emit()
        self._water_timer.start()

    def start_reality_phase(self) -> None:
        """Reality reminders appear randomly during the last stretch of the session."""
        self._reality_active = True
        self._schedule_next_reality()

    def stop_reality_phase(self) -> None:
        self._reality_active = False
        self._reality_timer.stop()

    def _schedule_next_reality(self) -> None:
        if not self._reality_active:
            return
        delay_ms = random.randint(45, 120) * 1000
        self._reality_timer.start(delay_ms)

    def _fire_reality(self) -> None:
        if not self._reality_active:
            return
        question = self.pick_reality_question()
        self.reality_reminder.emit(question)
        self._schedule_next_reality()

    def pick_reality_question(self) -> str:
        pool = [q for q in self._reality_pool if q not in self._used]
        if not pool:
            self._used.clear()
            pool = list(self._reality_pool)
        question = random.choice(pool)
        self._used.append(question)
        return question
