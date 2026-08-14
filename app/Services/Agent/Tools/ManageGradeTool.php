<?php

namespace App\Services\Agent\Tools;

use App\Enums\PermissionName;
use App\Models\Grade;
use App\Models\User;
use App\Services\Agent\AgentScope;
use App\Services\Audit\ActivityLogger;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ManageGradeTool extends AbstractAgentTool
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
        return 'manage_grade';
    }

    public function description(): string
    {
        return 'List, create, update, or delete grades (1–13). Requires manage-system-config.';
    }

    public function parameters(): array
    {
        return $this->objectSchema([
            'action' => $this->stringParam('list, create, update, or delete.'),
            'number' => $this->integerParam('Grade number 1–13.'),
            'name' => $this->stringParam('Display name such as Grade 10.'),
        ], ['action']);
    }

    public function authorized(User $user): bool
    {
        return $user->can(PermissionName::ManageSystemConfig->value);
    }

    public function handle(User $user, array $arguments): array
    {
        return match ($this->normalizedAction($arguments)) {
            'list' => $this->list($user),
            'create' => $this->create($user, $arguments),
            'update' => $this->update($user, $arguments),
            'delete' => $this->delete($user, $arguments),
            default => $this->unknownAction(self::ACTIONS),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function list(User $user): array
    {
        Gate::forUser($user)->authorize('viewAny', Grade::class);

        $grades = [];

        foreach (Grade::query()->orderBy('number')->get() as $grade) {
            $grades[] = [
                'id' => $grade->id,
                'number' => $grade->number,
                'name' => $grade->name,
            ];
        }

        return ['ok' => true, 'grades' => $grades];
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     */
    private function create(User $user, array $arguments): array
    {
        Gate::forUser($user)->authorize('create', Grade::class);

        $data = Validator::make([
            'number' => $this->intArg($arguments, 'number'),
            'name' => $this->stringArg($arguments, 'name'),
        ], [
            'number' => ['required', 'integer', 'min:1', 'max:13', 'unique:grades,number'],
            'name' => ['required', 'string', 'max:50'],
        ])->validate();

        $grade = Grade::query()->create($data);

        $this->logMutation(
            $this->activityLogger,
            $user,
            __('SMIS Agent created grade :name.', ['name' => $grade->name]),
            $grade,
        );

        return ['ok' => true, 'grade' => ['id' => $grade->id, 'number' => $grade->number, 'name' => $grade->name]];
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     */
    private function update(User $user, array $arguments): array
    {
        $number = $this->intArg($arguments, 'number');

        if ($number === null) {
            return ['ok' => false, 'error' => 'number is required to identify the grade.'];
        }

        $grade = $this->scope->resolveGrade((string) $number);
        Gate::forUser($user)->authorize('update', $grade);

        $data = Validator::make([
            'number' => $number,
            'name' => $this->stringArg($arguments, 'name') ?? $grade->name,
        ], [
            'number' => ['required', 'integer', 'min:1', 'max:13', Rule::unique('grades', 'number')->ignore($grade->id)],
            'name' => ['required', 'string', 'max:50'],
        ])->validate();

        $grade->update($data);

        $this->logMutation(
            $this->activityLogger,
            $user,
            __('SMIS Agent updated grade :name.', ['name' => $grade->name]),
            $grade,
        );

        return ['ok' => true, 'grade' => ['id' => $grade->id, 'number' => $grade->number, 'name' => $grade->name]];
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     */
    private function delete(User $user, array $arguments): array
    {
        $number = $this->intArg($arguments, 'number');

        if ($number === null) {
            return ['ok' => false, 'error' => 'number is required.'];
        }

        $grade = $this->scope->resolveGrade((string) $number);
        Gate::forUser($user)->authorize('delete', $grade);

        if ($grade->schoolClasses()->exists()) {
            throw ValidationException::withMessages([
                'grade' => __('Cannot delete a grade that has classes.'),
            ]);
        }

        $label = $grade->name;
        $grade->delete();

        $this->logMutation(
            $this->activityLogger,
            $user,
            __('SMIS Agent deleted grade :name.', ['name' => $label]),
        );

        return ['ok' => true, 'deleted' => true, 'name' => $label];
    }
}
