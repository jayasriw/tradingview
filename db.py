"""
SQLite storage for TradingView alerts and their Claude analyses.
"""
import json
import os
import sqlite3
from datetime import datetime, timezone

DB_PATH = os.getenv("DB_PATH", "alerts.db")

_CREATE_TABLE = """
CREATE TABLE IF NOT EXISTS alerts (
    id        INTEGER PRIMARY KEY AUTOINCREMENT,
    received_at TEXT NOT NULL,
    symbol    TEXT,
    action    TEXT,
    interval  TEXT,
    raw       TEXT NOT NULL,
    analysis  TEXT NOT NULL
);
"""


def _connect() -> sqlite3.Connection:
    conn = sqlite3.connect(DB_PATH)
    conn.row_factory = sqlite3.Row
    return conn


def init() -> None:
    """Create the alerts table if it doesn't exist."""
    with _connect() as conn:
        conn.execute(_CREATE_TABLE)


def save(alert_data: dict | str, analysis: str) -> int:
    """
    Persist an alert + analysis. Returns the new row id.
    """
    if isinstance(alert_data, dict):
        raw = json.dumps(alert_data)
        symbol = alert_data.get("symbol") or alert_data.get("ticker")
        action = alert_data.get("action") or alert_data.get("side")
        interval = alert_data.get("interval")
    else:
        raw = alert_data
        symbol = action = interval = None

    now = datetime.now(timezone.utc).isoformat()
    with _connect() as conn:
        cursor = conn.execute(
            "INSERT INTO alerts (received_at, symbol, action, interval, raw, analysis) "
            "VALUES (?, ?, ?, ?, ?, ?)",
            (now, symbol, action, interval, raw, analysis),
        )
        return cursor.lastrowid


def recent(limit: int = 20) -> list[dict]:
    """Return the most recent alerts, newest first."""
    with _connect() as conn:
        rows = conn.execute(
            "SELECT id, received_at, symbol, action, interval, raw, analysis "
            "FROM alerts ORDER BY id DESC LIMIT ?",
            (limit,),
        ).fetchall()
    return [
        {
            "id": r["id"],
            "received_at": r["received_at"],
            "symbol": r["symbol"],
            "action": r["action"],
            "interval": r["interval"],
            "alert": json.loads(r["raw"]) if r["raw"].startswith("{") else r["raw"],
            "analysis": r["analysis"],
        }
        for r in rows
    ]


def get(record_id: int) -> dict | None:
    """Return a single alert by id, or None if not found."""
    with _connect() as conn:
        row = conn.execute(
            "SELECT id, received_at, symbol, action, interval, raw, analysis "
            "FROM alerts WHERE id = ?",
            (record_id,),
        ).fetchone()
    if row is None:
        return None
    return {
        "id": row["id"],
        "received_at": row["received_at"],
        "symbol": row["symbol"],
        "action": row["action"],
        "interval": row["interval"],
        "alert": json.loads(row["raw"]) if row["raw"].startswith("{") else row["raw"],
        "analysis": row["analysis"],
    }
