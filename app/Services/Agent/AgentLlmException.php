<?php

namespace App\Services\Agent;

use RuntimeException;

class AgentLlmException extends RuntimeException
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function __construct(
        string $message,
        private readonly array $context = [],
    ) {
        parent::__construct($message);
    }

    /**
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return array_filter(
            $this->context,
            fn (mixed $value): bool => $value !== null,
        );
    }
}
