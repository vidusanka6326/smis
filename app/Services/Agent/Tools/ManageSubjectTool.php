<?php

namespace App\Services\Agent\Tools;

use App\Enums\PermissionName;
use App\Models\Subject;
use App\Models\User;
use App\Services\Agent\AgentScope;
use App\Services\Audit\ActivityLogger;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ManageSubjectTool extends AbstractAgentTool
{
    /**
     * @var list<string>
     */
    private const ACTIONS = ['list', 'create', 'update', 'delete'];

    public function __construct(
        private AgentScope $scope,
        private ActivityLogger $activityLogger,
    ) {}

    public function name(): string
    {
        return 'manage_subject';
    }

    public function description(): string
    {
        return 'List, create, update, or delete subjects and their grade ranges. Requires manage-system-config.';
    }

    public function parameters(): array
    {
        return $this->objectSchema([
            'action' => $this->stringParam('list, create, update, or delete.'),
            'name' => $this->stringParam('Subject name such as Mathematics.'),
            'code' => $this->stringParam('Subject code such as MATH.'),
            'min_grade' => $this->integerParam('Lowest grade number this subject applies to.'),
            'max_grade' => $this->integerParam('Highest grade number this subject applies to.'),
        ], ['action']);
    }

    public function authorized(User $user): bool
    {
        return $user->can(PermissionName::ManageSystemConfig->value);
    }

    public function handle(User $user, array $arguments): array
    {
        return match ($this->normalizedAction($arguments)) {
            'list' => $this->list($user, $arguments),
            'create' => $this->create($user, $arguments),
            'update' => $this->update($user, $arguments),
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
        Gate::forUser($user)->authorize('viewAny', Subject::class);

        $search = $this->stringArg($arguments, 'name') ?? $this->stringArg($arguments, 'code');
        $subjects = [];

        foreach (Subject::query()
            ->when($search !== null, function ($query) use ($search): void {
                $query->where(function ($inner) use ($search): void {
                    $inner->where('name', 'like', '%'.$search.'%')
                        ->orWhere('code', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('name')
            ->limit(40)
            ->get() as $subject) {
            $subjects[] = [
                'id' => $subject->id,
                'name' => $subject->name,
                'code' => $subject->code,
                'min_grade' => $subject->min_grade,
                'max_grade' => $subject->max_grade,
            ];
        }

        return ['ok' => true, 'subjects' => $subjects];
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     */
    private function create(User $user, array $arguments): array
    {
        Gate::forUser($user)->authorize('create', Subject::class);

        $code = $this->stringArg($arguments, 'code');

        $data = Validator::make([
            'name' => $this->stringArg($arguments, 'name'),
            'code' => $code !== null ? Str::upper($code) : null,
            'min_grade' => $this->intArg($arguments, 'min_grade'),
            'max_grade' => $this->intArg($arguments, 'max_grade'),
        ], [
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:20', 'alpha_dash', 'unique:subjects,code'],
            'min_grade' => ['required', 'integer', 'min:1', 'max:13'],
            'max_grade' => ['required', 'integer', 'min:1', 'max:13'],
        ])->validate();

        if ($data['min_grade'] > $data['max_grade']) {
            throw ValidationException::withMessages([
                'max_grade' => __('The maximum grade must be greater than or equal to the minimum grade.'),
            ]);
        }

        $subject = Subject::query()->create($data);

        $this->logMutation(
            $this->activityLogger,
            $user,
            __('SMIS Agent created subject :name.', ['name' => $subject->name]),
            $subject,
        );

        return ['ok' => true, 'subject' => $this->payload($subject)];
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     */
    private function update(User $user, array $arguments): array
    {
        $lookup = $this->stringArg($arguments, 'name') ?? $this->stringArg($arguments, 'code');

        if ($lookup === null) {
            return ['ok' => false, 'error' => 'name or code is required to identify the subject.'];
        }

        $subject = $this->scope->resolveSubjectByName($lookup);
        Gate::forUser($user)->authorize('update', $subject);

        $code = $this->stringArg($arguments, 'code');

        $data = Validator::make([
            'name' => $this->stringArg($arguments, 'name') ?? $subject->name,
            'code' => $code !== null ? Str::upper($code) : $subject->code,
            'min_grade' => $this->intArg($arguments, 'min_grade') ?? $subject->min_grade,
            'max_grade' => $this->intArg($arguments, 'max_grade') ?? $subject->max_grade,
        ], [
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:20', 'alpha_dash', Rule::unique('subjects', 'code')->ignore($subject->id)],
            'min_grade' => ['required', 'integer', 'min:1', 'max:13'],
            'max_grade' => ['required', 'integer', 'min:1', 'max:13'],
        ])->validate();

        if ($data['min_grade'] > $data['max_grade']) {
            throw ValidationException::withMessages([
                'max_grade' => __('The maximum grade must be greater than or equal to the minimum grade.'),
            ]);
        }

        $subject->update($data);

        $this->logMutation(
            $this->activityLogger,
            $user,
            __('SMIS Agent updated subject :name.', ['name' => $subject->name]),
            $subject,
        );

        return ['ok' => true, 'subject' => $this->payload($subject->refresh())];
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     */
    private function delete(User $user, array $arguments): array
    {
        $lookup = $this->stringArg($arguments, 'name') ?? $this->stringArg($arguments, 'code');

        if ($lookup === null) {
            return ['ok' => false, 'error' => 'name or code is required.'];
        }

        $subject = $this->scope->resolveSubjectByName($lookup);
        Gate::forUser($user)->authorize('delete', $subject);

        if ($subject->schoolClasses()->exists()) {
            throw ValidationException::withMessages([
                'subject' => __('Cannot delete a subject that is assigned to classes.'),
            ]);
        }

        $label = $subject->name;
        $subject->delete();

        $this->logMutation(
            $this->activityLogger,
            $user,
            __('SMIS Agent deleted subject :name.', ['name' => $label]),
        );

        return ['ok' => true, 'deleted' => true, 'name' => $label];
    }

    /**
     * @return array{id: int, name: string, code: string, min_grade: int, max_grade: int}
     */
    private function payload(Subject $subject): array
    {
        return [
            'id' => $subject->id,
            'name' => $subject->name,
            'code' => $subject->code,
            'min_grade' => $subject->min_grade,
            'max_grade' => $subject->max_grade,
        ];
    }
}
