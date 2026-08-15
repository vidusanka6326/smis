<?php

namespace App\Services\Agent;

use App\Contracts\AgentLlm;

class PreferConfiguredAgentLlm implements AgentLlm
{
    /**
     * @param  list<AgentLlm>  $providers
     */
    public function __construct(private array $providers) {}

    public function isConfigured(): bool
    {
        return $this->active() !== null;
    }

    public function streamTurn(array $contents, array $tools, string $systemInstruction): iterable
    {
        $active = $this->active();

        if ($active === null) {
            throw new AgentLlmException(__('SMIS Agent is not configured. Add OPENROUTER_API_KEY or GEMINI_API_KEY and retry.'));
        }

        yield from $active->streamTurn($contents, $tools, $systemInstruction);
    }

    private function active(): ?AgentLlm
    {
        foreach ($this->providers as $provider) {
            if ($provider->isConfigured()) {
                return $provider;
            }
        }

        return null;
    }
}
