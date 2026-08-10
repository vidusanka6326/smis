# ADR 0005 — Manual relief teacher allocation

## Context

When the scheduled teacher is unavailable, another teacher may cover that period. The product brief allows either manual assignment or auto-suggestion. Auto-suggestion needs availability scoring, preference rules, and workload balancing that are not yet specified.

## Decision

Relief is **manual**: an admin picks the timetable entry, date, and relief teacher. The system enforces:

- date weekday matches the entry’s `day_of_week`
- relief teacher ≠ original teacher
- relief teacher is not already busy that period (timetable or another relief)

## Consequences

- `AssignReliefTeacher` action owns validation; UI is admin-only.
- Auto-suggest / “available teachers” helpers can be added later without schema change.
- Product owner can revisit auto-allocation in a later phase.
