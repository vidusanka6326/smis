<?php

namespace Database\Factories;

use App\Models\Teacher;
use App\Models\TeacherDataSheet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeacherDataSheet>
 */
class TeacherDataSheetFactory extends Factory
{
    protected $model = TeacherDataSheet::class;

    public function definition(): array
    {
        return [
            'teacher_id' => Teacher::factory(),
            'year' => now()->year,
            'school_census_no' => $this->faker->numerify('CE-#####'),
            'nic' => $this->faker->numerify('#########V'),
            'title' => $this->faker->randomElement(['Mr', 'Mrs', 'Ms', 'Rev']),
            'full_name' => strtoupper($this->faker->name()),
            'email' => $this->faker->safeEmail(),
            'date_of_birth' => $this->faker->dateTimeBetween('-60 years', '-25 years')->format('Y-m-d'),
            'ethnicity' => $this->faker->randomElement(['Sinhalese', 'Tamil', 'Muslim', 'Burgher']),
            'gender' => $this->faker->randomElement(['Male', 'Female']),
            'religion' => $this->faker->randomElement(['Buddhist', 'Hindu', 'Christian', 'Islam']),
            'mobile_no' => $this->faker->phoneNumber(),
            'blood_group' => $this->faker->randomElement(['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-']),
            'perm_address' => $this->faker->streetAddress(),
            'perm_district' => $this->faker->city(),
            'perm_ds_division' => $this->faker->word(),
            'perm_gs_division' => $this->faker->word(),
            'perm_telephone' => $this->faker->phoneNumber(),
            'perm_effective_date' => $this->faker->date(),
            'curr_address' => $this->faker->streetAddress(),
            'curr_district' => $this->faker->city(),
            'curr_ds_division' => $this->faker->word(),
            'curr_gs_division' => $this->faker->word(),
            'curr_telephone' => $this->faker->phoneNumber(),
            'curr_postal_code' => $this->faker->postcode(),
            'curr_latitude' => null,
            'curr_longitude' => null,
            'civil_status' => $this->faker->randomElement(['Single', 'Married', 'Widowed', 'Other']),
            'spouse_nic' => null,
            'spouse_full_name' => null,
            'spouse_date_of_birth' => null,
            'spouse_occupation' => null,
            'spouse_office_address' => null,
            'children' => [],
            'gceo_l_1_year' => (string) $this->faker->year(),
            'gceo_l_1_subjects' => ['Mathematics', 'Science', 'English', 'Sinhala', 'History'],
            'gceo_l_2_year' => null,
            'gceo_l_2_subjects' => [],
            'gcea_l_year' => (string) $this->faker->year(),
            'gcea_l_subjects' => ['01 Combined Mathematics', '02 Physics', '03 Chemistry'],
            'professional_qualifications' => [
                ['qual' => 'Diploma', 'subjects_degree' => '', 'effective_date' => ''],
            ],
            'appt_date' => $this->faker->date(),
            'appt_attendant_date' => $this->faker->date(),
            'appt_letter_no_date' => $this->faker->bothify('APP-####/??'),
            'appt_designation' => 'Teacher',
            'appt_grade' => 'III',
            'appt_province' => 'Central',
            'appt_district' => $this->faker->city(),
            'appt_zonal_office' => $this->faker->city(),
            'appt_school' => $this->faker->company().' School',
            'appt_subject' => $this->faker->word(),
            'service_history' => [],
            'ctch_appt_date' => $this->faker->date(),
            'ctch_attended_date' => $this->faker->date(),
            'ctch_appt_letter_no_date' => $this->faker->bothify('APP-####/??'),
            'ctch_designation' => 'Teacher',
            'ctch_grade' => 'II',
            'ctch_province' => 'Central',
            'ctch_district' => $this->faker->city(),
            'ctch_zonal_office' => $this->faker->city(),
            'ctch_school' => $this->faker->company().' School',
            'ctch_subject' => $this->faker->word(),
            'ctch_teaching_without_appt' => null,
            'ctch_teaching_in_addition' => null,
            'capability_subjects' => [],
            'submitted_at' => null,
        ];
    }

    public function submitted(): static
    {
        return $this->state(fn (array $attributes): array => [
            'submitted_at' => now(),
        ]);
    }
}
