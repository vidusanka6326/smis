# ADR 0020 — SMIS Agent dual LLM providers

**Superseded by [ADR 0021](0021-smis-agent-gemini-only.md).** The agent is Gemini-only.

## Context

ADR 0019 switched the agent to OpenRouter and dropped Gemini. Local setups often have both keys. Staff should keep using whichever provider is ready without a code change.

## Decision

1. Keep both transports: OpenRouter `chat/completions` and Gemini `generateContent`.
2. `AGENT_LLM_PROVIDERS` is an ordered list (default `openrouter,gemini`). The **first listed provider with a non-empty API key** is used.
3. The orchestrator still speaks OpenAI-shaped messages. `GeminiAgentLlm` converts those to Gemini `contents` / `functionDeclarations`.
4. If neither key is set, chat shows a setup message naming both env vars.

## Consequences

- Env: `AGENT_LLM_PROVIDERS`, `OPENROUTER_API_KEY` / `OPENROUTER_MODEL`, `GEMINI_API_KEY` / `GEMINI_MODEL`.
- Put the preferred provider first. Example: `gemini,openrouter` prefers Gemini when both keys exist.
- There is no mid-request failover (a 429 does not automatically switch providers).
