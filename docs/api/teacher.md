# Teacher API

Web routes (Phase 3). Sanctum REST mirror deferred to Phase 8.

| Method | Path | Auth | Notes |
|---|---|---|---|
| resource | `/admin/teachers` | admin + `manage-teachers` | Profile CRUD |
| GET/PUT | `/admin/teachers/{id}/assignments` | admin + `manage-teachers` | Body: `academic_year_id`, `assignments[]` |
| GET | `/teacher/dashboard` | teacher | Own assignments |
| resource | `/teacher/students` | teacher (class teacher scope) | except show/destroy |
