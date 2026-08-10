# Admin API

Web routes for admin academic structure (Phase 2). Sanctum REST mirror deferred to Phase 8.

| Method | Path | Auth | Notes |
|---|---|---|---|
| GET/POST | `/admin/users*` | admin + `manage-users` | User creation |
| resource | `/admin/academic-years` | admin + `manage-system-config` | CRUD except show |
| resource | `/admin/grades` | admin + `manage-system-config` | CRUD except show |
| resource | `/admin/streams` | admin + `manage-system-config` | CRUD except show |
| resource | `/admin/subjects` | admin + `manage-system-config` | CRUD except show |
| resource | `/admin/classes` | admin + `manage-system-config` | CRUD except show; body may include `subject_ids[]` |
