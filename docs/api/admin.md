# Admin API

Web routes for admin academic structure and account management. Sanctum REST mirror skipped (ADR 0009).

| Method | Path | Auth | Notes |
|---|---|---|---|
| resource | `/admin/officers` | admin + `manage-officers` | Officers CRUD (ADR 0013) |
| GET | `/admin/activity-logs` | admin\|officer + `view-activity-log` | Audit trail viewer |
| resource | `/admin/academic-years` | admin\|officer + `manage-system-config` | CRUD except show |
| resource | `/admin/grades` | admin\|officer + `manage-system-config` | CRUD except show |
| resource | `/admin/streams` | admin\|officer + `manage-system-config` | CRUD except show |
| resource | `/admin/subjects` | admin\|officer + `manage-system-config` | CRUD except show |
| resource | `/admin/classes` | admin\|officer + `manage-system-config` | CRUD except show; body may include `subject_ids[]` |
