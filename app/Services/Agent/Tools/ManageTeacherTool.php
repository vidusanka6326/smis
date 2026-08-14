<?php

namespace App\Services\Agent\Tools;

use App\Actions\Teachers\CreateTeacher;
use App\Actions\Teachers\SyncTeacherAssignments;
use App\Actions\Teachers\UpdateTeacher;
use App\Enums\PermissionName;
use App\Enums\TeacherAssignmentRole;
use App\Enums\UserStatus;
use App\Models\Teacher;
use App\Models\User;
use App\Services\Agent\AgentScope;
use App\Services\Audit\ActivityLogger;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class ManageTeacherTool extends AbstractAgentTool
{
    /**
     * @var list<string>
     */
    private const ACTIONS = ['create', 'update', 'delete', 'list_assignments', 'sync_assignments'];

    public function __construct(
        private AgentScope $scope,
        private CreateTeacher $createTeacher,
        private UpdateTeacher $updateTeacher,
        private SyncTeacherAssignments $syncTeacherAssignments,
        private ActivityLogger $activityLogger,
    ) {}

    public function name(): string
    {
        return 'manage_teacher';
    }

    public function description(): string
    {
        return 'Create, update, or delete a teacher, list their assignments, or replace assignments for the current year. Requires manage-teachers. Assignment roles: class_teacher, subject_teacher, pt_pd_teacher.';
    }

    public function parameters(): array
    {
        return $this->objectSchema([
            'action' => $this->stringParam('create, update, delete, list_assignments, or sync_assignments.'),
            'teacher_name' => $this->stringParam('Existing teacher name or employee number.'),
            'name' => $this->stringParam('Full name for create/update.'),
            'email' => $this->stringParam('Login email.'),
            'password' => $this->stringParam('Required on create. Optional on update.'),
            'employee_no' => $this->stringParam('Employee number.'),
            'phone' => $this->stringParam('Optional phone.'),
            'status' => $this->stringParam('active or inactive.'),
            'assignments' => $this->arrayParam('Replacement assignment rows for sync_assignments.', $this->objectSchema([
                'class_code' => $this->stringParam('Class code such as 10-A.'),
                'subject_name' => $this->stringParam('Required for subject_teacher.'),
                'role' => $this->stringParam('class_teacher, subject_teacher, or pt_pd_teacher.'),
            ], ['class_code', 'role'])),
        ], ['action']);
    }

    public function authorized(User $user): bool
    {
        return $user->can(PermissionName::ManageTeachers->value);
    }

    public function handle(User $user, array $arguments): array
    {
        return match ($this->normalizedAction($arguments)) {
            'create' => $this->create($user, $arguments),
            'update' => $this->update($user, $arguments),
            'delete' => $this->delete($user, $arguments),
            'list_assignments' => $this->listAssignments($user, $arguments),
            'sync_assignments' => $this->syncAssignments($user, $arguments),
            default => $this->unknownAction(self::ACTIONS),
        };
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     */
    private function create(User $user, array $arguments): array
    {
        Gate::forUser($user)->authorize('create', Teacher::class);

        $data = Validator::make([
            'name' => $this->stringArg($arguments, 'name'),
            'email' => $this->stringArg($arguments, 'email'),
            'password' => $this->stringArg($arguments, 'password'),
            'status' => $this->stringArg($arguments, 'status') ?? UserStatus::Active->value,
            'employee_no' => $this->stringArg($arguments, 'employee_no'),
            'phone' => $this->stringArg($arguments, 'phone'),
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', Password::default()],
            'status' => ['required', 'string', Rule::enum(UserStatus::class)],
            'employee_no' => ['required', 'string', 'max:50', 'unique:teachers,employee_no'],
            'phone' => ['nullable', 'string', 'max:30'],
        ])->validate();

        $teacher = $this->createTeacher->handle([
            'name' => (string) $data['name'],
            'email' => (string) $data['email'],
            'password' => (string) $data['password'],
            'status' => (string) $data['status'],
            'employee_no' => (string) $data['employee_no'],
            'phone' => isset($data['phone']) ? (string) $data['phone'] : null,
        ]);

        $this->logMutation(
            $this->activityLogger,
            $user,
            __('SMIS Agent created teacher :name.', ['name' => $teacher->user->name]),
            $teacher,
        );

        return ['ok' => true, 'teacher' => $this->payload($teacher)];
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     */
    private function update(User $user, array $arguments): array
    {
        $lookup = $this->stringArg($arguments, 'teacher_name') ?? $this->stringArg($arguments, 'employee_no');

        if ($lookup === null) {
            return ['ok' => false, 'error' => 'teacher_name is required.'];
        }

        $teacher = $this->scope->resolveTeacherOrFail($lookup)->load('user');
        Gate::forUser($user)->authorize('update', $teacher);

        $data = Validator::make([
            'name' => $this->stringArg($arguments, 'name') ?? $teacher->user->name,
            'email' => $this->stringArg($arguments, 'email') ?? $teacher->user->email,
            'password' => $this->stringArg($arguments, 'password'),
            'status' => $this->stringArg($arguments, 'status') ?? $teacher->user->status->value,
            'employee_no' => $this->stringArg($arguments, 'employee_no') ?? $teacher->employee_no,
            'phone' => $this->stringArg($arguments, 'phone') ?? $teacher->phone,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($teacher->user_id)],
            'password' => ['nullable', 'string', Password::default()],
            'status' => ['required', 'string', Rule::enum(UserStatus::class)],
            'employee_no' => ['required', 'string', 'max:50', Rule::unique('teachers', 'employee_no')->ignore($teacher->id)],
            'phone' => ['nullable', 'string', 'max:30'],
        ])->validate();

        $teacher = $this->updateTeacher->handle($teacher, [
            'name' => (string) $data['name'],
            'email' => (string) $data['email'],
            'password' => isset($data['password']) ? (string) $data['password'] : null,
            'status' => (string) $data['status'],
            'employee_no' => (string) $data['employee_no'],
            'phone' => isset($data['phone']) ? (string) $data['phone'] : null,
        ]);

        $this->logMutation(
            $this->activityLogger,
            $user,
            __('SMIS Agent updated teacher :name.', ['name' => $teacher->user->name]),
            $teacher,
        );

        return ['ok' => true, 'teacher' => $this->payload($teacher)];
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     */
    private function delete(User $user, array $arguments): array
    {
        $lookup = $this->stringArg($arguments, 'teacher_name') ?? $this->stringArg($arguments, 'employee_no');

        if ($lookup === null) {
            return ['ok' => false, 'error' => 'teacher_name is required.'];
        }

        $teacher = $this->scope->resolveTeacherOrFail($lookup);
        Gate::forUser($user)->authorize('delete', $teacher);

        $label = $teacher->user->name;
        $teacher->delete();

        $this->logMutation(
            $this->activityLogger,
            $user,
            __('SMIS Agent deleted teacher :name.', ['name' => $label]),
        );

        return ['ok' => true, 'deleted' => true, 'name' => $label];
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     */
    private function listAssignments(User $user, array $arguments): array
    {
        $lookup = $this->stringArg($arguments, 'teacher_name') ?? $this->stringArg($arguments, 'employee_no');

        if ($lookup === null) {
            return ['ok' => false, 'error' => 'teacher_name is required.'];
        }

        $teacher = $this->scope->resolveTeacherOrFail($lookup)->load(['assignments.schoolClass', 'assignments.subject', 'assignments.academicYear', 'user']);
        Gate::forUser($user)->authorize('view', $teacher);

        $assignments = [];

        foreach ($teacher->assignments as $assignment) {
            $assignments[] = [
                'class' => $assignment->schoolClass?->code,
                'subject' => $assignment->subject?->name,
                'role' => (string) $assignment->getRawOriginal('role_in_assignment'),
                'academic_year' => $assignment->academicYear?->name,
            ];
        }

        return [
            'ok' => true,
            'teacher' => $this->payload($teacher),
            'assignments' => $assignments,
        ];
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     */
    private function syncAssignments(User $user, array $arguments): array
    {
        $lookup = $this->stringArg($arguments, 'teacher_name') ?? $this->stringArg($arguments, 'employee_no');

        if ($lookup === null) {
            return ['ok' => false, 'error' => 'teacher_name is required.'];
        }

        $teacher = $this->scope->resolveTeacherOrFail($lookup);
        Gate::forUser($user)->authorize('manageAssignments', $teacher);

        $yearId = $this->scope->requireAcademicYearId();
        $rows = [];

        foreach ($this->arrayArg($arguments, 'assignments') as $row) {
            if (! is_array($row)) {
                continue;
            }

            $classCode = is_string($row['class_code'] ?? null) ? trim((string) $row['class_code']) : '';
            $role = is_string($row['role'] ?? $row['role_in_assignment'] ?? null)
                ? trim((string) ($row['role'] ?? $row['role_in_assignment']))
                : '';

            if ($classCode === '' || TeacherAssignmentRole::tryFrom($role) === null) {
                throw ValidationException::withMessages([
                    'assignments' => __('Each assignment needs class_code and a valid role (class_teacher, subject_teacher, pt_pd_teacher).'),
                ]);
            }

            $class = $this->scope->resolveClass($user, $classCode);
            $subjectName = is_string($row['subject_name'] ?? null) ? trim((string) $row['subject_name']) : '';

            $rows[] = [
                'school_class_id' => $class->id,
                'subject_id' => $subjectName !== '' ? $this->scope->resolveSubject($class, $subjectName)->id : null,
                'role_in_assignment' => $role,
            ];
        }

        $teacher = $this->syncTeacherAssignments->handle($teacher, $yearId, $rows);

        $this->logMutation(
            $this->activityLogger,
            $user,
            __('SMIS Agent synced assignments for :name.', ['name' => $teacher->user->name]),
            $teacher,
        );

        return $this->listAssignments($user, $arguments);
    }

    /**
     * @return array{id: int, name: string, email: string, employee_no: string, phone: string|null, status: string}
     */
    private function payload(Teacher $teacher): array
    {
        return [
            'id' => $teacher->id,
            'name' => $teacher->user->name,
            'email' => $teacher->user->email,
            'employee_no' => $teacher->employee_no,
            'phone' => $teacher->phone,
            'status' => $teacher->user->status->value,
        ];
    }
}
