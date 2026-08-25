<?php

namespace App\Actions\Students;

use App\Enums\EnrollmentStatus;
use App\Enums\Gender;
use App\Enums\RoleName;
use App\Enums\UserStatus;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateStudent
{
    /**
     * Create a student user, profile, and active enrollment in one transaction.
     *
     * @param  array{
     *     name: string,
     *     email: string,
     *     password: string,
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
     *     school_class_id: int,
     *     academic_year_id: int
     * }  $data
     */
    public function handle(array $data): Student
    {
        return DB::transaction(function () use ($data): Student {
            $schoolClass = SchoolClass::query()->with('grade')->findOrFail($data['school_class_id']);

            if ((int) $schoolClass->academic_year_id !== (int) $data['academic_year_id']) {
                throw ValidationException::withMessages([
                    'school_class_id' => __('The class must belong to the selected academic year.'),
                ]);
            }

            $user = User::query()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'status' => UserStatus::tryFrom($data['status'] ?? UserStatus::Active->value) ?? UserStatus::Active,
                'email_verified_at' => now(),
            ]);

            $user->assignRole(RoleName::Student);

            $student = Student::query()->create([
                'user_id' => $user->id,
                'admission_no' => $data['admission_no'],
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'gender' => Gender::from($data['gender']),
                'guardian_name' => $data['guardian_name'] ?? null,
                'guardian_phone' => $data['guardian_phone'] ?? null,
                'guardian_email' => $data['guardian_email'] ?? null,
                'guardian_relationship' => $data['guardian_relationship'] ?? null,
                'current_class_id' => $schoolClass->id,
                'address' => $data['address'] ?? null,
                'grama_niladari_division' => $data['grama_niladari_division'] ?? null,
                'travel_method' => $data['travel_method'] ?? null,
                'town' => $data['town'] ?? null,
                'relations_in_school' => $data['relations_in_school'] ?? null,
            ]);

            StudentEnrollment::query()->create([
                'student_id' => $student->id,
                'school_class_id' => $schoolClass->id,
                'academic_year_id' => $data['academic_year_id'],
                'status' => EnrollmentStatus::Active,
            ]);

            return $student->load(['user', 'currentClass', 'enrollments']);
        });
    }
}
