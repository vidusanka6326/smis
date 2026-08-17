<?php

use App\Contracts\AgentLlm;
use App\Services\Agent\AgentLlmException;
use App\Services\Agent\GeminiAgentLlm;
use App\Services\Agent\Tools\ListCapabilitiesTool;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    config([
        'services.gemini.key' => 'test-gemini-key',
        'services.gemini.model' => 'gemini-flash-latest',
        'services.gemini.fallbacks' => [],
        'services.gemini.base_url' => 'https://generativelanguage.googleapis.com/v1beta',
        'services.gemini.timeout' => 5,
        'services.gemini.connect_timeout' => 2,
        'services.gemini.retry_delay_ms' => 0,
    ]);
});

test('generateContent yields text from gemini-flash-latest', function () {
    Http::preventStrayRequests();
    Http::fake([
        'https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent' => Http::response([
            'candidates' => [[
                'content' => [
                    'parts' => [['text' => 'Hello from Gemini.']],
                ],
                'finishReason' => 'STOP',
            ]],
        ]),
    ]);

    $events = iterator_to_array(app(GeminiAgentLlm::class)->streamTurn(
        [['role' => 'user', 'content' => 'Hi']],
        [],
        'You are SMIS Agent.',
    ));

    expect($events[0]->textDelta)->toBe('Hello from Gemini.')
        ->and($events[1]->complete)->toBeTrue()
        ->and($events[1]->functionCalls)->toBe([]);

    Http::assertSent(function ($request): bool {
        return $request->url() === 'https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent'
            && $request->hasHeader('x-goog-api-key', 'test-gemini-key')
            && $request['contents'][0]['parts'][0]['text'] === 'Hi'
            && $request['systemInstruction']['parts'][0]['text'] === 'You are SMIS Agent.'
            && $request['generationConfig']['thinkingConfig']['thinkingBudget'] === 0;
    });
});

test('generateContent yields function calls and converts json schema types', function () {
    Http::preventStrayRequests();
    Http::fake([
        'https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent' => Http::response([
            'candidates' => [[
                'content' => [
                    'parts' => [[
                        'functionCall' => [
                            'name' => 'find_free_periods',
                            'args' => ['class_code' => '10-A'],
                        ],
                    ]],
                ],
                'finishReason' => 'STOP',
            ]],
        ]),
    ]);

    $events = iterator_to_array(app(GeminiAgentLlm::class)->streamTurn(
        [['role' => 'user', 'content' => 'Free periods']],
        [['name' => 'find_free_periods', 'description' => 'Find empty periods', 'parameters' => ['type' => 'object', 'properties' => (object) []]]],
        'You are SMIS Agent.',
    ));

    expect($events[0]->functionCalls[0]['name'])->toBe('find_free_periods')
        ->and($events[0]->functionCalls[0]['args']['class_code'])->toBe('10-A')
        ->and($events[0]->complete)->toBeTrue();

    Http::assertSent(function ($request): bool {
        $body = $request->body();

        return str_contains($body, '"type":"OBJECT"')
            && str_contains($body, '"properties":{}');
    });
});

test('converts openai tool results into gemini function responses', function () {
    Http::preventStrayRequests();
    Http::fake([
        'https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent' => Http::response([
            'candidates' => [[
                'content' => [
                    'parts' => [['text' => 'Monday is free.']],
                ],
                'finishReason' => 'STOP',
            ]],
        ]),
    ]);

    iterator_to_array(app(GeminiAgentLlm::class)->streamTurn(
        [
            ['role' => 'user', 'content' => 'Free periods in 10-A'],
            [
                'role' => 'assistant',
                'tool_calls' => [[
                    'id' => 'call_1',
                    'type' => 'function',
                    'function' => [
                        'name' => 'find_free_periods',
                        'arguments' => '{"class_code":"10-A"}',
                    ],
                ]],
            ],
            [
                'role' => 'tool',
                'tool_call_id' => 'call_1',
                'content' => '{"ok":true}',
            ],
        ],
        [],
        'You are SMIS Agent.',
    ));

    Http::assertSent(function ($request): bool {
        return $request['contents'][1]['role'] === 'model'
            && $request['contents'][1]['parts'][0]['functionCall']['name'] === 'find_free_periods'
            && $request['contents'][2]['role'] === 'user'
            && $request['contents'][2]['parts'][0]['functionResponse']['name'] === 'find_free_periods';
    });
});

test('empty properties encode as a json object in the gemini payload', function () {
    Http::preventStrayRequests();
    Http::fake([
        'https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent' => Http::response([
            'candidates' => [[
                'content' => [
                    'parts' => [['text' => 'ok']],
                ],
                'finishReason' => 'STOP',
            ]],
        ]),
    ]);

    iterator_to_array(app(GeminiAgentLlm::class)->streamTurn(
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
            && ! str_contains($body, '"properties":[]');
    });
});

