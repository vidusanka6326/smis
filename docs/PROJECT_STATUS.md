# Project Status — Smart School Data Gathering & Management System

## Current Phase

**Auth — Officer role** (Done)

Admin-only Officers CRUD replaces Create user; officers get school data-entry access + activity log (ADR 0013).

## Module Tracker

| Module | Status | % Complete | Test Coverage | Last Updated | Notes |
|---|---|---|---|---|---|
| Auth | Done | 100% | Feature + policy tests passing | 2026-08-14 | Roles incl. officer; Officers CRUD; inactive gate; role dashboards |
| Admin | Done | 100% | Academic + people + attendance + exams + reports + activity log + officers + dashboard | 2026-08-14 | Shared with officers for data entry |
| Teacher | Done | 100% | Profiles, assignments, students, timetable, attendance, marks, reports, dashboard | 2026-08-14 | Scoped reports enrichment + theme |
| Student | Done | 100% | CRUD, enrollment, filters, timetable, attendance, results, own report, dashboard | 2026-08-14 | Report-card depth + theme |
| Attendance | Done | 100% | Feature + policy + % calculator unit coverage | 2026-08-10 | Sessions, teacher attendance, monthly summaries; audited upserts |
| Timetable | Done | 100% | Feature + policy + conflict + period schedule unit | 2026-08-10 | Visual period×day grid + default times |
| Examination | Done | 100% | Feature + policy + grade/pass unit branch coverage | 2026-08-10 | Exams, subjects, marks, publish lock; audited marks/publish |
| Reporting | Done | 100% | Feature + policy + ranking/stats + at-risk/by_class unit coverage | 2026-08-14 | At-risk attendance, by_class exam stats, themed UI |
| API (Sanctum) | Skipped | 0% | — | 2026-08-10 | Phase 8 skipped; see ADR 0009 |
| Hardening / audit | Done | 100% | Feature + policy + unit logger tests | 2026-08-10 | Custom `activity_logs` (ADR 0010) |

## Deliverables Checklist

- [x] Functional centralized school management web system (Phases 1–7)
- [x] Role-based authentication (Admin / Teacher / Student)
- [x] Student management module (grade/class/subject/gender filters; streams via class)
- [x] Teacher management module (class/subject/PT-PD assignments)
- [x] Attendance management module (student + teacher, monthly summaries)
- [x] Timetable management module (class/teacher views, relief, conflict detection)
- [x] Examination management module (term tests, scholarship, O/L, A/L)
- [x] Reporting & analytics module (grade/class/subject/gender-wise, best/poor performers)
- [x] REST API mirroring web functionality — **Skipped** (web-only; ADR 0009)
- [ ] Automated test suite meeting coverage targets (≥80% line coverage not yet measured — no pcov/xdebug)
- [x] Complete `/docs` documentation set (through Phase 9)
- [x] `docs/PROJECT_STATUS.md` kept current

## Changelog

