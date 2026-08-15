<?php

namespace App\Services\Agent;

use App\Models\User;

interface AgentTool
{
    public function name(): string;

    public function description(): string;

    /**
     * OpenAI-compatible JSON Schema for this tool’s arguments.
     *
     * @return array<string, mixed>
     */
    public function parameters(): array;

    public function authorized(User $user): bool;

    /**
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     */
    public function handle(User $user, array $arguments): array;
}
