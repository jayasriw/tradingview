"""
Claude-powered analysis of TradingView alerts.
"""
import os

import anthropic

_client: anthropic.Anthropic | None = None

SYSTEM_PROMPT = """You are an expert technical analyst reviewing automated trading alerts from TradingView.

When given an alert, provide a concise structured analysis covering:
1. **Signal Summary** — what the alert is indicating
2. **Market Context** — what this signal means in typical market conditions
3. **Risk Level** — Low / Medium / High, with brief rationale
4. **Suggested Action** — what a trader might consider, with key price levels to watch
5. **Caveats** — any important limitations or conditions where this signal fails

Keep your response clear and actionable. Do not provide financial advice — this is analytical commentary only."""

# When set, skip real Claude calls and return canned responses (useful for testing)
MOCK_MODE = os.getenv("MOCK_ANALYSIS", "").lower() in ("1", "true", "yes")


def get_client() -> anthropic.Anthropic:
    global _client
    if _client is None:
        api_key = os.getenv("ANTHROPIC_API_KEY")
        if not api_key:
            raise EnvironmentError(
                "ANTHROPIC_API_KEY is not set. "
                "Add it to your .env file or set it as an environment variable. "
                "Set MOCK_ANALYSIS=true to run without a real API key."
            )
        _client = anthropic.Anthropic(api_key=api_key)
    return _client


def format_alert(data: dict | str) -> str:
    """Convert alert payload to readable text for Claude."""
    if isinstance(data, str):
        return data.strip()

    # Common TradingView webhook fields
    field_labels = {
        "symbol": "Symbol",
        "ticker": "Symbol",
        "exchange": "Exchange",
        "interval": "Timeframe",
        "action": "Signal",
        "side": "Signal",
        "close": "Close Price",
        "open": "Open Price",
        "high": "High",
        "low": "Low",
        "volume": "Volume",
        "time": "Alert Time",
        "message": "Alert Message",
        "comment": "Comment",
        "strategy": "Strategy",
    }

    lines = []
    for key, value in data.items():
        label = field_labels.get(key.lower(), key.replace("_", " ").title())
        lines.append(f"{label}: {value}")

    return "\n".join(lines) if lines else str(data)


def _mock_analysis(alert_data: dict | str) -> str:
    """Return a canned analysis for testing (no API key required)."""
    text = format_alert(alert_data)
    return (
        f"**[MOCK MODE] Analysis for alert:**\n\n{text}\n\n"
        "**Signal Summary:** RSI has crossed below 30, indicating oversold conditions.\n\n"
        "**Market Context:** Oversold RSI on a 1h chart often precedes short-term bounces, "
        "though in strong downtrends it can remain depressed.\n\n"
        "**Risk Level:** Medium — counter-trend signals carry higher failure rates.\n\n"
        "**Suggested Action:** Watch for a candle close back above 33 RSI before considering "
        "a long entry. Key level to hold: recent swing low.\n\n"
        "**Caveats:** This is mock data. Set MOCK_ANALYSIS=false and provide a real "
        "ANTHROPIC_API_KEY for genuine Claude analysis."
    )


def analyze(alert_data: dict | str) -> str:
    """
    Analyze a TradingView alert with Claude.
    Returns the full analysis text.
    """
    if MOCK_MODE:
        return _mock_analysis(alert_data)

    client = get_client()
    alert_text = format_alert(alert_data)

    response = client.messages.create(
        model="claude-opus-4-6",
        max_tokens=1024,
        thinking={"type": "adaptive"},
        system=[
            {
                "type": "text",
                "text": SYSTEM_PROMPT,
                "cache_control": {"type": "ephemeral"},
            }
        ],
        messages=[
            {
                "role": "user",
                "content": f"Analyze this TradingView alert:\n\n{alert_text}",
            }
        ],
    )

    return next((b.text for b in response.content if b.type == "text"), "")


def stream_analyze(alert_data: dict | str):
    """
    Stream Claude's analysis of a TradingView alert.
    Yields text chunks as they arrive.
    """
    if MOCK_MODE:
        # Yield mock response word-by-word to simulate streaming
        for word in _mock_analysis(alert_data).split(" "):
            yield word + " "
        return

    client = get_client()
    alert_text = format_alert(alert_data)

    with client.messages.stream(
        model="claude-opus-4-6",
        max_tokens=1024,
        system=[
            {
                "type": "text",
                "text": SYSTEM_PROMPT,
                "cache_control": {"type": "ephemeral"},
            }
        ],
        messages=[
            {
                "role": "user",
                "content": f"Analyze this TradingView alert:\n\n{alert_text}",
            }
        ],
    ) as stream:
        for text in stream.text_stream:
            yield text
