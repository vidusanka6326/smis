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

1. `RolesAndPermissionsSeeder` — roles `admin` / `teacher` / `student` and granular permissions
2. `AdminUserSeeder` — default admin `admin@smis.test` / `password` (skipped if an admin already exists)
3. `AcademicStructureSeeder` — grades 1–13, A/L streams, sample subjects, current academic year `2025/2026`, sample classes
4. `TeacherStudentSeeder` — demo class/subject teachers and a student in `10-A` (`class.teacher@smis.test`, `subject.teacher@smis.test`, `student@smis.test` / `password`)
5. `TimetableSeeder` — sample weekly slots for `10-A`

There is **no public registration**. Create additional users while logged in as admin via **Create user**, or use Admin → Teachers / Students. Academic structure is managed under Admin → Academic years / Grades / Streams / Subjects / Classes. Timetables and relief are under Admin → Timetables / Relief.

## Tests

```bash
php artisan test --compact
composer ci:check
```

## Packages installed

- `spatie/laravel-permission` (wired on `User` via `HasRoles`)
- Existing starter: Fortify, Livewire, Flux, Pest, Laravel Boost
