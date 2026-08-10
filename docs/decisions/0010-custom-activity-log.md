# ADR 0010 — Custom activity log (no Spatie Activitylog package)

## Context

NFRs require an audit trail for sensitive actions (marks edits, attendance edits after finalization, user creation/role assignment). Spatie Activitylog is a common package, but this project avoids new Composer dependencies without approval.

## Decision

Implement a **custom** `activity_logs` table + `ActivityLogger` service, wired into domain Actions (`CreateUser`, `UpsertMarks`, `PublishExam`, `UpsertAttendanceSession`, `UpsertTeacherAttendance`). Admin-only viewer at `/admin/activity-logs` gated by `view-activity-log`.

## Consequences

- No third-party package upgrade risk; schema stays lean and app-owned.
- Logging is explicit in Actions (easy to audit in code review).
- Can migrate to Spatie Activitylog later if product wants richer tooling (diff UI, soft-delete events, etc.).
