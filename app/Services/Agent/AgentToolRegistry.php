<?php

namespace App\Services\Agent;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

class AgentToolRegistry
{
    /**
     * @var list<AgentTool>
     */
    private array $tools;

    /**
     * @param  iterable<int, AgentTool>  $tools
     */
    public function __construct(iterable $tools)
    {
        $this->tools = [];

        foreach ($tools as $tool) {
            $this->tools[] = $tool;
        }
    }

    /**
     * @return list<AgentTool>
     */
    public function forUser(User $user): array
    {
        return array_values(array_filter(
            $this->tools,
            fn (AgentTool $tool): bool => $tool->authorized($user),
        ));
    }

    /**
     * Gemini functionDeclarations for the signed-in user.
     *
     * @return list<array<string, mixed>>
     */
    public function declarationsFor(User $user): array
    {
        return array_map(fn (AgentTool $tool): array => [
            'name' => $tool->name(),
            'description' => $tool->description(),
            'parameters' => $tool->parameters(),
        ], $this->forUser($user));
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     */
    public function execute(User $user, string $name, array $arguments): array
    {
        $tool = collect($this->forUser($user))->first(
            fn (AgentTool $candidate): bool => $candidate->name() === $name,
        );

        if ($tool === null) {
            return [
                'ok' => false,
                'error' => __('That action is not available for your role.'),
            ];
        }

        try {
            return $tool->handle($user, $arguments);
        } catch (AuthorizationException) {
            return [
                'ok' => false,
                'error' => __('You do not have permission to do that.'),
            ];
        } catch (ValidationException $exception) {
            return [
                'ok' => false,
                'error' => collect($exception->errors())->flatten()->first() ?: $exception->getMessage(),
            ];
        }
    }
}
