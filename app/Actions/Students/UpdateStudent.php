<?php

namespace App\Actions\Students;

use App\Enums\EnrollmentStatus;
use App\Enums\Gender;
use App\Enums\UserStatus;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentEnrollment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateStudent
{
    /**
     * Update student profile/user and optionally re-enroll into a class for an academic year.
     *
     * @param  array{
     *     name: string,
     *     email: string,
     *     password?: string|null,
     *     status?: string,
     *     admission_no: string,
     *     date_of_birth?: string|null,
     *     gender: string,
     *     guardian_name?: string|null,
     *     guardian_phone?: string|null,
     *     guardian_email?: string|null,
     *     guardian_relationship?: string|null,
     *     address?: string|null,
     *     grama_niladari_division?: string|null,
     *     travel_method?: string|null,
     *     town?: string|null,
     *     relations_in_school?: array|null,
     *     school_class_id?: int|null,
     *     academic_year_id?: int|null
     * }  $data
     */
    public function handle(Student $student, array $data, bool $adminUpdate = true): Student
    {
        return DB::transaction(function () use ($student, $data, $adminUpdate): Student {
            $userData = [
                'name' => $data['name'],
                'email' => $data['email'],
            ];

            if ($adminUpdate && isset($data['status'])) {
                $userData['status'] = UserStatus::from($data['status']);
            }

            if ($adminUpdate && ! empty($data['password'])) {
                $userData['password'] = $data['password'];
            }

            $student->user->update($userData);

            $profile = [
                'admission_no' => $data['admission_no'],
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'gender' => Gender::from($data['gender']),
                'guardian_name' => $data['guardian_name'] ?? null,
                'guardian_phone' => $data['guardian_phone'] ?? null,
                'guardian_email' => $data['guardian_email'] ?? null,
                'guardian_relationship' => $data['guardian_relationship'] ?? null,
                'address' => $data['address'] ?? null,
                'grama_niladari_division' => $data['grama_niladari_division'] ?? null,
                'travel_method' => $data['travel_method'] ?? null,
                'town' => $data['town'] ?? null,
                'relations_in_school' => $data['relations_in_school'] ?? null,
            ];

            if ($adminUpdate && ! empty($data['school_class_id']) && ! empty($data['academic_year_id'])) {
                $schoolClass = SchoolClass::query()->findOrFail($data['school_class_id']);

                if ((int) $schoolClass->academic_year_id !== (int) $data['academic_year_id']) {
                    throw ValidationException::withMessages([
                        'school_class_id' => __('The class must belong to the selected academic year.'),
                    ]);
                }

                $profile['current_class_id'] = $schoolClass->id;

                StudentEnrollment::query()->updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'academic_year_id' => $data['academic_year_id'],
                    ],
                    [
                        'school_class_id' => $schoolClass->id,
                        'status' => EnrollmentStatus::Active,
                    ],
                );
            }

            $student->update($profile);

            return $student->refresh()->load(['user', 'currentClass', 'enrollments']);
        });
    }
}
