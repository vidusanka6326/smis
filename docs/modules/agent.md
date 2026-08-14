# SMIS Agent

## Purpose

In-app Gemini assistant for **admin**, **officer**, and **teacher**. Staff chat in a ChatGPT-style UI; the model calls permissioned tools that read and change school data through existing Actions, Policies, and services.

## User roles involved

- Admin / officer — school-wide lookups plus timetable mutations (`use-smis-agent` + existing `manage-timetable`, `view-reports`, …)
- Teacher — scoped lookups (own classes, students, timetable, attendance, marks). Cannot assign timetable slots or relief.
- Student — no access

## DB tables used

- `agent_conversations` — per-user chat threads
- `agent_messages` — user/assistant turns, optional `choices` and `tool_trace`
- Domain tables via tools (classes, timetables, teachers, students, attendance, exams)

## Routes

| Method | Path | Name | Notes |
|---|---|---|---|
| GET | `/agent` | `agent.chat` | Livewire chat (`role:admin\|officer\|teacher`) |

## Tools (connection layer)

The model never writes the database itself. `AgentToolRegistry` exposes only tools the user is `authorized()` for, then each tool re-checks Policies / Actions.

| Tool | Typical roles | What it does |
|---|---|---|
| `offer_choices` | all agent users | Clickable follow-up buttons |
| `list_classes` / `lookup_class` | office + assigned teachers | Resolve `10-A` |
| `get_class_timetable` / `find_free_periods` | office + assigned teachers | Empty Mon–Fri periods 1–8 |
| `find_free_teachers` / `search_teachers` | admin / officer | Who is free on a timeslot |
| `assign_timetable_slot` | admin / officer | Fill an empty period (`UpsertTimetableEntry`) |
| `assign_relief_teacher` | admin / officer | Cover an existing lesson (`AssignReliefTeacher`) |
| `get_teacher_timetable` | office; teachers = self | Weekly teaching slots |
| `search_students` / `get_student_summary` | office + class teachers | Profile + monthly attendance % |
| `get_class_attendance` / `get_at_risk_students` | view-attendance + view-reports | &lt;80% flag |
| `search_exams` / `get_exam_results` | view/enter marks | Published/scoped results |

## Key business rules

- Gemini key is `config('services.gemini.key')` (`GEMINI_API_KEY`). Missing key returns a setup message instead of calling Google.
- Streaming uses Livewire `stream()` so the assistant reply types in live. Markdown is rendered with `Str::markdown()` (`html_input` strip).
- Assigning a **named teacher to a free period** creates a timetable entry (subject required). Relief is only for an existing lesson on a matching weekday date.
- Teachers may inspect timetables of classes they are assigned to (assumption; class/subject/PT-PD via `TeacherReportScope`).
- Mutations write `activity_logs` with `agent.mutated`.
- 20 messages / minute / user.

## Edge cases

- Ambiguous teacher/student/exam names return matches and expect `offer_choices`.
- `10A` normalizes to `10-A`.
- Cross-user conversations 403.

## Status

Done.
