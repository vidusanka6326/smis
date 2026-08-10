<?php

namespace App\Actions\Students;

use App\Enums\EnrollmentStatus;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentEnrollment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EnrollStudent
{
    /**
     * Enroll or move a student into a class for an academic year.
     */
    public function handle(
        Student $student,
        SchoolClass $schoolClass,
        int $academicYearId,
        EnrollmentStatus $status = EnrollmentStatus::Active,
    ): StudentEnrollment {
        if ((int) $schoolClass->academic_year_id !== $academicYearId) {
            throw ValidationException::withMessages([
                'school_class_id' => __('The class must belong to the selected academic year.'),
            ]);
        }

        return DB::transaction(function () use ($student, $schoolClass, $academicYearId, $status): StudentEnrollment {
            $enrollment = StudentEnrollment::query()->updateOrCreate(
                [
                    'student_id' => $student->id,
                    'academic_year_id' => $academicYearId,
                ],
                [
                    'school_class_id' => $schoolClass->id,
                    'status' => $status,
                ],
            );

            if ($status === EnrollmentStatus::Active) {
                $student->forceFill(['current_class_id' => $schoolClass->id])->save();
            }

            return $enrollment->refresh();
        });
    }
}
