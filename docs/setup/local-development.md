# Local Development Setup

## Prerequisites

- PHP 8.4+
- Composer 2
- Node.js 22+
- SQLite (default) or MySQL 8+

## Quick start

```bash
composer setup
# or manually:
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install && npm run build
php artisan serve
```

Optional concurrent Vite + server:

```bash
composer run dev
```

## Environment variables

| Variable | Purpose | Notes |
|---|---|---|
| `APP_NAME` | Application name | Default: `SMIS` |
| `APP_URL` | Base URL | Used for links / Sanctum later |
| `DB_CONNECTION` | `sqlite` or `mysql` | Local default `sqlite` |
| `DB_HOST` / `DB_PORT` / `DB_DATABASE` / `DB_USERNAME` / `DB_PASSWORD` | MySQL settings | Uncomment for MySQL |
| `SESSION_DRIVER` | Session store | Default `database` |
| `QUEUE_CONNECTION` | Queue | Default `database` |
| `CACHE_STORE` | Cache | Default `database` |
| `MAIL_*` | Mail | Default `log` driver locally |
| `VITE_APP_NAME` | Frontend title | Mirrors `APP_NAME` |

## Seeding

```bash
php artisan migrate:fresh --seed
```

This runs:

1. `RolesAndPermissionsSeeder` — roles `admin` / `officer` / `teacher` / `student` and granular permissions
2. `AdminUserSeeder` — principal `Chamara Wickramasinghe` / `admin@smis.test` / `password`
3. `OfficerUserSeeder` — five office staff (`officer@smis.test` plus four named accounts)
4. `AcademicStructureSeeder` — grades 1–13, A/L streams, Sri Lankan national-curriculum subjects, academic year `2026`, 28 classes in grades 6–13 (ADR 0015)
5. `TeacherStudentSeeder` — 30 teachers and 600 students (Sinhala names in English). Demo logins: `class.teacher@smis.test` (10-A homeroom), `subject.teacher@smis.test` (O/L Mathematics), `student@smis.test` (Kasun Perera, 10-A)
6. `TimetableSeeder` — conflict-free weekly grid for every class
7. `AttendanceSeeder` — last 10 school days of class + teacher attendance
8. `ExaminationSeeder` — published First Term tests (grades 6, 8, 10, 11 and each A/L class) with marks

There is **no public registration**. Admins create **Officers** under Admin → Officers; teachers/students via Admin → Teachers / Students. Officers share operational data-entry modules and the activity log. Academic structure is under Admin → Academic years / Grades / Streams / Subjects / Classes. Timetables and relief are under Admin → Timetables / Relief. Attendance is under Admin/Teacher → Attendance (students also have My attendance). Exams/marks are under Admin → Exams / Marks (teachers: Marks; students: My results). **Reports** is a card catalog (admin/teacher/student): open a report, filter, then download PDF or CSV.

## Tests

```bash
php artisan test --compact
composer ci:check
```

## Packages installed

- `spatie/laravel-permission` (wired on `User` via `HasRoles`)
- Existing starter: Fortify, Livewire, Flux, Pest, Laravel Boost
