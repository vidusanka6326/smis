<?php

namespace Database\Seeders;

use App\Enums\AttendanceStatus;
use App\Models\AcademicYear;
use App\Models\AttendanceSession;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\Teacher;
use App\Models\TeacherAttendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    /**
     * Seed recent class attendance for every class plus teacher attendance.
     */
    public function run(): void
    {
        $year = AcademicYear::query()->where('is_current', true)->first()
            ?? AcademicYear::query()->latest('starts_on')->first();

        $admin = User::query()->where('email', SriLankanDemoCatalog::ADMIN_EMAIL)->first();

        if ($year === null || $admin === null) {
            return;
        }

        $classes = SchoolClass::query()
            ->where('academic_year_id', $year->id)
            ->get();

        $studentsByClass = Student::query()
            ->whereIn('current_class_id', $classes->pluck('id'))
            ->get()
            ->groupBy('current_class_id');

        $dates = $this->recentSchoolDays(10);
        $now = now();
        $attendances = [];

        foreach ($classes as $schoolClass) {
            $students = $studentsByClass->get($schoolClass->id, collect());

            if ($students->isEmpty()) {
                continue;
            }

            foreach ($dates as $date) {
                $session = AttendanceSession::query()->firstOrCreate(
                    [
                        'school_class_id' => $schoolClass->id,
                        'date' => $date,
                        'scope' => AttendanceSession::SCOPE_CLASS,
                    ],
                    [
                        'academic_year_id' => $year->id,
                        'subject_id' => null,
                        'taken_by_teacher_id' => $schoolClass->class_teacher_id,
                        'finalized_at' => $now,
                    ],
                );

                if ($session->studentAttendances()->exists()) {
                    continue;
                }

                foreach ($students as $student) {
                    $attendances[] = [
                        'attendance_session_id' => $session->id,
                        'student_id' => $student->id,
                        'status' => $this->statusFor((int) $student->id, $date),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }

        foreach (array_chunk($attendances, 500) as $chunk) {
            StudentAttendance::query()->insert($chunk);
        }

        $teacherRows = [];

        foreach (Teacher::query()->get() as $teacher) {
            foreach ($dates as $date) {
                if (TeacherAttendance::query()
                    ->where('teacher_id', $teacher->id)
                    ->whereDate('date', $date)
                    ->exists()) {
                    continue;
                }

                $teacherRows[] = [
                    'teacher_id' => $teacher->id,
                    'date' => $date,
                    'status' => $this->statusFor((int) $teacher->id + 50, $date),
                    'recorded_by' => $admin->id,
                    'notes' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        foreach (array_chunk($teacherRows, 200) as $chunk) {
            TeacherAttendance::query()->insert($chunk);
        }
    }

    /**
     * @return list<string>
     */
    private function recentSchoolDays(int $count): array
    {
        $dates = [];
        $cursor = Carbon::today();

        while (count($dates) < $count) {
            if ($cursor->isWeekday()) {
                $dates[] = $cursor->toDateString();
            }

            $cursor = $cursor->subDay();
        }

        return array_reverse($dates);
    }

    private function statusFor(int $seed, string $date): string
    {
        $roll = ($seed + (int) str_replace('-', '', $date)) % 20;

        return match ($roll) {
            0 => AttendanceStatus::Absent->value,
            1 => AttendanceStatus::Late->value,
            2 => AttendanceStatus::Excused->value,
            default => AttendanceStatus::Present->value,
        };
    }
}
