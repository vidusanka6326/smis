# Examination Module

## Purpose

Create exams (term test, scholarship, O/L, A/L), configure subjects/max/pass marks, scoped marks entry, pass/fail and grade-letter calculation, result locking after publication.

## User roles involved

- Admin — manage exams, subjects, publish/unpublish, enter any marks
- Class teacher — enter marks for all subjects in own class
- Subject teacher — enter marks for assigned subject only
- PT/PD — cannot enter marks
- Student — view own published results

## DB tables used

- `exams` — name, type, academic year, grade and/or class scope, dates, published_at
- `exam_subjects` — exam, subject, max_marks, pass_mark
- `marks` — exam_subject, student, marks_obtained, grade_letter, is_pass, entered_by_teacher_id

## Routes

| Method | Path | Name | Notes |
|---|---|---|---|
| CRUD + publish | `/admin/exams*` | `admin.exams.*` | Admin exam management |
| GET/PUT | `/admin/exams/{exam}/subjects` | `admin.exams.subjects.*` | Subject config |
| GET/PUT | `/admin/marks*` | `admin.marks.*` | Admin mark entry |
| GET/PUT | `/teacher/marks*` | `teacher.marks.*` | Scoped mark entry |
| GET | `/student/results` | `student.results` | Published only |

## Key business rules

- Grade letters: A≥75%, B≥65%, C≥55%, S≥40%, else F.
- Pass/fail: marks ≥ configured pass mark.
- Marks locked after publish; admin may unpublish to edit again.
- Class teachers may enter all subjects in own class (assumed).
- Mark upsert and exam publish/unpublish write `activity_logs` entries (ADR 0010).
- Marks entry grids use Flux `flux:input` number fields.
- Exam and mark-entry indexes use shared `x-list.filters` + pagination (search, year, type, published/draft; ADR 0016). Student results paginate published marks.

## Edge cases

- Publishing requires at least one exam subject.
- Students only see published results.
- Teacher partial mark updates do not wipe other teachers’ rows.

## Status

Done (Phase 6).
