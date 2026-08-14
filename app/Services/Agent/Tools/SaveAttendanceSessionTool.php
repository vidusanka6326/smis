<?php

namespace App\Services\Agent\Tools;

use App\Actions\Attendance\UpsertAttendanceSession;
use App\Enums\AttendanceStatus;
use App\Enums\PermissionName;
use App\Models\AttendanceSession;
use App\Models\Student;
use App\Models\User;
use App\Services\Agent\AgentScope;
use App\Services\Audit\ActivityLogger;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class SaveAttendanceSessionTool extends AbstractAgentTool
{
    /**
     * @var list<string>
     */
    private const ACTIONS = ['list', 'save', 'finalize', 'delete'];

    public function __construct(
        private AgentScope $scope,
        private UpsertAttendanceSession $upsertAttendanceSession,
        private ActivityLogger $activityLogger,
    ) {}

    public function name(): string
    {
        return 'save_attendance_session';
    }

    public function description(): string
    {
        return 'List, save, finalize, or delete a class attendance session. Records are {student (name or admission_no), status: present|absent|late|excused}. Teachers may only take attendance for classes they teach.';
    }

    public function parameters(): array
    {
        return $this->objectSchema([
            'action' => $this->stringParam('list, save, finalize, or delete.'),
            'class_code' => $this->stringParam('Class code such as 10-A.'),
            'date' => $this->stringParam('Session date YYYY-MM-DD.'),
            'subject_name' => $this->stringParam('Optional subject for a period session. Omit for whole-class attendance.'),
            'notes' => $this->stringParam('Optional notes.'),
            'finalize' => $this->booleanParam('Finalize after saving.'),
            'records' => $this->arrayParam('Student attendance rows.', $this->objectSchema([
                'student' => $this->stringParam('Name or admission number.'),
                'status' => $this->stringParam('present, absent, late, or excused.'),
            ], ['student', 'status'])),
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
            'finalize' => $this->finalize($user, $arguments),
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
        Gate::forUser($user)->authorize('viewAny', AttendanceSession::class);

        $classCode = $this->stringArg($arguments, 'class_code');
        $query = AttendanceSession::query()
            ->with(['schoolClass', 'subject'])
            ->latest('date');

        if ($classCode !== null) {
            $class = $this->scope->resolveClass($user, $classCode);
            $query->where('school_class_id', $class->id);
        } else {
            $classIds = $this->scope->accessibleClassIds($user);

            if ($classIds !== null) {
                $query->whereIn('school_class_id', $classIds);
            }
        }

        $date = $this->stringArg($arguments, 'date');

        if ($date !== null) {
            $query->whereDate('date', $date);
        }

        $sessions = [];

        foreach ($query->limit(20)->get() as $session) {
            if (! $user->can('view', $session)) {
                continue;
            }

            $sessions[] = [
                'id' => $session->id,
                'class' => $session->schoolClass?->code,
                'date' => $this->dateString($session->getRawOriginal('date')),
                'subject' => $session->subject?->name,
                'finalized' => $session->isFinalized(),
            ];
        }

        return ['ok' => true, 'sessions' => $sessions];
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     */
    private function save(User $user, array $arguments): array
    {
        $classCode = $this->stringArg($arguments, 'class_code');
        $date = $this->stringArg($arguments, 'date');

        if ($classCode === null || $date === null) {
            return ['ok' => false, 'error' => 'class_code and date are required.'];
        }

        $class = $this->scope->resolveClass($user, $classCode);
        $subjectId = null;
        $subjectName = $this->stringArg($arguments, 'subject_name');

        if ($subjectName !== null) {
            $subjectId = $this->scope->resolveSubject($class, $subjectName)->id;
        }

        Gate::forUser($user)->authorize('createForClass', [AttendanceSession::class, $class, $subjectId]);

        $existing = AttendanceSession::query()
            ->where('school_class_id', $class->id)
            ->whereDate('date', $date)
            ->where('scope', AttendanceSession::scopeKey($subjectId))
            ->first();

        if ($existing !== null) {
            Gate::forUser($user)->authorize('update', $existing);
        }

        $records = $this->records($class->id, $arguments);

        if ($records === []) {
            return ['ok' => false, 'error' => 'records are required (student + status).'];
        }

        $session = $this->upsertAttendanceSession->handle([
            'academic_year_id' => (int) $class->academic_year_id,
            'school_class_id' => $class->id,
            'subject_id' => $subjectId,
            'date' => $date,
            'notes' => $this->stringArg($arguments, 'notes'),
            'taken_by_teacher_id' => $user->teacher?->id,
            'finalize' => $this->boolArg($arguments, 'finalize') ?? false,
            'records' => $records,
        ], $existing);

        $this->logMutation(
            $this->activityLogger,
            $user,
            __('SMIS Agent saved attendance for :class on :date.', [
                'class' => $class->code,
                'date' => $date,
            ]),
            $session,
        );

        return [
            'ok' => true,
            'session_id' => $session->id,
            'class' => $class->code,
            'date' => $date,
            'finalized' => $session->isFinalized(),
            'record_count' => count($records),
        ];
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     */
    private function finalize(User $user, array $arguments): array
    {
        $session = $this->findSession($user, $arguments);

        if ($session === null) {
            return ['ok' => false, 'error' => 'class_code and date are required to finalize.'];
        }

        Gate::forUser($user)->authorize('finalize', $session);
        $session = $this->upsertAttendanceSession->finalize($session, $user->teacher);

        $this->logMutation(
            $this->activityLogger,
            $user,
            __('SMIS Agent finalized attendance session :id.', ['id' => $session->id]),
            $session,
        );

        return ['ok' => true, 'finalized' => true, 'session_id' => $session->id];
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     */
    private function delete(User $user, array $arguments): array
    {
        $session = $this->findSession($user, $arguments);

        if ($session === null) {
            return ['ok' => false, 'error' => 'class_code and date are required to delete.'];
        }

        Gate::forUser($user)->authorize('delete', $session);
        $session->delete();

        $this->logMutation(
            $this->activityLogger,
            $user,
            __('SMIS Agent deleted an attendance session.'),
        );

        return ['ok' => true, 'deleted' => true];
    }

    /**
     * @param  array<string, mixed>  $arguments
     */
    private function findSession(User $user, array $arguments): ?AttendanceSession
    {
        $classCode = $this->stringArg($arguments, 'class_code');
        $date = $this->stringArg($arguments, 'date');

        if ($classCode === null || $date === null) {
            return null;
        }

        $class = $this->scope->resolveClass($user, $classCode);
        $subjectId = null;
        $subjectName = $this->stringArg($arguments, 'subject_name');

        if ($subjectName !== null) {
            $subjectId = $this->scope->resolveSubject($class, $subjectName)->id;
        }

        return AttendanceSession::query()
            ->where('school_class_id', $class->id)
            ->whereDate('date', $date)
            ->where('scope', AttendanceSession::scopeKey($subjectId))
            ->first();
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return list<array{student_id: int, status: string}>
     */
    private function records(int $classId, array $arguments): array
    {
        $records = [];

        foreach ($this->arrayArg($arguments, 'records') as $row) {
            if (! is_array($row)) {
                continue;
            }

            $lookup = is_string($row['student'] ?? $row['admission_no'] ?? $row['student_name'] ?? null)
                ? trim((string) ($row['student'] ?? $row['admission_no'] ?? $row['student_name']))
                : '';
            $studentId = isset($row['student_id']) && is_numeric($row['student_id']) ? (int) $row['student_id'] : null;
            $statusRaw = is_string($row['status'] ?? null) ? trim((string) $row['status']) : '';

            if (AttendanceStatus::tryFrom($statusRaw) === null) {
                throw ValidationException::withMessages([
                    'records' => __('Each record needs status present, absent, late, or excused.'),
                ]);
            }

            if ($studentId !== null) {
                $student = Student::query()->with('user')->whereKey($studentId)->where('current_class_id', $classId)->first();
            } elseif ($lookup !== '') {
                $roster = Student::query()->with('user')->where('current_class_id', $classId)->get();
                $student = $this->scope->pickStudent($roster, $lookup);
            } else {
                throw ValidationException::withMessages([
                    'records' => __('Each record needs a student name, admission number, or student_id.'),
                ]);
            }

            if ($student === null || (int) $student->current_class_id !== $classId) {
                throw ValidationException::withMessages([
                    'records' => __('All students must belong to the selected class.'),
                ]);
            }

            $records[] = [
                'student_id' => $student->id,
                'status' => $statusRaw,
            ];
        }

        return $records;
    }
}
