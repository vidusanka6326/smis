# Examination API

Web routes (Phase 6). Sanctum REST mirror deferred to Phase 8.

| Method | Path | Auth | Notes |
|---|---|---|---|
| GET/POST | `/admin/exams` | admin + manage-examinations | List/create |
| PUT/DELETE | `/admin/exams/{exam}` | admin + manage-examinations | Update/delete |
| POST | `/admin/exams/{exam}/publish` | admin + manage-examinations | Publish |
| POST | `/admin/exams/{exam}/unpublish` | admin + manage-examinations | Unlock |
| GET/PUT | `/admin/exams/{exam}/subjects` | admin + manage-examinations | Sync subjects |
| GET/PUT | `/admin/marks*` | admin + enter-marks | Mark entry |
| GET/PUT | `/teacher/marks*` | teacher + enter-marks | Scoped |
| GET | `/student/results` | student + view-marks | Published only |
