# Changelog

All notable phase-level changes for **Smart School Data Gathering & Management System (SMIS)**.

Detailed day-to-day notes live in `docs/PROJECT_STATUS.md`.

## [Unreleased]

### Phase 8 — API layer (2026-08-10) — skipped

- Product decision: no Sanctum `/api/v1` for the current web-only release (ADR 0009).
- Domain Actions/Policies remain reusable if an API is added later.

### Phase 7 — Reporting & analytics (2026-08-10)

- Added demographics, attendance, examination, and performance reports with role scoping.
- Chart.js dashboards; CSV + print exports; ranking/stats unit coverage; 217 tests passing.

### Phase 6 — Examination module (2026-08-10)

- Added `exams`, `exam_subjects`, and `marks` with scoped mark entry and publish lock.
- Grade-letter and pass/fail calculators with full unit branch coverage; ExaminationSeeder demo; 196 tests passing.

### Phase 5 — Attendance module (2026-08-10)

- Added `attendance_sessions`, `student_attendance`, `teacher_attendance` with role-scoped capture.
- Monthly percentage summaries; finalize lock for teachers; AttendanceSeeder demo data; 160 tests passing.

### Phase 4 — Timetable module (2026-08-10)

- Added `timetables` and `relief_teacher_assignments` with conflict detection service.
- Admin class timetable builder + manual relief workflow.
- Teacher/student timetable views; TimetableSeeder demo slots; 133 tests passing.

### Phase 3 — Teacher & Student modules (2026-08-10)

- Added `teachers`, `students`, `teacher_class_subject_assignments`, `student_enrollments`.
- Admin teacher/student CRUD; assignment sync; class-teacher scoped student management.
- Student categorization filters (grade/class/subject/gender); role dashboards show profile data.
- Retargeted `classes.class_teacher_id` to `teachers`; 117 tests passing.

### Phase 2 — Academic structure & Admin core (2026-08-10)

- Added `academic_years`, `grades`, `streams`, `subjects`, `classes`, and `class_subject` tables.
- Admin CRUD UI + policies gated by `manage-system-config`.
- Stream eligibility rules for grades 12–13; auto class codes; subject sync per class grade.
- `AcademicStructureSeeder` demo data; 99 tests passing.

### Phase 1 — Auth & Authorization (2026-08-10)

- Seeded Spatie roles (`admin`, `teacher`, `student`) and permissions.
- Extended `users` with `status` + SoftDeletes; wired `HasRoles`.
- Disabled public registration; admin-only user creation flow.
- Role-specific dashboard shells and login redirects.
- `EnsureUserIsActive` middleware; inactive users cannot log in.
- `UserPolicy` with full ability coverage tests.

### Phase 0 — Scaffolding (2026-08-10)

- Initialized documentation tree under `/docs` (architecture, modules, api, decisions, testing, setup).
- Added Cursor operating rules (`.cursor/rules/project.mdc`).
- Installed and published `spatie/laravel-permission`.
- Created base application folders: `app/Actions`, `app/Services`, `app/Policies`, `app/Enums`.
- Confirmed CI workflow (`.github/workflows/tests.yml`).
- Documented ADRs: Spatie permission, Fortify/Livewire vs Breeze, SQLite local / MySQL production.
- Added Phase 0 scaffolding feature test.
