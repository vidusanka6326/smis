# Agent (web)

SMIS Agent is a Livewire page, not a Sanctum API. Tools run in-process with the signed-in user’s Policies.

| Method | Path | Auth | Notes |
|---|---|---|---|
| GET | `/agent` | admin / officer / teacher + `use-smis-agent` | ChatGPT-style streaming chat. Query `?c=` selects a conversation. |
