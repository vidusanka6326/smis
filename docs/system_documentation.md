# Smart School Data Gathering & Management System (SMIS)
## System Documentation

This document provides a comprehensive overview of the SMIS application, detailing its architecture, roles, capabilities, and workflows.

---

## 1. System Overview
SMIS is a centralized, role-based, web-based school administration platform built with Laravel. It replaces manual, paper-based record-keeping with a secure, digital solution for managing student data, attendance, timetables, examinations, and reporting. 

### Key Features:
- **Role-Based Access Control (RBAC):** Granular permissions for Admins, Officers, Teachers, and Students.
- **Academic Structure Management:** Manage grades, classes, streams, and subjects.
- **Attendance Tracking:** Student and teacher attendance with monthly summaries.
- **Examination Management:** Handle term tests, scholarship exams, and O/L & A/L results.
- **Timetable Management:** Class and teacher timetables with conflict detection.
- **Reporting & Analytics:** Generate PDF/CSV reports and view dashboard analytics.
- **SMIS Agent:** An integrated AI assistant (Gemini) capable of executing role-scoped actions via natural language.

---

## 2. Roles & Capabilities

The system defines four primary roles, each with specific capabilities enforced via Laravel Policies and Spatie Permissions.

### 2.1 Administrator (`admin`)
The system owner with full access to all modules and configurations.
- **Capabilities:** Manage users (Admins, Officers, Teachers, Students), configure academic structure (grades, streams, classes, subjects), manage all timetables and exams, view all reports, and access system audit logs.

### 2.2 Officer (`officer`)
Administrative staff responsible for data entry and day-to-day operations.
- **Capabilities:** Similar to Admin but lacks access to destructive actions or core system configurations. They handle student enrollments, teacher assignments, data entry for exams, and report generation.

### 2.3 Teacher (`teacher`)
Teachers have scoped access based on their specific assignments (Class Teacher, Subject Teacher, or PT/PD Teacher).
- **Capabilities:**
  - **Class Teacher:** Manage students in their class, take class attendance, enter marks for their class, and view class-level reports.
  - **Subject Teacher:** Take attendance for their subject periods, enter marks for their assigned subjects, and view subject-specific performance.
  - **General:** View own timetable, view own attendance, and access the SMIS Agent for assistance.

### 2.4 Student (`student`)
Read-only access for students to track their academic progress.
- **Capabilities:** View own timetable, track personal attendance, view own examination results, and access personal reports.

---

## 3. Module Overview

| Module | Description |
|---|---|
| **Authentication** | Secure login, session management, and localization (English/Sinhala/Tamil). |
| **Admin/Officer** | Core management for academic structures, user profiles, and system settings. |
| **Teacher** | Dashboards and tools tailored to a teacher's specific assignments. |
| **Student** | Read-only portals for students to view their data. |
| **Attendance** | Session-based attendance tracking for both students and staff. |
| **Timetable** | Period-by-period scheduling, grid visualization, and conflict detection. |
| **Examination** | Exam setup, marks entry, pass/fail calculation, and result publishing. |
| **Reporting** | Data aggregation for at-risk students, rankings, and downloadable PDF/CSV exports. |
| **SMIS Agent** | Context-aware AI assistant leveraging Gemini for automated data retrieval and task execution. |

---

## 4. System Diagrams

### 4.1 Use Case Diagram
Visualizes the primary interactions each role has with the system.

```mermaid
flowchart LR
    Admin([Administrator])
    Officer([Officer])
    Teacher([Teacher])
    Student([Student])
    
    subgraph SMIS System
        UC1(Manage Users & System)
        UC2(Manage Academic Structure)
        UC3(Data Entry & Management)
        UC4(View Timetables)
        UC5(Take Attendance)
        UC6(Enter Marks)
        UC7(View Own Results)
        UC8(Generate Reports)
        UC9(Interact with SMIS Agent)
    end
    
    Admin --> UC1
    Admin --> UC2
    Admin --> UC8
    Admin --> UC9
    
    Officer --> UC3
    Officer --> UC8
    Officer --> UC9
    
    Teacher --> UC4
    Teacher --> UC5
    Teacher --> UC6
    Teacher --> UC8
    Teacher --> UC9
    
    Student --> UC4
    Student --> UC7
```

