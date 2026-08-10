# Timetable API

Web routes (Phase 4). Sanctum REST mirror deferred to Phase 8.

| Method | Path | Auth | Notes |
|---|---|---|---|
| GET | `/admin/timetables` | admin + manage-timetable | Query: `academic_year_id`, `school_class_id` |
| POST | `/admin/timetables` | admin + manage-timetable | Create slot; conflict validation |
| PUT/DELETE | `/admin/timetables/{timetable_entry}` | admin + manage-timetable | Update/delete slot |
| GET/POST/DELETE | `/admin/relief-assignments*` | admin + manage-timetable | Manual relief |
| GET | `/teacher/timetable` | teacher + view-timetable | Own slots |
| GET | `/student/timetable` | student + view-timetable | Class slots |
