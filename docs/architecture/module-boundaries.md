# Module Boundaries

| Module | Owns | Depends on | Phase |
|---|---|---|---|
| Auth | Login/logout/password reset, roles, permissions, middleware, policy scaffolding | Fortify, Spatie | 1 |
| Admin | Admin dashboard, user management, system config | Auth, Academic structure | 1–2 |
| Academic structure | Academic years, grades, streams, subjects, classes | Auth | 2 |
| Teacher | Teacher profiles, assignment types (class/subject/PT-PD) | Auth, Academic | 3 |
| Student | Student profiles, enrollments, categorization | Auth, Academic, Teacher (class scope) | 3 |
| Timetable | Class/teacher timetables, relief, conflict detection | Academic, Teacher | 4 |
| Attendance | Student/teacher attendance, monthly summaries | Academic, Teacher, Student | 5 |
| Examination | Exams, marks entry, pass/fail, grade letters | Academic, Teacher, Student | 6 |
| Reporting | Analytics dashboards, exports | All domain modules | 7 |
| API | `/api/v1` Sanctum surface reusing Policies/Requests | All modules | 8 — **skipped** (ADR 0009) |

**Rule:** authorization = Spatie role/permission **AND** assignment-based scope, both enforced in Policies — never only in the UI.