### 4.2 Entity-Relationship (ER) Diagram
Illustrates the core database schema and relationships.

```mermaid
erDiagram
    USER ||--o| TEACHER : "is extended by"
    USER ||--o| STUDENT : "is extended by"
    
    TEACHER ||--o{ TEACHER_ASSIGNMENT : "has"
    SCHOOL_CLASS ||--o{ TEACHER_ASSIGNMENT : "includes"
    SUBJECT ||--o{ TEACHER_ASSIGNMENT : "involves"
    
    STUDENT ||--o{ STUDENT_ENROLLMENT : "has"
    SCHOOL_CLASS ||--o{ STUDENT_ENROLLMENT : "includes"
    
    SCHOOL_CLASS ||--o{ TIMETABLE_ENTRY : "has"
    SUBJECT ||--o{ TIMETABLE_ENTRY : "scheduled"
    TEACHER ||--o{ TIMETABLE_ENTRY : "teaches"
    
    SCHOOL_CLASS ||--o{ ATTENDANCE_SESSION : "tracks"
    ATTENDANCE_SESSION ||--o{ STUDENT_ATTENDANCE : "contains"
    STUDENT ||--o{ STUDENT_ATTENDANCE : "logged in"
    
    EXAM ||--o{ EXAM_SUBJECT : "includes"
    SUBJECT ||--o{ EXAM_SUBJECT : "tested in"
    EXAM_SUBJECT ||--o{ MARK : "records"
    STUDENT ||--o{ MARK : "achieves"
```

### 4.3 Class Diagram
A high-level view of the primary Laravel Eloquent Models.

```mermaid
classDiagram
    class User {
        +int id
        +string name
        +string email
        +string role
        +string locale
    }
    class Teacher {
        +int id
        +int user_id
    }
    class Student {
        +int id
        +int user_id
        +string admission_no
    }
    class SchoolClass {
        +int id
        +string name
        +int grade_id
    }
    class Exam {
        +int id
        +string name
        +string type
    }
    class Mark {
        +int id
        +int student_id
        +int exam_subject_id
        +int marks_obtained
        +string grade_letter
    }
    
    User <|-- Teacher : extends
    User <|-- Student : extends
    Teacher "1" -- "*" TeacherAssignment : has
    SchoolClass "1" -- "*" TeacherAssignment : has
    SchoolClass "1" -- "*" Student : contains
    Exam "1" -- "*" Mark : has
    Student "1" -- "*" Mark : receives
```

### 4.4 Sequence Diagram: Marks Entry Workflow
Demonstrates the secure process of a teacher entering exam marks.

```mermaid
sequenceDiagram
    actor Teacher
    participant UI as Dashboard / UI
    participant Controller as MarkController
    participant Policy as MarkPolicy
    participant DB as Database

    Teacher->>UI: Selects Class, Subject & Exam
    UI->>Controller: GET /marks/entry (params)
    Controller->>Policy: check('enter-marks', teacher, class, subject)
    Policy-->>Controller: Authorized
    Controller->>DB: Fetch Enrolled Students
    DB-->>Controller: Students List
    Controller-->>UI: Render Marks Entry Form
    
    Teacher->>UI: Enters marks and submits
    UI->>Controller: POST /marks/store
    Controller->>Policy: check('enter-marks')
    Policy-->>Controller: Authorized
    Controller->>DB: Save Marks & compute grades/status
    DB-->>Controller: Success (Transaction Committed)
    Controller-->>UI: Redirect with Success Notification
```

---

## 5. Technology Stack
- **Backend:** Laravel 11.x (PHP 8.2+)
- **Database:** MySQL / SQLite (for local testing)
- **Frontend:** Laravel Blade, Tailwind CSS, Flux UI, Alpine.js
- **Authentication:** Laravel Fortify + Spatie Laravel Permission
- **AI Integration:** Google Gemini API (for SMIS Agent)
- **PDF Generation:** DomPDF

## 6. Security & Auditing
- All routes and actions are strictly protected using **Laravel Policies** to ensure users can only access data relevant to their role and assignments.
- An **ActivityLogger** tracks critical actions (e.g., marks entry, attendance modification, user creation) to maintain a secure audit trail.
