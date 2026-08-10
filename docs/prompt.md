# SUPER PROMPT — Paste into Cursor to build the "Smart School Data Gathering & Management System"

> Copy everything below this line into Cursor (as a new Composer/Agent chat, or drop it into `.cursor/rules/project.mdc` + your first prompt as instructed inside). It is written to be handed to an AI coding agent directly.

---

## 0. HOW TO USE THIS PROMPT

1. Create an empty Git repo / Laravel project folder and open it in Cursor.
2. Paste **Section 2 (Cursor Operating Rules)** into `.cursor/rules/project.mdc` (or `.cursorrules` if you're on an older Cursor version) BEFORE you paste anything else. This makes the agent obey the docs/testing/status discipline for the rest of the build.
3. Then start a new Composer/Agent chat and paste **Sections 1, 3–12** as your first message, and tell the agent: *"Start with Phase 0 (scaffolding) and stop for my review before moving to Phase 1."*
4. Work phase-by-phase (Section 11). Do not let the agent jump ahead — each phase ends with the agent updating `docs/PROJECT_STATUS.md` and you reviewing before continuing.

---

## 1. PROJECT OVERVIEW

Build **"Smart School Data Gathering & Management System"** — a centralized, role-based, web-based school administration platform, using **Laravel** as the primary application framework.

### 1.1 Problem it solves
Schools currently rely on manual, paper-based, or fragmented digital record-keeping for student data, attendance, timetables, examinations, and reporting. This causes data loss, security risks, slow reporting, poor access control, and administrative overhead.

### 1.2 Goal
A single Laravel application providing:
- Role-based authentication and authorization (Admin, Teachers, Students).
- Centralized student data management (grade-wise, class-wise, subject-wise, gender-wise).
- Teacher management (Class Teachers, Subject Teachers, PT/PD Teachers) with scoped permissions.
- Timetable management (class timetables, teacher timetables, relief teacher allocation, period/subject scheduling).
- Attendance management (student & teacher, present/absent, monthly summaries).
- Examination & term test management (term tests, scholarship exams, O/L, A/L, pass/fail analysis, subject-wise results).
- Reporting & analytics dashboards (grade-wise, class-wise, subject-wise, gender-wise, attendance, examination statistics, best/poor performance reports).

### 1.3 Tech stack (mandatory)
- **Backend:** Laravel (latest stable LTS-compatible version), PHP 8.2+.
- **Database:** MySQL.
- **Frontend:** Laravel Blade + Tailwind CSS + Alpine.js/vanilla JavaScript for interactivity. (Use Livewire or Blade+Alpine for dynamic UI instead of a separate SPA, unless I explicitly ask for a React/Vue SPA later — keep this a monolith first.)
- **API layer:** Laravel REST API (Sanctum-authenticated) exposed alongside the web UI, so the system could later support a mobile app.
- **Auth:** Laravel Breeze or Fortify (Blade stack) + Spatie `laravel-permission` for role/permission management.
- **Testing:** Pest (preferred) or PHPUnit, with Laravel's built-in HTTP/feature testing tools.
- **Dev tools assumed:** VS Code/Cursor, GitHub, Postman/Insomnia for API testing.

### 1.4 Non-functional requirements
- Secure authentication, hashed passwords, CSRF protection, rate-limited login.
- Role-based access control (RBAC) enforced at route, controller, and policy level — never trust the frontend.
- Data privacy: teachers/students only ever see data they're authorized for.
- Responsive UI (desktop + tablet + mobile) using Tailwind.
- Auditable: key actions (marks entry, attendance edits, user creation) should be logged.
- Scalable schema: must support Grades 1–13, multiple classes per grade, multiple subjects, and streams (for Grade 12–13, e.g. Science/Commerce/Arts).

---

## 2. CURSOR OPERATING RULES (put this in `.cursor/rules/project.mdc`)

```md
---
description: Global project rules for Smart School Data Gathering & Management System
alwaysApply: true
---

# Project Operating Rules

You are building a production-grade Laravel application called "Smart School Data
Gathering & Management System". Follow these rules for EVERY task in this repo,
without exception.

## A. Documentation discipline

1. All application documentation lives under `/docs` at the project root. Never put
   design docs, ERDs, module specs, or API docs anywhere else.
2. Required structure (create it if missing):
   - `docs/PROJECT_STATUS.md` — living status tracker (see rule C below).
   - `docs/architecture/` — architecture overview, ER diagrams (as Mermaid), module
     boundaries, folder structure explanation.
   - `docs/modules/` — one `.md` file per module (auth.md, admin.md, teacher.md,
     student.md, attendance.md, timetable.md, examination.md, reporting.md). Each
     file documents: purpose, user roles involved, DB tables used, routes, key
     business rules, and edge cases handled.
   - `docs/api/` — API endpoint documentation grouped by module (method, path,
     auth/role required, request/response shape).
   - `docs/decisions/` — one short ADR (Architecture Decision Record) markdown file
     per notable technical decision (e.g. `0001-use-spatie-permission.md`). Use the
     format: Context / Decision / Consequences.
   - `docs/testing/` — testing strategy and current coverage notes.
   - `docs/setup/` — local dev setup, `.env` variables explained, seeding
     instructions.
3. Whenever you create or modify a module, controller, migration, or route group,
   update the corresponding file(s) under `docs/`. Documentation updates are NOT
   optional and are part of the definition of "done" for any task.
4. Never let `docs/` drift from the actual code. If you rename/remove something,
   update or delete the relevant doc in the same change.

## B. Project status tracking

1. `docs/PROJECT_STATUS.md` is the single source of truth for progress. It must
   always contain, kept up to date after every task:
   - A table of all modules (Auth, Admin, Teacher, Student, Attendance, Timetable,
     Examination, Reporting) with columns: `Status` (Not Started / In Progress /
     Done / Blocked), `% Complete`, `Test Coverage`, `Last Updated`, `Notes`.
   - A "Current Phase" section stating which phase from the build plan is active.
   - A running "Changelog" section (most recent entry at the top) — one bullet per
     meaningful change, dated, e.g. `2026-08-10 — Added Student CRUD + policies +
     feature tests (coverage: 92%)`.
   - A "Known Issues / TODO" section.
   - A "Decisions Needed From Product Owner" section for anything ambiguous — do not
     silently guess on business rules that affect grading, pass/fail logic, or
     access control; log the question here and pick the most reasonable default,
     clearly marked as an assumption.
2. Update `docs/PROJECT_STATUS.md` at the START of a task (mark it In Progress) and
   at the END of a task (mark it Done/Blocked, log the changelog entry, update
   coverage numbers). Never skip this step, even for small changes.
3. Before starting any new task, read `docs/PROJECT_STATUS.md` first to understand
   current state and avoid rebuilding or conflicting with existing work.

## C. Testing & coverage discipline

1. Every module MUST ship with automated tests before it is marked "Done" in
   PROJECT_STATUS.md. No feature is complete without tests.
2. Required test types per module:
   - **Unit tests** for models, form requests, custom validation rules, and helper
     classes (e.g. attendance percentage calculator, pass/fail calculator, GPA/
     average calculators).
   - **Feature tests** for every controller/route: happy path, validation failures,
     and — critically — authorization failures (assert a Teacher cannot access
     Admin routes, a Student cannot write data, a Class Teacher cannot touch another
     class's data, etc.).
   - **Policy tests** for every Laravel Policy class, covering every ability for
     every role.
   - At least one **integration/browser test** (Pest v3 browser testing, Dusk, or an
     HTTP test hitting multiple endpoints in sequence) per major user journey (e.g.
     "Admin creates teacher → assigns class → teacher logs in → uploads marks →
     student views result").
3. Minimum coverage targets (enforced, not aspirational):
   - Overall project: **≥ 80% line coverage**.
   - Authorization/policy code: **100% of ability methods must have a passing and a
     failing test.**
   - Any code that computes grades, pass/fail status, attendance percentages, or
     GPA/averages: **100% branch coverage**, since these are business-critical and
     hard to eyeball-verify.
4. Run the test suite (`php artisan test --coverage` or `pest --coverage`) before
   marking any task complete. Paste/record the resulting coverage summary into
   `docs/testing/coverage-log.md` with a date stamp.
5. If a task cannot reach the coverage target, do NOT mark it Done — mark it
   "In Progress — coverage gap" in PROJECT_STATUS.md and list exactly which cases
   are missing under Known Issues.
6. Never delete or weaken a test to make the suite pass. If a test seems wrong,
   flag it in PROJECT_STATUS.md instead of silently deleting it.
7. Use Laravel Model Factories + Seeders for all test data — never hand-craft
   ad-hoc DB rows in tests.

## D. Code & architecture conventions

1. Follow Laravel conventions: thin controllers, business logic in Action classes
   or Service classes under `app/Services/` or `app/Actions/`, validation in Form
   Request classes, authorization in Policy classes (never inline `if
   (auth()->user()->role...)` checks scattered through controllers).
2. Use Spatie `laravel-permission` for roles (`admin`, `teacher`, `student`) and
   granular permissions (e.g. `manage-timetable`, `enter-marks`, `view-reports`).
   Layer teacher-type distinctions (Class/Subject/PT-PD) and class-scoping on top
   of this via a dedicated pivot/assignment table, not by inventing new "roles" per
   class.
3. Use Laravel Policies for every model that has role-dependent access (Student,
   Attendance, Mark, Timetable, Report).
4. Use database transactions for any multi-table write (e.g. creating a student
   also creates a guardian record and enrollment record).
5. Use Form Request classes for all validation; never validate inline in a
   controller.
6. Name migrations, models, and tables consistently and in English, following
   Laravel naming conventions (snake_case tables/columns, singular model names).
7. Every table that can be soft-deleted (students, teachers, users) should use
   `SoftDeletes`.
8. Write PHPDoc blocks on non-trivial methods, especially calculation logic
   (attendance %, pass/fail, averages).
9. Keep a `CHANGELOG.md` at the project root that summarizes releases/phases (this
   is separate from and higher-level than `docs/PROJECT_STATUS.md`'s changelog).

## E. Workflow rules

1. Work in small, reviewable increments corresponding to the phases in the build
   plan I gave you. Do not silently jump ahead to a later phase.
2. At the end of every phase: run the full test suite, update
   `docs/PROJECT_STATUS.md`, update relevant `docs/modules/*.md` and
   `docs/api/*.md`, then summarize what changed and stop for review.
3. If a requirement is ambiguous (e.g. exact pass mark thresholds, exact stream
   names for Grade 12–13, whether relief teacher allocation is manual or
   auto-suggested), do not block — implement the most reasonable default, clearly
   label it as an assumption in code comments AND in the "Decisions Needed From
   Product Owner" section of PROJECT_STATUS.md.
4. Never invent scope creep (no unrelated modules, no premature SPA rewrite, no
   third-party services) without flagging it first.
```

---

## 3. USER ROLES & PERMISSIONS MATRIX

| Capability | Admin | Class Teacher | Subject Teacher | PT/PD Teacher | Student |
|---|---|---|---|---|---|
| Create/manage other admins | ✅ | ❌ | ❌ | ❌ | ❌ |
| Create/manage teachers | ✅ | ❌ | ❌ | ❌ | ❌ |
| Create/manage students | ✅ | ➕ (own class, limited fields) | ❌ | ❌ | ❌ |
| Assign class teacher to a class | ✅ | ❌ | ❌ | ❌ | ❌ |
| Manage timetables (all) | ✅ | ❌ | ❌ | ❌ | ❌ |
| View own timetable | ✅ | ✅ | ✅ | ✅ | ✅ |
| Manage attendance | ✅ | ✅ (own class) | ✅ (own periods, if enabled) | ✅ (own sessions) | ❌ |
| View attendance | ✅ | ✅ (own class) | ✅ (own subject's students) | ✅ (own sessions) | ✅ (own only) |
| Upload/edit marks | ✅ | ✅ (own class, all subjects — configurable) | ✅ (own subject only) | ❌ | ❌ |
| View marks/results | ✅ | ✅ (own class) | ✅ (own subject) | ❌ | ✅ (own only) |
| Manage examinations (create exam, set dates) | ✅ | ❌ | ❌ | ❌ | ❌ |
| Generate reports/analytics | ✅ | ✅ (own class scope) | ✅ (own subject scope) | ❌ | ❌ (read-only own report) |
| System configuration (grades, classes, subjects, academic year) | ✅ | ❌ | ❌ | ❌ | ❌ |

> Implement this as: `roles` (admin/teacher/student) + `permissions` (Spatie) + a `class_teacher_assignments` / `subject_teacher_assignments` / `pt_pd_assignments` pivot tables that scope a teacher's access to specific classes/subjects. Authorization = role permission AND assignment-based scope check, both enforced in Policies.

---

## 4. CORE DATA MODEL (guidance — agent should design full migrations from this)

Design normalized MySQL tables covering at least:

- `users` (base auth: name, email, password, role, status, timestamps, soft deletes)
- `admins` (profile extension of users where role=admin)
- `teachers` (profile extension: teacher_type enum[class,subject,pt_pd] — a teacher can hold multiple type-assignments, so prefer a separate `teacher_assignments` table over a single enum if a teacher can be both a class teacher AND a subject teacher)
- `students` (profile extension: admission_no, dob, gender[G,B], guardian info, current class_id, current grade)
- `academic_years` (e.g. 2025/2026)
- `grades` (1–13)
- `streams` (Science, Commerce, Arts, Technology — used for Grade 12–13 only)
- `classes` (e.g. "Grade 1A"): grade_id, class_name, academic_year_id, class_teacher_id
- `subjects` (name, code, applicable grade range)
- `class_subject` (pivot: which subjects apply to which class/grade)
- `teacher_class_subject_assignments` (teacher_id, class_id, subject_id, academic_year_id, role_in_assignment[class_teacher, subject_teacher, pt_pd_teacher])
- `student_enrollments` (student_id, class_id, academic_year_id, status)
- `timetables` (class_id, day_of_week, period_number, subject_id, teacher_id, academic_year_id)
- `relief_teacher_assignments` (original timetable_entry_id, relief_teacher_id, date, reason)
- `attendance_sessions` (date, class_id or session context, taken_by_teacher_id)
- `student_attendance` (attendance_session_id, student_id, status[present,absent,late,excused])
- `teacher_attendance` (teacher_id, date, status)
- `exams` (name, type[term_test, scholarship, ol, al], academic_year_id, grade_id/class scope, start_date, end_date)
- `exam_subjects` (exam_id, subject_id, max_marks, pass_mark)
- `marks` (exam_subjects_id, student_id, marks_obtained, grade_letter (computed/stored), entered_by_teacher_id, timestamps)
- `reports` (optional: cached/generated report metadata, or generate on the fly)
- `activity_log` (Spatie activitylog or custom: who did what, when — for audit trail)

Require the agent to produce a Mermaid ER diagram in `docs/architecture/er-diagram.md` reflecting the final schema.

---

## 5. MODULE BREAKDOWN (map 1:1 to `docs/modules/*.md`)

1. **Authentication & Authorization Module** — registration (admin-only creation of accounts, no public signup), login, logout, password reset, role assignment, Spatie permission setup, middleware, policies.
2. **Admin Module** — dashboard, manage admins, manage teachers, manage students, manage academic structure (grades/classes/subjects/streams/academic years), system settings.
3. **Teacher Module** — teacher dashboard (varies by teacher type), assigned classes/subjects view, profile.
4. **Student Module** — student CRUD (admin + class teacher scoped), categorization by grade/class/subject/gender, guardian info, enrollment history, student dashboard (read-only).
5. **Attendance Module** — take attendance (class teacher/subject teacher/PT-PD), student attendance history, teacher self/admin-tracked attendance, monthly summary reports, present/absent/late/excused states.
6. **Timetable Module** — build/edit class timetables, teacher timetable view (auto-derived from class timetables + assignments), relief teacher allocation workflow, conflict detection (a teacher can't be in two places in the same period).
7. **Examination Module** — create exams (term test/scholarship/O-L/A-L), define subjects & max/pass marks per exam, marks entry (scoped by teacher assignment), pass/fail calculation, grade-letter calculation, result locking after publication.
8. **Reporting & Analytics Module** — grade-wise/class-wise/subject-wise/gender-wise reports, attendance reports, examination statistics, best/poor performer reports, exportable (PDF/Excel) results, dashboard charts (e.g. Chart.js).

---

## 6. API LAYER

Expose a versioned REST API (`/api/v1/...`) alongside the Blade UI, authenticated via Laravel Sanctum, mirroring the same authorization rules as the web routes (reuse the same Policies/Form Requests — do not duplicate authorization logic between web and API controllers). Document every endpoint in `docs/api/`.

---

## 7. UI/UX EXPECTATIONS

- Tailwind CSS, clean and simple admin-panel style UI suitable for non-technical school staff.
- Separate dashboard layouts per role (Admin/Teacher/Student), sharing a common component library (Blade components/partials).
- Data tables with search/filter/sort/pagination for students, teachers, marks, attendance (consider Livewire or a lightweight JS table library).
- Forms with clear validation error messaging.
- Mobile-responsive, since teachers may take attendance on a phone/tablet.

---

## 8. SECURITY REQUIREMENTS

- No public registration route — only Admin can create accounts.
- Enforce strong password rules and rate-limit login attempts.
- CSRF protection on all forms (Laravel default).
- Authorization enforced server-side via Policies on every model action — write tests that specifically attempt cross-role and cross-class access and assert 403.
- Sanitize/validate all uploaded files (if profile photos or report attachments are supported).
- Audit log for sensitive actions (marks edits, attendance edits after finalization, user role changes).

---

## 9. TESTING STRATEGY (see also Section 2.C rules)

- Use Pest for readability. Structure: `tests/Unit`, `tests/Feature`, mirroring `app/` structure.
- Seed a realistic demo dataset (multiple grades, classes, subjects, teachers of each type, students, a sample exam, sample attendance) via `DatabaseSeeder` for both testing and local dev/demo purposes.
- CI-ready: agent should also produce a GitHub Actions workflow (`.github/workflows/tests.yml`) that runs migrations + the full test suite + coverage report on every push/PR.
- Document the testing strategy itself in `docs/testing/strategy.md`, and log every coverage run in `docs/testing/coverage-log.md`.

---

## 10. DELIVERABLES CHECKLIST (from the project brief — agent should track these in PROJECT_STATUS.md)

- [ ] Functional centralized school management web system
- [ ] Role-based authentication (Admin / Teacher / Student)
- [ ] Student management module (grade/class/subject/gender categorization, Grades 1–13 incl. streams for 12–13)
- [ ] Teacher management module (class/subject/PT-PD teachers)
- [ ] Attendance management module (student + teacher, monthly summaries)
- [ ] Timetable management module (class/teacher timetables, relief teachers)
- [ ] Examination management module (term tests, scholarship, O/L, A/L)
- [ ] Reporting & analytics module (grade/class/subject/gender-wise, best/poor performers)
- [ ] REST API mirroring web functionality
- [ ] Automated test suite meeting coverage targets in Section 2.C
- [ ] Complete `/docs` documentation set
- [ ] `docs/PROJECT_STATUS.md` kept current throughout

---

## 11. BUILD PLAN — WORK IN THESE PHASES, ONE AT A TIME

**Phase 0 — Scaffolding**
Fresh Laravel install, Tailwind + Breeze setup, Spatie permission install, base folder structure, `.env.example`, initial `docs/` skeleton (all files listed in Section 2.A created with placeholder content), CI workflow file, `docs/PROJECT_STATUS.md` initialized with all 8 modules listed as "Not Started".

**Phase 1 — Auth & Authorization foundation**
Roles/permissions seeding, admin-only user creation flow, login/logout/password reset, base middleware, role-specific dashboard shells (empty), Policies scaffolding. Tests + docs.

**Phase 2 — Academic structure & Admin core**
Academic years, grades, streams, subjects, classes CRUD (admin only). Tests + docs.

**Phase 3 — Teacher & Student modules**
Teacher CRUD + assignment tables (class/subject/PT-PD), Student CRUD + enrollment, categorization views (grade/class/subject/gender filters). Tests + docs.

**Phase 4 — Timetable module**
Class/teacher timetable builder, conflict detection, relief teacher workflow. Tests + docs.

**Phase 5 — Attendance module**
Student & teacher attendance capture, monthly summaries, role-scoped views. Tests + docs.

**Phase 6 — Examination module**
Exam types, exam-subject config, marks entry (scoped), pass/fail + grade-letter calculation engine (100% branch coverage requirement applies here specifically). Tests + docs.

**Phase 7 — Reporting & analytics**
Dashboards with charts, exportable reports (PDF/Excel), best/poor performer logic. Tests + docs.

**Phase 8 — API layer**
Sanctum-authenticated `/api/v1` mirroring all modules, API docs. Tests + docs.

**Phase 9 — Hardening & polish**
Full regression test pass, coverage audit against Section 2.C targets, security review pass (authz tests for every role×module combination), UI polish, final `CHANGELOG.md` and `docs/PROJECT_STATUS.md` update marking overall completion.

At the end of each phase: run tests, update all relevant docs and `PROJECT_STATUS.md`, then STOP and summarize for review before starting the next phase.

---

## 12. FIRST MESSAGE TO SEND THE AGENT

> "Read Sections 1–11 above as the full spec for this project. Confirm you understand the Cursor Operating Rules in Section 2 and will follow them for every task, including keeping `/docs` and `docs/PROJECT_STATUS.md` up to date and meeting the test coverage targets before marking anything Done. Then execute Phase 0 only, and stop for my review."

---

*Source: derived from the "Smart School Data Gathering & Management System" project brief (Laravel/MySQL/Tailwind stack, Agile methodology, role-based access for Admin/Teacher/Student).*
