# Project Status — Smart School Data Gathering & Management System

## Current Phase

**Phase 1 — Auth & Authorization foundation** (complete — awaiting review before Phase 2)

## Module Tracker

| Module | Status | % Complete | Test Coverage | Last Updated | Notes |
|---|---|---|---|---|---|
| Auth | Done | 100% | Feature + policy tests passing | 2026-08-10 | Roles/permissions, admin user creation, inactive gate, role dashboards |
| Admin | In Progress | 15% | Dashboard + create-user flow tested | 2026-08-10 | Shell + user creation only; academic CRUD in Phase 2 |
| Teacher | Not Started | 5% | Dashboard shell tested | 2026-08-10 | Empty teacher dashboard shell only |
| Student | Not Started | 5% | Dashboard shell tested | 2026-08-10 | Empty student dashboard shell only |
| Attendance | Not Started | 0% | — | 2026-08-10 | Phase 5 |
| Timetable | Not Started | 0% | — | 2026-08-10 | Phase 4 |
| Examination | Not Started | 0% | — | 2026-08-10 | Phase 6 |
| Reporting | Not Started | 0% | — | 2026-08-10 | Phase 7 |

## Deliverables Checklist

- [ ] Functional centralized school management web system
- [x] Role-based authentication (Admin / Teacher / Student)
- [ ] Student management module
- [ ] Teacher management module
- [ ] Attendance management module
- [ ] Timetable management module
- [ ] Examination management module
- [ ] Reporting & analytics module
- [ ] REST API mirroring web functionality
- [ ] Automated test suite meeting coverage targets
- [x] Complete `/docs` documentation set (skeleton + Phase 1 updates)
- [x] `docs/PROJECT_STATUS.md` kept current

## Changelog

- **2026-08-10** — Phase 1 complete: Spatie roles/permissions seeded; `User` gains `HasRoles`, `SoftDeletes`, `status`; public registration disabled; admin-only user creation (`CreateUser` action + Form Request + policy); role dashboard shells; `EnsureUserIsActive` + Spatie `role` middleware; Fortify login redirect by role; inactive users blocked; full suite 58 passing.
- **2026-08-10** — Phase 0 scaffolding complete: Spatie install, docs skeleton, Cursor rules, base folders, CI present.

## Known Issues / TODO

- Sanctum / API layer deferred to Phase 8.
- Overall ≥80% line coverage not yet measured with `--coverage` against growing domain code (logged in coverage-log).
- Teacher/student profile tables and assignment pivots deferred to Phase 3.
- Activity/audit log package not yet installed (Phase 9 / hardening).

## Decisions Needed From Product Owner

| Topic | Assumption (until decided) | Status |
|---|---|---|
| Auth stack: Breeze vs Fortify/Livewire | Keep Fortify + Livewire + Flux | Assumed |
| Local DB: SQLite vs MySQL | SQLite for local/tests; MySQL production | Assumed |
| Default admin seed credentials | `admin@smis.test` / `password` (local only) | Assumed |
| Pass mark thresholds / grade letters | Defer defaults to Phase 6 | Pending |
| Stream names for Grades 12–13 | Science, Commerce, Arts, Technology | Assumed |
| Relief teacher allocation | Manual assignment with conflict detection | Assumed |
| Class teacher marks entry for all subjects | Configurable; default allow for own class | Assumed |
