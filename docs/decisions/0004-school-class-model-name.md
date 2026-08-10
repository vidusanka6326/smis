# ADR 0004 — Use `SchoolClass` model with `classes` table

## Context

PHP reserves the keyword `class`, so an Eloquent model named `Class` is awkward and error-prone. The domain still needs a table named `classes` to match the school domain language in the project brief.

## Decision

Use model `App\Models\SchoolClass` with `protected $table = 'classes'`. Route parameter remains `school_class`; URI resource is `/admin/classes`.

## Consequences

- Controllers/policies/factories refer to `SchoolClass`.
- SQL and ER docs refer to `classes` / `class_subject.school_class_id`.
- Slight naming mismatch is intentional and documented.
