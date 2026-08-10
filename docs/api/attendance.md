# Attendance API

Web routes (Phase 5). Sanctum REST mirror deferred to Phase 8.

| Method | Path | Auth | Notes |
|---|---|---|---|
| GET/POST | `/admin/attendance/sessions` | admin + manage-attendance | List/create |
| PUT/DELETE | `/admin/attendance/sessions/{id}` | admin + manage-attendance | Update/delete |
| POST | `/admin/attendance/sessions/{id}/finalize` | admin + manage-attendance | Finalize |
| GET | `/admin/attendance/monthly` | admin + view/manage attendance | Query: month, class, subject |
| GET/POST/DELETE | `/admin/attendance/teachers*` | admin + manage-attendance | Teacher daily |
| GET/POST/PUT | `/teacher/attendance/sessions*` | teacher + manage-attendance | Scoped |
| GET | `/teacher/attendance/monthly` | teacher + view-attendance | Scoped |
| GET/POST | `/teacher/attendance/self*` | teacher + manage-attendance | Own daily only |
| GET | `/student/attendance` | student + view-attendance | Own history/% |
