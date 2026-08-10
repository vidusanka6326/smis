# ADR 0009 — Skip Phase 8 Sanctum REST API (for now)

## Context

The build plan included Phase 8: a Sanctum-authenticated `/api/v1` layer mirroring web functionality for a possible future mobile app or integrations. The web UI (Phases 1–7) already delivers the school management product.

Product owner decided a REST API is not required for the current release.

## Decision

**Skip Phase 8.** Do not install/implement Sanctum API routes, API resources, or a full `/api/v1` mirror at this time.

`docs/api/*.md` remain as **web route inventories** (and future API placeholders), not a live Sanctum surface.

## Consequences

- Next build work can focus on Phase 9 (audit log), coverage ≥80%, or hardening.
- A thin or full API can be added later without rewriting domain Actions/Policies.
- Deliverable checklist marks REST API as skipped/out of scope for the current release.
