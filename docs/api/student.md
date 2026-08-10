# Student API

Web routes (Phase 3). Sanctum REST mirror deferred to Phase 8.

| Method | Path | Auth | Notes |
|---|---|---|---|
| resource | `/admin/students` | admin + `manage-students` | Index supports `search`, `gender`, `grade_id`, `class_id`, `subject_id` |
| resource | `/teacher/students` | class teacher | Limited create/update |
| GET | `/student/dashboard` | student | Read-only own profile/class |
