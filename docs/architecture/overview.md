# Architecture Overview

## Application

**Smart School Data Gathering & Management System** — a Laravel monolith providing role-based school administration (web UI + future Sanctum API).

## Stack (as installed)

| Layer | Choice |
|---|---|
| Framework | Laravel 13 (PHP 8.4) |
| Auth | Laravel Fortify (login, password reset, 2FA/passkeys) |
| Authorization | Spatie `laravel-permission` + Laravel Policies |
| UI | Livewire 4 + Flux UI + Tailwind CSS 4 |
| Database | MySQL (production); SQLite (local/tests default) |
| Testing | Pest 5 |
| API (Phase 8) | Laravel Sanctum, `/api/v1` — **skipped** for current release (ADR 0009) |

## High-level module boundaries

```
┌─────────────────────────────────────────────────────────┐
│                     Web (Livewire/Blade)                │
│  Admin Dashboard │ Teacher Dashboard │ Student Dashboard │
└────────────┬────────────────┬────────────────┬──────────┘
             │                │                │
┌────────────▼────────────────▼────────────────▼──────────┐
│              Actions / Services / Policies / Audit       │
│  Auth │ Academic │ Teachers │ Students │ Timetable │ …  │
│              ActivityLogger → activity_logs              │
└────────────┬────────────────────────────────────────────┘
             │
┌────────────▼────────────────────────────────────────────┐
│                     Eloquent / MySQL                     │
└─────────────────────────────────────────────────────────┘
             │
┌────────────▼────────────────────────────────────────────┐
│     API /api/v1 — skipped for current release (ADR 0009) │
└─────────────────────────────────────────────────────────┘
```

## Folder structure (application code)

```
app/
  Actions/          # Single-purpose write operations
  Services/         # Domain services (calculators, reports)
  Policies/         # Authorization per model
  Enums/            # Domain enums (exam types, attendance status, …)
  Http/
    Controllers/    # Thin HTTP adapters (web + api later)
    Requests/       # Form Request validation
  Livewire/         # Interactive UI components
  Models/           # Eloquent models
```

Domain models and migrations for academic structure, attendance, exams, etc. are introduced in Phases 2–7.

## Related docs

- [ER diagram](er-diagram.md) — schema (filled in as tables are created)
- [Module boundaries](module-boundaries.md)
- [Folder structure](folder-structure.md)
