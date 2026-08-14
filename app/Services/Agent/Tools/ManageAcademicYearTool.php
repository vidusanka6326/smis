<?php

namespace App\Services\Agent\Tools;

use App\Actions\Academic\SetCurrentAcademicYear;
use App\Enums\PermissionName;
use App\Models\AcademicYear;
use App\Models\User;
use App\Services\Agent\AgentScope;
use App\Services\Audit\ActivityLogger;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ManageAcademicYearTool extends AbstractAgentTool
{
    /**
     * @var list<string>
     */
    private const ACTIONS = ['list', 'create', 'update', 'delete', 'set_current'];

    public function __construct(
        private AgentScope $scope,
        private SetCurrentAcademicYear $setCurrentAcademicYear,
        private ActivityLogger $activityLogger,
    ) {}

    public function name(): string
    {
        return 'manage_academic_year';
    }

    public function description(): string
    {
        return 'List, create, update, delete, or set the current academic year. Requires manage-system-config.';
    }

    public function parameters(): array
    {
        return $this->objectSchema([
            'action' => $this->stringParam('list, create, update, delete, or set_current.'),
            'name' => $this->stringParam('Academic year name such as 2026.'),
            'starts_on' => $this->stringParam('Start date YYYY-MM-DD.'),
            'ends_on' => $this->stringParam('End date YYYY-MM-DD.'),
            'is_current' => $this->booleanParam('Mark as the current year after create/update.'),
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
            'set_current' => $this->setCurrent($user, $arguments),
            default => $this->unknownAction(self::ACTIONS),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function list(User $user): array
    {
        Gate::forUser($user)->authorize('viewAny', AcademicYear::class);

        $years = [];

        foreach (AcademicYear::query()->orderByDesc('starts_on')->limit(20)->get() as $year) {
            $years[] = [
                'id' => $year->id,
                'name' => $year->name,
                'starts_on' => $this->dateString($year->getRawOriginal('starts_on')),
                'ends_on' => $this->dateString($year->getRawOriginal('ends_on')),
                'is_current' => $year->is_current,
            ];
        }

        return ['ok' => true, 'academic_years' => $years];
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     */
    private function create(User $user, array $arguments): array
    {
        Gate::forUser($user)->authorize('create', AcademicYear::class);

        $data = Validator::make([
            'name' => $this->stringArg($arguments, 'name'),
            'starts_on' => $this->stringArg($arguments, 'starts_on'),
            'ends_on' => $this->stringArg($arguments, 'ends_on'),
            'is_current' => $this->boolArg($arguments, 'is_current') ?? false,
        ], [
            'name' => ['required', 'string', 'max:50', 'unique:academic_years,name'],
            'starts_on' => ['required', 'date'],
            'ends_on' => ['required', 'date', 'after:starts_on'],
            'is_current' => ['boolean'],
        ])->validate();

        $year = AcademicYear::query()->create([
            'name' => $data['name'],
            'starts_on' => $data['starts_on'],
            'ends_on' => $data['ends_on'],
            'is_current' => false,
        ]);

        if ($data['is_current']) {
            $this->setCurrentAcademicYear->handle($year);
        }

        $this->logMutation(
            $this->activityLogger,
            $user,
            __('SMIS Agent created academic year :name.', ['name' => $year->name]),
            $year,
        );

        return ['ok' => true, 'academic_year' => $this->payload($year->refresh())];
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     */
    private function update(User $user, array $arguments): array
    {
        $year = $this->scope->resolveAcademicYear($this->stringArg($arguments, 'name'));
        Gate::forUser($user)->authorize('update', $year);

        $data = Validator::make([
            'name' => $this->stringArg($arguments, 'name') ?? $year->name,
            'starts_on' => $this->stringArg($arguments, 'starts_on') ?? $this->dateString($year->getRawOriginal('starts_on')),
            'ends_on' => $this->stringArg($arguments, 'ends_on') ?? $this->dateString($year->getRawOriginal('ends_on')),
            'is_current' => $this->boolArg($arguments, 'is_current'),
        ], [
            'name' => ['required', 'string', 'max:50', Rule::unique('academic_years', 'name')->ignore($year->id)],
            'starts_on' => ['required', 'date'],
            'ends_on' => ['required', 'date', 'after:starts_on'],
            'is_current' => ['nullable', 'boolean'],
        ])->validate();

        $year->update([
            'name' => $data['name'],
            'starts_on' => $data['starts_on'],
            'ends_on' => $data['ends_on'],
        ]);

        if (($data['is_current'] ?? null) === true) {
            $this->setCurrentAcademicYear->handle($year);
        } elseif (($data['is_current'] ?? null) === false && $year->is_current) {
            $year->forceFill(['is_current' => false])->save();
        }

        $this->logMutation(
            $this->activityLogger,
            $user,
            __('SMIS Agent updated academic year :name.', ['name' => $year->name]),
            $year,
        );

        return ['ok' => true, 'academic_year' => $this->payload($year->refresh())];
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     */
    private function delete(User $user, array $arguments): array
    {
        $name = $this->stringArg($arguments, 'name');

        if ($name === null) {
            return ['ok' => false, 'error' => 'name is required to delete an academic year.'];
        }

        $year = $this->scope->resolveAcademicYear($name);
        Gate::forUser($user)->authorize('delete', $year);

        if ($year->schoolClasses()->exists()) {
            throw ValidationException::withMessages([
                'academic_year' => __('Cannot delete an academic year that has classes.'),
            ]);
        }

        $label = $year->name;
        $year->delete();

        $this->logMutation(
            $this->activityLogger,
            $user,
            __('SMIS Agent deleted academic year :name.', ['name' => $label]),
        );

        return ['ok' => true, 'deleted' => true, 'name' => $label];
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     */
    private function setCurrent(User $user, array $arguments): array
    {
        $name = $this->stringArg($arguments, 'name');

        if ($name === null) {
            return ['ok' => false, 'error' => 'name is required.'];
        }

        $year = $this->scope->resolveAcademicYear($name);
        Gate::forUser($user)->authorize('update', $year);
        $this->setCurrentAcademicYear->handle($year);

        $this->logMutation(
            $this->activityLogger,
            $user,
            __('SMIS Agent set current academic year to :name.', ['name' => $year->name]),
            $year,
        );

        return ['ok' => true, 'academic_year' => $this->payload($year->refresh())];
    }

    /**
     * @return array{id: int, name: string, starts_on: string|null, ends_on: string|null, is_current: bool}
     */
    private function payload(AcademicYear $year): array
    {
        return [
            'id' => $year->id,
            'name' => $year->name,
            'starts_on' => $this->dateString($year->getRawOriginal('starts_on')),
            'ends_on' => $this->dateString($year->getRawOriginal('ends_on')),
            'is_current' => $year->is_current,
        ];
    }
}
