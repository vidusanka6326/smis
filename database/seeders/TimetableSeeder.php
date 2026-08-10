<?php

namespace Database\Seeders;

use App\Actions\Timetable\UpsertTimetableEntry;
use App\Enums\DayOfWeek;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TimetableEntry;
use Illuminate\Database\Seeder;

class TimetableSeeder extends Seeder
{
    /**
     * Seed a small weekly timetable for demo class 10-A.
     */
    public function run(): void
    {
        $year = AcademicYear::query()->where('is_current', true)->first();
        $schoolClass = SchoolClass::query()
            ->where('code', '10-A')
            ->when($year, fn ($q) => $q->where('academic_year_id', $year->id))
            ->first();

        $math = Subject::query()->where('code', 'MATH')->first();
        $english = Subject::query()->where('code', 'ENG')->first();
        $classTeacher = Teacher::query()->where('employee_no', 'TCH-1001')->first();
        $subjectTeacher = Teacher::query()->where('employee_no', 'TCH-1002')->first();

        if ($year === null || $schoolClass === null || $math === null || $english === null || $classTeacher === null || $subjectTeacher === null) {
            return;
        }

        if (TimetableEntry::query()->where('school_class_id', $schoolClass->id)->exists()) {
            return;
        }

        $upsert = app(UpsertTimetableEntry::class);

        $slots = [
            [DayOfWeek::Monday, 1, $math->id, $subjectTeacher->id],
            [DayOfWeek::Monday, 2, $english->id, $classTeacher->id],
            [DayOfWeek::Tuesday, 1, $english->id, $classTeacher->id],
            [DayOfWeek::Wednesday, 3, $math->id, $subjectTeacher->id],
        ];

        foreach ($slots as [$day, $period, $subjectId, $teacherId]) {
            $upsert->handle([
                'academic_year_id' => $year->id,
                'school_class_id' => $schoolClass->id,
                'day_of_week' => $day->value,
                'period_number' => $period,
                'subject_id' => $subjectId,
                'teacher_id' => $teacherId,
            ]);
        }
    }
}
