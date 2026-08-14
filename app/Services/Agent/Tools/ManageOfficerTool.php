<?php

namespace App\Services\Agent\Tools;

use App\Actions\Officers\CreateOfficer;
use App\Actions\Officers\UpdateOfficer;
use App\Enums\RoleName;
use App\Enums\UserStatus;
use App\Models\User;
use App\Services\Agent\AgentScope;
use App\Services\Audit\ActivityLogger;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ManageOfficerTool extends AbstractAgentTool
{
    /**
     * @var list<string>
     */
    private const ACTIONS = ['list', 'create', 'update', 'delete'];

    public function __construct(
        private AgentScope $scope,
        private CreateOfficer $createOfficer,
        private UpdateOfficer $updateOfficer,
        private ActivityLogger $activityLogger,
    ) {}

    public function name(): string
    {
        return 'manage_officer';
    }

    public function description(): string
    {
        return 'List, create, update, or delete officer accounts. Admin only.';
    }

    public function parameters(): array
    {
        return $this->objectSchema([
            'action' => $this->stringParam('list, create, update, or delete.'),
            'search' => $this->stringParam('Existing officer name or email (update/delete).'),
            'name' => $this->stringParam('Full name.'),
            'email' => $this->stringParam('Login email.'),
            'password' => $this->stringParam('Required on create.'),
            'status' => $this->stringParam('active or inactive.'),
        ], ['action']);
    }

    public function authorized(User $user): bool
    {
        return $user->can('manageOfficers', User::class);
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
        Gate::forUser($user)->authorize('manageOfficers', User::class);

        $officers = [];

        foreach (User::query()->role(RoleName::Officer)->orderBy('name')->limit(40)->get() as $officer) {
            $officers[] = $this->payload($officer);
        }

        return ['ok' => true, 'officers' => $officers];
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     */
    private function create(User $user, array $arguments): array
    {
        Gate::forUser($user)->authorize('manageOfficers', User::class);

        $data = Validator::make([
            'name' => $this->stringArg($arguments, 'name'),
            'email' => $this->stringArg($arguments, 'email'),
            'password' => $this->stringArg($arguments, 'password'),
            'status' => $this->stringArg($arguments, 'status') ?? UserStatus::Active->value,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', Password::default()],
            'status' => ['required', 'string', Rule::enum(UserStatus::class)],
        ])->validate();

        $officer = $this->createOfficer->handle([
            'name' => (string) $data['name'],
            'email' => (string) $data['email'],
            'password' => (string) $data['password'],
            'status' => (string) $data['status'],
        ]);

        $this->logMutation(
            $this->activityLogger,
            $user,
            __('SMIS Agent created officer :name.', ['name' => $officer->name]),
            $officer,
        );

        return ['ok' => true, 'officer' => $this->payload($officer)];
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     */
    private function update(User $user, array $arguments): array
    {
        $search = $this->stringArg($arguments, 'search') ?? $this->stringArg($arguments, 'email');

        if ($search === null) {
            return ['ok' => false, 'error' => 'search is required.'];
        }

        $officer = $this->scope->resolveOfficer($search);
        Gate::forUser($user)->authorize('updateOfficer', $officer);

        $data = Validator::make([
            'name' => $this->stringArg($arguments, 'name') ?? $officer->name,
            'email' => $this->stringArg($arguments, 'email') ?? $officer->email,
            'password' => $this->stringArg($arguments, 'password'),
            'status' => $this->stringArg($arguments, 'status') ?? $officer->status->value,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($officer->id)],
            'password' => ['nullable', 'string', Password::default()],
            'status' => ['required', 'string', Rule::enum(UserStatus::class)],
        ])->validate();

        $officer = $this->updateOfficer->handle($officer, [
            'name' => (string) $data['name'],
            'email' => (string) $data['email'],
            'password' => isset($data['password']) ? (string) $data['password'] : null,
            'status' => (string) $data['status'],
        ]);

        $this->logMutation(
            $this->activityLogger,
            $user,
            __('SMIS Agent updated officer :name.', ['name' => $officer->name]),
            $officer,
        );

        return ['ok' => true, 'officer' => $this->payload($officer)];
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     */
    private function delete(User $user, array $arguments): array
    {
        $search = $this->stringArg($arguments, 'search') ?? $this->stringArg($arguments, 'email');

        if ($search === null) {
            return ['ok' => false, 'error' => 'search is required.'];
        }

        $officer = $this->scope->resolveOfficer($search);
        Gate::forUser($user)->authorize('deleteOfficer', $officer);

        $label = $officer->name;
        $officer->delete();

        $this->logMutation(
            $this->activityLogger,
            $user,
            __('SMIS Agent deleted officer :name.', ['name' => $label]),
        );

        return ['ok' => true, 'deleted' => true, 'name' => $label];
    }

    /**
     * @return array{id: int, name: string, email: string, status: string}
     */
    private function payload(User $officer): array
    {
        return [
            'id' => $officer->id,
            'name' => $officer->name,
            'email' => $officer->email,
            'status' => $officer->status->value,
        ];
    }
}
