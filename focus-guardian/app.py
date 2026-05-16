"""Cinematic Focus Guardian - main entry point.

Local ADHD focus brake for Windows.
- PySide6 UI in cinematic dark style
- Timer, warning stages, water + reality reminders
- Mini window from 90% progress
- Fullscreen end-screen alarm
- Recovery / pause mode
- Optional Hart-Modus closes selected distracting processes
"""

from __future__ import annotations

import sys
from pathlib import Path

from PySide6.QtCore import QTimer, Qt
from PySide6.QtGui import QIcon
from PySide6.QtWidgets import QApplication, QMessageBox

from modules.audio import AudioService
from modules.blocker import close_processes, list_running_targets
from modules.overlay import (
    EndScreen,
    MiniWindow,
    RealityReminderDialog,
    WaterReminderDialog,
)
from modules.reminders import ReminderScheduler
from modules.settings import Settings
from modules.timer import FocusTimer, SessionConfig
from modules.ui import MainWindow


BASE_DIR = Path(__file__).resolve().parent


class FocusGuardianApp:
    def __init__(self, qt_app: QApplication) -> None:
        self.qt_app = qt_app
        self.settings = Settings(BASE_DIR)
        self.audio = AudioService(self.settings)

        self.window = MainWindow(self.settings)
        self.window.start_view.session_requested.connect(self.start_session)
        self.window.focus_view.abort_requested.connect(self.abort_session)
        self.window.pause_view.pause_finished.connect(self.on_pause_finished)

        self.timer: FocusTimer | None = None
        self.reminders: ReminderScheduler | None = None
        self.mini: MiniWindow | None = None
        self.end_screen: EndScreen | None = None
        self.session: SessionConfig | None = None

        self._mini_shown = False
        self._open_dialogs: list = []

    # ------------------------------------------------------ session lifecycle

    def start_session(self, project: str, goal: str, duration_seconds: int, mode: str) -> None:
        if mode == "Hart":
            if not self._confirm_hart_mode():
                return

        self.session = SessionConfig(
            project=project,
            goal=goal,
            duration_seconds=duration_seconds,
            mode=mode,
        )

        self.window.focus_view.set_session(project, goal)
        self.window.focus_view.set_time(duration_seconds, duration_seconds)
        self.window.focus_view.set_stage("blue")
        self.window.focus_view.set_warning_text(self.settings.get("warning_texts", {}).get("start", ""))
        self.window.show_focus()

        self.timer = FocusTimer(
            self.session,
            self.settings.get("warning_thresholds", [0, 0.5, 0.75, 0.9, 0.95, 1.0]),
        )
        self.timer.tick.connect(self._on_tick)
        self.timer.stage_changed.connect(self._on_stage_changed)
        self.timer.warning_triggered.connect(self._on_warning)
        self.timer.finished.connect(self._on_finished)

        water_interval = int(self.settings.get("water_reminder_interval_minutes", 30)) * 60
        self.reminders = ReminderScheduler(
            self.settings.get("reality_reminders", []),
            water_interval_seconds=water_interval,
        )
        self.reminders.water_reminder.connect(self._show_water_reminder)
        self.reminders.reality_reminder.connect(self._show_reality_reminder)
        self.reminders.start_water()

        self._mini_shown = False
        self.audio.play("start")
        self.timer.start()

    def abort_session(self) -> None:
        self._teardown_session()
        self.window.show_start()

    def _teardown_session(self) -> None:
        if self.timer:
            self.timer.stop()
            self.timer = None
        if self.reminders:
            self.reminders.stop_water()
            self.reminders.stop_reality_phase()
            self.reminders = None
        if self.mini:
            self.mini.close()
            self.mini = None
        for dlg in list(self._open_dialogs):
            try:
                dlg.close()
            except Exception:
                pass
        self._open_dialogs.clear()
        self._mini_shown = False

    # ----------------------------------------------------------------- ticks

    def _on_tick(self, remaining: int, total: int) -> None:
        self.window.focus_view.set_time(remaining, total)
        if self.mini:
            mins, secs = divmod(remaining, 60)
            hours, mins = divmod(mins, 60)
            text = f"{hours:02d}:{mins:02d}:{secs:02d}" if hours else f"{mins:02d}:{secs:02d}"
            self.mini.update_time(text)

    def _on_stage_changed(self, stage: str) -> None:
        self.window.focus_view.set_stage(stage)

    def _on_warning(self, key: str) -> None:
        texts = self.settings.get("warning_texts", {})
        text = texts.get(key, "")
        if text:
            self.window.focus_view.set_warning_text(text)
            if self.mini:
                self.mini.set_warning(text)

        if key in ("start",):
            self.audio.play("start")
        elif key in ("50", "75"):
            self.audio.play("warning")
        elif key in ("90", "95"):
            self.audio.play("critical")

        if key == "90" and not self._mini_shown:
            self._show_mini_window()
        if key == "90" and self.reminders:
            self.reminders.start_reality_phase()

    def _on_finished(self) -> None:
        self.audio.play("alarm")
        if self.reminders:
            self.reminders.stop_water()
            self.reminders.stop_reality_phase()
        if self.mini:
            self.mini.close()
            self.mini = None

        if self.session and self.session.mode == "Hart":
            self._run_hart_mode()

        self._show_end_screen()

    # ----------------------------------------------------------- mini window

    def _show_mini_window(self) -> None:
        self._mini_shown = True
        if self.mini is None:
            self.mini = MiniWindow(self.settings.theme)
            self.mini.save_now.connect(lambda: self.audio.play("reminder"))
            self.mini.prepare_break.connect(self._prepare_break)
        if self.reminders:
            self.mini.set_reality(self.reminders.pick_reality_question())
        if self.timer:
            self.mini.set_warning(self.window.focus_view.warning_label.text())
        self.mini.show()

    def _prepare_break(self) -> None:
        if self.timer and self.session:
            # Bring break forward: jump remaining to <= 60s if currently larger.
            if self.timer.remaining_seconds > 60:
                self.timer.remaining_seconds = 60

    # ----------------------------------------------------------- reminders

    def _show_water_reminder(self) -> None:
        self.audio.play("reminder")
        dlg = WaterReminderDialog(self.settings.theme, self.window)
        self._open_dialogs.append(dlg)
        dlg.later_requested.connect(lambda: self.reminders and self.reminders.snooze_water(5))
        dlg.finished.connect(lambda _: self._open_dialogs.remove(dlg) if dlg in self._open_dialogs else None)
        dlg.show()

    def _show_reality_reminder(self, question: str) -> None:
        dlg = RealityReminderDialog(self.settings.theme, question, self.window)
        self._open_dialogs.append(dlg)
        dlg.finished.connect(lambda _: self._open_dialogs.remove(dlg) if dlg in self._open_dialogs else None)
        dlg.show()
        if self.mini:
            self.mini.set_reality(question)

    # ----------------------------------------------------------- end / pause

    def _show_end_screen(self) -> None:
        unlock = int(self.settings.get("endscreen_unlock_seconds", 30))
        self.end_screen = EndScreen(self.settings.theme, unlock_seconds=unlock)
        self.end_screen.pause_started.connect(self._start_pause)
        self.end_screen.show_fullscreen()

    def _start_pause(self) -> None:
        self.audio.play("finish")
        if self.end_screen:
            self.end_screen.close()
            self.end_screen = None

        pause_minutes = int(self.settings.get("pause_duration_minutes", 10))
        self.window.show_pause()
        self.window.pause_view.start(pause_minutes * 60)
        self.window.showNormal()
        self.window.raise_()
        self.window.activateWindow()

    def on_pause_finished(self) -> None:
        self._teardown_session()
        self.session = None
        self.window.show_start()

    # ------------------------------------------------------------- hart mode

    def _confirm_hart_mode(self) -> bool:
        blocked = self.settings.get("blocked_processes", [])
        running = list_running_targets(blocked)
        msg = (
            "Hart-Modus aktiv:\n\n"
            f"Folgende Programme werden am Ende der Session geschlossen:\n  - "
            + "\n  - ".join(blocked)
            + f"\n\nAktuell laufend: {len(running)}\n\nFortfahren?"
        )
        box = QMessageBox(self.window)
        box.setWindowTitle("Hart-Modus bestaetigen")
        box.setIcon(QMessageBox.Warning)
        box.setText(msg)
        box.setStandardButtons(QMessageBox.Yes | QMessageBox.No)
        box.setDefaultButton(QMessageBox.No)
        return box.exec() == QMessageBox.Yes

    def _run_hart_mode(self) -> None:
        blocked = self.settings.get("blocked_processes", [])
        closed = close_processes(blocked)
        if closed:
            box = QMessageBox(self.window)
            box.setWindowTitle("Hart-Modus")
            box.setText("Geschlossen:\n  - " + "\n  - ".join(closed))
            box.exec()

    # ------------------------------------------------------------------- run

    def run(self) -> int:
        self.window.show()
        return self.qt_app.exec()


def main() -> int:
    qt_app = QApplication(sys.argv)
    qt_app.setApplicationName("Cinematic Focus Guardian")
    qt_app.setOrganizationName("Cinematic Vision Studio")

    # Icon (optional). PNG/ICO under assets/icon.png will be used if present.
    icon_path = BASE_DIR / "assets" / "icon.png"
    if icon_path.exists():
        qt_app.setWindowIcon(QIcon(str(icon_path)))

    guardian = FocusGuardianApp(qt_app)
    return guardian.run()


if __name__ == "__main__":
    sys.exit(main())
