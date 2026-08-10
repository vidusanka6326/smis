# Project Status — Smart School Data Gathering & Management System

## Current Phase

**Phase 4 — Timetable module** (complete — awaiting review before Phase 5)

## Module Tracker

| Module | Status | % Complete | Test Coverage | Last Updated | Notes |
|---|---|---|---|---|---|
| Auth | Done | 100% | Feature + policy tests passing | 2026-08-10 | Roles/permissions, admin user creation, inactive gate, role dashboards |
| Admin | Done | 95% | Academic + teacher/student admin CRUD covered | 2026-08-10 | Includes timetable + relief admin UI |
| Teacher | Done | 90% | Profiles, assignments, students, own timetable view | 2026-08-10 | Attendance next |
| Student | Done | 90% | CRUD, enrollment, filters, class timetable view | 2026-08-10 | Attendance/results later |
| Attendance | Not Started | 0% | — | 2026-08-10 | Phase 5 |
| Timetable | Done | 100% | Feature + policy + conflict unit coverage | 2026-08-10 | Class builder, teacher/student views, relief workflow |
| Examination | Not Started | 0% | — | 2026-08-10 | Phase 6 |
| Reporting | Not Started | 0% | — | 2026-08-10 | Phase 7 |

## Deliverables Checklist

- [ ] Functional centralized school management web system
- [x] Role-based authentication (Admin / Teacher / Student)
- [x] Student management module (grade/class/subject/gender filters; streams via class)
- [x] Teacher management module (class/subject/PT-PD assignments)
- [ ] Attendance management module
- [x] Timetable management module (class/teacher views, relief, conflict detection)
- [ ] Examination management module
- [ ] Reporting & analytics module
- [ ] REST API mirroring web functionality
- [ ] Automated test suite meeting coverage targets
- [x] Complete `/docs` documentation set (skeleton + Phase 1–4 updates)
- [x] `docs/PROJECT_STATUS.md` kept current

## Changelog

- **2026-08-10** — Phase 4 complete: `timetables` + `relief_teacher_assignments`; admin class timetable builder with conflict detection; teacher/student timetable views; manual relief workflow; 133 tests passing.
- **2026-08-10** — Phase 3 complete: teacher/student profiles, assignments, enrollments; class-teacher scoped student CRUD; filters; 117 tests passing.
- **2026-08-10** — Phase 2 complete: academic years, grades, streams, subjects, classes CRUD; 99 tests passing.
- **2026-08-10** — Phase 1 complete: Spatie roles/permissions; admin user creation; role dashboards; 58 tests passing.
- **2026-08-10** — Phase 0 scaffolding complete.

## Known Issues / TODO

- Sanctum / API layer deferred to Phase 8.
- Overall ≥80% line coverage not yet measured with `--coverage`.
- Activity/audit log package not yet installed (Phase 9).
- `admins` profile extension table still deferred.
- Period count fixed at 8 (Mon–Fri); make configurable later if needed.

## Decisions Needed From Product Owner

| Topic | Assumption (until decided) | Status |
|---|---|---|
| Auth stack: Breeze vs Fortify/Livewire | Keep Fortify + Livewire + Flux | Assumed |
| Local DB: SQLite vs MySQL | SQLite for local/tests; MySQL production | Assumed |
| Default admin seed credentials | `admin@smis.test` / `password` (local only) | Assumed |
| Demo teacher/student seeds | `class.teacher@smis.test`, `subject.teacher@smis.test`, `student@smis.test` / `password` | Assumed |
| Periods per day | 8 periods, Monday–Friday | Assumed |
| Relief teacher allocation | Manual assignment with conflict detection | Assumed |
| Pass mark thresholds / grade letters | Defer defaults to Phase 6 | Pending |
| Stream names for Grades 12–13 | Science, Commerce, Arts, Technology | Assumed |
| Class teacher marks entry for all subjects | Configurable; default allow for own class | Assumed |
| Class code format | `{grade}-{section}` or `{grade}-{STREAM}-{section}` | Assumed |
| Class teacher limited student fields | Name, email, admission, DOB, gender, guardian; no status/password/class move | Assumed |
