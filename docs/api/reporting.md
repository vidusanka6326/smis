# Reporting API

Web routes. Sanctum REST mirror deferred to Phase 8.

Catalog pages have no export query. Report pages accept `export=csv` or `export=pdf`.

| Method | Path | Auth | Notes |
|---|---|---|---|
| GET | `/admin/reports` | admin/officer + view-reports | Catalog |
| GET | `/admin/reports/demographics` | admin/officer + view-reports | Class/subject filters |
| GET | `/admin/reports/attendance` | admin/officer + view-reports | `month=YYYY-MM`, class |
| GET | `/admin/reports/at-risk` | admin/officer + view-reports | `month=YYYY-MM`, class |
| GET | `/admin/reports/staff-attendance` | admin/officer + view-reports | `month=YYYY-MM`, teacher |
| GET | `/admin/reports/enrollment` | admin/officer + view-reports | Grade/class/gender |
| GET | `/admin/reports/examination` | admin/officer + view-reports | `exam_id`, `subject_id` |
| GET | `/admin/reports/exam-results` | admin/officer + view-reports | `exam_id`, `subject_id`, `result` |
| GET | `/admin/reports/performance` | admin/officer + view-reports | `limit` top/bottom N |
| GET | `/admin/reports/assignments` | admin/officer + view-reports | Year/class/role |
| GET | `/teacher/reports*` | teacher + view-reports | Scoped; 403 outside assignment |
| GET | `/student/reports` | student + view-marks | Catalog |
| GET | `/student/report` | student + view-marks | Report card |
| GET | `/student/reports/attendance` | student + view-marks | Own month |
| GET | `/student/reports/results` | student + view-marks | Own published marks |
