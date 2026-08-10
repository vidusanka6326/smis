<?php

namespace Database\Seeders;

use App\Actions\Attendance\UpsertAttendanceSession;
use App\Actions\Attendance\UpsertTeacherAttendance;
use App\Enums\AttendanceStatus;
use App\Models\AcademicYear;
use App\Models\AttendanceSession;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeacherAttendance;
use App\Models\User;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $year = AcademicYear::query()->where('is_current', true)->first()
            ?? AcademicYear::query()->latest('starts_on')->first();

        $class10a = SchoolClass::query()
            ->when($year !== null, fn ($q) => $q->where('academic_year_id', $year->id))
            ->where('code', '10-A')
            ->first();

        $classTeacher = Teacher::query()->where('employee_no', 'TCH-1001')->first();
        $student = Student::query()->where('admission_no', 'ADM-10001')->first();
        $admin = User::query()->where('email', 'admin@smis.test')->first();

        if ($year === null || $class10a === null || $classTeacher === null || $student === null || $admin === null) {
            return;
        }

        if (! AttendanceSession::query()
            ->where('school_class_id', $class10a->id)
            ->whereDate('date', now()->toDateString())
            ->where('scope', AttendanceSession::SCOPE_CLASS)
            ->exists()) {
            app(UpsertAttendanceSession::class)->handle([
                'academic_year_id' => $year->id,
                'school_class_id' => $class10a->id,
                'subject_id' => null,
                'date' => now()->toDateString(),
                'taken_by_teacher_id' => $classTeacher->id,
                'notes' => 'Demo class attendance',
                'records' => [
                    [
                        'student_id' => $student->id,
                        'status' => AttendanceStatus::Present->value,
                    ],
                ],
            ]);
        }

        if (! TeacherAttendance::query()
            ->where('teacher_id', $classTeacher->id)
            ->whereDate('date', now()->toDateString())
            ->exists()) {
            app(UpsertTeacherAttendance::class)->handle([
                'teacher_id' => $classTeacher->id,
                'date' => now()->toDateString(),
                'status' => AttendanceStatus::Present->value,
                'notes' => 'Demo teacher attendance',
            ], $admin);
        }
    }
}
