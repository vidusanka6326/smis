# ADR 0018 — SMIS Agent via Gemini function calling

## Context

Staff need to ask operational questions (free periods in 10-A, who is free, assign a named teacher) without leaving the app. The school already has Policies, Actions, and Spatie permissions. A general-purpose LLM must not bypass them.

## Decision

1. Add a Gemini connection layer (`AgentLlm` + `AgentToolRegistry` + `AgentOrchestrator`). The model only proposes tool calls; PHP executes them.
2. Use the Gemini REST `streamGenerateContent?alt=sse` API with Laravel’s HTTP client (no extra Composer AI SDK).
3. Gate the UI with `use-smis-agent` for admin, officer, and teacher. Students have no agent.
4. Persist chats in `agent_conversations` / `agent_messages`. Assistant turns may include clickable `choices` from the `offer_choices` tool.
5. Stream tokens through Livewire and render Markdown with `Str::markdown()` (unsafe HTML stripped).

## Consequences

- New env: `GEMINI_API_KEY`, optional `GEMINI_MODEL` (default `gemini-2.5-flash`).
- Existing Actions (`UpsertTimetableEntry`, `AssignReliefTeacher`) remain the only writers for those flows.
- Re-run `RolesAndPermissionsSeeder` on existing databases to grant `use-smis-agent`.