test('unavailable model explains how to switch', function () {
    Http::preventStrayRequests();
    Http::fake([
        'https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent' => Http::response([
            'error' => [
                'code' => 404,
                'message' => 'This model is no longer available to new users.',
                'status' => 'NOT_FOUND',
            ],
        ], 404),
    ]);

    expect(fn () => iterator_to_array(app(GeminiAgentLlm::class)->streamTurn(
        [['role' => 'user', 'content' => 'Hi']],
        [],
        'You are SMIS Agent.',
    )))->toThrow(AgentLlmException::class, 'not available');
});

test('exhausted credits explain billing', function () {
    Http::preventStrayRequests();
    Http::fake([
        'https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent' => Http::response([
            'error' => [
                'code' => 429,
                'message' => 'Your prepayment credits are depleted.',
                'status' => 'RESOURCE_EXHAUSTED',
            ],
        ], 429),
    ]);

    expect(fn () => iterator_to_array(app(GeminiAgentLlm::class)->streamTurn(
        [['role' => 'user', 'content' => 'Hi']],
        [],
        'You are SMIS Agent.',
    )))->toThrow(AgentLlmException::class, 'quota');
});

test('agent llm contract resolves to gemini', function () {
    expect(app(AgentLlm::class))->toBeInstanceOf(GeminiAgentLlm::class);
});

test('empty function call args encode as a json object', function () {
    Http::preventStrayRequests();
    Http::fake([
        'https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent' => Http::response([
            'candidates' => [[
                'content' => [
                    'parts' => [['text' => 'Done.']],
                ],
                'finishReason' => 'STOP',
            ]],
        ]),
    ]);

    iterator_to_array(app(GeminiAgentLlm::class)->streamTurn(
        [
            ['role' => 'user', 'content' => 'What can you do?'],
            [
                'role' => 'assistant',
                'thoughtSignature' => 'sig-1',
                'tool_calls' => [[
                    'id' => 'call_1',
                    'type' => 'function',
                    'thoughtSignature' => 'sig-1',
                    'function' => [
                        'name' => 'list_capabilities',
                        'arguments' => '{}',
                    ],
                ]],
            ],
            [
                'role' => 'tool',
                'tool_call_id' => 'call_1',
                'content' => '{"ok":true}',
            ],
        ],
        [],
        'You are SMIS Agent.',
    ));

    Http::assertSent(function ($request): bool {
        $body = $request->body();

        return str_contains($body, '"args":{}')
            && ! str_contains($body, '"args":[]')
            && str_contains($body, '"thoughtSignature":"sig-1"')
            && str_contains($body, '"response":{"ok":true}');
    });
});

test('busy gemini retries then explains the outage', function () {
    Http::preventStrayRequests();
    Http::fake([
        'https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent' => Http::response([
            'error' => [
                'code' => 503,
                'message' => 'This model is currently experiencing high demand.',
                'status' => 'UNAVAILABLE',
            ],
        ], 503),
    ]);

    expect(fn () => iterator_to_array(app(GeminiAgentLlm::class)->streamTurn(
        [['role' => 'user', 'content' => 'Hi']],
        [],
        'You are SMIS Agent.',
    )))->toThrow(AgentLlmException::class, 'overloaded');

    Http::assertSentCount(2);
});

test('overloaded primary model falls through to a fallback', function () {
    config(['services.gemini.fallbacks' => ['gemini-2.5-flash']]);

    Http::preventStrayRequests();
    Http::fake([
        'https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent' => Http::response([
            'error' => [
                'code' => 503,
                'message' => 'This model is currently experiencing high demand.',
                'status' => 'UNAVAILABLE',
            ],
        ], 503),
        'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent' => Http::response([
            'candidates' => [[
                'content' => [
                    'parts' => [['text' => 'Hello from fallback.']],
                ],
                'finishReason' => 'STOP',
            ]],
        ]),
    ]);

    $events = iterator_to_array(app(GeminiAgentLlm::class)->streamTurn(
        [['role' => 'user', 'content' => 'Hi']],
        [],
        'You are SMIS Agent.',
    ));

    expect($events[0]->textDelta)->toBe('Hello from fallback.');

    Http::assertSentCount(3);
});

test('connection failures explain the timeout', function () {
    Http::preventStrayRequests();
    Http::fake([
        'https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent' => function () {
            throw new ConnectionException('cURL error 28: Operation timed out');
        },
    ]);

    expect(fn () => iterator_to_array(app(GeminiAgentLlm::class)->streamTurn(
        [['role' => 'user', 'content' => 'Hi']],
        [],
        'You are SMIS Agent.',
    )))->toThrow(AgentLlmException::class, 'did not respond in time');
});
