# Reporting API

Web routes (Phase 7). Sanctum REST mirror deferred to Phase 8.

| Method | Path | Auth | Notes |
|---|---|---|---|
| GET | `/admin/reports` | admin + view-reports | Dashboard |
| GET | `/admin/reports/demographics` | admin + view-reports | `export=csv`, `print=1` |
| GET | `/admin/reports/attendance` | admin + view-reports | `month=YYYY-MM` |
| GET | `/admin/reports/examination` | admin + view-reports | `exam_id`, `subject_id` |
| GET | `/admin/reports/performance` | admin + view-reports | `limit` top/bottom N |
| GET | `/teacher/reports*` | teacher + view-reports | Scoped |
| GET | `/student/report` | student + view-marks | Own only |
