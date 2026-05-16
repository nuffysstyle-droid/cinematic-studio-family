"""Overlay widgets: reality-reminder popup, mini-window, fullscreen end-screen."""

from __future__ import annotations

from typing import Callable, Optional

from PySide6.QtCore import Qt, QTimer, Signal
from PySide6.QtGui import QColor, QFont, QPainter, QPainterPath, QPen
from PySide6.QtWidgets import (
    QApplication,
    QDialog,
    QGraphicsDropShadowEffect,
    QHBoxLayout,
    QLabel,
    QPushButton,
    QVBoxLayout,
    QWidget,
)


# -------------------------------------------------------------------- helpers --

def _shadow(color_hex: str, blur: int = 40) -> QGraphicsDropShadowEffect:
    effect = QGraphicsDropShadowEffect()
    effect.setBlurRadius(blur)
    effect.setColor(QColor(color_hex))
    effect.setOffset(0, 0)
    return effect


def _panel_style(theme: dict) -> str:
    return f"""
        QWidget#panel {{
            background-color: {theme['panel']};
            border: 1px solid {theme['border']};
            border-radius: 14px;
        }}
        QLabel {{ color: {theme['text']}; }}
        QPushButton {{
            background-color: {theme['primary']};
            color: white;
            border: none;
            border-radius: 10px;
            padding: 10px 18px;
            font-weight: bold;
            font-size: 14px;
        }}
        QPushButton:hover {{ background-color: {theme['glow']}; }}
        QPushButton#ghost {{
            background-color: transparent;
            color: {theme['glow']};
            border: 1px solid {theme['border']};
        }}
        QPushButton#ghost:hover {{ color: {theme['text']}; border-color: {theme['glow']}; }}
        QPushButton#danger {{ background-color: {theme['critical']}; }}
        QPushButton#gold {{ background-color: {theme['gold']}; color: #050816; }}
        QPushButton:disabled {{ background-color: #2a3550; color: #6c7a99; }}
    """


# --------------------------------------------------------------- water popup --

class WaterReminderDialog(QDialog):
    later_requested = Signal()
    done_requested = Signal()

    def __init__(self, theme: dict, parent: Optional[QWidget] = None) -> None:
        super().__init__(parent)
        self.theme = theme
        self.setWindowTitle("Wasser-Erinnerung")
        self.setWindowFlag(Qt.WindowStaysOnTopHint, True)
        self.setStyleSheet(_panel_style(theme))
        self.setMinimumSize(420, 220)

        panel = QWidget(objectName="panel")
        panel.setGraphicsEffect(_shadow(theme["glow"]))

        title = QLabel("WASSER TRINKEN")
        title.setStyleSheet(f"color: {theme['glow']}; font-size: 22px; font-weight: bold; letter-spacing: 2px;")
        title.setAlignment(Qt.AlignCenter)

        body = QLabel("Jetzt kurz aufstehen.\nEin Glas Wasser. Schultern lockern.")
        body.setStyleSheet(f"color: {theme['text']}; font-size: 14px;")
        body.setAlignment(Qt.AlignCenter)
        body.setWordWrap(True)

        btn_done = QPushButton("Erledigt")
        btn_later = QPushButton("Spaeter erinnern")
        btn_later.setObjectName("ghost")
        btn_done.clicked.connect(self._done)
        btn_later.clicked.connect(self._later)

        row = QHBoxLayout()
        row.addWidget(btn_done)
        row.addWidget(btn_later)

        layout = QVBoxLayout(panel)
        layout.setContentsMargins(28, 24, 28, 24)
        layout.setSpacing(14)
        layout.addWidget(title)
        layout.addWidget(body)
        layout.addLayout(row)

        outer = QVBoxLayout(self)
        outer.setContentsMargins(12, 12, 12, 12)
        outer.addWidget(panel)
        self.setStyleSheet(self.styleSheet() + f"QDialog {{ background-color: {theme['background']}; }}")

    def _done(self) -> None:
        self.done_requested.emit()
        self.accept()

    def _later(self) -> None:
        self.later_requested.emit()
        self.reject()


# -------------------------------------------------------------- reality popup --

