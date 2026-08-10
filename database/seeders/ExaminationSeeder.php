<?php

namespace Database\Seeders;

use App\Actions\Examination\PublishExam;
use App\Actions\Examination\SyncExamSubjects;
use App\Actions\Examination\UpsertExam;
use App\Actions\Examination\UpsertMarks;
use App\Enums\ExamType;
use App\Models\AcademicYear;
use App\Models\Exam;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;

class ExaminationSeeder extends Seeder
{
    public function run(): void
    {
        $year = AcademicYear::query()->where('is_current', true)->first()
            ?? AcademicYear::query()->latest('starts_on')->first();
        $class10a = SchoolClass::query()
            ->when($year !== null, fn ($q) => $q->where('academic_year_id', $year->id))
            ->where('code', '10-A')
            ->first();
        $math = Subject::query()->where('code', 'MATH')->first();
        $student = Student::query()->where('admission_no', 'ADM-10001')->first();
        $classTeacher = Teacher::query()->where('employee_no', 'TCH-1001')->first();
        $admin = User::query()->where('email', 'admin@smis.test')->first();

        if ($year === null || $class10a === null || $math === null || $student === null || $admin === null) {
            return;
        }

        $exam = Exam::query()->where('name', 'Demo Term 1 Test')->first();

        if ($exam === null) {
            $exam = app(UpsertExam::class)->handle([
                'name' => 'Demo Term 1 Test',
                'type' => ExamType::TermTest->value,
                'academic_year_id' => $year->id,
                'grade_id' => $class10a->grade_id,
                'school_class_id' => $class10a->id,
                'starts_on' => now()->subDays(7)->toDateString(),
                'ends_on' => now()->addDays(7)->toDateString(),
            ], $admin);

            app(SyncExamSubjects::class)->handle($exam, [
                [
                    'subject_id' => $math->id,
                    'max_marks' => 100,
                    'pass_mark' => 40,
                ],
            ]);

            $examSubject = $exam->examSubjects()->first();

            if ($examSubject !== null) {
                app(UpsertMarks::class)->handle($examSubject, [
                    [
                        'student_id' => $student->id,
                        'marks_obtained' => 78,
                    ],
                ], $classTeacher);

                app(PublishExam::class)->handle($exam, true);
            }
        }
    }
}
