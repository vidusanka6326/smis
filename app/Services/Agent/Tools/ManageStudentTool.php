<?php

namespace App\Services\Agent\Tools;

use App\Actions\Students\CreateStudent;
use App\Actions\Students\EnrollStudent;
use App\Actions\Students\UpdateStudent;
use App\Enums\EnrollmentStatus;
use App\Enums\Gender;
use App\Enums\PermissionName;
use App\Enums\UserStatus;
use App\Models\Student;
use App\Models\User;
use App\Services\Agent\AgentScope;
use App\Services\Audit\ActivityLogger;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class ManageStudentTool extends AbstractAgentTool
{
    /**
     * @var list<string>
     */
    private const ACTIONS = ['create', 'update', 'delete', 'enroll'];

    public function __construct(
        private AgentScope $scope,
        private CreateStudent $createStudent,
        private UpdateStudent $updateStudent,
        private EnrollStudent $enrollStudent,
        private ActivityLogger $activityLogger,
    ) {}

    public function name(): string
    {
        return 'manage_student';
    }

    public function description(): string
    {
        return 'Create, update, delete, or enroll a student. Office staff may manage any student. Class teachers may create/update only in their own homeroom. Gender is G or B. Password is required on create.';
    }

    public function parameters(): array
    {
        return $this->objectSchema([
            'action' => $this->stringParam('create, update, delete, or enroll.'),
            'search' => $this->stringParam('Existing student name or admission number (update/delete/enroll).'),
            'name' => $this->stringParam('Full name.'),
            'email' => $this->stringParam('Login email.'),
            'password' => $this->stringParam('Required on create.'),
            'admission_no' => $this->stringParam('Admission number.'),
            'gender' => $this->stringParam('G or B (girl/boy).'),
            'date_of_birth' => $this->stringParam('YYYY-MM-DD.'),
            'guardian_name' => $this->stringParam('Guardian name.'),
            'guardian_phone' => $this->stringParam('Guardian phone.'),
            'guardian_email' => $this->stringParam('Guardian email.'),
            'guardian_relationship' => $this->stringParam('Guardian relationship.'),
            'class_code' => $this->stringParam('Class such as 10-A.'),
            'status' => $this->stringParam('active or inactive (office only).'),
        ], ['action']);
    }

    public function authorized(User $user): bool
    {
        return $this->scope->canViewStudents($user);
    }

    public function handle(User $user, array $arguments): array
    {
        return match ($this->normalizedAction($arguments)) {
            'create' => $this->create($user, $arguments),
            'update' => $this->update($user, $arguments),
            'delete' => $this->delete($user, $arguments),
            'enroll' => $this->enroll($user, $arguments),
            default => $this->unknownAction(self::ACTIONS),
        };
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     */
    private function create(User $user, array $arguments): array
    {
        Gate::forUser($user)->authorize('create', Student::class);

        $classCode = $this->stringArg($arguments, 'class_code');
        $genderRaw = $this->stringArg($arguments, 'gender');

        if ($classCode === null || $genderRaw === null) {
            return ['ok' => false, 'error' => 'class_code and gender are required.'];
        }

        $class = $this->scope->resolveClass($user, $classCode);
        Gate::forUser($user)->authorize('createInClass', [Student::class, $class]);

        $gender = $this->scope->parseGender($genderRaw);
        $adminCreate = $user->can(PermissionName::ManageStudents->value);

        $data = Validator::make([
            'name' => $this->stringArg($arguments, 'name'),
            'email' => $this->stringArg($arguments, 'email'),
            'password' => $this->stringArg($arguments, 'password'),
            'status' => $adminCreate
                ? ($this->stringArg($arguments, 'status') ?? UserStatus::Active->value)
                : UserStatus::Active->value,
            'admission_no' => $this->stringArg($arguments, 'admission_no'),
            'date_of_birth' => $this->stringArg($arguments, 'date_of_birth'),
            'gender' => $gender->value,
            'guardian_name' => $this->stringArg($arguments, 'guardian_name'),
            'guardian_phone' => $this->stringArg($arguments, 'guardian_phone'),
            'guardian_email' => $this->stringArg($arguments, 'guardian_email'),
            'guardian_relationship' => $this->stringArg($arguments, 'guardian_relationship'),
            'school_class_id' => $class->id,
            'academic_year_id' => $class->academic_year_id,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', Password::default()],
            'status' => ['required', 'string', Rule::enum(UserStatus::class)],
            'admission_no' => ['required', 'string', 'max:50', 'unique:students,admission_no'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'gender' => ['required', 'string', Rule::enum(Gender::class)],
            'guardian_name' => ['nullable', 'string', 'max:255'],
            'guardian_phone' => ['nullable', 'string', 'max:30'],
            'guardian_email' => ['nullable', 'email', 'max:255'],
            'guardian_relationship' => ['nullable', 'string', 'max:50'],
            'school_class_id' => ['required', 'integer'],
            'academic_year_id' => ['required', 'integer'],
        ])->validate();

        $student = $this->createStudent->handle([
            'name' => (string) $data['name'],
            'email' => (string) $data['email'],
            'password' => (string) $data['password'],
            'status' => (string) $data['status'],
            'admission_no' => (string) $data['admission_no'],
            'date_of_birth' => isset($data['date_of_birth']) ? (string) $data['date_of_birth'] : null,
            'gender' => (string) $data['gender'],
            'guardian_name' => isset($data['guardian_name']) ? (string) $data['guardian_name'] : null,
            'guardian_phone' => isset($data['guardian_phone']) ? (string) $data['guardian_phone'] : null,
            'guardian_email' => isset($data['guardian_email']) ? (string) $data['guardian_email'] : null,
            'guardian_relationship' => isset($data['guardian_relationship']) ? (string) $data['guardian_relationship'] : null,
            'school_class_id' => (int) $data['school_class_id'],
            'academic_year_id' => (int) $data['academic_year_id'],
        ]);

        $this->logMutation(
            $this->activityLogger,
            $user,
            __('SMIS Agent created student :name.', ['name' => $student->user->name]),
            $student,
        );

        return ['ok' => true, 'student' => $this->payload($student)];
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     */
    private function update(User $user, array $arguments): array
    {
        $search = $this->stringArg($arguments, 'search') ?? $this->stringArg($arguments, 'admission_no');

        if ($search === null) {
            return ['ok' => false, 'error' => 'search or admission_no is required.'];
        }

        $student = $this->scope->resolveStudent($user, $search)->load(['user', 'currentClass']);
        Gate::forUser($user)->authorize('update', $student);

        $adminUpdate = $user->can(PermissionName::ManageStudents->value);
        $genderRaw = $this->stringArg($arguments, 'gender');
        $gender = $this->scope->parseGender(
            $genderRaw ?? (string) $student->getRawOriginal('gender'),
        );

        $class = $student->currentClass;
        $classCode = $this->stringArg($arguments, 'class_code');

        if ($adminUpdate && $classCode !== null) {
            $class = $this->scope->resolveClass($user, $classCode);
        }

        $data = Validator::make([
            'name' => $this->stringArg($arguments, 'name') ?? $student->user->name,
            'email' => $this->stringArg($arguments, 'email') ?? $student->user->email,
            'password' => $this->stringArg($arguments, 'password'),
            'status' => $adminUpdate
                ? ($this->stringArg($arguments, 'status') ?? $student->user->status->value)
                : $student->user->status->value,
            'admission_no' => $this->stringArg($arguments, 'admission_no') ?? $student->admission_no,
            'date_of_birth' => $this->stringArg($arguments, 'date_of_birth') ?? $this->dateString($student->getRawOriginal('date_of_birth')),
            'gender' => $gender->value,
            'guardian_name' => $this->stringArg($arguments, 'guardian_name') ?? $student->guardian_name,
            'guardian_phone' => $this->stringArg($arguments, 'guardian_phone') ?? $student->guardian_phone,
            'guardian_email' => $this->stringArg($arguments, 'guardian_email') ?? $student->guardian_email,
            'guardian_relationship' => $this->stringArg($arguments, 'guardian_relationship') ?? $student->guardian_relationship,
            'school_class_id' => $class?->id,
            'academic_year_id' => $class?->academic_year_id,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($student->user_id)],
            'password' => ['nullable', 'string', Password::default()],
            'status' => ['required', 'string', Rule::enum(UserStatus::class)],
            'admission_no' => ['required', 'string', 'max:50', Rule::unique('students', 'admission_no')->ignore($student->id)],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'gender' => ['required', 'string', Rule::enum(Gender::class)],
            'guardian_name' => ['nullable', 'string', 'max:255'],
            'guardian_phone' => ['nullable', 'string', 'max:30'],
            'guardian_email' => ['nullable', 'email', 'max:255'],
            'guardian_relationship' => ['nullable', 'string', 'max:50'],
            'school_class_id' => ['nullable', 'integer'],
            'academic_year_id' => ['nullable', 'integer'],
        ])->validate();

        $student = $this->updateStudent->handle($student, [
            'name' => (string) $data['name'],
            'email' => (string) $data['email'],
            'password' => isset($data['password']) ? (string) $data['password'] : null,
            'status' => (string) $data['status'],
            'admission_no' => (string) $data['admission_no'],
            'date_of_birth' => isset($data['date_of_birth']) ? (string) $data['date_of_birth'] : null,
            'gender' => (string) $data['gender'],
            'guardian_name' => isset($data['guardian_name']) ? (string) $data['guardian_name'] : null,
            'guardian_phone' => isset($data['guardian_phone']) ? (string) $data['guardian_phone'] : null,
            'guardian_email' => isset($data['guardian_email']) ? (string) $data['guardian_email'] : null,
            'guardian_relationship' => isset($data['guardian_relationship']) ? (string) $data['guardian_relationship'] : null,
            'school_class_id' => isset($data['school_class_id']) ? (int) $data['school_class_id'] : null,
            'academic_year_id' => isset($data['academic_year_id']) ? (int) $data['academic_year_id'] : null,
        ], adminUpdate: $adminUpdate);

        $this->logMutation(
            $this->activityLogger,
            $user,
            __('SMIS Agent updated student :name.', ['name' => $student->user->name]),
            $student,
        );

        return ['ok' => true, 'student' => $this->payload($student)];
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     */
    private function delete(User $user, array $arguments): array
    {
        $search = $this->stringArg($arguments, 'search') ?? $this->stringArg($arguments, 'admission_no');

        if ($search === null) {
            return ['ok' => false, 'error' => 'search or admission_no is required.'];
        }

        $student = $this->scope->resolveStudent($user, $search);
        Gate::forUser($user)->authorize('delete', $student);

        $label = $student->user->name;
        $student->delete();

        $this->logMutation(
            $this->activityLogger,
            $user,
            __('SMIS Agent deleted student :name.', ['name' => $label]),
        );

        return ['ok' => true, 'deleted' => true, 'name' => $label];
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     */
    private function enroll(User $user, array $arguments): array
    {
        if (! $user->can(PermissionName::ManageStudents->value)) {
            throw ValidationException::withMessages([
                'student' => __('Only office staff may move a student to another class.'),
            ]);
        }

        $search = $this->stringArg($arguments, 'search') ?? $this->stringArg($arguments, 'admission_no');
        $classCode = $this->stringArg($arguments, 'class_code');

        if ($search === null || $classCode === null) {
            return ['ok' => false, 'error' => 'search and class_code are required.'];
        }

        $student = $this->scope->resolveStudent($user, $search);
        Gate::forUser($user)->authorize('update', $student);

        $class = $this->scope->resolveClass($user, $classCode);
        $this->enrollStudent->handle($student, $class, (int) $class->academic_year_id, EnrollmentStatus::Active);

        $this->logMutation(
            $this->activityLogger,
            $user,
            __('SMIS Agent enrolled :name in :class.', [
                'name' => $student->user->name,
                'class' => $class->code,
            ]),
            $student,
        );

        return ['ok' => true, 'student' => $this->payload($student->refresh()->load(['user', 'currentClass']))];
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Student $student): array
    {
        $student->loadMissing(['user', 'currentClass']);

        return [
            'id' => $student->id,
            'name' => $student->user->name,
            'email' => $student->user->email,
            'admission_no' => $student->admission_no,
            'gender' => (string) $student->getRawOriginal('gender'),
            'class' => $student->currentClass?->code,
            'status' => $student->user->status->value,
        ];
    }
}
