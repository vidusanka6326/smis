# API Documentation Index

## Status

**Phase 8 (Sanctum `/api/v1`) was skipped** for the current web-only release — see [ADR 0009](../decisions/0009-skip-phase-8-rest-api.md).

Files in this folder document **web routes** used by the Blade/Livewire UI (and remain ready if an API is added later). They are not a live Sanctum surface today.

| File | Module | Status |
|---|---|---|
| [auth.md](auth.md) | Auth | Web routes |
| [admin.md](admin.md) | Admin | Web routes |
| [teacher.md](teacher.md) | Teacher | Web routes |
| [student.md](student.md) | Student | Web routes |
| [attendance.md](attendance.md) | Attendance | Web routes (Phase 5) |
| [timetable.md](timetable.md) | Timetable | Web routes (Phase 4) |
| [examination.md](examination.md) | Examination | Web routes (Phase 6) |
| [reporting.md](reporting.md) | Reporting | Web routes (Phase 7) |
| [agent.md](agent.md) | SMIS Agent | Livewire chat (ADR 0020) |

**If/when an API is added:** controllers should reuse the same Policies and Form Requests as the web UI — do not duplicate authorization logic.
