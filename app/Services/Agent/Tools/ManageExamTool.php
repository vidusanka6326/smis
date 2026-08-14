<?php

namespace App\Services\Agent\Tools;

use App\Actions\Examination\PublishExam;
use App\Actions\Examination\SyncExamSubjects;
use App\Actions\Examination\UpsertExam;
use App\Enums\ExamType;
use App\Enums\PermissionName;
use App\Models\Exam;
use App\Models\User;
use App\Services\Agent\AgentScope;
use App\Services\Audit\ActivityLogger;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ManageExamTool extends AbstractAgentTool
{
    /**
     * @var list<string>
     */
    private const ACTIONS = ['create', 'update', 'delete', 'publish', 'unpublish', 'sync_subjects'];

    public function __construct(
        private AgentScope $scope,
        private UpsertExam $upsertExam,
        private PublishExam $publishExam,
        private SyncExamSubjects $syncExamSubjects,
        private ActivityLogger $activityLogger,
    ) {}

    public function name(): string
    {
        return 'manage_exam';
    }

    public function description(): string
    {
        return 'Create, update, delete, publish, unpublish, or sync exam subjects. Types: term_test, scholarship, ol, al. Requires manage-examinations. Use search_exams to find names.';
    }

    public function parameters(): array
    {
        return $this->objectSchema([
            'action' => $this->stringParam('create, update, delete, publish, unpublish, or sync_subjects.'),
            'exam_name' => $this->stringParam('Existing exam name for update/delete/publish/sync.'),
            'name' => $this->stringParam('Exam title for create/update.'),
            'type' => $this->stringParam('term_test, scholarship, ol, or al.'),
            'grade' => $this->stringParam('Grade number or name (grade and/or class required).'),
            'class_code' => $this->stringParam('Optional class scope such as 10-A.'),
            'starts_on' => $this->stringParam('Start date YYYY-MM-DD.'),
            'ends_on' => $this->stringParam('End date YYYY-MM-DD.'),
            'subjects' => $this->arrayParam('Subjects for sync_subjects.', $this->objectSchema([
                'subject_name' => $this->stringParam('Subject name or code.'),
                'max_marks' => $this->integerParam('Maximum marks (default 100).'),
                'pass_mark' => $this->integerParam('Pass mark (default 40).'),
            ], ['subject_name'])),
        ], ['action']);
    }

    public function authorized(User $user): bool
    {
        return $user->can(PermissionName::ManageExaminations->value);
    }

    public function handle(User $user, array $arguments): array
    {
        return match ($this->normalizedAction($arguments)) {
            'create' => $this->create($user, $arguments),
            'update' => $this->update($user, $arguments),
            'delete' => $this->delete($user, $arguments),
            'publish' => $this->publish($user, $arguments, true),
            'unpublish' => $this->publish($user, $arguments, false),
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
        Gate::forUser($user)->authorize('create', Exam::class);

        $year = $this->scope->resolveAcademicYear();
        $gradeId = null;
        $gradeValue = $this->stringArg($arguments, 'grade') ?? (string) ($this->intArg($arguments, 'grade') ?? '');

        if ($gradeValue !== '') {
            $gradeId = $this->scope->resolveGrade($gradeValue)->id;
        }

        $classId = null;
        $classCode = $this->stringArg($arguments, 'class_code');

        if ($classCode !== null) {
            $class = $this->scope->resolveClass($user, $classCode);
            $classId = $class->id;
            $gradeId ??= (int) $class->grade_id;
        }

        $data = Validator::make([
            'name' => $this->stringArg($arguments, 'name') ?? $this->stringArg($arguments, 'exam_name'),
            'type' => $this->stringArg($arguments, 'type'),
            'academic_year_id' => $year->id,
            'grade_id' => $gradeId,
            'school_class_id' => $classId,
            'starts_on' => $this->stringArg($arguments, 'starts_on'),
            'ends_on' => $this->stringArg($arguments, 'ends_on'),
        ], [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', Rule::enum(ExamType::class)],
            'academic_year_id' => ['required', 'integer'],
            'grade_id' => ['nullable', 'integer'],
            'school_class_id' => ['nullable', 'integer'],
            'starts_on' => ['required', 'date'],
            'ends_on' => ['required', 'date', 'after_or_equal:starts_on'],
        ])->validate();

        $exam = $this->upsertExam->handle([
            'name' => (string) $data['name'],
            'type' => (string) $data['type'],
            'academic_year_id' => (int) $data['academic_year_id'],
            'grade_id' => isset($data['grade_id']) ? (int) $data['grade_id'] : null,
            'school_class_id' => isset($data['school_class_id']) ? (int) $data['school_class_id'] : null,
            'starts_on' => (string) $data['starts_on'],
            'ends_on' => (string) $data['ends_on'],
        ], $user);

        $this->logMutation(
            $this->activityLogger,
            $user,
            __('SMIS Agent created exam :name.', ['name' => $exam->name]),
            $exam,
        );

        return ['ok' => true, 'exam' => $this->payload($exam)];
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     */
    private function update(User $user, array $arguments): array
    {
        $examName = $this->stringArg($arguments, 'exam_name');

        if ($examName === null) {
            return ['ok' => false, 'error' => 'exam_name is required.'];
        }

        $exam = $this->scope->resolveExam($user, $examName);
        Gate::forUser($user)->authorize('update', $exam);

        $gradeId = $exam->grade_id;
        $gradeValue = $this->stringArg($arguments, 'grade') ?? (string) ($this->intArg($arguments, 'grade') ?? '');

        if ($gradeValue !== '') {
            $gradeId = $this->scope->resolveGrade($gradeValue)->id;
        }

        $classId = $exam->school_class_id;
        $classCode = $this->stringArg($arguments, 'class_code');

        if ($classCode !== null) {
            $class = $this->scope->resolveClass($user, $classCode);
            $classId = $class->id;
            $gradeId ??= (int) $class->grade_id;
        }

        $data = Validator::make([
            'name' => $this->stringArg($arguments, 'name') ?? $exam->name,
            'type' => $this->stringArg($arguments, 'type') ?? (string) $exam->getRawOriginal('type'),
            'academic_year_id' => $exam->academic_year_id,
            'grade_id' => $gradeId,
            'school_class_id' => $classId,
            'starts_on' => $this->stringArg($arguments, 'starts_on') ?? $this->dateString($exam->getRawOriginal('starts_on')),
            'ends_on' => $this->stringArg($arguments, 'ends_on') ?? $this->dateString($exam->getRawOriginal('ends_on')),
        ], [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', Rule::enum(ExamType::class)],
            'academic_year_id' => ['required', 'integer'],
            'grade_id' => ['nullable', 'integer'],
            'school_class_id' => ['nullable', 'integer'],
            'starts_on' => ['required', 'date'],
            'ends_on' => ['required', 'date', 'after_or_equal:starts_on'],
        ])->validate();

        $exam = $this->upsertExam->handle([
            'name' => (string) $data['name'],
            'type' => (string) $data['type'],
            'academic_year_id' => (int) $data['academic_year_id'],
            'grade_id' => isset($data['grade_id']) ? (int) $data['grade_id'] : null,
            'school_class_id' => isset($data['school_class_id']) ? (int) $data['school_class_id'] : null,
            'starts_on' => (string) $data['starts_on'],
            'ends_on' => (string) $data['ends_on'],
        ], $user, $exam);

        $this->logMutation(
            $this->activityLogger,
            $user,
            __('SMIS Agent updated exam :name.', ['name' => $exam->name]),
            $exam,
        );

        return ['ok' => true, 'exam' => $this->payload($exam)];
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     */
    private function delete(User $user, array $arguments): array
    {
        $examName = $this->stringArg($arguments, 'exam_name');

        if ($examName === null) {
            return ['ok' => false, 'error' => 'exam_name is required.'];
        }

        $exam = $this->scope->resolveExam($user, $examName);
        Gate::forUser($user)->authorize('delete', $exam);

        $label = $exam->name;
        $exam->delete();

        $this->logMutation(
            $this->activityLogger,
            $user,
            __('SMIS Agent deleted exam :name.', ['name' => $label]),
        );

        return ['ok' => true, 'deleted' => true, 'name' => $label];
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     */
    private function publish(User $user, array $arguments, bool $publish): array
    {
        $examName = $this->stringArg($arguments, 'exam_name');

        if ($examName === null) {
            return ['ok' => false, 'error' => 'exam_name is required.'];
        }

        $exam = $this->scope->resolveExam($user, $examName);
        Gate::forUser($user)->authorize('publish', $exam);
        $exam = $this->publishExam->handle($exam, $publish);

        $this->logMutation(
            $this->activityLogger,
            $user,
            $publish
                ? __('SMIS Agent published exam :name.', ['name' => $exam->name])
                : __('SMIS Agent unpublished exam :name.', ['name' => $exam->name]),
            $exam,
        );

        return ['ok' => true, 'exam' => $this->payload($exam), 'published' => $exam->isPublished()];
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     */
    private function syncSubjects(User $user, array $arguments): array
    {
        $examName = $this->stringArg($arguments, 'exam_name');

        if ($examName === null) {
            return ['ok' => false, 'error' => 'exam_name is required.'];
        }

        $exam = $this->scope->resolveExam($user, $examName);
        Gate::forUser($user)->authorize('update', $exam);

        $subjects = [];

        foreach ($this->arrayArg($arguments, 'subjects') as $row) {
            if (! is_array($row)) {
                continue;
            }

            $subjectName = is_string($row['subject_name'] ?? null) ? trim((string) $row['subject_name']) : '';

            if ($subjectName === '') {
                throw ValidationException::withMessages([
                    'subjects' => __('Each subject row needs subject_name.'),
                ]);
            }

            $subjects[] = [
                'subject_id' => $this->scope->resolveSubjectByName($subjectName)->id,
                'max_marks' => isset($row['max_marks']) && is_numeric($row['max_marks']) ? $row['max_marks'] : 100,
                'pass_mark' => isset($row['pass_mark']) && is_numeric($row['pass_mark']) ? $row['pass_mark'] : 40,
            ];
        }

        if ($subjects === []) {
            return ['ok' => false, 'error' => 'subjects are required.'];
        }

        $exam = $this->syncExamSubjects->handle($exam, $subjects);

        $this->logMutation(
            $this->activityLogger,
            $user,
            __('SMIS Agent synced subjects for exam :name.', ['name' => $exam->name]),
            $exam,
        );

        return ['ok' => true, 'exam' => $this->payload($exam->load('examSubjects.subject'))];
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Exam $exam): array
    {
        $exam->loadMissing(['grade', 'schoolClass', 'examSubjects.subject']);

        $subjects = [];

        foreach ($exam->examSubjects as $examSubject) {
            $subjects[] = [
                'subject' => $examSubject->subject?->name,
                'max_marks' => $examSubject->max_marks,
                'pass_mark' => $examSubject->pass_mark,
            ];
        }

        return [
            'id' => $exam->id,
            'name' => $exam->name,
            'type' => (string) $exam->getRawOriginal('type'),
            'grade' => $exam->grade?->number,
            'class' => $exam->schoolClass?->code,
            'starts_on' => $this->dateString($exam->getRawOriginal('starts_on')),
            'ends_on' => $this->dateString($exam->getRawOriginal('ends_on')),
            'published' => $exam->isPublished(),
            'subjects' => $subjects,
        ];
    }
}
