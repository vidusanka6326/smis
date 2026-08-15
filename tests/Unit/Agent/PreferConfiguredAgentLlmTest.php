<?php

use App\Contracts\AgentLlm;
use App\Services\Agent\AgentLlmEvent;
use App\Services\Agent\AgentLlmException;
use App\Services\Agent\PreferConfiguredAgentLlm;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(TestCase::class);

test('uses the first configured provider', function () {
    $first = new class implements AgentLlm
    {
        public bool $called = false;

        public function isConfigured(): bool
        {
            return true;
        }

        public function streamTurn(array $contents, array $tools, string $systemInstruction): iterable
        {
            $this->called = true;

            yield new AgentLlmEvent(textDelta: 'from-first', complete: true);
        }
    };

    $second = new class implements AgentLlm
    {
        public bool $called = false;

        public function isConfigured(): bool
        {
            return true;
        }

        public function streamTurn(array $contents, array $tools, string $systemInstruction): iterable
        {
            $this->called = true;

            yield new AgentLlmEvent(textDelta: 'from-second', complete: true);
        }
    };

    $events = iterator_to_array((new PreferConfiguredAgentLlm([$first, $second]))->streamTurn(
        [['role' => 'user', 'content' => 'Hi']],
        [],
        'You are SMIS Agent.',
    ));

    expect($events[0]->textDelta)->toBe('from-first')
        ->and($first->called)->toBeTrue()
        ->and($second->called)->toBeFalse();
});

test('skips an unconfigured provider and uses the next', function () {
    $empty = new class implements AgentLlm
    {
        public function isConfigured(): bool
        {
            return false;
        }

        public function streamTurn(array $contents, array $tools, string $systemInstruction): iterable
        {
            throw new AgentLlmException('should not run');
        }
    };

    $ready = new class implements AgentLlm
    {
        public function isConfigured(): bool
        {
            return true;
        }

        public function streamTurn(array $contents, array $tools, string $systemInstruction): iterable
        {
            yield new AgentLlmEvent(textDelta: 'from-gemini', complete: true);
        }
    };

    $events = iterator_to_array((new PreferConfiguredAgentLlm([$empty, $ready]))->streamTurn(
        [['role' => 'user', 'content' => 'Hi']],
        [],
        'You are SMIS Agent.',
    ));

    expect($events[0]->textDelta)->toBe('from-gemini');
});

test('is unconfigured when no provider has a key', function () {
    $empty = new class implements AgentLlm
    {
        public function isConfigured(): bool
        {
            return false;
        }

        public function streamTurn(array $contents, array $tools, string $systemInstruction): iterable
        {
            yield new AgentLlmEvent(complete: true);
        }
    };

    $llm = new PreferConfiguredAgentLlm([$empty]);

    expect($llm->isConfigured())->toBeFalse();
    expect(fn () => iterator_to_array($llm->streamTurn([], [], 'sys')))
        ->toThrow(AgentLlmException::class, 'OPENROUTER_API_KEY');
});

test('container uses the first listed provider that has a key', function () {
    config([
        'app.name' => 'SMIS',
        'app.url' => 'http://localhost:8000',
        'services.agent.providers' => ['openrouter', 'gemini'],
        'services.openrouter.key' => 'or-key',
        'services.openrouter.model' => 'openai/gpt-oss-20b:free',
        'services.openrouter.base_url' => 'https://openrouter.ai/api/v1',
        'services.openrouter.timeout' => 5,
        'services.openrouter.connect_timeout' => 2,
        'services.gemini.key' => 'g-key',
        'services.gemini.model' => 'gemini-flash-latest',
        'services.gemini.base_url' => 'https://generativelanguage.googleapis.com/v1beta',
        'services.gemini.timeout' => 5,
        'services.gemini.connect_timeout' => 2,
    ]);
    app()->forgetInstance(AgentLlm::class);

    Http::preventStrayRequests();
    Http::fake([
        'https://openrouter.ai/api/v1/chat/completions' => Http::response([
            'choices' => [[
                'message' => ['role' => 'assistant', 'content' => 'from-openrouter'],
                'finish_reason' => 'stop',
            ]],
        ]),
    ]);

    $events = iterator_to_array(app(AgentLlm::class)->streamTurn(
        [['role' => 'user', 'content' => 'Hi']],
        [],
        'You are SMIS Agent.',
    ));

    expect($events[0]->textDelta)->toBe('from-openrouter');
});

test('container falls through to gemini when openrouter has no key', function () {
    config([
        'services.agent.providers' => ['openrouter', 'gemini'],
        'services.openrouter.key' => '',
        'services.gemini.key' => 'g-key',
        'services.gemini.model' => 'gemini-flash-latest',
        'services.gemini.base_url' => 'https://generativelanguage.googleapis.com/v1beta',
        'services.gemini.timeout' => 5,
        'services.gemini.connect_timeout' => 2,
    ]);
    app()->forgetInstance(AgentLlm::class);

    Http::preventStrayRequests();
    Http::fake([
        'https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent' => Http::response([
            'candidates' => [[
                'content' => ['parts' => [['text' => 'from-gemini']]],
                'finishReason' => 'STOP',
            ]],
        ]),
    ]);

    $events = iterator_to_array(app(AgentLlm::class)->streamTurn(
        [['role' => 'user', 'content' => 'Hi']],
        [],
        'You are SMIS Agent.',
    ));

    expect($events[0]->textDelta)->toBe('from-gemini');
});
