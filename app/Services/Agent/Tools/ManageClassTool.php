<?php

namespace App\Services\Agent\Tools;

use App\Actions\Academic\SyncClassSubjects;
use App\Enums\PermissionName;
use App\Enums\TeacherAssignmentRole;
use App\Models\SchoolClass;
use App\Models\TeacherAssignment;
use App\Models\User;
use App\Services\Agent\AgentScope;
use App\Services\Audit\ActivityLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class ManageClassTool extends AbstractAgentTool
{
    /**
     * @var list<string>
     */
    private const ACTIONS = ['create', 'update', 'delete', 'sync_subjects'];

    public function __construct(
        private AgentScope $scope,
        private SyncClassSubjects $syncClassSubjects,
        private ActivityLogger $activityLogger,
    ) {}

    public function name(): string
    {
        return 'manage_class';
    }

    public function description(): string
    {
        return 'Create, update, or delete a class, or sync its subjects. Use list_classes / lookup_class to find existing classes. Requires manage-system-config. Grades 12–13 need a stream.';
    }

    public function parameters(): array
    {
        return $this->objectSchema([
            'action' => $this->stringParam('create, update, delete, or sync_subjects.'),
            'class_code' => $this->stringParam('Existing class code such as 10-A (required for update/delete/sync_subjects).'),
            'section' => $this->stringParam('Section letter such as A.'),
            'grade' => $this->stringParam('Grade number or name.'),
            'stream' => $this->stringParam('Stream name or code (required for grades 12–13).'),
            'class_teacher' => $this->stringParam('Optional class teacher name or employee number.'),
            'subject_names' => $this->arrayParam('Subject names or codes to attach.', $this->stringParam('Subject name.')),
        ], ['action']);
    }

    public function authorized(User $user): bool
    {
        return $user->can(PermissionName::ManageSystemConfig->value);
    }

    public function handle(User $user, array $arguments): array
    {
        return match ($this->normalizedAction($arguments)) {
            'create' => $this->create($user, $arguments),
            'update' => $this->update($user, $arguments),
            'delete' => $this->delete($user, $arguments),
            'sync_subjects' => $this->syncSubjects($user, $arguments),
            default => $this->unknownAction(self::ACTIONS),
        };
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     */
    private function create(User $user, array $arguments): array
    {
        Gate::forUser($user)->authorize('create', SchoolClass::class);

        $section = $this->stringArg($arguments, 'section');
        $gradeValue = $this->stringArg($arguments, 'grade') ?? (string) ($this->intArg($arguments, 'grade') ?? '');

        if ($section === null || $gradeValue === '') {
            return ['ok' => false, 'error' => 'section and grade are required to create a class.'];
        }

        $year = $this->scope->resolveAcademicYear();
        $grade = $this->scope->resolveGrade($gradeValue);
        $stream = null;
        $streamName = $this->stringArg($arguments, 'stream');

        if ($grade->allowsStream()) {
            if ($streamName === null) {
                throw ValidationException::withMessages([
                    'stream' => __('A stream is required for grades 12 and 13.'),
                ]);
            }

            $stream = $this->scope->resolveStream($streamName);
        } elseif ($streamName !== null) {
            throw ValidationException::withMessages([
                'stream' => __('Streams may only be assigned to grades 12 and 13.'),
            ]);
        }

        $classTeacherId = null;
        $teacherName = $this->stringArg($arguments, 'class_teacher');

        if ($teacherName !== null) {
            $classTeacherId = $this->scope->resolveTeacherOrFail($teacherName)->id;
        }

        $subjectIds = $this->subjectIds($arguments);

        $schoolClass = DB::transaction(function () use ($year, $grade, $stream, $section, $classTeacherId, $subjectIds): SchoolClass {
            $schoolClass = SchoolClass::query()->create([
                'name' => $section,
                'academic_year_id' => $year->id,
                'grade_id' => $grade->id,
                'stream_id' => $stream?->id,
                'class_teacher_id' => $classTeacherId,
                'code' => SchoolClass::buildCode($grade, $section, $stream),
            ]);

            $this->syncClassSubjects->handle($schoolClass, $subjectIds);
            $this->syncHomeroomAssignment($schoolClass);

            return $schoolClass->load(['grade', 'stream', 'classTeacher.user', 'subjects']);
        });

        $this->logMutation(
            $this->activityLogger,
            $user,
            __('SMIS Agent created class :code.', ['code' => $schoolClass->code]),
            $schoolClass,
        );

        return ['ok' => true, 'class' => $this->payload($schoolClass)];
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     */
    private function update(User $user, array $arguments): array
    {
        $code = $this->stringArg($arguments, 'class_code');

        if ($code === null) {
            return ['ok' => false, 'error' => 'class_code is required.'];
        }

        $schoolClass = $this->scope->resolveClass($user, $code);
        Gate::forUser($user)->authorize('update', $schoolClass);

        $section = $this->stringArg($arguments, 'section') ?? $schoolClass->name;
        $gradeValue = $this->stringArg($arguments, 'grade') ?? (string) ($this->intArg($arguments, 'grade') ?? $schoolClass->grade?->number);
        $grade = $this->scope->resolveGrade((string) $gradeValue);
        $stream = $schoolClass->stream;
        $streamName = $this->stringArg($arguments, 'stream');

        if ($grade->allowsStream()) {
            if ($streamName !== null) {
                $stream = $this->scope->resolveStream($streamName);
            } elseif ($stream === null) {
                throw ValidationException::withMessages([
                    'stream' => __('A stream is required for grades 12 and 13.'),
                ]);
            }
        } else {
            $stream = null;
        }

        $classTeacherId = $schoolClass->class_teacher_id;
        $teacherName = $this->stringArg($arguments, 'class_teacher');

        if ($teacherName !== null) {
            $classTeacherId = $this->scope->resolveTeacherOrFail($teacherName)->id;
        }

        $subjectIds = [];

        if (array_key_exists('subject_names', $arguments)) {
            $subjectIds = $this->subjectIds($arguments);
        } else {
            foreach ($schoolClass->subjects()->pluck('subjects.id') as $id) {
                $subjectIds[] = (int) $id;
            }
        }

        DB::transaction(function () use ($schoolClass, $grade, $stream, $section, $classTeacherId, $subjectIds): void {
            $schoolClass->update([
                'name' => $section,
                'grade_id' => $grade->id,
                'stream_id' => $stream?->id,
                'class_teacher_id' => $classTeacherId,
                'code' => SchoolClass::buildCode($grade, $section, $stream),
            ]);

            $this->syncClassSubjects->handle($schoolClass, $subjectIds);
            $this->syncHomeroomAssignment($schoolClass->fresh() ?? $schoolClass);
        });

        $schoolClass = $schoolClass->refresh()->load(['grade', 'stream', 'classTeacher.user', 'subjects']);

        $this->logMutation(
            $this->activityLogger,
            $user,
            __('SMIS Agent updated class :code.', ['code' => $schoolClass->code]),
            $schoolClass,
        );

        return ['ok' => true, 'class' => $this->payload($schoolClass)];
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     */
    private function delete(User $user, array $arguments): array
    {
        $code = $this->stringArg($arguments, 'class_code');

        if ($code === null) {
            return ['ok' => false, 'error' => 'class_code is required.'];
        }

        $schoolClass = $this->scope->resolveClass($user, $code);
        Gate::forUser($user)->authorize('delete', $schoolClass);

        $label = $schoolClass->code;
        $schoolClass->delete();

        $this->logMutation(
            $this->activityLogger,
            $user,
            __('SMIS Agent deleted class :code.', ['code' => $label]),
        );

        return ['ok' => true, 'deleted' => true, 'code' => $label];
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     */
    private function syncSubjects(User $user, array $arguments): array
    {
        $code = $this->stringArg($arguments, 'class_code');

        if ($code === null) {
            return ['ok' => false, 'error' => 'class_code is required.'];
        }

        $schoolClass = $this->scope->resolveClass($user, $code);
        Gate::forUser($user)->authorize('update', $schoolClass);

        $this->syncClassSubjects->handle($schoolClass, $this->subjectIds($arguments));
        $schoolClass = $schoolClass->refresh()->load(['grade', 'stream', 'classTeacher.user', 'subjects']);

        $this->logMutation(
            $this->activityLogger,
            $user,
            __('SMIS Agent synced subjects for class :code.', ['code' => $schoolClass->code]),
            $schoolClass,
        );

        return ['ok' => true, 'class' => $this->payload($schoolClass)];
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return list<int>
     */
    private function subjectIds(array $arguments): array
    {
        $ids = [];

        foreach ($this->arrayArg($arguments, 'subject_names') as $name) {
            if (! is_string($name) || trim($name) === '') {
                continue;
            }

            $ids[] = $this->scope->resolveSubjectByName($name)->id;
        }

        return $ids;
    }

    private function syncHomeroomAssignment(SchoolClass $schoolClass): void
    {
        TeacherAssignment::query()
            ->where('school_class_id', $schoolClass->id)
            ->where('academic_year_id', $schoolClass->academic_year_id)
            ->where('role_in_assignment', TeacherAssignmentRole::ClassTeacher)
            ->delete();

        if ($schoolClass->class_teacher_id === null) {
            return;
        }

        TeacherAssignment::query()->create([
            'teacher_id' => $schoolClass->class_teacher_id,
            'school_class_id' => $schoolClass->id,
            'subject_id' => null,
            'academic_year_id' => $schoolClass->academic_year_id,
            'role_in_assignment' => TeacherAssignmentRole::ClassTeacher,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(SchoolClass $schoolClass): array
    {
        $subjects = [];

        foreach ($schoolClass->subjects as $subject) {
            $subjects[] = $subject->name;
        }

        return [
            'id' => $schoolClass->id,
            'code' => $schoolClass->code,
            'section' => $schoolClass->name,
            'grade' => $schoolClass->grade?->number,
            'stream' => $schoolClass->stream?->name,
            'class_teacher' => $schoolClass->classTeacher?->user?->name,
            'subjects' => $subjects,
        ];
    }
}
