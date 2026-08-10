# ER Diagram

> Updated through Phase 4 (timetables, relief teacher assignments).

## Current schema

```mermaid
erDiagram
    users {
        bigint id PK
        string name
        string email UK
        string password
        string status
        timestamp deleted_at
    }

    teachers {
        bigint id PK
        bigint user_id FK
        string employee_no UK
        string phone
        timestamp deleted_at
    }

    students {
        bigint id PK
        bigint user_id FK
        string admission_no UK
        date date_of_birth
        string gender
        string guardian_name
        bigint current_class_id FK
        timestamp deleted_at
    }

    academic_years {
        bigint id PK
        string name UK
        date starts_on
        date ends_on
        boolean is_current
    }

    grades {
        bigint id PK
        tinyint number UK
        string name
    }

    streams {
        bigint id PK
        string name
        string code UK
    }

    subjects {
        bigint id PK
        string name
        string code UK
        tinyint min_grade
        tinyint max_grade
    }

    classes {
        bigint id PK
        string name
        string code
        bigint academic_year_id FK
        bigint grade_id FK
        bigint stream_id FK
        bigint class_teacher_id FK
    }

    class_subject {
        bigint id PK
        bigint school_class_id FK
        bigint subject_id FK
    }

    teacher_class_subject_assignments {
        bigint id PK
        bigint teacher_id FK
        bigint school_class_id FK
        bigint subject_id FK
        bigint academic_year_id FK
        string role_in_assignment
    }

    student_enrollments {
        bigint id PK
        bigint student_id FK
        bigint school_class_id FK
        bigint academic_year_id FK
        string status
    }

    timetables {
        bigint id PK
        bigint academic_year_id FK
        bigint school_class_id FK
        tinyint day_of_week
        tinyint period_number
        bigint subject_id FK
        bigint teacher_id FK
    }

    relief_teacher_assignments {
        bigint id PK
        bigint timetable_entry_id FK
        bigint relief_teacher_id FK
        date date
        string reason
        bigint assigned_by FK
    }

    users ||--o| teachers : "profile"
    users ||--o| students : "profile"
    teachers ||--o{ teacher_class_subject_assignments : "has"
    teachers ||--o{ classes : "homeroom"
    students ||--o{ student_enrollments : "history"
    classes ||--o{ students : "current"
    academic_years ||--o{ classes : "contains"
    grades ||--o{ classes : "groups"
    streams ||--o{ classes : "optional"
    classes ||--o{ class_subject : "has"
    subjects ||--o{ class_subject : "taught_in"
    classes ||--o{ teacher_class_subject_assignments : "assigned"
    subjects ||--o{ teacher_class_subject_assignments : "optional"
    academic_years ||--o{ teacher_class_subject_assignments : "year"
    academic_years ||--o{ student_enrollments : "year"
    classes ||--o{ student_enrollments : "placed_in"
    academic_years ||--o{ timetables : "schedules"
    classes ||--o{ timetables : "has"
    subjects ||--o{ timetables : "slot"
    teachers ||--o{ timetables : "teaches"
    timetables ||--o{ relief_teacher_assignments : "relieved"
    teachers ||--o{ relief_teacher_assignments : "covers"
    users ||--o{ relief_teacher_assignments : "assigned_by"
```

## Planned entities (Phases 5–7)

attendance_sessions, student_attendance, teacher_attendance, exams, exam_subjects, marks, activity_log, admins profile.
