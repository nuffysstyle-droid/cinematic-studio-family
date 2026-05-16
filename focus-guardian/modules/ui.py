"""Main window UI: Start screen, Focus core, Pause screen."""

from __future__ import annotations

from typing import Optional

from PySide6.QtCore import Qt, QTimer, Signal
from PySide6.QtGui import QColor, QFont
from PySide6.QtWidgets import (
    QComboBox,
    QFrame,
    QGraphicsDropShadowEffect,
    QHBoxLayout,
    QLabel,
    QLineEdit,
    QMessageBox,
    QProgressBar,
    QPushButton,
    QStackedWidget,
    QVBoxLayout,
    QWidget,
)

from .overlay import CountdownCircle


def _shadow(color_hex: str, blur: int = 50) -> QGraphicsDropShadowEffect:
    effect = QGraphicsDropShadowEffect()
    effect.setBlurRadius(blur)
    effect.setColor(QColor(color_hex))
    effect.setOffset(0, 0)
    return effect


def app_stylesheet(theme: dict) -> str:
    return f"""
        QWidget {{
            background-color: {theme['background']};
            color: {theme['text']};
            font-family: 'Segoe UI', 'Inter', sans-serif;
        }}
        QFrame#panel {{
            background-color: {theme['panel']};
            border: 1px solid {theme['border']};
            border-radius: 16px;
        }}
        QLabel#title {{
            color: {theme['glow']};
            font-size: 28px;
            font-weight: 800;
            letter-spacing: 4px;
        }}
        QLabel#subtitle {{
            color: {theme['text']};
            font-size: 14px;
            letter-spacing: 1px;
        }}
        QLabel#sectionTag {{
            color: {theme['gold']};
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 3px;
        }}
        QLineEdit, QComboBox {{
            background-color: #0a1530;
            border: 1px solid {theme['border']};
            border-radius: 10px;
            padding: 10px 12px;
            color: {theme['text']};
            font-size: 14px;
            selection-background-color: {theme['primary']};
        }}
        QLineEdit:focus, QComboBox:focus {{
            border: 1px solid {theme['glow']};
        }}
        QComboBox::drop-down {{ border: none; width: 24px; }}
        QComboBox QAbstractItemView {{
            background-color: {theme['panel']};
            border: 1px solid {theme['border']};
            color: {theme['text']};
            selection-background-color: {theme['primary']};
        }}
        QPushButton {{
            background-color: {theme['primary']};
            color: white;
            border: none;
            border-radius: 12px;
            padding: 14px 22px;
            font-weight: bold;
            font-size: 15px;
            letter-spacing: 1px;
        }}
        QPushButton:hover {{ background-color: {theme['glow']}; }}
        QPushButton:disabled {{ background-color: #2a3550; color: #6c7a99; }}
        QPushButton#ghost {{
            background-color: transparent;
            color: {theme['glow']};
            border: 1px solid {theme['border']};
        }}
        QPushButton#ghost:hover {{ color: {theme['text']}; border-color: {theme['glow']}; }}
        QPushButton#danger {{ background-color: {theme['critical']}; }}
        QPushButton#gold {{ background-color: {theme['gold']}; color: #050816; }}
        QProgressBar {{
            background-color: #0a1530;
            border: 1px solid {theme['border']};
            border-radius: 8px;
            height: 14px;
            text-align: center;
            color: {theme['text']};
        }}
        QProgressBar::chunk {{
            background-color: {theme['primary']};
            border-radius: 7px;
        }}
    """


# ---------------------------------------------------------------- start view --

