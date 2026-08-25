<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_data_sheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('year');

            // Header
            $table->string('school_census_no')->nullable();

            // 1. Personal Information
            $table->string('nic')->nullable();
            $table->string('title')->nullable();          // Rev/Mr/Mrs/Ms
            $table->string('full_name')->nullable();
            $table->string('email')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('ethnicity')->nullable();
            $table->string('gender')->nullable();
            $table->string('religion')->nullable();
            $table->string('mobile_no')->nullable();
            $table->string('blood_group')->nullable();

            // 2. Permanent Residence
            $table->string('perm_address')->nullable();
            $table->string('perm_district')->nullable();
            $table->string('perm_ds_division')->nullable();
            $table->string('perm_gs_division')->nullable();
            $table->string('perm_telephone')->nullable();
            $table->date('perm_effective_date')->nullable();

            // 3. Current Residence
            $table->string('curr_address')->nullable();
            $table->string('curr_district')->nullable();
            $table->string('curr_ds_division')->nullable();
            $table->string('curr_gs_division')->nullable();
            $table->string('curr_telephone')->nullable();
            $table->string('curr_postal_code')->nullable();
            $table->string('curr_latitude')->nullable();
            $table->string('curr_longitude')->nullable();

            // 4. Family Information
            $table->string('civil_status')->nullable();
            $table->string('spouse_nic')->nullable();
            $table->string('spouse_full_name')->nullable();
            $table->date('spouse_date_of_birth')->nullable();
            $table->string('spouse_occupation')->nullable();
            $table->string('spouse_office_address')->nullable();
            $table->json('children')->nullable();           // [{name, dob, gender}]

            // 5. Educational Qualifications
            $table->string('gceo_l_1_year')->nullable();
            $table->json('gceo_l_1_subjects')->nullable(); // ["Math", "Science", ...]
            $table->string('gceo_l_2_year')->nullable();
            $table->json('gceo_l_2_subjects')->nullable();
            $table->string('gcea_l_year')->nullable();
            $table->json('gcea_l_subjects')->nullable();   // ["01 ...", "02 ...", ...]

            // Professional Qualifications (table rows)
            $table->json('professional_qualifications')->nullable(); // [{qual, subjects_degree, effective_date}]

            // 6. First Appointment
            $table->date('appt_date')->nullable();
            $table->date('appt_attendant_date')->nullable();
            $table->string('appt_letter_no_date')->nullable();
            $table->string('appt_designation')->nullable();
            $table->string('appt_grade')->nullable();
            $table->string('appt_province')->nullable();
            $table->string('appt_district')->nullable();
            $table->string('appt_zonal_office')->nullable();
            $table->string('appt_school')->nullable();
            $table->string('appt_subject')->nullable();

            // 7. Service History
            $table->json('service_history')->nullable(); // [{zone, school, period, grade}]

            // 8. Currently Teaching
            $table->date('ctch_appt_date')->nullable();
            $table->date('ctch_attended_date')->nullable();
            $table->string('ctch_appt_letter_no_date')->nullable();
            $table->string('ctch_designation')->nullable();
            $table->string('ctch_grade')->nullable();
            $table->string('ctch_province')->nullable();
            $table->string('ctch_district')->nullable();
            $table->string('ctch_zonal_office')->nullable();
            $table->string('ctch_school')->nullable();
            $table->string('ctch_subject')->nullable();
            $table->text('ctch_teaching_without_appt')->nullable();
            $table->text('ctch_teaching_in_addition')->nullable();
            $table->json('capability_subjects')->nullable(); // [{subject, medium, section_grades}]

            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->unique(['teacher_id', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_data_sheets');
    }
};
