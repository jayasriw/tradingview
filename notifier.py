"""
Notification dispatch — sends Claude's analysis to Telegram and/or Slack.

Configure via environment variables (all optional):

  TELEGRAM_BOT_TOKEN   — Bot token from @BotFather
  TELEGRAM_CHAT_ID     — Chat/channel ID to post to (e.g. -100123456789)

  SLACK_WEBHOOK_URL    — Incoming Webhook URL from your Slack app

If neither is configured the module is a no-op: alerts are stored in the DB
but no external notification is sent.
"""
import json
import logging
import os
import urllib.request

log = logging.getLogger(__name__)

TELEGRAM_TOKEN = os.getenv("TELEGRAM_BOT_TOKEN", "")
TELEGRAM_CHAT_ID = os.getenv("TELEGRAM_CHAT_ID", "")
SLACK_WEBHOOK_URL = os.getenv("SLACK_WEBHOOK_URL", "")


# ---------------------------------------------------------------------------
# Formatting helpers
# ---------------------------------------------------------------------------

def _alert_title(alert_data: dict | str) -> str:
    if isinstance(alert_data, dict):
        symbol = alert_data.get("symbol") or alert_data.get("ticker", "Unknown")
        action = (alert_data.get("action") or alert_data.get("side", "")).upper()
        interval = alert_data.get("interval", "")
        parts = [symbol]
        if action:
            parts.append(action)
        if interval:
            parts.append(interval)
        return " · ".join(parts)
    return str(alert_data)[:80]


def _telegram_text(alert_data: dict | str, analysis: str) -> str:
    title = _alert_title(alert_data)
    # Telegram supports basic Markdown (MarkdownV2 is fiddly; use HTML mode)
    return (
        f"<b>📊 TradingView Alert: {title}</b>\n\n"
        f"{analysis}"
    )[:4096]  # Telegram message limit


def _slack_blocks(alert_data: dict | str, analysis: str) -> list:
    title = _alert_title(alert_data)
    return [
        {
            "type": "header",
            "text": {"type": "plain_text", "text": f"📊 TradingView Alert: {title}"},
        },
        {"type": "divider"},
        {
            "type": "section",
            "text": {"type": "mrkdwn", "text": analysis[:3000]},
        },
    ]


# ---------------------------------------------------------------------------
# Delivery functions
# ---------------------------------------------------------------------------

def _post_json(url: str, payload: dict, *, timeout: int = 10) -> None:
    data = json.dumps(payload).encode()
    req = urllib.request.Request(
        url,
        data=data,
        headers={"Content-Type": "application/json"},
        method="POST",
    )
    with urllib.request.urlopen(req, timeout=timeout) as resp:
        if resp.status not in (200, 201):
            raise RuntimeError(f"HTTP {resp.status} from {url}")


def _send_telegram(alert_data: dict | str, analysis: str) -> None:
    if not TELEGRAM_TOKEN or not TELEGRAM_CHAT_ID:
        return
    url = f"https://api.telegram.org/bot{TELEGRAM_TOKEN}/sendMessage"
    _post_json(url, {
        "chat_id": TELEGRAM_CHAT_ID,
        "text": _telegram_text(alert_data, analysis),
        "parse_mode": "HTML",
    })
    log.info("Telegram notification sent")


def _send_slack(alert_data: dict | str, analysis: str) -> None:
    if not SLACK_WEBHOOK_URL:
        return
    _post_json(SLACK_WEBHOOK_URL, {
        "blocks": _slack_blocks(alert_data, analysis),
        "text": f"TradingView Alert: {_alert_title(alert_data)}",  # fallback
    })
    log.info("Slack notification sent")


# ---------------------------------------------------------------------------
# Public interface
# ---------------------------------------------------------------------------

def send(alert_data: dict | str, analysis: str) -> None:
    """
    Dispatch the analysis to all configured notification channels.
    Errors are logged but never raised — a failed notification
    must not break the webhook response.
    """
    for fn in (_send_telegram, _send_slack):
        try:
            fn(alert_data, analysis)
        except Exception as exc:
            log.warning("Notification error (%s): %s", fn.__name__, exc)
