"""
TradingView → Claude integration server.

Receives webhook alerts from TradingView and returns AI-powered
technical analysis via the Anthropic Claude API.

Usage:
    pip install -r requirements.txt
    cp .env.example .env  # add your ANTHROPIC_API_KEY
    python main.py

TradingView webhook setup:
    1. In TradingView, create an alert on any chart/strategy.
    2. Under "Notifications", enable "Webhook URL".
    3. Set the URL to: http://YOUR_SERVER:8000/webhook
    4. In the alert message box, paste JSON like:
       {
         "symbol": "{{ticker}}",
         "exchange": "{{exchange}}",
         "interval": "{{interval}}",
         "close": "{{close}}",
         "action": "buy",
         "message": "{{strategy.order.comment}}"
       }
"""

import json
import os

import uvicorn
from dotenv import load_dotenv
from fastapi import FastAPI, HTTPException, Query, Request
from fastapi.responses import JSONResponse, StreamingResponse

import analyzer

load_dotenv()

WEBHOOK_SECRET = os.getenv("WEBHOOK_SECRET", "")

app = FastAPI(
    title="TradingView–Claude Integration",
    description="Webhook server that analyzes TradingView alerts with Claude AI.",
    version="1.0.0",
)


def _verify_secret(token: str | None) -> None:
    """Reject requests that don't carry the configured secret token."""
    if WEBHOOK_SECRET and token != WEBHOOK_SECRET:
        raise HTTPException(status_code=403, detail="Invalid webhook secret")


async def _parse_body(request: Request) -> dict | str:
    """Parse the request body as JSON or plain text."""
    body = await request.body()
    try:
        return json.loads(body)
    except (json.JSONDecodeError, ValueError):
        return body.decode("utf-8")


# ---------------------------------------------------------------------------
# Endpoints
# ---------------------------------------------------------------------------


@app.get("/health")
async def health():
    """Liveness check."""
    return {"status": "ok"}


@app.post("/webhook")
async def webhook(
    request: Request,
    token: str | None = Query(default=None),
):
    """
    Receive a TradingView alert and return Claude's analysis as JSON.

    TradingView expects a 2xx response quickly, so this is synchronous.
    For long-running analysis, prefer /webhook/stream.
    """
    _verify_secret(token)
    alert_data = await _parse_body(request)

    if not alert_data:
        raise HTTPException(status_code=400, detail="Empty alert payload")

    analysis = analyzer.analyze(alert_data)

    return JSONResponse(
        {
            "status": "ok",
            "alert": alert_data,
            "analysis": analysis,
        }
    )


@app.post("/webhook/stream")
async def webhook_stream(
    request: Request,
    token: str | None = Query(default=None),
):
    """
    Receive a TradingView alert and stream Claude's analysis as Server-Sent Events.

    Use this endpoint when you want to display the analysis token-by-token
    in a dashboard or chatbot UI.
    """
    _verify_secret(token)
    alert_data = await _parse_body(request)

    if not alert_data:
        raise HTTPException(status_code=400, detail="Empty alert payload")

    def event_stream():
        # Send alert data first so the client knows what's being analyzed
        yield f"data: {json.dumps({'type': 'alert', 'data': alert_data})}\n\n"
        for chunk in analyzer.stream_analyze(alert_data):
            yield f"data: {json.dumps({'type': 'text', 'text': chunk})}\n\n"
        yield "data: [DONE]\n\n"

    return StreamingResponse(event_stream(), media_type="text/event-stream")


@app.post("/analyze")
async def analyze_direct(request: Request):
    """
    Directly submit an alert payload for analysis (no secret required).
    Useful for testing from curl or a local dashboard.

    Example:
        curl -X POST http://localhost:8000/analyze \\
             -H 'Content-Type: application/json' \\
             -d '{"symbol":"BTCUSD","action":"buy","close":"67000","interval":"1h"}'
    """
    alert_data = await _parse_body(request)
    if not alert_data:
        raise HTTPException(status_code=400, detail="Empty payload")

    analysis = analyzer.analyze(alert_data)
    return {"alert": alert_data, "analysis": analysis}


if __name__ == "__main__":
    host = os.getenv("HOST", "0.0.0.0")
    port = int(os.getenv("PORT", "8000"))
    uvicorn.run("main:app", host=host, port=port, reload=True)
