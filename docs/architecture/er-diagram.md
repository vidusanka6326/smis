# ER Diagram

> Updated through Phase 2 (academic structure). Teacher/student profiles arrive in Phase 3.

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

    academic_years {
        bigint id PK
        string name UK
        date starts_on
        date ends_on
        boolean is_current
        timestamps created_updated
    }

    grades {
        bigint id PK
        tinyint number UK
        string name
        timestamps created_updated
    }

    streams {
        bigint id PK
        string name
        string code UK
        timestamps created_updated
    }

    subjects {
        bigint id PK
        string name
        string code UK
        tinyint min_grade
        tinyint max_grade
        timestamps created_updated
    }

    classes {
        bigint id PK
        string name
        string code
        bigint academic_year_id FK
        bigint grade_id FK
        bigint stream_id FK
        bigint class_teacher_id FK
        timestamps created_updated
    }

    class_subject {
        bigint id PK
        bigint school_class_id FK
        bigint subject_id FK
        timestamps created_updated
    }

    users ||--o{ model_has_roles : "has"
    roles ||--o{ model_has_roles : "assigned"
    roles ||--o{ role_has_permissions : "grants"
    permissions ||--o{ role_has_permissions : "granted_by"
    permissions ||--o{ model_has_permissions : "direct"
    users ||--o{ model_has_permissions : "has"

    academic_years ||--o{ classes : "contains"
    grades ||--o{ classes : "groups"
    streams ||--o{ classes : "optional"
    users ||--o{ classes : "class_teacher"
    classes ||--o{ class_subject : "has"
    subjects ||--o{ class_subject : "taught_in"
```

## Planned entities (Phases 3–7)

admins, teachers, students, teacher_class_subject_assignments, student_enrollments, timetables, relief_teacher_assignments, attendance_sessions, student_attendance, teacher_attendance, exams, exam_subjects, marks, activity_log.
