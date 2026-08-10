# Project Status — Smart School Data Gathering & Management System

## Current Phase

**Phase 7 — Reporting & analytics** (complete — awaiting review before Phase 8)

## Module Tracker

| Module | Status | % Complete | Test Coverage | Last Updated | Notes |
|---|---|---|---|---|---|
| Auth | Done | 100% | Feature + policy tests passing | 2026-08-10 | Roles/permissions, admin user creation, inactive gate, role dashboards |
| Admin | Done | 100% | Academic + people + attendance + exams + reports | 2026-08-10 | Reporting dashboards included |
| Teacher | Done | 100% | Profiles, assignments, students, timetable, attendance, marks, reports | 2026-08-10 | Scoped analytics |
| Student | Done | 100% | CRUD, enrollment, filters, timetable, attendance, results, own report | 2026-08-10 | Read-only own report |
| Attendance | Done | 100% | Feature + policy + % calculator unit coverage | 2026-08-10 | Sessions, teacher attendance, monthly summaries |
| Timetable | Done | 100% | Feature + policy + conflict unit coverage | 2026-08-10 | Class builder, teacher/student views, relief workflow |
| Examination | Done | 100% | Feature + policy + grade/pass unit branch coverage | 2026-08-10 | Exams, subjects, marks, publish lock |
| Reporting | Done | 100% | Feature + policy + ranking/stats unit coverage | 2026-08-10 | Charts, CSV, print/PDF, best/poor |

## Deliverables Checklist

- [ ] Functional centralized school management web system
- [x] Role-based authentication (Admin / Teacher / Student)
- [x] Student management module (grade/class/subject/gender filters; streams via class)
- [x] Teacher management module (class/subject/PT-PD assignments)
- [x] Attendance management module (student + teacher, monthly summaries)
- [x] Timetable management module (class/teacher views, relief, conflict detection)
- [x] Examination management module (term tests, scholarship, O/L, A/L)
- [x] Reporting & analytics module (grade/class/subject/gender-wise, best/poor performers)
- [ ] REST API mirroring web functionality
- [ ] Automated test suite meeting coverage targets
- [x] Complete `/docs` documentation set (skeleton + Phase 1–7 updates)
- [x] `docs/PROJECT_STATUS.md` kept current

## Changelog

- **2026-08-10** — Phase 7 complete: demographics/attendance/exam analytics; best/poor rankings; Chart.js dashboards; CSV + print export; 217 tests passing.
- **2026-08-10** — Phase 6 complete: exams + exam subjects + marks entry; grade-letter/pass-fail calculators; publish lock; 196 tests passing.
- **2026-08-10** — Phase 5 complete: attendance sessions + student/teacher attendance; role-scoped capture; monthly % summaries; 160 tests passing.
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
- True DomPDF / XLSX packages not installed — CSV + browser print used instead.

## Decisions Needed From Product Owner

| Topic | Assumption (until decided) | Status |
|---|---|---|
| Auth stack: Breeze vs Fortify/Livewire | Keep Fortify + Livewire + Flux | Assumed |
| Local DB: SQLite vs MySQL | SQLite for local/tests; MySQL production | Assumed |
| Default admin seed credentials | `admin@smis.test` / `password` (local only) | Assumed |
| Demo teacher/student seeds | `class.teacher@smis.test`, `subject.teacher@smis.test`, `student@smis.test` / `password` | Assumed |
| Periods per day | 8 periods, Monday–Friday | Assumed |
| Relief teacher allocation | Manual assignment with conflict detection | Assumed |
| Subject-teacher attendance | Enabled by default for assigned subject/class | Assumed |
| Attendance % formula | Present+Late attended; Excused excluded from denominator | Assumed |
| Class teacher attendance scope | May take class-level and any subject session in own class | Assumed |
| Grade letters | A≥75, B≥65, C≥55, S≥40, else F (of max marks) | Assumed |
| Pass/fail | `marks_obtained >= pass_mark` (per exam subject; default pass 40/100) | Assumed |
| Class teacher marks entry for all subjects | Allowed for own class | Assumed |
| Report exports | CSV download + browser print-to-PDF (no DomPDF/Excel packages yet) | Assumed |
| Best/poor performers | Top/bottom 5 by average % across exam subjects | Assumed |
| Stream names for Grades 12–13 | Science, Commerce, Arts, Technology | Assumed |
| Class code format | `{grade}-{section}` or `{grade}-{STREAM}-{section}` | Assumed |
| Class teacher limited student fields | Name, email, admission, DOB, gender, guardian; no status/password/class move | Assumed |
