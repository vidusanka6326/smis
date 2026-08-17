# ADR 0021 — SMIS Agent is Gemini-only

## Context

ADR 0020 kept OpenRouter and Gemini behind `AGENT_LLM_PROVIDERS`. OpenRouter free models returned opaque 400/429 errors. The working key is Gemini (`GEMINI_API_KEY` + `generateContent`). Dual-provider selection added setup noise without helping staff.

## Decision

1. SMIS Agent calls Gemini `models/{GEMINI_MODEL}:generateContent` only (default `gemini-2.5-flash`).
2. Bind `AgentLlm` to `GeminiAgentLlm`. Remove OpenRouter, `PreferConfiguredAgentLlm`, and `AGENT_LLM_PROVIDERS`.
3. Empty Gemini Struct fields (`functionCall.args`, `functionResponse.response`) must JSON-encode as `{}`. PHP empty arrays encode as `[]` and Gemini returns HTTP 400.
4. Echo `thoughtSignature` on function-call follow-ups. Show 401/403/404/429/502/503 and timeouts in chat instead of a generic failure.
5. HTTP 503 from Google is model capacity, not billing. Retry once, then try `GEMINI_MODEL_FALLBACKS` (`gemini-2.5-flash`, `gemini-2.0-flash`).

## Consequences

- Env: `GEMINI_API_KEY`, optional `GEMINI_MODEL` (default `gemini-2.5-flash`).
- OpenRouter env vars are unused and should be removed from `.env`.
- Role gating, tools, and Policies from ADR 0018 are unchanged.
