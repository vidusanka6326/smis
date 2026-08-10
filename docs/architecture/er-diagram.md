# ER Diagram

> Updated through Phase 1. Domain tables for academic structure and later modules will extend this diagram.

## Current schema

```mermaid
erDiagram
    users {
        bigint id PK
        string name
        string email UK
        string password
        string status
        timestamp email_verified_at
        timestamp deleted_at
        timestamps created_updated
    }

    roles {
        bigint id PK
        string name
        string guard_name
    }

    permissions {
        bigint id PK
        string name
        string guard_name
    }

    model_has_roles {
        bigint role_id FK
        string model_type
        bigint model_id
    }

    model_has_permissions {
        bigint permission_id FK
        string model_type
        bigint model_id
    }

    role_has_permissions {
        bigint permission_id FK
        bigint role_id FK
    }

    users ||--o{ model_has_roles : "has"
    roles ||--o{ model_has_roles : "assigned"
    roles ||--o{ role_has_permissions : "grants"
    permissions ||--o{ role_has_permissions : "granted_by"
    permissions ||--o{ model_has_permissions : "direct"
    users ||--o{ model_has_permissions : "has"
```

## Planned entities (Phases 2–7)

admins, teachers, students, academic_years, grades, streams, classes, subjects, class_subject, teacher_class_subject_assignments, student_enrollments, timetables, relief_teacher_assignments, attendance_sessions, student_attendance, teacher_attendance, exams, exam_subjects, marks, activity_log.
