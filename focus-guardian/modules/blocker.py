"""Hart-Modus: optionally terminate distracting processes (Windows-safe)."""

from __future__ import annotations

import sys
from typing import List, Tuple


def list_running_targets(blocked: List[str]) -> List[Tuple[int, str]]:
    """Return [(pid, name)] of currently running blocked processes."""
    try:
        import psutil
    except ImportError:
        return []

    targets = {name.lower() for name in blocked}
    hits: list[tuple[int, str]] = []
    for proc in psutil.process_iter(["pid", "name"]):
        try:
            name = (proc.info.get("name") or "").lower()
            if name in targets:
                hits.append((int(proc.info["pid"]), proc.info["name"]))
        except Exception:
            continue
    return hits


def close_processes(blocked: List[str]) -> List[str]:
    """Gracefully terminate matched processes. Returns list of names closed.

    Safety:
      - Never touches system processes.
      - Only sends terminate() (SIGTERM-equivalent), no kill().
      - Caller must confirm before invoking.
    """
    try:
        import psutil
    except ImportError:
        return []

    closed: list[str] = []
    targets = {name.lower() for name in blocked}
    for proc in psutil.process_iter(["pid", "name"]):
        try:
            name = (proc.info.get("name") or "")
            if name.lower() in targets:
                proc.terminate()
                closed.append(name)
        except Exception:
            continue
    return closed


def is_windows() -> bool:
    return sys.platform.startswith("win")
