# Source-Code Screenshot Selection for the Final Report

## Purpose

This document identifies the 15 strongest source-code screenshots for demonstrating that the SMIS system was implemented. Each screenshot should capture only the specified code section and should exclude API keys, passwords, tokens, database credentials, and personal information.

## Screenshot 1: Authentication

**Exact File Path:** [app/Providers/FortifyServiceProvider.php](../app/Providers/FortifyServiceProvider.php#L46-L63)

**Exact Class/Method:** `FortifyServiceProvider::configureActions()` and `Fortify::authenticateUsing()`

**Lines or Code Section to Capture:** Lines 46-63

**Chapter 6 Section:** Authentication and Security

**Figure Caption:** *Role-aware authentication with active-user and password verification*

The authentication callback verifies the submitted identity, active account status, hashed password, and selected role before authentication succeeds. This provides evidence that the login process is implemented using Fortify rather than relying only on frontend role selection.

## Screenshot 2: Role-Based Access Control

**Exact File Path:** [app/Policies/ExamSubjectPolicy.php](../app/Policies/ExamSubjectPolicy.php#L42-L60)

**Exact Class/Method:** `ExamSubjectPolicy::enterMarks()` and `teacherCanAccess()`

**Lines or Code Section to Capture:** Lines 42-60, including the class-scoping logic immediately below if required

**Chapter 6 Section:** Role-Based Access Control

**Figure Caption:** *Policy-based authorization for teacher examination access*

The policy checks examination permissions, teacher status, assignment scope, and publication state before allowing mark entry. It demonstrates that subject teachers and class teachers are authorized according to their actual teaching responsibilities rather than by a simple interface restriction.

## Screenshot 3: Student Validation and Authorization

**Exact File Path:** [app/Http/Requests/Admin/StoreStudentRequest.php](../app/Http/Requests/Admin/StoreStudentRequest.php#L20-L67)

**Exact Class/Method:** `StoreStudentRequest::authorize()`, `rules()`, and `withValidator()`

**Lines or Code Section to Capture:** Lines 20-67

**Chapter 6 Section:** Validation and Security

**Figure Caption:** *Layered authorization and validation for student creation*

The request validates student identity, admission, date, gender, guardian, academic year, and class data. The additional validator ensures that the selected class belongs to the academic year and that class teachers can create students only within their permitted class.

## Screenshot 4: Student Creation

**Exact File Path:** [app/Actions/Students/CreateStudent.php](../app/Actions/Students/CreateStudent.php#L42-L87)

**Exact Class/Method:** `CreateStudent::handle()`

**Lines or Code Section to Capture:** Lines 42-87

**Chapter 6 Section:** Student Management

**Figure Caption:** *Atomic creation of student account, profile, role, and enrollment*

The action creates the user account, assigns the student role, creates the student profile, and records enrollment within a database transaction. This demonstrates that student registration is implemented as one consistent business operation rather than as disconnected database inserts.

## Screenshot 5: Enrollment Database Design

**Exact File Path:** [database/migrations/2026_08_10_113224_create_student_enrollments_table.php](../database/migrations/2026_08_10_113224_create_student_enrollments_table.php#L9-L23)

**Exact Class/Method:** Anonymous migration `up()`

**Lines or Code Section to Capture:** Lines 9-23

**Chapter 6 Section:** Database Design and Relationships

**Figure Caption:** *Student enrollment schema with foreign keys, uniqueness, and indexing*

The migration connects students with classes and academic years through foreign keys. The composite uniqueness constraint prevents duplicate yearly enrollments, while the index supports class-based enrollment queries.

## Screenshot 6: Attendance Processing

**Exact File Path:** [app/Actions/Attendance/UpsertAttendanceSession.php](../app/Actions/Attendance/UpsertAttendanceSession.php#L35-L163)

**Exact Class/Method:** `UpsertAttendanceSession::handle()`

**Lines or Code Section to Capture:** Lines 35-163

**Chapter 6 Section:** Attendance Management

**Figure Caption:** *Validated attendance-session persistence and student-status synchronization*

The method validates the academic year, class-subject relationship, duplicate sessions, student membership, and duplicate attendance rows. It then persists the session and individual attendance statuses transactionally and records the operation for audit purposes.

## Screenshot 7: Attendance Calculation

**Exact File Path:** [app/Services/Attendance/AttendancePercentageCalculator.php](../app/Services/Attendance/AttendancePercentageCalculator.php#L20-L45)

**Exact Class/Method:** `AttendancePercentageCalculator::percentage()`

**Lines or Code Section to Capture:** Lines 20-45

**Chapter 6 Section:** Attendance Analytics

**Figure Caption:** *Attendance percentage calculation using status-specific rules*

The calculation distinguishes between attended, absent, and excused statuses according to the system's attendance rules. It excludes non-countable records and applies consistent rounding, demonstrating implemented domain-specific analytics.

## Screenshot 8: Timetable Assignment Rules

**Exact File Path:** [app/Actions/Timetable/UpsertTimetableEntry.php](../app/Actions/Timetable/UpsertTimetableEntry.php#L30-L93)

**Exact Class/Method:** `UpsertTimetableEntry::handle()`

**Lines or Code Section to Capture:** Lines 30-93

**Chapter 6 Section:** Timetable Management

**Figure Caption:** *Validated timetable assignment with academic and subject compatibility checks*

The action checks academic-year ownership, class-subject relationships, grade applicability, and valid period limits. It delegates collision detection to a dedicated service before creating or updating a timetable entry.

## Screenshot 9: Timetable Conflict Detection

**Exact File Path:** [app/Services/Timetable/TimetableConflictDetector.php](../app/Services/Timetable/TimetableConflictDetector.php#L17-L58)

**Exact Class/Method:** `TimetableConflictDetector::detect()`

**Lines or Code Section to Capture:** Lines 17-58

**Chapter 6 Section:** Timetable Conflict Detection

**Figure Caption:** *Detection of class and teacher timetable collisions*

The service independently checks whether a class or teacher is already assigned during the proposed day and period. It returns structured conflict indicators and explanatory messages, providing evidence of automated timetable consistency checking.

## Screenshot 10: Examination Management

**Exact File Path:** [app/Actions/Examination/UpsertExam.php](../app/Actions/Examination/UpsertExam.php#L26-L93)

**Exact Class/Method:** `UpsertExam::handle()`

**Lines or Code Section to Capture:** Lines 26-93

**Chapter 6 Section:** Examination Management

**Figure Caption:** *Exam creation with scope, date, and publication safeguards*

The action prevents editing published examinations and validates grade/class scope, academic-year consistency, grade compatibility, and date ordering. This demonstrates that the examination module implements business rules beyond basic CRUD functionality.

## Screenshot 11: Mark Entry and Automatic Grading

**Exact File Path:** [app/Actions/Examination/UpsertMarks.php](../app/Actions/Examination/UpsertMarks.php#L24-L109)

**Exact Class/Method:** `UpsertMarks::handle()`

**Lines or Code Section to Capture:** Lines 24-109

**Chapter 6 Section:** Mark Entry and Automatic Grading

**Figure Caption:** *Eligible-student mark entry with automatic grade and pass-result calculation*

The method prevents editing marks after publication and verifies that submitted students are eligible for the examination. Each mark is passed to the result calculator, after which the calculated grade letter and pass/fail result are stored with the mark.

## Screenshot 12: PDF Report Generation

**Exact File Path:** [app/Services/Reporting/ReportPdfExporter.php](../app/Services/Reporting/ReportPdfExporter.php#L13-L28)

**Exact Class/Method:** `ReportPdfExporter::download()`

**Lines or Code Section to Capture:** Lines 13-28

**Chapter 6 Section:** Reporting and PDF Export

**Figure Caption:** *Reusable PDF export service for structured school reports*

The service prepares report data, metadata, school information, timestamps, tables, and paper orientation before generating a PDF response. This demonstrates separation between report preparation and document-generation infrastructure.

## Screenshot 13: Gemini API Integration

**Exact File Path:** [app/Services/Agent/GeminiAgentLlm.php](../app/Services/Agent/GeminiAgentLlm.php#L14-L91)

**Exact Class/Method:** `GeminiAgentLlm::isConfigured()` and `streamTurn()`

**Lines or Code Section to Capture:** Lines 14-91

**Chapter 6 Section:** AI Agent and Gemini API Integration

**Figure Caption:** *Gemini-powered agent responses with tool schemas and safety handling*

The service constructs the Gemini request payload, system instructions, generation configuration, and optional function declarations. It also handles missing configuration, connection failures, invalid responses, and safety-filter blocks without displaying or exposing the API key.

## Screenshot 14: Audit Logging

**Exact File Path:** [app/Services/Audit/ActivityLogger.php](../app/Services/Audit/ActivityLogger.php#L19-L37)

**Exact Class/Method:** `ActivityLogger::log()`

**Lines or Code Section to Capture:** Lines 19-37

**Chapter 6 Section:** Auditability and Security

**Figure Caption:** *Centralized audit logging of sensitive system actions*

The logger records the acting user, action type, related model, description, properties, IP address, and timestamp. Centralizing these fields provides evidence that sensitive actions such as attendance updates, mark entry, and examination publishing are traceable.

## Screenshot 15: Livewire Agent Interface

**Exact File Path:** [app/Livewire/Agent/Chat.php](../app/Livewire/Agent/Chat.php#L36-L49) and [app/Livewire/Agent/Chat.php](../app/Livewire/Agent/Chat.php#L163-L190)

**Exact Class/Method:** `Chat::mount()`, `send()`, and `submit()`

**Lines or Code Section to Capture:** The `mount()` authorization block and the `submit()` validation, authorization, rate-limiting, and streaming block

**Chapter 6 Section:** Livewire Interactive Components

**Figure Caption:** *Authorized and rate-limited Livewire workflow for the SMIS Agent*

The Livewire component authorizes access to conversations, validates submitted messages, applies per-user rate limiting, and streams the agent's response state. This demonstrates meaningful server-side interactivity with security and operational controls.

## Screenshot Preparation Rules

- Capture only the specified method or bounded code section.
- Do not capture `.env` files or any configuration value containing credentials.
- Do not capture API keys, passwords, secret tokens, or database credentials.
- Do not capture test data containing personal user information.
- Keep the file path and method name visible in the editor where possible.
- Use the figure caption exactly or adapt it only to match the university report's naming style.
