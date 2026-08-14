# Agent (web)

SMIS Agent is a Livewire page, not a Sanctum API. Tools run in-process with the signed-in user’s Policies and cover every staff UI action that user is allowed to perform.

| Method | Path | Auth | Notes |
|---|---|---|---|
| GET | `/agent` | admin / officer / teacher + `use-smis-agent` | ChatGPT-style streaming chat. Query `?c=` selects a conversation. |

Tools are registered in `AgentToolRegistry` (tagged `agent.tools`). Each tool’s `authorized()` hides it from other roles; `handle()` re-checks Gates and existing Actions. See `docs/modules/agent.md` for the tool list.