class StartView(QWidget):
    session_requested = Signal(str, str, int, str)  # project, goal, seconds, mode

    def __init__(self, settings, parent: Optional[QWidget] = None) -> None:
        super().__init__(parent)
        self.settings = settings
        self.theme = settings.theme

        title = QLabel("CINEMATIC FOCUS GUARDIAN")
        title.setObjectName("title")
        title.setAlignment(Qt.AlignCenter)
        title.setGraphicsEffect(_shadow(self.theme["glow"], blur=60))

        subtitle = QLabel("ADHS Fokus-Bremse. Lokal. Cinematic.")
        subtitle.setObjectName("subtitle")
        subtitle.setAlignment(Qt.AlignCenter)

        panel = QFrame(objectName="panel")
        panel.setGraphicsEffect(_shadow(self.theme["primary"], blur=70))

        tag_project = QLabel("PROJEKT")
        tag_project.setObjectName("sectionTag")
        self.project_input = QLineEdit()
        self.project_input.setPlaceholderText("z.B. Cinematic Vision Studio")

        tag_goal = QLabel("ZIEL DIESER SESSION")
        tag_goal.setObjectName("sectionTag")
        self.goal_input = QLineEdit()
        self.goal_input.setPlaceholderText("z.B. Auth-Flow auf Render testen")

        tag_dur = QLabel("DAUER")
        tag_dur.setObjectName("sectionTag")
        self.duration_combo = QComboBox()
        for minutes in self.settings.get("available_durations", [30, 60, 90, 120]):
            self.duration_combo.addItem(f"{minutes} Minuten", int(minutes))
        default_dur = int(self.settings.get("default_duration_minutes", 60))
        idx = self.duration_combo.findData(default_dur)
        if idx >= 0:
            self.duration_combo.setCurrentIndex(idx)

        tag_mode = QLabel("MODUS")
        tag_mode.setObjectName("sectionTag")
        self.mode_combo = QComboBox()
        for mode in self.settings.get("available_modes", ["Soft", "Normal", "Hart"]):
            self.mode_combo.addItem(mode, mode)
        default_mode = str(self.settings.get("default_mode", "Normal"))
        idx = self.mode_combo.findData(default_mode)
        if idx >= 0:
            self.mode_combo.setCurrentIndex(idx)

        self.start_btn = QPushButton("FOKUS STARTEN")
        self.start_btn.setGraphicsEffect(_shadow(self.theme["glow"], blur=40))
        self.start_btn.clicked.connect(self._on_start)

        panel_layout = QVBoxLayout(panel)
        panel_layout.setContentsMargins(36, 32, 36, 32)
        panel_layout.setSpacing(10)
        panel_layout.addWidget(tag_project)
        panel_layout.addWidget(self.project_input)
        panel_layout.addSpacing(6)
        panel_layout.addWidget(tag_goal)
        panel_layout.addWidget(self.goal_input)
        panel_layout.addSpacing(6)

        row = QHBoxLayout()
        row.setSpacing(20)
        col_a = QVBoxLayout(); col_a.addWidget(tag_dur); col_a.addWidget(self.duration_combo)
        col_b = QVBoxLayout(); col_b.addWidget(tag_mode); col_b.addWidget(self.mode_combo)
        row.addLayout(col_a); row.addLayout(col_b)
        panel_layout.addLayout(row)
        panel_layout.addSpacing(18)
        panel_layout.addWidget(self.start_btn)

        outer = QVBoxLayout(self)
        outer.setContentsMargins(40, 40, 40, 40)
        outer.setSpacing(16)
        outer.addWidget(title)
        outer.addWidget(subtitle)
        outer.addSpacing(12)
        outer.addWidget(panel)
        outer.addStretch(1)

        footer = QLabel("v1.0  -  lokal, offline, kein Tracking")
        footer.setStyleSheet(f"color: #6c7a99; font-size: 11px;")
        footer.setAlignment(Qt.AlignCenter)
        outer.addWidget(footer)

    def _on_start(self) -> None:
        project = self.project_input.text().strip() or "Unbenanntes Projekt"
        goal = self.goal_input.text().strip() or "(kein Ziel gesetzt)"
        minutes = int(self.duration_combo.currentData())
        mode = str(self.mode_combo.currentData())
        self.session_requested.emit(project, goal, minutes * 60, mode)


# ---------------------------------------------------------------- focus view --

