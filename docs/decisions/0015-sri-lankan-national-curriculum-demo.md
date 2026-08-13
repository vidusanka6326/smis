# 0015 — Sri Lankan national-curriculum demo dataset

## Context

Local demo data was a thin sample (two teachers, one student, two classes). The product needs to look like a working Type 1AB Maha Vidyalaya: many classes, realistic subject loads, and Sinhala personal names in English (Roman script) — never Sinhala script in the UI.

Sri Lankan school structure used:

- **Grades 6–9 (junior secondary):** 12 subjects — Buddhism, Sinhala Language, English, Mathematics, Science, History, Geography, Civic Education, Health and Physical Education, Practical and Technical Skills, Art, Tamil as a Second Language.
- **Grades 10–11 (O/L):** 9 subjects — the six core (Religion, First Language, English, Mathematics, Science, History) plus Civic Education, ICT, and Business and Accounting Studies.
- **Grades 12–13 (A/L):** three main subjects per stream — Physical Science (Combined Mathematics, Physics, Chemistry), Biological Science (Biology, Physics, Chemistry), Commerce (Accounting, Business Studies, Economics), Arts (Political Science, Logic and Scientific Method, Geography), Technology (Engineering Technology, Science for Technology, ICT).

## Decision

Seed a current **2026** academic year with 28 classes (grades 6–13 only; grades 1–5 remain as grade records), **1 admin**, **5 officers**, **30 teachers**, and **600 students**. Keep the documented demo logins (`admin@smis.test`, `officer@smis.test`, `class.teacher@smis.test`, `subject.teacher@smis.test`, `student@smis.test`). All labels stay English.

## Consequences

- `php artisan migrate:fresh --seed` is heavier (hundreds of timetable/attendance/mark rows) but dashboards, filters, and reports look populated.
- A/L Science uses two class sections (`A` physical, `B` biological) under the existing single Science stream.
- Re-seed is mostly idempotent by email / employee number / admission number / exam name.
