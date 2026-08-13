<?php

namespace Database\Seeders;

use App\Actions\Teachers\CreateTeacher;
use App\Actions\Teachers\SyncTeacherAssignments;
use App\Enums\EnrollmentStatus;
use App\Enums\Gender;
use App\Enums\RoleName;
use App\Enums\TeacherAssignmentRole;
use App\Enums\UserStatus;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class TeacherStudentSeeder extends Seeder
{
    /**
     * Seed 30 teachers with assignments and 600 students for the current academic year.
     */
    public function run(): void
    {
        $year = AcademicYear::query()->where('is_current', true)->first()
            ?? AcademicYear::query()->latest('starts_on')->first();

        if ($year === null) {
            return;
        }

        $this->seedTeachers($year->id);
        $this->seedStudents($year->id);
    }

    private function seedTeachers(int $academicYearId): void
    {
        $createTeacher = app(CreateTeacher::class);

        foreach (SriLankanDemoCatalog::teachers() as $blueprint) {
            $teacher = Teacher::query()->where('employee_no', $blueprint['employee_no'])->first();

            if ($teacher === null) {
                $createTeacher->handle([
                    'name' => $blueprint['name'],
                    'email' => $blueprint['email'],
                    'password' => SriLankanDemoCatalog::PASSWORD,
                    'status' => UserStatus::Active->value,
                    'employee_no' => $blueprint['employee_no'],
                    'phone' => $blueprint['phone'],
                ]);

                continue;
            }

            $teacher->user?->forceFill(['name' => $blueprint['name']])->save();
            $teacher->forceFill(['phone' => $blueprint['phone']])->save();
        }

        $this->syncAssignments($academicYearId);
    }

    private function syncAssignments(int $academicYearId): void
    {
        $teachers = Teacher::query()->with('user')->get()->keyBy('employee_no');
        $classes = SchoolClass::query()
            ->with(['grade', 'stream', 'subjects'])
            ->where('academic_year_id', $academicYearId)
            ->get()
            ->keyBy('code');
        $subjects = Subject::query()->get()->keyBy('code');
        $catalog = collect(SriLankanDemoCatalog::teachers())->keyBy('employee_no');

        $assignments = [];
        $loads = [];

        foreach ($classes as $schoolClass) {
            $codes = SriLankanDemoCatalog::subjectCodesFor(
                $schoolClass->grade->number,
                $schoolClass->stream?->code,
                $schoolClass->name,
            );

            foreach ($codes as $code) {
                $subject = $subjects->get($code);

                if ($subject === null || ! $schoolClass->subjects->contains('id', $subject->id)) {
                    continue;
                }

                $employeeNo = $this->pickTeacherEmployeeNo(
                    $catalog,
                    $code,
                    $schoolClass,
                    $loads,
                );

                if ($employeeNo === null) {
                    continue;
                }

                $assignments[$employeeNo][] = [
                    'school_class_id' => $schoolClass->id,
                    'subject_id' => $subject->id,
                    'role_in_assignment' => TeacherAssignmentRole::SubjectTeacher->value,
                ];
            }
        }

        foreach ($catalog as $blueprint) {
            if ($blueprint['homeroom'] === null) {
                continue;
            }

            $homeroom = $classes->get($blueprint['homeroom']);

            if ($homeroom === null) {
                continue;
            }

            array_unshift($assignments[$blueprint['employee_no']], [
                'school_class_id' => $homeroom->id,
                'subject_id' => null,
                'role_in_assignment' => TeacherAssignmentRole::ClassTeacher->value,
            ]);
        }

        $ptPd = $catalog->firstWhere('pt_pd', true);

        if ($ptPd !== null) {
            foreach ($classes as $schoolClass) {
                if ($schoolClass->grade->number < 6 || $schoolClass->grade->number > 9) {
                    continue;
                }

                $assignments[$ptPd['employee_no']][] = [
                    'school_class_id' => $schoolClass->id,
                    'subject_id' => null,
                    'role_in_assignment' => TeacherAssignmentRole::PtPdTeacher->value,
                ];
            }
        }

        $sync = app(SyncTeacherAssignments::class);

        foreach ($assignments as $employeeNo => $rows) {
            $teacher = $teachers->get($employeeNo);

            if ($teacher === null) {
                continue;
            }

            $sync->handle($teacher, $academicYearId, $rows);
        }
    }

    /**
     * @param  Collection<string, array<string, mixed>>  $catalog
     * @param  array<string, int>  $loads
     */
    private function pickTeacherEmployeeNo($catalog, string $subjectCode, SchoolClass $schoolClass, array &$loads): ?string
    {
        $grade = $schoolClass->grade->number;

        $ranked = $catalog
            ->filter(fn (array $teacher): bool => in_array($subjectCode, $teacher['subjects'], true))
            ->sortBy(function (array $teacher) use ($schoolClass, $grade, $loads): int {
                $load = $loads[$teacher['employee_no']] ?? 0;
                $score = $load * 10;

                if ($teacher['homeroom'] === $schoolClass->code) {
                    $score -= 1000;
                }

                if (in_array($grade, $teacher['grades'], true)) {
                    $score -= 100;
                } else {
                    $score += 500;
                }

                return $score;
            });

        $chosen = $ranked->first();

        if ($chosen === null) {
            return null;
        }

        $loads[$chosen['employee_no']] = ($loads[$chosen['employee_no']] ?? 0) + 1;

        return $chosen['employee_no'];
    }

    private function seedStudents(int $academicYearId): void
    {
        $classes = SchoolClass::query()
            ->with('grade')
            ->where('academic_year_id', $academicYearId)
            ->get()
            ->keyBy('code');

        $existingAdmissions = Student::query()->pluck('admission_no')->all();
        $existingEmails = User::query()->pluck('email')->all();

        $now = now();
        $password = Hash::make(SriLankanDemoCatalog::PASSWORD);
        $users = [];
        $pending = [];
        $index = 0;
        $nextAdmission = 10002;

        foreach (SriLankanDemoCatalog::classPlans() as $plan) {
            $code = $plan['stream'] === null
                ? sprintf('%d-%s', $plan['grade'], $plan['section'])
                : sprintf('%d-%s-%s', $plan['grade'], $plan['stream'], $plan['section']);

            $schoolClass = $classes->get($code);

            if ($schoolClass === null) {
                continue;
            }

            for ($seat = 0; $seat < $plan['size']; $seat++) {
                $isDemo = $code === '10-A' && $seat === 0;

                if ($isDemo) {
                    $admissionNo = SriLankanDemoCatalog::DEMO_ADMISSION_NO;
                    $female = false;
                    $person = ['first' => 'Kasun', 'last' => 'Perera', 'full' => 'Kasun Perera'];
                    $email = SriLankanDemoCatalog::STUDENT_EMAIL;
                } else {
                    $admissionNo = sprintf('ADM-%d', $nextAdmission);
                    $nextAdmission++;
                    $female = $index % 2 === 1;
                    $person = SriLankanDemoCatalog::person($index, $female);
                    $email = sprintf(
                        '%s.%s.%s@smis.test',
                        Str::lower($person['first']),
                        Str::lower($person['last']),
                        Str::lower(str_replace('-', '', $admissionNo)),
                    );
                }

                $index++;

                if (in_array($admissionNo, $existingAdmissions, true) || in_array($email, $existingEmails, true)) {
                    continue;
                }

                $guardian = SriLankanDemoCatalog::guardianFor($person['last'], $index, $index % 3 !== 0);
                $birthYear = SriLankanDemoCatalog::birthYearForGrade($plan['grade']);

                $users[] = [
                    'name' => $person['full'],
                    'email' => $email,
                    'email_verified_at' => $now,
                    'password' => $password,
                    'status' => UserStatus::Active->value,
                    'remember_token' => Str::random(10),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                $pending[] = [
                    'email' => $email,
                    'admission_no' => $admissionNo,
                    'date_of_birth' => Carbon::createFromDate($birthYear, 1, 1)->addDays($index % 330)->toDateString(),
                    'gender' => $female ? Gender::Girl->value : Gender::Boy->value,
                    'guardian_name' => $guardian['full'],
                    'guardian_phone' => sprintf('07%d%07d', [1, 2, 5, 6, 7, 8][$index % 6], $index % 10000000),
                    'guardian_email' => sprintf('guardian.%s@smis.test', Str::lower(str_replace('-', '', $admissionNo))),
                    'guardian_relationship' => $guardian['female'] ? 'Mother' : 'Father',
                    'school_class_id' => $schoolClass->id,
                ];
            }
        }

        if ($users === []) {
            return;
        }

        foreach (array_chunk($users, 100) as $chunk) {
            User::query()->insert($chunk);
        }

        $userIds = User::query()
            ->whereIn('email', array_column($users, 'email'))
            ->pluck('id', 'email');

        $studentRows = [];
        $now = now();

        foreach ($pending as $row) {
            $userId = $userIds->get($row['email']);

            if ($userId === null) {
                continue;
            }

            $studentRows[] = [
                'user_id' => $userId,
                'admission_no' => $row['admission_no'],
                'date_of_birth' => $row['date_of_birth'],
                'gender' => $row['gender'],
                'guardian_name' => $row['guardian_name'],
                'guardian_phone' => $row['guardian_phone'],
                'guardian_email' => $row['guardian_email'],
                'guardian_relationship' => $row['guardian_relationship'],
                'current_class_id' => $row['school_class_id'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($studentRows, 100) as $chunk) {
            Student::query()->insert($chunk);
        }

        $students = Student::query()
            ->whereIn('admission_no', array_column($pending, 'admission_no'))
            ->get(['id', 'user_id', 'current_class_id', 'admission_no']);

        $enrollments = [];

        foreach ($students as $student) {
            $enrollments[] = [
                'student_id' => $student->id,
                'school_class_id' => $student->current_class_id,
                'academic_year_id' => $academicYearId,
                'status' => EnrollmentStatus::Active->value,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($enrollments, 100) as $chunk) {
            StudentEnrollment::query()->insert($chunk);
        }

        $role = Role::findByName(RoleName::Student->value, 'web');
        $roleRows = [];

        foreach ($students as $student) {
            $roleRows[] = [
                'role_id' => $role->id,
                'model_type' => User::class,
                'model_id' => $student->user_id,
            ];
        }

        foreach (array_chunk($roleRows, 100) as $chunk) {
            DB::table('model_has_roles')->insert($chunk);
        }
    }
}
