# Project Status — Smart School Data Gathering & Management System

## Current Phase

**Phase 3 — Teacher & Student modules** (complete — awaiting review before Phase 4)

## Module Tracker

| Module | Status | % Complete | Test Coverage | Last Updated | Notes |
|---|---|---|---|---|---|
| Auth | Done | 100% | Feature + policy tests passing | 2026-08-10 | Roles/permissions, admin user creation, inactive gate, role dashboards |
| Admin | Done | 95% | Academic + teacher/student admin CRUD covered | 2026-08-10 | Teacher/student management + academic structure |
| Teacher | Done | 85% | Profiles, assignments, class-teacher student CRUD, dashboards | 2026-08-10 | PT/PD workflows expand with attendance/timetable |
| Student | Done | 85% | CRUD, enrollment, filters, class-teacher scope, read-only dashboard | 2026-08-10 | Marks/attendance views in later phases |
| Attendance | Not Started | 0% | — | 2026-08-10 | Phase 5 |
| Timetable | Not Started | 0% | — | 2026-08-10 | Phase 4 |
| Examination | Not Started | 0% | — | 2026-08-10 | Phase 6 |
| Reporting | Not Started | 0% | — | 2026-08-10 | Phase 7 |

## Deliverables Checklist

- [ ] Functional centralized school management web system
- [x] Role-based authentication (Admin / Teacher / Student)
- [x] Student management module (grade/class/subject/gender filters; streams via class)
- [x] Teacher management module (class/subject/PT-PD assignments)
- [ ] Attendance management module
- [ ] Timetable management module
- [ ] Examination management module
- [ ] Reporting & analytics module
- [ ] REST API mirroring web functionality
- [ ] Automated test suite meeting coverage targets
- [x] Complete `/docs` documentation set (skeleton + Phase 1–3 updates)
- [x] `docs/PROJECT_STATUS.md` kept current

## Changelog

- **2026-08-10** — Phase 3 complete: `teachers` / `students` profiles, `teacher_class_subject_assignments`, `student_enrollments`; admin teacher/student CRUD; class-teacher scoped student create/update; categorization filters; `classes.class_teacher_id` now FK to `teachers`; 117 tests passing.
- **2026-08-10** — Phase 2 complete: academic years, grades, streams, subjects, classes CRUD (admin-only) with `manage-system-config` policies; stream rules for grades 12–13; class–subject pivot sync; AcademicStructureSeeder; 99 tests passing.
- **2026-08-10** — Phase 1 complete: Spatie roles/permissions seeded; `User` gains `HasRoles`, `SoftDeletes`, `status`; public registration disabled; admin-only user creation; role dashboard shells; inactive gate; 58 tests passing.
- **2026-08-10** — Phase 0 scaffolding complete: Spatie install, docs skeleton, Cursor rules, base folders, CI present.

## Known Issues / TODO

- Sanctum / API layer deferred to Phase 8.
- Overall ≥80% line coverage not yet measured with `--coverage` against growing domain code (logged in coverage-log).
- Activity/audit log package not yet installed (Phase 9 / hardening).
- Teacher assignment UI uses Alpine dynamic rows (not Livewire); acceptable for Phase 3.
- `admins` profile extension table still deferred (admin uses `users` + role only).

## Decisions Needed From Product Owner

| Topic | Assumption (until decided) | Status |
|---|---|---|
| Auth stack: Breeze vs Fortify/Livewire | Keep Fortify + Livewire + Flux | Assumed |
| Local DB: SQLite vs MySQL | SQLite for local/tests; MySQL production | Assumed |
| Default admin seed credentials | `admin@smis.test` / `password` (local only) | Assumed |
| Demo teacher/student seeds | `class.teacher@smis.test`, `subject.teacher@smis.test`, `student@smis.test` / `password` | Assumed |
| Pass mark thresholds / grade letters | Defer defaults to Phase 6 | Pending |
| Stream names for Grades 12–13 | Science, Commerce, Arts, Technology | Assumed |
| Relief teacher allocation | Manual assignment with conflict detection | Assumed |
| Class teacher marks entry for all subjects | Configurable; default allow for own class | Assumed |
| Class code format | `{grade}-{section}` or `{grade}-{STREAM}-{section}` | Assumed |
| Class teacher limited student fields | Name, email, admission, DOB, gender, guardian; no status/password/class move | Assumed |
