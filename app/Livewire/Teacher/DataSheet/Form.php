<?php

namespace App\Livewire\Teacher\DataSheet;

use App\Models\TeacherDataSheet;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Form extends Component
{
    public $year;

    public $school_census_no;

    // 1. Personal Information
    public $nic;

    public $title;

    public $full_name;

    public $email;

    public $date_of_birth;

    public $ethnicity;

    public $gender;

    public $religion;

    public $mobile_no;

    public $blood_group;

    // 2. Permanent Residence
    public $perm_address;

    public $perm_district;

    public $perm_ds_division;

    public $perm_gs_division;

    public $perm_telephone;

    public $perm_effective_date;

    // 3. Current Residence
    public $curr_address;

    public $curr_district;

    public $curr_ds_division;

    public $curr_gs_division;

    public $curr_telephone;

    public $curr_postal_code;

    public $curr_latitude;

    public $curr_longitude;

    // 4. Family Information
    public $civil_status;

    public $spouse_nic;

    public $spouse_full_name;

    public $spouse_date_of_birth;

    public $spouse_occupation;

    public $spouse_office_address;

    public $children = [];

    // 5. Educational Qualifications
    public $gceo_l_1_year;

    public $gceo_l_1_subjects = ['', '', '', '', '', '', '', '', '', ''];

    public $gceo_l_2_year;

    public $gceo_l_2_subjects = ['', '', '', '', '', '', '', '', '', ''];

    public $gcea_l_year;

    public $gcea_l_subjects = ['', '', '', ''];

    public $professional_qualifications = [];

    // 6. First Appointment
    public $appt_date;

    public $appt_attendant_date;

    public $appt_letter_no_date;

    public $appt_designation;

    public $appt_grade;

    public $appt_province;

    public $appt_district;

    public $appt_zonal_office;

    public $appt_school;

    public $appt_subject;

    // 7. Service History
    public $service_history = [];

    // 8. Currently Teaching
    public $ctch_appt_date;

    public $ctch_attended_date;

    public $ctch_appt_letter_no_date;

    public $ctch_designation;

    public $ctch_grade;

    public $ctch_province;

    public $ctch_district;

    public $ctch_zonal_office;

    public $ctch_school;

    public $ctch_subject;

    public $ctch_teaching_without_appt;

    public $ctch_teaching_in_addition;

    public $capability_subjects = [];

    public $isSubmitted = false;

    public function mount()
    {
        $this->year = now()->year;

        $teacher = Auth::user()->teacher;
        abort_unless($teacher, 403);

        $dataSheet = $teacher->dataSheetForYear($this->year)->first();

        if ($dataSheet) {
            $this->fill($dataSheet->toArray());

            // Format arrays to correct length if missing
            $this->gceo_l_1_subjects = array_pad((array) $this->gceo_l_1_subjects, 10, '');
            $this->gceo_l_2_subjects = array_pad((array) $this->gceo_l_2_subjects, 10, '');
            $this->gcea_l_subjects = array_pad((array) $this->gcea_l_subjects, 4, '');
            $this->children = (array) $this->children;
            $this->professional_qualifications = (array) $this->professional_qualifications;
            $this->service_history = (array) $this->service_history;
            $this->capability_subjects = (array) $this->capability_subjects;

            if ($dataSheet->isSubmitted()) {
                $this->isSubmitted = true;
            }
        } else {
            // Default lengths
            $this->gceo_l_1_subjects = array_fill(0, 10, '');
            $this->gceo_l_2_subjects = array_fill(0, 10, '');
            $this->gcea_l_subjects = array_fill(0, 4, '');

            // Initialize with empty row
            $this->addEmptyRow('children');
            $this->addEmptyRow('professional_qualifications');
            $this->addEmptyRow('service_history');
            $this->addEmptyRow('capability_subjects');
        }
    }

    public function addEmptyRow($property)
    {
        switch ($property) {
            case 'children':
                $this->children[] = ['name' => '', 'dob' => '', 'gender' => ''];
                break;
            case 'professional_qualifications':
                $this->professional_qualifications[] = ['qual' => '', 'subjects_degree' => '', 'effective_date' => ''];
                break;
            case 'service_history':
                $this->service_history[] = ['zone' => '', 'school' => '', 'period' => '', 'grade' => ''];
                break;
            case 'capability_subjects':
                $this->capability_subjects[] = ['subject' => '', 'medium' => '', 'section_grades' => ''];
                break;
        }
    }

    public function removeRow($property, $index)
    {
        unset($this->{$property}[$index]);
        $this->{$property} = array_values($this->{$property});
    }

    public function saveDraft()
    {
        $this->saveToDb();
        Flux::toast(variant: 'success', text: __('Draft saved successfully.'));
    }

    public function submitForm()
    {
        // Simple required validation
        $this->validate([
            'school_census_no' => 'required',
            'nic' => 'required',
            'full_name' => 'required',
        ]);

        $dataSheet = $this->saveToDb();
        $dataSheet->update(['submitted_at' => now()]);
        $this->isSubmitted = true;
        
        Flux::toast(variant: 'success', text: __('Data sheet submitted successfully.'));
    }

    private function saveToDb()
    {
        $teacher = Auth::user()->teacher;

        return TeacherDataSheet::updateOrCreate(
            ['teacher_id' => $teacher->id, 'year' => $this->year],
            [
                'school_census_no' => $this->school_census_no,
                'nic' => $this->nic,
                'title' => $this->title,
                'full_name' => $this->full_name,
                'email' => $this->email,
                'date_of_birth' => $this->date_of_birth,
                'ethnicity' => $this->ethnicity,
                'gender' => $this->gender,
                'religion' => $this->religion,
                'mobile_no' => $this->mobile_no,
                'blood_group' => $this->blood_group,

                'perm_address' => $this->perm_address,
                'perm_district' => $this->perm_district,
                'perm_ds_division' => $this->perm_ds_division,
                'perm_gs_division' => $this->perm_gs_division,
                'perm_telephone' => $this->perm_telephone,
                'perm_effective_date' => $this->perm_effective_date,

                'curr_address' => $this->curr_address,
                'curr_district' => $this->curr_district,
                'curr_ds_division' => $this->curr_ds_division,
                'curr_gs_division' => $this->curr_gs_division,
                'curr_telephone' => $this->curr_telephone,
                'curr_postal_code' => $this->curr_postal_code,
                'curr_latitude' => $this->curr_latitude,
                'curr_longitude' => $this->curr_longitude,

                'civil_status' => $this->civil_status,
                'spouse_nic' => $this->spouse_nic,
                'spouse_full_name' => $this->spouse_full_name,
                'spouse_date_of_birth' => $this->spouse_date_of_birth,
                'spouse_occupation' => $this->spouse_occupation,
                'spouse_office_address' => $this->spouse_office_address,
                'children' => array_values($this->children),

                'gceo_l_1_year' => $this->gceo_l_1_year,
                'gceo_l_1_subjects' => array_values($this->gceo_l_1_subjects),
                'gceo_l_2_year' => $this->gceo_l_2_year,
                'gceo_l_2_subjects' => array_values($this->gceo_l_2_subjects),
                'gcea_l_year' => $this->gcea_l_year,
                'gcea_l_subjects' => array_values($this->gcea_l_subjects),
                'professional_qualifications' => array_values($this->professional_qualifications),

                'appt_date' => $this->appt_date,
                'appt_attendant_date' => $this->appt_attendant_date,
                'appt_letter_no_date' => $this->appt_letter_no_date,
                'appt_designation' => $this->appt_designation,
                'appt_grade' => $this->appt_grade,
                'appt_province' => $this->appt_province,
                'appt_district' => $this->appt_district,
                'appt_zonal_office' => $this->appt_zonal_office,
                'appt_school' => $this->appt_school,
                'appt_subject' => $this->appt_subject,

                'service_history' => array_values($this->service_history),

                'ctch_appt_date' => $this->ctch_appt_date,
                'ctch_attended_date' => $this->ctch_attended_date,
                'ctch_appt_letter_no_date' => $this->ctch_appt_letter_no_date,
                'ctch_designation' => $this->ctch_designation,
                'ctch_grade' => $this->ctch_grade,
                'ctch_province' => $this->ctch_province,
                'ctch_district' => $this->ctch_district,
                'ctch_zonal_office' => $this->ctch_zonal_office,
                'ctch_school' => $this->ctch_school,
                'ctch_subject' => $this->ctch_subject,
                'ctch_teaching_without_appt' => $this->ctch_teaching_without_appt,
                'ctch_teaching_in_addition' => $this->ctch_teaching_in_addition,
                'capability_subjects' => array_values($this->capability_subjects),
            ]
        );
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.teacher.data-sheet.form');
    }
}
