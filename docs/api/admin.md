# Admin API

Web routes for admin academic structure and account management. Sanctum REST mirror skipped (ADR 0009).

| Method | Path | Auth | Notes |
|---|---|---|---|
| GET/POST | `/admin/users*` | admin + `manage-users` | User creation |
| GET | `/admin/activity-logs` | admin + `view-activity-log` | Audit trail viewer |
| resource | `/admin/academic-years` | admin + `manage-system-config` | CRUD except show |
| resource | `/admin/grades` | admin + `manage-system-config` | CRUD except show |
| resource | `/admin/streams` | admin + `manage-system-config` | CRUD except show |
| resource | `/admin/subjects` | admin + `manage-system-config` | CRUD except show |
| resource | `/admin/classes` | admin + `manage-system-config` | CRUD except show; body may include `subject_ids[]` |
