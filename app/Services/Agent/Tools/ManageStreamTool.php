<?php

namespace App\Services\Agent\Tools;

use App\Enums\PermissionName;
use App\Models\Stream;
use App\Models\User;
use App\Services\Agent\AgentScope;
use App\Services\Audit\ActivityLogger;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ManageStreamTool extends AbstractAgentTool
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
        return 'manage_stream';
    }

    public function description(): string
    {
        return 'List, create, update, or delete A/L streams (Science, Commerce, Arts, Technology). Requires manage-system-config.';
    }

    public function parameters(): array
    {
        return $this->objectSchema([
            'action' => $this->stringParam('list, create, update, or delete.'),
            'name' => $this->stringParam('Stream name such as Science.'),
            'code' => $this->stringParam('Short code such as SCI.'),
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
        Gate::forUser($user)->authorize('viewAny', Stream::class);

        $streams = [];

        foreach (Stream::query()->orderBy('name')->get() as $stream) {
            $streams[] = [
                'id' => $stream->id,
                'name' => $stream->name,
                'code' => $stream->code,
            ];
        }

        return ['ok' => true, 'streams' => $streams];
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     */
    private function create(User $user, array $arguments): array
    {
        Gate::forUser($user)->authorize('create', Stream::class);

        $code = $this->stringArg($arguments, 'code');

        $data = Validator::make([
            'name' => $this->stringArg($arguments, 'name'),
            'code' => $code !== null ? Str::upper($code) : null,
        ], [
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:20', 'alpha_dash', 'unique:streams,code'],
        ])->validate();

        $stream = Stream::query()->create($data);

        $this->logMutation(
            $this->activityLogger,
            $user,
            __('SMIS Agent created stream :name.', ['name' => $stream->name]),
            $stream,
        );

        return ['ok' => true, 'stream' => ['id' => $stream->id, 'name' => $stream->name, 'code' => $stream->code]];
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     */
    private function update(User $user, array $arguments): array
    {
        $lookup = $this->stringArg($arguments, 'name') ?? $this->stringArg($arguments, 'code');

        if ($lookup === null) {
            return ['ok' => false, 'error' => 'name or code is required to identify the stream.'];
        }

        $stream = $this->scope->resolveStream($lookup);
        Gate::forUser($user)->authorize('update', $stream);

        $code = $this->stringArg($arguments, 'code');

        $data = Validator::make([
            'name' => $this->stringArg($arguments, 'name') ?? $stream->name,
            'code' => $code !== null ? Str::upper($code) : $stream->code,
        ], [
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:20', 'alpha_dash', Rule::unique('streams', 'code')->ignore($stream->id)],
        ])->validate();

        $stream->update($data);

        $this->logMutation(
            $this->activityLogger,
            $user,
            __('SMIS Agent updated stream :name.', ['name' => $stream->name]),
            $stream,
        );

        return ['ok' => true, 'stream' => ['id' => $stream->id, 'name' => $stream->name, 'code' => $stream->code]];
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

        $stream = $this->scope->resolveStream($lookup);
        Gate::forUser($user)->authorize('delete', $stream);

        if ($stream->schoolClasses()->exists()) {
            throw ValidationException::withMessages([
                'stream' => __('Cannot delete a stream that is assigned to classes.'),
            ]);
        }

        $label = $stream->name;
        $stream->delete();

        $this->logMutation(
            $this->activityLogger,
            $user,
            __('SMIS Agent deleted stream :name.', ['name' => $label]),
        );

        return ['ok' => true, 'deleted' => true, 'name' => $label];
    }
}
