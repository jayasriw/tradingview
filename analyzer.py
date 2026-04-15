"""
Claude-powered analysis of TradingView alerts.
"""
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


def get_client() -> anthropic.Anthropic:
    global _client
    if _client is None:
        _client = anthropic.Anthropic()
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


def analyze(alert_data: dict | str) -> str:
    """
    Analyze a TradingView alert with Claude.
    Returns the full analysis text.
    """
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
