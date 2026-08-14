<?php

namespace Tests\Support;

use App\Contracts\AgentLlm;
use App\Services\Agent\AgentLlmEvent;

class ScriptedAgentLlm implements AgentLlm
{
    /**
     * @param  list<list<AgentLlmEvent>>  $turns
     */
    public function __construct(private array $turns, private bool $configured = true) {}

    public function isConfigured(): bool
    {
        return $this->configured;
    }

    public function streamTurn(array $contents, array $tools, string $systemInstruction): iterable
    {
        $turn = array_shift($this->turns);

        if ($turn === null) {
            yield new AgentLlmEvent(textDelta: 'Done.', complete: true);

            return;
        }

        foreach ($turn as $event) {
            yield $event;
        }
    }
}
