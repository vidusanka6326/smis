# SMIS Agent

## Purpose

In-app Gemini assistant for **admin**, **officer**, and **teacher**. Staff chat in a ChatGPT-style UI; the model calls permissioned tools that read and change school data through existing Actions, Policies, and services. Anything the signed-in user can do in the web UI is available as a tool; anything they cannot do is hidden and rejected.

## User roles involved

- Admin — every staff tool (including officers CRUD)
- Officer — school-wide data entry (academic structure, people except officers, timetable, attendance, exams, reports, activity log)
- Teacher — scoped lookups and writes they already have (own classes, homeroom students, attendance they may take, marks they may enter, reports in their scope). Cannot manage timetable, officers, or system config.
- Student — no access

## DB tables used

- `agent_conversations` — per-user chat threads
- `agent_messages` — user/assistant turns, optional `choices` and `tool_trace`
- Domain tables via tools (academic structure, people, timetables, attendance, exams, reports, activity log)

## Routes

| Method | Path | Name | Notes |
|---|---|---|---|
| GET | `/agent` | `agent.chat` | Livewire chat (`role:admin\|officer\|teacher`) |

## Tools (connection layer)

The model never writes the database itself. `AgentToolRegistry` exposes only tools the user is `authorized()` for, then each tool re-checks Policies / Actions.

| Tool | Typical roles | What it does |
|---|---|---|
| `offer_choices` | all agent users | Clickable follow-up buttons |
| `list_capabilities` | all agent users | Role, permissions, and operating notes |
| `get_dashboard_summary` | office + teachers | KPI counts from the role dashboard |
| `list_classes` / `lookup_class` | office + assigned teachers | Resolve `10-A` |
| `get_class_timetable` / `find_free_periods` | office + assigned teachers | Empty Mon–Fri periods 1–8 |
| `find_free_teachers` / `search_teachers` | admin / officer | Who is free on a timeslot |
| `assign_timetable_slot` | admin / officer | Fill an empty period (`UpsertTimetableEntry`) |
| `delete_timetable_slot` | admin / officer | Clear a period |
| `assign_relief_teacher` | admin / officer | Cover an existing lesson (`AssignReliefTeacher`) |
| `manage_relief` | view/manage timetable | List or delete covers |
| `get_teacher_timetable` | office; teachers = self | Weekly teaching slots |
| `search_students` / `get_student_summary` | office + class teachers | Profile + monthly attendance % |
| `manage_student` | office; class teachers (own class) | Create / update / delete / enroll (`CreateStudent`, `UpdateStudent`, `EnrollStudent`) |
| `manage_teacher` | manage-teachers | Create / update / delete / sync assignments |
| `manage_officer` | admin | Officers CRUD |
| `manage_academic_year` / `manage_grade` / `manage_stream` / `manage_subject` / `manage_class` | manage-system-config | Academic structure |
| `get_class_attendance` / `get_at_risk_students` | view-attendance + view-reports | &lt;80% flag |
| `save_attendance_session` | manage-attendance | List / save / finalize / delete (`UpsertAttendanceSession`) |
| `save_teacher_attendance` | manage-attendance | Staff daily attendance |
| `search_exams` / `get_exam_results` | view/enter marks | Published/scoped results |
| `manage_exam` | manage-examinations | Create / update / delete / publish / sync subjects |
| `enter_marks` | enter-marks | `UpsertMarks` for one exam subject |
| `get_report_data` | view-reports | Catalog keys (attendance, at-risk, enrollment, …) |
| `list_activity_logs` | view-activity-log | Recent audit rows |

## Key business rules

- Gemini key is `config('services.gemini.key')` (`GEMINI_API_KEY`). Missing key returns a setup message instead of calling Google.
- Live replies use `models/gemini-flash-latest:generateContent` (Google AI Studio sample). Model 404, quota 429, bad-key 403, and (when `APP_DEBUG`) Gemini 400 messages are shown in chat instead of a generic failure.
- Livewire `stream()` still updates the composer; the model response arrives as one turn. Markdown is rendered with `Str::markdown()` (`html_input` strip).
- The chat is a full-height shell: conversation list (title + relative time), compact composer, and Gemini/setup failures as Flux callouts (with an AI Studio link for quota errors).
- Assigning a **named teacher to a free period** creates a timetable entry (subject required). Relief is only for an existing lesson on a matching weekday date.
- Teachers may inspect timetables of classes they are assigned to (assumption; class/subject/PT-PD via `TeacherReportScope`).
- Class teachers may create students only in their homeroom (`StudentPolicy::createInClass`). They cannot change status, password, or class.
- Creating people requires a password in the tool arguments (same as the forms). Gender is `G`/`B`.
- Mutations write `activity_logs` with `agent.mutated` (domain Actions may also log their own actions).
- 20 messages / minute / user.

## Edge cases

- Ambiguous teacher/student/exam names return matches and expect `offer_choices`.
- `10A` normalizes to `10-A`.
- Cross-user conversations 403.
- Unauthorized tools are omitted from Gemini’s function list; calling one anyway returns a role error.
- Tools with no arguments must JSON-encode `parameters.properties` as `{}`. PHP empty arrays become `[]`, which Gemini rejects with HTTP 400 (`Cannot bind a list to map for field 'properties'`).

## Status

Done — full Policy-gated coverage of staff UI actions.
