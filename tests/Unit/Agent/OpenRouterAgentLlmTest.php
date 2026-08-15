<?php

use App\Services\Agent\AgentLlmException;
use App\Services\Agent\OpenRouterAgentLlm;
use App\Services\Agent\Tools\ListCapabilitiesTool;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    config([
        'app.name' => 'SMIS',
        'app.url' => 'http://localhost:8000',
        'services.openrouter.key' => 'test-openrouter-key',
        'services.openrouter.model' => 'openai/gpt-oss-20b:free',
        'services.openrouter.base_url' => 'https://openrouter.ai/api/v1',
        'services.openrouter.timeout' => 5,
        'services.openrouter.connect_timeout' => 2,
    ]);
});

test('chat completions yields text from gpt-oss-20b free', function () {
    Http::preventStrayRequests();
    Http::fake([
        'https://openrouter.ai/api/v1/chat/completions' => Http::response([
            'choices' => [[
                'message' => [
                    'role' => 'assistant',
                    'content' => 'Hello from OpenRouter.',
                ],
                'finish_reason' => 'stop',
            ]],
        ]),
    ]);

    $events = iterator_to_array(app(OpenRouterAgentLlm::class)->streamTurn(
        [['role' => 'user', 'content' => 'Hi']],
        [],
        'You are SMIS Agent.',
    ));

    expect($events[0]->textDelta)->toBe('Hello from OpenRouter.')
        ->and($events[1]->complete)->toBeTrue()
        ->and($events[1]->functionCalls)->toBe([]);

    Http::assertSent(function ($request): bool {
        return $request->url() === 'https://openrouter.ai/api/v1/chat/completions'
            && $request->hasHeader('Authorization', 'Bearer test-openrouter-key')
            && $request->hasHeader('HTTP-Referer', 'http://localhost:8000')
            && $request['model'] === 'openai/gpt-oss-20b:free'
            && $request['messages'][0]['role'] === 'system'
            && $request['messages'][1]['content'] === 'Hi';
    });
});

test('chat completions yields function calls', function () {
    Http::preventStrayRequests();
    Http::fake([
        'https://openrouter.ai/api/v1/chat/completions' => Http::response([
            'choices' => [[
                'message' => [
                    'role' => 'assistant',
                    'content' => null,
                    'tool_calls' => [[
                        'id' => 'call_periods',
                        'type' => 'function',
                        'function' => [
                            'name' => 'find_free_periods',
                            'arguments' => '{"class_code":"10-A"}',
                        ],
                    ]],
                ],
                'finish_reason' => 'tool_calls',
            ]],
        ]),
    ]);

    $events = iterator_to_array(app(OpenRouterAgentLlm::class)->streamTurn(
        [['role' => 'user', 'content' => 'Free periods']],
        [['name' => 'find_free_periods', 'description' => 'Find empty periods', 'parameters' => ['type' => 'object', 'properties' => (object) []]]],
        'You are SMIS Agent.',
    ));

    expect($events[0]->functionCalls[0]['name'])->toBe('find_free_periods')
        ->and($events[0]->functionCalls[0]['id'])->toBe('call_periods')
        ->and($events[0]->functionCalls[0]['args']['class_code'])->toBe('10-A')
        ->and($events[0]->complete)->toBeTrue();
});

test('empty properties encode as a json object in the openrouter payload', function () {
    Http::preventStrayRequests();
    Http::fake([
        'https://openrouter.ai/api/v1/chat/completions' => Http::response([
            'choices' => [[
                'message' => [
                    'role' => 'assistant',
                    'content' => 'ok',
                ],
                'finish_reason' => 'stop',
            ]],
        ]),
    ]);

    iterator_to_array(app(OpenRouterAgentLlm::class)->streamTurn(
        [['role' => 'user', 'content' => 'Hi']],
        [[
            'name' => 'list_capabilities',
            'description' => 'List capabilities',
            'parameters' => app(ListCapabilitiesTool::class)->parameters(),
        ]],
        'You are SMIS Agent.',
    ));

    Http::assertSent(function ($request): bool {
        $body = $request->body();

        return str_contains($body, '"properties":{}')
            && ! str_contains($body, '"properties":[]')
            && str_contains($body, '"type":"function"');
    });
});

test('unavailable model explains how to switch', function () {
    Http::preventStrayRequests();
    Http::fake([
        'https://openrouter.ai/api/v1/chat/completions' => Http::response([
            'error' => [
                'message' => 'No endpoints found for openai/gpt-oss-20b:free.',
                'code' => 404,
            ],
        ], 404),
    ]);

    expect(fn () => iterator_to_array(app(OpenRouterAgentLlm::class)->streamTurn(
        [['role' => 'user', 'content' => 'Hi']],
        [],
        'You are SMIS Agent.',
    )))->toThrow(AgentLlmException::class, 'gpt-oss-20b:free');
});

test('invalid tool schema includes openrouter’s message when debug is on', function () {
    config(['app.debug' => true]);

    Http::preventStrayRequests();
    Http::fake([
        'https://openrouter.ai/api/v1/chat/completions' => Http::response([
            'error' => [
                'message' => 'Invalid schema for function list_capabilities.',
                'code' => 400,
            ],
        ], 400),
    ]);

    expect(fn () => iterator_to_array(app(OpenRouterAgentLlm::class)->streamTurn(
        [['role' => 'user', 'content' => 'Hi']],
        [],
        'You are SMIS Agent.',
    )))->toThrow(AgentLlmException::class, 'Invalid schema');
});

test('rate limits explain waiting', function () {
    Http::preventStrayRequests();
    Http::fake([
        'https://openrouter.ai/api/v1/chat/completions' => Http::response([
            'error' => [
                'message' => 'Rate limit exceeded: free-models-per-min.',
                'code' => 429,
            ],
        ], 429),
    ]);

    expect(fn () => iterator_to_array(app(OpenRouterAgentLlm::class)->streamTurn(
        [['role' => 'user', 'content' => 'Hi']],
        [],
        'You are SMIS Agent.',
    )))->toThrow(AgentLlmException::class, 'rate-limited');
});
