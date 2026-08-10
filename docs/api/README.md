# API Documentation Index

Versioned REST API at `/api/v1/...` (Sanctum) is introduced in **Phase 8**.

Until then, this folder holds placeholders per module. Each endpoint doc should list: method, path, auth/role required, request/response shape.

| File | Module | Status |
|---|---|---|
| [auth.md](auth.md) | Auth | Placeholder |
| [admin.md](admin.md) | Admin | Placeholder |
| [teacher.md](teacher.md) | Teacher | Placeholder |
| [student.md](student.md) | Student | Placeholder |
| [attendance.md](attendance.md) | Attendance | Placeholder |
| [timetable.md](timetable.md) | Timetable | Placeholder |
| [examination.md](examination.md) | Examination | Placeholder |
| [reporting.md](reporting.md) | Reporting | Placeholder |

**Rule:** API controllers reuse the same Policies and Form Requests as the web UI — do not duplicate authorization logic.
