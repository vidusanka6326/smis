<?php

namespace Database\Seeders;

use App\Actions\Students\CreateStudent;
use App\Actions\Teachers\CreateTeacher;
use App\Actions\Teachers\SyncTeacherAssignments;
use App\Enums\Gender;
use App\Enums\TeacherAssignmentRole;
use App\Enums\UserStatus;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Seeder;

class TeacherStudentSeeder extends Seeder
{
    /**
     * Seed demo teachers, assignments, and students for the current academic year.
     */
    public function run(): void
    {
        $year = AcademicYear::query()->where('is_current', true)->first()
            ?? AcademicYear::query()->latest('starts_on')->first();

        if ($year === null) {
            return;
        }

        $class10a = SchoolClass::query()
            ->where('academic_year_id', $year->id)
            ->where('code', '10-A')
            ->first();

        $math = Subject::query()->where('code', 'MATH')->first();

        if ($class10a === null) {
            return;
        }

        $createTeacher = app(CreateTeacher::class);
        $syncAssignments = app(SyncTeacherAssignments::class);
        $createStudent = app(CreateStudent::class);

        $classTeacher = Teacher::query()->where('employee_no', 'TCH-1001')->first()
            ?? $createTeacher->handle([
                'name' => 'Class Teacher Demo',
                'email' => 'class.teacher@smis.test',
                'password' => 'password',
                'status' => UserStatus::Active->value,
                'employee_no' => 'TCH-1001',
                'phone' => '0710000001',
            ]);

        $subjectTeacher = Teacher::query()->where('employee_no', 'TCH-1002')->first()
            ?? $createTeacher->handle([
                'name' => 'Subject Teacher Demo',
                'email' => 'subject.teacher@smis.test',
                'password' => 'password',
                'status' => UserStatus::Active->value,
                'employee_no' => 'TCH-1002',
                'phone' => '0710000002',
            ]);

        $assignments = [
            [
                'school_class_id' => $class10a->id,
                'subject_id' => null,
                'role_in_assignment' => TeacherAssignmentRole::ClassTeacher->value,
            ],
        ];

        if ($math !== null && $class10a->subjects()->whereKey($math->id)->exists()) {
            $assignments[] = [
                'school_class_id' => $class10a->id,
                'subject_id' => $math->id,
                'role_in_assignment' => TeacherAssignmentRole::SubjectTeacher->value,
            ];
        }

        $syncAssignments->handle($classTeacher, $year->id, [
            [
                'school_class_id' => $class10a->id,
                'subject_id' => null,
                'role_in_assignment' => TeacherAssignmentRole::ClassTeacher->value,
            ],
        ]);

        if ($math !== null && $class10a->subjects()->whereKey($math->id)->exists()) {
            $syncAssignments->handle($subjectTeacher, $year->id, [
                [
                    'school_class_id' => $class10a->id,
                    'subject_id' => $math->id,
                    'role_in_assignment' => TeacherAssignmentRole::SubjectTeacher->value,
                ],
            ]);
        }

        if (! Student::query()->where('admission_no', 'ADM-10001')->exists()) {
            $createStudent->handle([
                'name' => 'Demo Student',
                'email' => 'student@smis.test',
                'password' => 'password',
                'status' => UserStatus::Active->value,
                'admission_no' => 'ADM-10001',
                'date_of_birth' => '2012-05-01',
                'gender' => Gender::Boy->value,
                'guardian_name' => 'Parent Demo',
                'guardian_phone' => '0710000099',
                'guardian_email' => 'parent@smis.test',
                'guardian_relationship' => 'Father',
                'school_class_id' => $class10a->id,
                'academic_year_id' => $year->id,
            ]);
        }
    }
}