- **2026-08-14** — Officer role + admin-only Officers section; removed Create user; officers share data-entry routes + activity log (ADR 0013).
- **2026-08-14** — Split form layouts: shared `x-form` page/section/grid components; all create/edit screens sectioned + multi-column.
- **2026-08-14** — Decluttered role dashboards: hero + few KPIs + one chart + action lists (cut widget spam).
- **2026-08-14** — Aligned system colors to the SMIS logo (teal → mint primary, light/dark).
- **2026-08-14** — Applied violet `shad.css` theme (light + dark) with Plus Jakarta Sans / Lora; Flux accent → primary.
- **2026-08-14** — Applied coral/peach `sha.css` theme (light + dark) with Montserrat/Merriweather; Flux accent → primary.
- **2026-08-14** — Full-coverage role dashboards: denser KPIs, subject/class exam charts, at-risk & ranking panels, student attendance/subject analytics.
- **2026-08-14** — Reports enrichment + shad theme: at-risk attendance (&lt;80%), exam by-class stats, student report card (P/A/L/E + grouped exams), mint/Outfit Flux accent; reporting unit/feature tests updated.
- **2026-08-10** — Added root `README.md` (stack, setup, demo accounts, docs index).
- **2026-08-10** — Replaced starter welcome page with branded SMIS homepage (hero + module overview + auth CTAs).
- **2026-08-10** — Removed Fortify passkeys (login/settings UI, `@laravel/passkeys`, `passkeys` table); password + 2FA remain (ADR 0012).
- **2026-08-10** — Dashboard/timetable UI: Chart.js KPIs on admin/teacher/student dashboards (`RoleDashboardMetrics`); shared `x-timetable.grid` with period clock times (ADR 0011); **233 tests** passing.
- **2026-08-10** — Phase 9: custom `activity_logs` + `ActivityLogger`; wired into user create, marks, exam publish, attendance; admin viewer; ADR 0010; **228 tests** passing. Coverage % still blocked without pcov/xdebug.
- **2026-08-10** — Phase 8 skipped by product decision: no Sanctum `/api/v1` for current release (ADR 0009).
- **2026-08-10** — Phase 7 complete: demographics/attendance/exam analytics; best/poor rankings; Chart.js dashboards; CSV + print export; 217 tests passing.
- **2026-08-10** — Phase 6 complete: exams + exam subjects + marks entry; grade-letter/pass-fail calculators; publish lock; 196 tests passing.
- **2026-08-10** — Phase 5 complete: attendance sessions + student/teacher attendance; role-scoped capture; monthly % summaries; 160 tests passing.
- **2026-08-10** — Phase 4 complete: `timetables` + `relief_teacher_assignments`; admin class timetable builder with conflict detection; teacher/student timetable views; manual relief workflow; 133 tests passing.
- **2026-08-10** — Phase 3 complete: teacher/student profiles, assignments, enrollments; class-teacher scoped student CRUD; filters; 117 tests passing.
- **2026-08-10** — Phase 2 complete: academic years, grades, streams, subjects, classes CRUD; 99 tests passing.
- **2026-08-10** — Phase 1 complete: Spatie roles/permissions; admin user creation; role dashboards; 58 tests passing.
- **2026-08-10** — Phase 0 scaffolding complete.

## Known Issues / TODO

- Sanctum / `/api/v1` **skipped** for current release (can revisit later without rewriting Policies/Actions).
- Overall ≥80% line coverage not yet measured — install pcov or xdebug, then run `php artisan test --coverage` and log in `docs/testing/coverage-log.md`.
- `admins` profile extension table still deferred.
- Period count fixed at 8 (Mon–Fri); make configurable later if needed.
- True DomPDF / XLSX packages not installed — CSV + browser print used instead.
- Re-run `RolesAndPermissionsSeeder` on existing environments to pick up `view-activity-log` and `manage-officers` / `officer` role.

## Decisions Needed From Product Owner

| Topic | Assumption (until decided) | Status |
|---|---|---|
| Auth stack: Breeze vs Fortify/Livewire | Keep Fortify + Livewire + Flux | Assumed |
| Local DB: SQLite vs MySQL | SQLite for local/tests; MySQL production | Assumed |
| Default admin seed credentials | `admin@smis.test` / `password` (local only) | Assumed |
| Demo teacher/student seeds | `class.teacher@smis.test`, `subject.teacher@smis.test`, `student@smis.test` / `password` | Assumed |
| Demo officer seed | `officer@smis.test` / `password` (local only) | Assumed |
| Periods per day | 8 periods, Monday–Friday; default times via PeriodSchedule (ADR 0011) | Assumed |
| Relief teacher allocation | Manual assignment with conflict detection | Assumed |
| Subject-teacher attendance | Enabled by default for assigned subject/class | Assumed |
| Attendance % formula | Present+Late attended; Excused excluded from denominator | Assumed |
| Class teacher attendance scope | May take class-level and any subject session in own class | Assumed |
| Grade letters | A≥75, B≥65, C≥55, S≥40, else F (of max marks) | Assumed |
| Pass/fail | `marks_obtained >= pass_mark` (per exam subject; default pass 40/100) | Assumed |
| Class teacher marks entry for all subjects | Allowed for own class | Assumed |
| Report exports | CSV download + browser print-to-PDF (no DomPDF/Excel packages yet) | Assumed |
| Best/poor performers | Top/bottom 5 by average % across exam subjects | Assumed |
| Attendance at-risk threshold | Monthly attendance **&lt; 80%** flagged as needs attention | Assumed |
| REST API (Phase 8) | Skip for current web-only release | **Decided — skipped** |
| Audit log implementation | Custom `activity_logs` (no Spatie Activitylog package) | **Decided — ADR 0010** |
| Stream names for Grades 12–13 | Science, Commerce, Arts, Technology | Assumed |
| Class code format | `{grade}-{section}` or `{grade}-{STREAM}-{section}` | Assumed |
| Class teacher limited student fields | Name, email, admission, DOB, gender, guardian; no status/password/class move | Assumed |
