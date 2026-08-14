<?php

namespace App\Contracts;

use App\Services\Agent\AgentLlmEvent;

interface AgentLlm
{
    /**
     * Stream one model turn (text deltas and/or function calls).
     *
     * @param  list<array<string, mixed>>  $contents
     * @param  list<array<string, mixed>>  $tools
     * @return iterable<AgentLlmEvent>
     */
    public function streamTurn(array $contents, array $tools, string $systemInstruction): iterable;

    public function isConfigured(): bool;
}
