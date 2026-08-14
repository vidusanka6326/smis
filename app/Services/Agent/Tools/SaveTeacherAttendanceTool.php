<?php

namespace App\Services\Agent\Tools;

use App\Actions\Attendance\UpsertTeacherAttendance;
use App\Enums\AttendanceStatus;
use App\Enums\PermissionName;
use App\Models\TeacherAttendance;
use App\Models\User;
use App\Services\Agent\AgentScope;
use App\Services\Audit\ActivityLogger;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class SaveTeacherAttendanceTool extends AbstractAgentTool
{
    /**
     * @var list<string>
     */
    private const ACTIONS = ['list', 'save', 'delete'];

    public function __construct(
        private AgentScope $scope,
        private UpsertTeacherAttendance $upsertTeacherAttendance,
        private ActivityLogger $activityLogger,
    ) {}

    public function name(): string
    {
        return 'save_teacher_attendance';
    }

    public function description(): string
    {
        return 'List, save, or delete a teacher’s daily attendance. Status: present, absent, late, or excused. Teachers may only save their own record. Office staff may manage anyone.';
    }

    public function parameters(): array
    {
        return $this->objectSchema([
            'action' => $this->stringParam('list, save, or delete.'),
            'teacher_name' => $this->stringParam('Teacher name or employee number. Teachers may omit this to mean themselves.'),
            'date' => $this->stringParam('Date YYYY-MM-DD.'),
            'status' => $this->stringParam('present, absent, late, or excused.'),
            'notes' => $this->stringParam('Optional notes.'),
        ], ['action']);
    }

    public function authorized(User $user): bool
    {
        return $user->can(PermissionName::ManageAttendance->value)
            || $user->can(PermissionName::ViewAttendance->value);
    }

    public function handle(User $user, array $arguments): array
    {
        return match ($this->normalizedAction($arguments)) {
            'list' => $this->list($user, $arguments),
            'save' => $this->save($user, $arguments),
            'delete' => $this->delete($user, $arguments),
            default => $this->unknownAction(self::ACTIONS),
        };
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     */
    private function list(User $user, array $arguments): array
    {
        Gate::forUser($user)->authorize('viewAny', TeacherAttendance::class);

        $query = TeacherAttendance::query()->with(['teacher.user'])->latest('date');
        $teacherName = $this->stringArg($arguments, 'teacher_name');

        if ($teacherName !== null) {
            $teacher = $this->scope->resolveTeacherOrFail($teacherName);
            $query->where('teacher_id', $teacher->id);
        } elseif ($user->isTeacher() && $user->teacher) {
            $query->where('teacher_id', $user->teacher->id);
        }

        $date = $this->stringArg($arguments, 'date');

        if ($date !== null) {
            $query->whereDate('date', $date);
        }

        $rows = [];

        foreach ($query->limit(30)->get() as $row) {
            if (! $user->can('view', $row)) {
                continue;
            }

            $rows[] = [
                'id' => $row->id,
                'teacher' => $row->teacher?->user?->name,
                'date' => $this->dateString($row->getRawOriginal('date')),
                'status' => (string) $row->getRawOriginal('status'),
                'notes' => $row->notes,
            ];
        }

        return ['ok' => true, 'records' => $rows];
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     */
    private function save(User $user, array $arguments): array
    {
        Gate::forUser($user)->authorize('create', TeacherAttendance::class);

        $date = $this->stringArg($arguments, 'date');
        $status = $this->stringArg($arguments, 'status');

        if ($date === null || $status === null || AttendanceStatus::tryFrom($status) === null) {
            return ['ok' => false, 'error' => 'date and a valid status (present, absent, late, excused) are required.'];
        }

        $teacherName = $this->stringArg($arguments, 'teacher_name');
        $teacher = $teacherName !== null
            ? $this->scope->resolveTeacherOrFail($teacherName)
            : $user->teacher;

        if ($teacher === null) {
            throw ValidationException::withMessages([
                'teacher' => __('teacher_name is required.'),
            ]);
        }

        $existing = TeacherAttendance::query()
            ->where('teacher_id', $teacher->id)
            ->whereDate('date', $date)
            ->first();

        if ($existing !== null) {
            Gate::forUser($user)->authorize('update', $existing);
        }

        $attendance = $this->upsertTeacherAttendance->handle([
            'teacher_id' => $teacher->id,
            'date' => $date,
            'status' => $status,
            'notes' => $this->stringArg($arguments, 'notes'),
        ], $user, $existing);

        $this->logMutation(
            $this->activityLogger,
            $user,
            __('SMIS Agent saved teacher attendance for :name on :date.', [
                'name' => $teacher->user->name,
                'date' => $date,
            ]),
            $attendance,
        );

        return [
            'ok' => true,
            'teacher' => $teacher->user->name,
            'date' => $date,
            'status' => $status,
        ];
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     */
    private function delete(User $user, array $arguments): array
    {
        $teacherName = $this->stringArg($arguments, 'teacher_name');
        $date = $this->stringArg($arguments, 'date');

        if ($teacherName === null || $date === null) {
            return ['ok' => false, 'error' => 'teacher_name and date are required.'];
        }

        $teacher = $this->scope->resolveTeacherOrFail($teacherName);
        $attendance = TeacherAttendance::query()
            ->where('teacher_id', $teacher->id)
            ->whereDate('date', $date)
            ->first();

        if ($attendance === null) {
            return ['ok' => false, 'error' => 'No teacher attendance matched that date.'];
        }

        Gate::forUser($user)->authorize('delete', $attendance);
        $attendance->delete();

        $this->logMutation(
            $this->activityLogger,
            $user,
            __('SMIS Agent deleted teacher attendance for :name on :date.', [
                'name' => $teacher->user->name,
                'date' => $date,
            ]),
        );

        return ['ok' => true, 'deleted' => true];
    }
}