class RealityReminderDialog(QDialog):
    def __init__(self, theme: dict, question: str, parent: Optional[QWidget] = None) -> None:
        super().__init__(parent)
        self.theme = theme
        self.setWindowTitle("Reality Check")
        self.setWindowFlag(Qt.WindowStaysOnTopHint, True)
        self.setStyleSheet(_panel_style(theme))
        self.setMinimumSize(460, 220)

        panel = QWidget(objectName="panel")
        panel.setGraphicsEffect(_shadow(theme["gold"]))

        tag = QLabel("REALITY CHECK")
        tag.setStyleSheet(f"color: {theme['gold']}; font-size: 13px; font-weight: bold; letter-spacing: 4px;")
        tag.setAlignment(Qt.AlignCenter)

        q = QLabel(question)
        q.setStyleSheet(f"color: {theme['text']}; font-size: 20px; font-weight: 600;")
        q.setAlignment(Qt.AlignCenter)
        q.setWordWrap(True)

        ok = QPushButton("Verstanden")
        ok.setObjectName("gold")
        ok.clicked.connect(self.accept)

        layout = QVBoxLayout(panel)
        layout.setContentsMargins(28, 24, 28, 24)
        layout.setSpacing(14)
        layout.addWidget(tag)
        layout.addWidget(q)
        layout.addWidget(ok)

        outer = QVBoxLayout(self)
        outer.setContentsMargins(12, 12, 12, 12)
        outer.addWidget(panel)
        self.setStyleSheet(self.styleSheet() + f"QDialog {{ background-color: {theme['background']}; }}")


# ------------------------------------------------------------------- mini-win --

class MiniWindow(QWidget):
    """Always-on-top compact window shown from 90% progress until session end."""

    save_now = Signal()
    prepare_break = Signal()

    def __init__(self, theme: dict, parent: Optional[QWidget] = None) -> None:
        super().__init__(parent)
        self.theme = theme
        self.setWindowFlag(Qt.WindowStaysOnTopHint, True)
        self.setWindowFlag(Qt.Tool, True)
        self.setWindowTitle("Focus Guardian - Mini")
        self.setStyleSheet(_panel_style(theme) + f"QWidget {{ background-color: {theme['background']}; }}")
        self.setFixedSize(340, 230)

        panel = QWidget(objectName="panel")
        panel.setGraphicsEffect(_shadow(theme["warning"], blur=30))

        self.time_label = QLabel("--:--")
        self.time_label.setStyleSheet(f"color: {theme['warning']}; font-size: 34px; font-weight: bold;")
        self.time_label.setAlignment(Qt.AlignCenter)

        self.warning_label = QLabel("Endspurt.")
        self.warning_label.setStyleSheet(f"color: {theme['text']}; font-size: 12px;")
        self.warning_label.setAlignment(Qt.AlignCenter)
        self.warning_label.setWordWrap(True)

        self.reality_label = QLabel("")
        self.reality_label.setStyleSheet(f"color: {theme['gold']}; font-size: 13px; font-style: italic;")
        self.reality_label.setAlignment(Qt.AlignCenter)
        self.reality_label.setWordWrap(True)

        btn_save = QPushButton("Ich speichere jetzt")
        btn_break = QPushButton("Pause vorbereiten")
        btn_break.setObjectName("ghost")
        btn_save.clicked.connect(self.save_now.emit)
        btn_break.clicked.connect(self.prepare_break.emit)

        row = QHBoxLayout()
        row.addWidget(btn_save)
        row.addWidget(btn_break)

        layout = QVBoxLayout(panel)
        layout.setContentsMargins(16, 14, 16, 14)
        layout.setSpacing(8)
        layout.addWidget(self.time_label)
        layout.addWidget(self.warning_label)
        layout.addWidget(self.reality_label)
        layout.addLayout(row)

        outer = QVBoxLayout(self)
        outer.setContentsMargins(8, 8, 8, 8)
        outer.addWidget(panel)

    def update_time(self, text: str) -> None:
        self.time_label.setText(text)

    def set_warning(self, text: str) -> None:
        self.warning_label.setText(text)

    def set_reality(self, text: str) -> None:
        self.reality_label.setText(text)


# ----------------------------------------------------------- fullscreen end --

