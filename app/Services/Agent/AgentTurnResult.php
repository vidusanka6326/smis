<?php

namespace App\Services\Agent;

final readonly class AgentTurnResult
{
    /**
     * @param  list<array{id: string, label: string, message: string}>  $choices
     * @param  list<array{name: string, ok: bool}>  $toolTrace
     */
    public function __construct(
        public string $markdown,
        public array $choices = [],
        public array $toolTrace = [],
    ) {}
}
