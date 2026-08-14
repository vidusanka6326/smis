<?php

namespace App\Services\Reporting;

use App\Enums\Gender;
use App\Models\Student;
use Illuminate\Support\Collection;

class ClassEnrollmentReport
{
    /**
     * @param  list<int>|null  $schoolClassIds
     * @return list<array{admission_no: string, name: string, gender: string, class: string, grade: string, date_of_birth: string, guardian_name: string, guardian_phone: string}>
     */
    public function rows(?array $schoolClassIds = null, ?int $gradeId = null, ?string $gender = null): array
    {
        /** @var Collection<int, Student> $students */
        $students = Student::query()
            ->with(['user', 'currentClass.grade'])
            ->when($schoolClassIds !== null, fn ($q) => $q->whereIn('current_class_id', $schoolClassIds))
            ->when($gradeId !== null, function ($q) use ($gradeId): void {
                $q->whereHas('currentClass', fn ($classQuery) => $classQuery->where('grade_id', $gradeId));
            })
            ->when($gender !== null, fn ($q) => $q->where('gender', $gender))
            ->orderBy('admission_no')
            ->get();

        return $students->map(function (Student $student): array {
            $gender = $student->gender instanceof Gender ? $student->gender->label() : (string) $student->gender;

            return [
                'admission_no' => (string) $student->admission_no,
                'name' => $student->user?->name ?? '—',
                'gender' => $gender,
                'class' => $student->currentClass?->code ?? '—',
                'grade' => $student->currentClass?->grade?->name ?? '—',
                'date_of_birth' => $student->date_of_birth?->toDateString() ?? '—',
                'guardian_name' => (string) ($student->guardian_name ?? '—'),
                'guardian_phone' => (string) ($student->guardian_phone ?? '—'),
            ];
        })->all();
    }
}
