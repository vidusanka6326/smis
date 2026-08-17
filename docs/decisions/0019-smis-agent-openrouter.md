# ADR 0019 — SMIS Agent via OpenRouter

**Superseded by [ADR 0021](0021-smis-agent-gemini-only.md).** OpenRouter is no longer used.

## Context

ADR 0018 used Gemini `generateContent`. New Google AI Studio keys 404 on `gemini-2.5-flash`, and prepaid Gemini credits were exhausting in local use. Staff still need an in-app assistant that cannot bypass Policies.

## Decision

1. Keep the same connection layer (`AgentLlm` + `AgentToolRegistry` + `AgentOrchestrator`). The model only proposes tool calls; PHP executes them.
2. Call OpenRouter’s OpenAI-compatible `POST /api/v1/chat/completions` with Laravel’s HTTP client (no extra Composer AI SDK).
3. Default model is `openai/gpt-oss-20b:free`. Override with `OPENROUTER_MODEL`.
4. Tool schemas use JSON Schema (`object` / `string` / `integer` / `boolean` / `array`). Empty `properties` encode as `{}`.
5. Gate the UI with `use-smis-agent` for admin, officer, and teacher. Students have no agent. Never bypass Policies.

This supersedes the Gemini-only transport in ADR 0018. Dual-provider selection is in [ADR 0020](0020-smis-agent-dual-llm.md). Role gating, tools, and persistence are unchanged.

## Consequences

- New env: `OPENROUTER_API_KEY`, optional `OPENROUTER_MODEL` (default `openai/gpt-oss-20b:free`).
- Gemini remains available when `GEMINI_API_KEY` is set and listed in `AGENT_LLM_PROVIDERS`.
- Free OpenRouter models may rate-limit; chat shows a wait-and-retry message (HTTP 429).
- Existing Actions remain the only writers.
