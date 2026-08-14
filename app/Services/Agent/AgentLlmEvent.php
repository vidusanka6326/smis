<?php

namespace App\Services\Agent;

final readonly class AgentLlmEvent
{
    /**
     * @param  list<array{name: string, args: array<string, mixed>}>  $functionCalls
     */
    public function __construct(
        public ?string $textDelta = null,
        public array $functionCalls = [],
        public bool $complete = false,
    ) {}
}