class FocusView(QWidget):
    abort_requested = Signal()

    def __init__(self, theme: dict, parent: Optional[QWidget] = None) -> None:
        super().__init__(parent)
        self.theme = theme

        self.project_label = QLabel("Projekt")
        self.project_label.setStyleSheet(f"color: {theme['glow']}; font-size: 14px; font-weight: bold; letter-spacing: 3px;")
        self.project_label.setAlignment(Qt.AlignCenter)

        self.goal_label = QLabel("Ziel")
        self.goal_label.setStyleSheet(f"color: {theme['text']}; font-size: 13px;")
        self.goal_label.setAlignment(Qt.AlignCenter)
        self.goal_label.setWordWrap(True)

        self.circle = CountdownCircle(theme)

        self.warning_box = QFrame(objectName="panel")
        self.warning_box.setGraphicsEffect(_shadow(theme["primary"], blur=30))
        self.warning_label = QLabel("Bereit.")
        self.warning_label.setStyleSheet(f"color: {theme['text']}; font-size: 14px;")
        self.warning_label.setAlignment(Qt.AlignCenter)
        self.warning_label.setWordWrap(True)
        wb_layout = QVBoxLayout(self.warning_box)
        wb_layout.setContentsMargins(20, 14, 20, 14)
        wb_layout.addWidget(self.warning_label)

        self.progress = QProgressBar()
        self.progress.setRange(0, 1000)
        self.progress.setValue(0)
        self.progress.setTextVisible(False)

        self.abort_btn = QPushButton("Session abbrechen")
        self.abort_btn.setObjectName("ghost")
        self.abort_btn.clicked.connect(self._confirm_abort)

        layout = QVBoxLayout(self)
        layout.setContentsMargins(40, 30, 40, 30)
        layout.setSpacing(14)
        layout.addWidget(self.project_label)
        layout.addWidget(self.goal_label)
        layout.addWidget(self.circle, stretch=1)
        layout.addWidget(self.warning_box)
        layout.addWidget(self.progress)
        layout.addWidget(self.abort_btn, alignment=Qt.AlignCenter)

    def set_session(self, project: str, goal: str) -> None:
        self.project_label.setText(project.upper())
        self.goal_label.setText(f"Ziel: {goal}")

    def set_time(self, remaining_seconds: int, total_seconds: int) -> None:
        mins, secs = divmod(remaining_seconds, 60)
        hours, mins = divmod(mins, 60)
        if hours > 0:
            text = f"{hours:02d}:{mins:02d}:{secs:02d}"
        else:
            text = f"{mins:02d}:{secs:02d}"
        self.circle.set_time(text)
        if total_seconds > 0:
            ratio = 1.0 - (remaining_seconds / total_seconds)
        else:
            ratio = 1.0
        self.circle.set_progress(ratio)
        self.progress.setValue(int(ratio * 1000))

    def set_stage(self, stage: str) -> None:
        self.circle.set_stage(stage)
        color = {
            "blue": self.theme["primary"],
            "yellow": self.theme["gold"],
            "orange": self.theme["warning"],
            "red": self.theme["critical"],
        }.get(stage, self.theme["primary"])
        self.progress.setStyleSheet(
            f"QProgressBar::chunk {{ background-color: {color}; border-radius: 7px; }}"
        )

    def set_warning_text(self, text: str) -> None:
        self.warning_label.setText(text)

    def _confirm_abort(self) -> None:
        box = QMessageBox(self)
        box.setWindowTitle("Session abbrechen?")
        box.setText("Willst du diese Fokus-Session wirklich abbrechen?")
        box.setStandardButtons(QMessageBox.Yes | QMessageBox.No)
        box.setDefaultButton(QMessageBox.No)
        if box.exec() == QMessageBox.Yes:
            self.abort_requested.emit()


# ---------------------------------------------------------------- pause view --

