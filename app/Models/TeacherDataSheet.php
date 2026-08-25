<?php

namespace App\Models;

use Database\Factories\TeacherDataSheetFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $teacher_id
 * @property int $year
 * @property string|null $school_census_no
 * @property string|null $nic
 * @property string|null $title
 * @property string|null $full_name
 * @property string|null $email
 * @property Carbon|null $date_of_birth
 * @property string|null $ethnicity
 * @property string|null $gender
 * @property string|null $religion
 * @property string|null $mobile_no
 * @property string|null $blood_group
 * @property string|null $perm_address
 * @property string|null $perm_district
 * @property string|null $perm_ds_division
 * @property string|null $perm_gs_division
 * @property string|null $perm_telephone
 * @property Carbon|null $perm_effective_date
 * @property string|null $curr_address
 * @property string|null $curr_district
 * @property string|null $curr_ds_division
 * @property string|null $curr_gs_division
 * @property string|null $curr_telephone
 * @property string|null $curr_postal_code
 * @property string|null $curr_latitude
 * @property string|null $curr_longitude
 * @property string|null $civil_status
 * @property string|null $spouse_nic
 * @property string|null $spouse_full_name
 * @property Carbon|null $spouse_date_of_birth
 * @property string|null $spouse_occupation
 * @property string|null $spouse_office_address
 * @property array<int, array{name: string, dob: string, gender: string}>|null $children
 * @property string|null $gceo_l_1_year
 * @property array<int, string>|null $gceo_l_1_subjects
 * @property string|null $gceo_l_2_year
 * @property array<int, string>|null $gceo_l_2_subjects
 * @property string|null $gcea_l_year
 * @property array<int, string>|null $gcea_l_subjects
 * @property array<int, array{qual: string, subjects_degree: string, effective_date: string}>|null $professional_qualifications
 * @property Carbon|null $appt_date
 * @property Carbon|null $appt_attendant_date
 * @property string|null $appt_letter_no_date
 * @property string|null $appt_designation
 * @property string|null $appt_grade
 * @property string|null $appt_province
 * @property string|null $appt_district
 * @property string|null $appt_zonal_office
 * @property string|null $appt_school
 * @property string|null $appt_subject
 * @property array<int, array{zone: string, school: string, period: string, grade: string}>|null $service_history
 * @property Carbon|null $ctch_appt_date
 * @property Carbon|null $ctch_attended_date
 * @property string|null $ctch_appt_letter_no_date
 * @property string|null $ctch_designation
 * @property string|null $ctch_grade
 * @property string|null $ctch_province
 * @property string|null $ctch_district
 * @property string|null $ctch_zonal_office
 * @property string|null $ctch_school
 * @property string|null $ctch_subject
 * @property string|null $ctch_teaching_without_appt
 * @property string|null $ctch_teaching_in_addition
 * @property array<int, array{subject: string, medium: string, section_grades: string}>|null $capability_subjects
 * @property Carbon|null $submitted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class TeacherDataSheet extends Model
{
    /** @use HasFactory<TeacherDataSheetFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'teacher_id',
        'year',
        'school_census_no',
        'nic',
        'title',
        'full_name',
        'email',
        'date_of_birth',
        'ethnicity',
        'gender',
        'religion',
        'mobile_no',
        'blood_group',
        'perm_address',
        'perm_district',
        'perm_ds_division',
        'perm_gs_division',
        'perm_telephone',
        'perm_effective_date',
        'curr_address',
        'curr_district',
        'curr_ds_division',
        'curr_gs_division',
        'curr_telephone',
        'curr_postal_code',
        'curr_latitude',
        'curr_longitude',
        'civil_status',
        'spouse_nic',
        'spouse_full_name',
        'spouse_date_of_birth',
        'spouse_occupation',
        'spouse_office_address',
        'children',
        'gceo_l_1_year',
        'gceo_l_1_subjects',
        'gceo_l_2_year',
        'gceo_l_2_subjects',
        'gcea_l_year',
        'gcea_l_subjects',
        'professional_qualifications',
        'appt_date',
        'appt_attendant_date',
        'appt_letter_no_date',
        'appt_designation',
        'appt_grade',
        'appt_province',
        'appt_district',
        'appt_zonal_office',
        'appt_school',
        'appt_subject',
        'service_history',
        'ctch_appt_date',
        'ctch_attended_date',
        'ctch_appt_letter_no_date',
        'ctch_designation',
        'ctch_grade',
        'ctch_province',
        'ctch_district',
        'ctch_zonal_office',
        'ctch_school',
        'ctch_subject',
        'ctch_teaching_without_appt',
        'ctch_teaching_in_addition',
        'capability_subjects',
        'submitted_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'perm_effective_date' => 'date',
            'spouse_date_of_birth' => 'date',
            'appt_date' => 'date',
            'appt_attendant_date' => 'date',
            'ctch_appt_date' => 'date',
            'ctch_attended_date' => 'date',
            'submitted_at' => 'datetime',
            'children' => 'array',
            'gceo_l_1_subjects' => 'array',
            'gceo_l_2_subjects' => 'array',
            'gcea_l_subjects' => 'array',
            'professional_qualifications' => 'array',
            'service_history' => 'array',
            'capability_subjects' => 'array',
        ];
    }

    public function isSubmitted(): bool
    {
        return $this->submitted_at !== null;
    }

    /**
     * @return BelongsTo<Teacher, $this>
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }
}
