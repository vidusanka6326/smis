<?php

namespace Database\Seeders;

use App\Enums\ExamType;
use App\Enums\GradeLetter;
use App\Models\AcademicYear;
use App\Models\Exam;
use App\Models\ExamSubject;
use App\Models\Mark;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class ExaminationSeeder extends Seeder
{
    /**
     * Seed published first-term tests with marks for junior, O/L, and A/L classes.
     */
    public function run(): void
    {
        $year = AcademicYear::query()->where('is_current', true)->first()
            ?? AcademicYear::query()->latest('starts_on')->first();
        $admin = User::query()->where('email', SriLankanDemoCatalog::ADMIN_EMAIL)->first();
        $enteredBy = Teacher::query()->where('employee_no', SriLankanDemoCatalog::DEMO_CLASS_TEACHER_NO)->first();

        if ($year === null || $admin === null) {
            return;
        }

        $classes = SchoolClass::query()
            ->with(['grade', 'stream', 'subjects'])
            ->where('academic_year_id', $year->id)
            ->get();

        $now = now();

        foreach ([6, 8, 10, 11] as $gradeNumber) {
            $gradeClasses = $classes->filter(fn (SchoolClass $class): bool => $class->grade->number === $gradeNumber);

            if ($gradeClasses->isEmpty()) {
                continue;
            }

            $sample = $gradeClasses->first();
            $type = $gradeNumber === 11 ? ExamType::Ol : ExamType::TermTest;

            $this->seedGradeExam(
                $year->id,
                $admin->id,
                $enteredBy?->id,
                sprintf('First Term Test — Grade %d', $gradeNumber),
                $type,
                $sample->grade_id,
                null,
                $sample->subjects,
                $now,
            );
        }

        foreach ($classes->filter(fn (SchoolClass $class): bool => in_array($class->grade->number, [12, 13], true)) as $schoolClass) {
            $this->seedGradeExam(
                $year->id,
                $admin->id,
                $enteredBy?->id,
                sprintf('First Term Test — %s', $schoolClass->code),
                ExamType::Al,
                $schoolClass->grade_id,
                $schoolClass->id,
                $schoolClass->subjects,
                $now,
            );
        }
    }

    /**
     * @param  Collection<int, Subject>  $subjects
     */
    private function seedGradeExam(
        int $yearId,
        int $adminId,
        ?int $enteredByTeacherId,
        string $name,
        ExamType $type,
        int $gradeId,
        ?int $schoolClassId,
        $subjects,
        $now,
    ): void {
        if (Exam::query()->where('name', $name)->where('academic_year_id', $yearId)->exists()) {
            return;
        }

        $exam = Exam::query()->create([
            'name' => $name,
            'type' => $type,
            'academic_year_id' => $yearId,
            'grade_id' => $gradeId,
            'school_class_id' => $schoolClassId,
            'starts_on' => '2026-03-02',
            'ends_on' => '2026-03-20',
            'published_at' => $now,
            'created_by' => $adminId,
        ]);

        $examSubjects = [];

        foreach ($subjects as $subject) {
            $examSubjects[] = [
                'exam_id' => $exam->id,
                'subject_id' => $subject->id,
                'max_marks' => 100,
                'pass_mark' => 40,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($examSubjects !== []) {
            ExamSubject::query()->insert($examSubjects);
        }

        $students = $exam->eligibleStudents();
        $createdSubjects = $exam->examSubjects()->get();
        $marks = [];

        foreach ($createdSubjects as $examSubject) {
            foreach ($students as $student) {
                $obtained = $this->marksFor((int) $student->id, (int) $examSubject->subject_id);
                $letter = $this->gradeLetter($obtained);

                $marks[] = [
                    'exam_subject_id' => $examSubject->id,
                    'student_id' => $student->id,
                    'marks_obtained' => $obtained,
                    'grade_letter' => $letter->value,
                    'is_pass' => $obtained >= 40,
                    'entered_by_teacher_id' => $enteredByTeacherId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        foreach (array_chunk($marks, 500) as $chunk) {
            Mark::query()->insert($chunk);
        }
    }

    private function marksFor(int $studentId, int $subjectId): int
    {
        return 38 + (($studentId * 17 + $subjectId * 13) % 58);
    }

    private function gradeLetter(int $marks): GradeLetter
    {
        return match (true) {
            $marks >= 75 => GradeLetter::A,
            $marks >= 65 => GradeLetter::B,
            $marks >= 55 => GradeLetter::C,
            $marks >= 40 => GradeLetter::S,
            default => GradeLetter::F,
        };
    }
}