class EndScreen(QWidget):
    """Fullscreen alarm screen at session end. Button unlocks after N seconds."""

    pause_started = Signal()

    def __init__(self, theme: dict, unlock_seconds: int = 30, parent: Optional[QWidget] = None) -> None:
        super().__init__(parent)
        self.theme = theme
        self.unlock_seconds = max(1, int(unlock_seconds))
        self._remaining = self.unlock_seconds

        self.setWindowFlag(Qt.FramelessWindowHint, True)
        self.setWindowFlag(Qt.WindowStaysOnTopHint, True)
        self.setStyleSheet(f"background-color: {theme['background']};")

        title = QLabel("SESSION TERMINATED")
        title.setStyleSheet(
            f"color: {theme['critical']}; font-size: 64px; font-weight: 900; letter-spacing: 8px;"
        )
        title.setAlignment(Qt.AlignCenter)
        title.setGraphicsEffect(_shadow(theme["critical"], blur=80))

        subtitle = QLabel("Speichern. Schliessen. Pause.")
        subtitle.setStyleSheet(f"color: {theme['gold']}; font-size: 26px; letter-spacing: 4px;")
        subtitle.setAlignment(Qt.AlignCenter)

        self.countdown = QLabel(f"Pause-Button entsperrt in {self._remaining}s")
        self.countdown.setStyleSheet(f"color: {theme['text']}; font-size: 16px;")
        self.countdown.setAlignment(Qt.AlignCenter)

        self.btn = QPushButton("Pause starten")
        self.btn.setObjectName("danger")
        self.btn.setEnabled(False)
        self.btn.setMinimumSize(280, 60)
        self.btn.setStyleSheet(_panel_style(theme))
        self.btn.clicked.connect(self._on_start_pause)

        layout = QVBoxLayout(self)
        layout.setContentsMargins(80, 80, 80, 80)
        layout.addStretch(1)
        layout.addWidget(title)
        layout.addWidget(subtitle)
        layout.addSpacing(30)
        layout.addWidget(self.countdown, alignment=Qt.AlignCenter)
        layout.addSpacing(20)
        layout.addWidget(self.btn, alignment=Qt.AlignCenter)
        layout.addStretch(2)

        self._timer = QTimer(self)
        self._timer.setInterval(1000)
        self._timer.timeout.connect(self._tick)

    def show_fullscreen(self) -> None:
        self._remaining = self.unlock_seconds
        self.countdown.setText(f"Pause-Button entsperrt in {self._remaining}s")
        self.btn.setEnabled(False)
        self.showFullScreen()
        self._timer.start()

    def _tick(self) -> None:
        self._remaining -= 1
        if self._remaining <= 0:
            self._timer.stop()
            self.btn.setEnabled(True)
            self.countdown.setText("Pause-Button bereit.")
        else:
            self.countdown.setText(f"Pause-Button entsperrt in {self._remaining}s")

    def _on_start_pause(self) -> None:
        self._timer.stop()
        self.pause_started.emit()
        self.close()


# ------------------------------------------------------ animated countdown --

class CountdownCircle(QWidget):
    """Animated ring widget. progress in [0..1], stage color drives ring color."""

    STAGE_COLORS = {
        "blue": "#1f6fff",
        "yellow": "#ffd84d",
        "orange": "#ff9f1c",
        "red": "#ff3b3b",
    }

    def __init__(self, theme: dict, parent: Optional[QWidget] = None) -> None:
        super().__init__(parent)
        self.theme = theme
        self._progress = 0.0
        self._stage = "blue"
        self._time_text = "00:00"
        self.setMinimumSize(320, 320)

    def set_progress(self, value: float) -> None:
        self._progress = max(0.0, min(1.0, value))
        self.update()

    def set_stage(self, stage: str) -> None:
        if stage in self.STAGE_COLORS:
            self._stage = stage
            self.update()

    def set_time(self, text: str) -> None:
        self._time_text = text
        self.update()

    def paintEvent(self, event) -> None:
        painter = QPainter(self)
        painter.setRenderHint(QPainter.Antialiasing, True)

        side = min(self.width(), self.height())
        margin = 24
        rect_x = (self.width() - side) / 2 + margin
        rect_y = (self.height() - side) / 2 + margin
        size = side - 2 * margin

        # Background ring
        bg_pen = QPen(QColor(self.theme["border"]))
        bg_pen.setWidth(14)
        bg_pen.setCapStyle(Qt.RoundCap)
        painter.setPen(bg_pen)
        painter.drawArc(int(rect_x), int(rect_y), int(size), int(size), 0, 360 * 16)

        # Foreground arc
        fg_color = QColor(self.STAGE_COLORS.get(self._stage, self.theme["primary"]))
        fg_pen = QPen(fg_color)
        fg_pen.setWidth(14)
        fg_pen.setCapStyle(Qt.RoundCap)
        painter.setPen(fg_pen)
        span_angle = -int(360 * 16 * self._progress)
        painter.drawArc(int(rect_x), int(rect_y), int(size), int(size), 90 * 16, span_angle)

        # Glow halo
        glow_pen = QPen(QColor(fg_color.red(), fg_color.green(), fg_color.blue(), 60))
        glow_pen.setWidth(28)
        glow_pen.setCapStyle(Qt.RoundCap)
        painter.setPen(glow_pen)
        painter.drawArc(int(rect_x), int(rect_y), int(size), int(size), 90 * 16, span_angle)

        # Center text
        painter.setPen(QColor(self.theme["text"]))
        font = QFont()
        font.setPointSize(int(size / 7))
        font.setBold(True)
        painter.setFont(font)
        painter.drawText(self.rect(), Qt.AlignCenter, self._time_text)

        # Label above
        painter.setPen(QColor(self.theme["glow"]))
        f2 = QFont()
        f2.setPointSize(int(size / 22))
        f2.setBold(True)
        painter.setFont(f2)
        label_rect = self.rect().adjusted(0, int(size / 4), 0, -int(size / 1.4))
        painter.drawText(label_rect, Qt.AlignCenter, "FOCUS CORE ACTIVE")

        painter.end()
