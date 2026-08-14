# ADR 0018 — SMIS Agent via Gemini function calling

## Context

Staff need to ask operational questions (free periods in 10-A, who is free, assign a named teacher) without leaving the app. The school already has Policies, Actions, and Spatie permissions. A general-purpose LLM must not bypass them.

## Decision

1. Add a Gemini connection layer (`AgentLlm` + `AgentToolRegistry` + `AgentOrchestrator`). The model only proposes tool calls; PHP executes them.
2. Use the Gemini REST `generateContent` API (`gemini-flash-latest`) with Laravel’s HTTP client (no extra Composer AI SDK). `gemini-2.5-flash` returns 404 for new API keys.
3. Gate the UI with `use-smis-agent` for admin, officer, and teacher. Students have no agent.
4. Persist chats in `agent_conversations` / `agent_messages`. Assistant turns may include clickable `choices` from the `offer_choices` tool.
5. Persist assistant Markdown through Livewire (composer `stream()` for status/reply) with `Str::markdown()` (unsafe HTML stripped).
6. Expose every staff UI capability as a tool (academic structure, people, timetable, attendance, exams, reports, activity log). Teachers stay scoped; students have no agent. Never bypass Policies.

## Consequences

- New env: `GEMINI_API_KEY`, optional `GEMINI_MODEL` (default `gemini-flash-latest`).
- Existing Actions remain the only writers (students, teachers, officers, timetable, attendance, exams, marks). Agent tools never duplicate that logic.
- Re-run `RolesAndPermissionsSeeder` on existing databases to grant `use-smis-agent`.
