<?php

namespace Database\Seeders;

use App\Enums\DayOfWeek;
use App\Enums\TeacherAssignmentRole;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use App\Models\TimetableEntry;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class TimetableSeeder extends Seeder
{
    /**
     * Seed a conflict-free weekly timetable for every class in the current year.
     */
    public function run(): void
    {
        $year = AcademicYear::query()->where('is_current', true)->first();

        if ($year === null) {
            return;
        }

        $classes = SchoolClass::query()
            ->with(['grade', 'stream', 'subjects'])
            ->where('academic_year_id', $year->id)
            ->get();

        $subjects = Subject::query()->get()->keyBy('code');
        $now = now();
        $busy = [];
        $entries = [];

        $existingClassIds = TimetableEntry::query()
            ->where('academic_year_id', $year->id)
            ->pluck('school_class_id')
            ->unique()
            ->all();

        foreach ($classes as $schoolClass) {
            if (in_array($schoolClass->id, $existingClassIds, true)) {
                continue;
            }

            $grade = $schoolClass->grade->number;
            $remaining = $grade >= 12
                ? $this->alRemaining($schoolClass, $subjects)
                : SriLankanDemoCatalog::weeklyPeriodCounts($grade);

            $teacherBySubjectId = TeacherAssignment::query()
                ->where('school_class_id', $schoolClass->id)
                ->where('academic_year_id', $year->id)
                ->where('role_in_assignment', TeacherAssignmentRole::SubjectTeacher)
                ->whereNotNull('subject_id')
                ->pluck('teacher_id', 'subject_id');

            $codes = array_keys($remaining);

            foreach (DayOfWeek::schoolDays() as $day) {
                for ($period = 1; $period <= TimetableEntry::MAX_PERIODS; $period++) {
                    $picked = $this->pickSlot(
                        $remaining,
                        $codes,
                        $subjects,
                        $teacherBySubjectId,
                        $busy,
                        $day->value,
                        $period,
                    );

                    if ($picked === null) {
                        continue;
                    }

                    [$code, $subjectId, $teacherId] = $picked;
                    $remaining[$code]--;
                    $busy[$teacherId.':'.$day->value.':'.$period] = true;

                    $entries[] = [
                        'academic_year_id' => $year->id,
                        'school_class_id' => $schoolClass->id,
                        'day_of_week' => $day->value,
                        'period_number' => $period,
                        'subject_id' => $subjectId,
                        'teacher_id' => $teacherId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }

        foreach (array_chunk($entries, 200) as $chunk) {
            TimetableEntry::query()->insert($chunk);
        }
    }

    /**
     * @param  Collection<string, Subject>  $subjects
     * @return array<string, int>
     */
    private function alRemaining(SchoolClass $schoolClass, $subjects): array
    {
        $codes = SriLankanDemoCatalog::subjectCodesFor(
            $schoolClass->grade->number,
            $schoolClass->stream?->code,
            $schoolClass->name,
        );

        $counts = [];

        foreach ($codes as $code) {
            if ($subjects->has($code)) {
                $counts[$code] = SriLankanDemoCatalog::alPeriodsPerSubject();
            }
        }

        return $counts;
    }

    /**
     * @param  array<string, int>  $remaining
     * @param  list<string>  $codes
     * @param  Collection<string, Subject>  $subjects
     * @param  Collection<int, int>  $teacherBySubjectId
     * @param  array<string, true>  $busy
     * @return array{0: string, 1: int, 2: int}|null
     */
    private function pickSlot(
        array $remaining,
        array $codes,
        $subjects,
        $teacherBySubjectId,
        array $busy,
        int $day,
        int $period,
    ): ?array {
        if ($codes === []) {
            return null;
        }

        $offset = ($day * TimetableEntry::MAX_PERIODS + $period) % count($codes);

        for ($i = 0; $i < count($codes); $i++) {
            $code = $codes[($offset + $i) % count($codes)];

            if (($remaining[$code] ?? 0) <= 0) {
                continue;
            }

            $subject = $subjects->get($code);

            if ($subject === null) {
                continue;
            }

            $teacherId = $teacherBySubjectId->get($subject->id);

            if ($teacherId === null) {
                continue;
            }

            if (isset($busy[$teacherId.':'.$day.':'.$period])) {
                continue;
            }

            return [$code, $subject->id, (int) $teacherId];
        }

        return null;
    }
}