class PauseView(QWidget):
    pause_finished = Signal()

    def __init__(self, theme: dict, parent: Optional[QWidget] = None) -> None:
        super().__init__(parent)
        self.theme = theme
        self.total = 0
        self.remaining = 0

        title = QLabel("RECOVERY MODE")
        title.setStyleSheet(f"color: {theme['glow']}; font-size: 36px; font-weight: 900; letter-spacing: 6px;")
        title.setAlignment(Qt.AlignCenter)
        title.setGraphicsEffect(_shadow(theme["glow"], blur=70))

        sub = QLabel("Kein PC. Kein neues Projekt. Gehirn resetten.")
        sub.setStyleSheet(f"color: {theme['text']}; font-size: 16px; letter-spacing: 2px;")
        sub.setAlignment(Qt.AlignCenter)

        panel = QFrame(objectName="panel")
        panel.setGraphicsEffect(_shadow(theme["primary"], blur=50))

        self.time_label = QLabel("00:00")
        self.time_label.setStyleSheet(f"color: {theme['glow']}; font-size: 72px; font-weight: 900;")
        self.time_label.setAlignment(Qt.AlignCenter)

        self.progress = QProgressBar()
        self.progress.setRange(0, 1000)
        self.progress.setValue(0)
        self.progress.setTextVisible(False)
        self.progress.setStyleSheet(
            f"QProgressBar::chunk {{ background-color: {theme['glow']}; border-radius: 7px; }}"
        )

        self.hint = QLabel("Aufstehen. Wasser. Bewegung. Fenster auf.")
        self.hint.setStyleSheet(f"color: {theme['gold']}; font-size: 14px; letter-spacing: 1px;")
        self.hint.setAlignment(Qt.AlignCenter)

        pl = QVBoxLayout(panel)
        pl.setContentsMargins(32, 28, 32, 28)
        pl.setSpacing(14)
        pl.addWidget(self.time_label)
        pl.addWidget(self.progress)
        pl.addWidget(self.hint)

        layout = QVBoxLayout(self)
        layout.setContentsMargins(40, 40, 40, 40)
        layout.setSpacing(20)
        layout.addStretch(1)
        layout.addWidget(title)
        layout.addWidget(sub)
        layout.addSpacing(16)
        layout.addWidget(panel)
        layout.addStretch(2)

        self._timer = QTimer(self)
        self._timer.setInterval(1000)
        self._timer.timeout.connect(self._tick)

    def start(self, duration_seconds: int) -> None:
        self.total = max(1, int(duration_seconds))
        self.remaining = self.total
        self._render()
        self._timer.start()

    def _tick(self) -> None:
        self.remaining = max(0, self.remaining - 1)
        self._render()
        if self.remaining <= 0:
            self._timer.stop()
            self.hint.setText("Reset abgeschlossen. Du kannst bewusst entscheiden.")
            QTimer.singleShot(2500, self.pause_finished.emit)

    def _render(self) -> None:
        mins, secs = divmod(self.remaining, 60)
        self.time_label.setText(f"{mins:02d}:{secs:02d}")
        ratio = 1.0 - (self.remaining / self.total) if self.total else 1.0
        self.progress.setValue(int(ratio * 1000))


# -------------------------------------------------------------- main window --

class MainWindow(QWidget):
    """Container with stacked views: Start -> Focus -> Pause."""

    def __init__(self, settings) -> None:
        super().__init__()
        self.settings = settings
        self.theme = settings.theme

        self.setWindowTitle("Cinematic Focus Guardian")
        self.setMinimumSize(720, 760)
        self.setStyleSheet(app_stylesheet(self.theme))

        self.stack = QStackedWidget()

        self.start_view = StartView(settings)
        self.focus_view = FocusView(self.theme)
        self.pause_view = PauseView(self.theme)

        self.stack.addWidget(self.start_view)   # 0
        self.stack.addWidget(self.focus_view)   # 1
        self.stack.addWidget(self.pause_view)   # 2

        outer = QVBoxLayout(self)
        outer.setContentsMargins(0, 0, 0, 0)
        outer.addWidget(self.stack)

    def show_start(self) -> None:
        self.stack.setCurrentIndex(0)

    def show_focus(self) -> None:
        self.stack.setCurrentIndex(1)

    def show_pause(self) -> None:
        self.stack.setCurrentIndex(2)
