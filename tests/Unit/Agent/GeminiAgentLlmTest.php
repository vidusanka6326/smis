<?php

use App\Services\Agent\GeminiAgentLlm;
use App\Services\Agent\GeminiRequestException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    config([
        'services.gemini.key' => 'test-gemini-key',
        'services.gemini.model' => 'gemini-flash-latest',
        'services.gemini.base_url' => 'https://generativelanguage.googleapis.com/v1beta',
        'services.gemini.timeout' => 5,
        'services.gemini.connect_timeout' => 2,
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
        [['role' => 'user', 'parts' => [['text' => 'Hi']]]],
        [],
        'You are SMIS Agent.',
    ));

    expect($events[0]->textDelta)->toBe('Hello from Gemini.')
        ->and($events[1]->complete)->toBeTrue()
        ->and($events[1]->functionCalls)->toBe([]);

    Http::assertSent(function ($request): bool {
        return $request->url() === 'https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent'
            && $request->hasHeader('x-goog-api-key', 'test-gemini-key')
            && $request['contents'][0]['parts'][0]['text'] === 'Hi';
    });
});

test('generateContent yields function calls', function () {
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
        [['role' => 'user', 'parts' => [['text' => 'Free periods']]]],
        [['name' => 'find_free_periods', 'description' => 'Find empty periods', 'parameters' => ['type' => 'object']]],
        'You are SMIS Agent.',
    ));

    expect($events[0]->functionCalls[0]['name'])->toBe('find_free_periods')
        ->and($events[0]->functionCalls[0]['args']['class_code'])->toBe('10-A')
        ->and($events[0]->complete)->toBeTrue();
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
        [['role' => 'user', 'parts' => [['text' => 'Hi']]]],
        [],
        'You are SMIS Agent.',
    )))->toThrow(GeminiRequestException::class, 'gemini-flash-latest');
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
        [['role' => 'user', 'parts' => [['text' => 'Hi']]]],
        [],
        'You are SMIS Agent.',
    )))->toThrow(GeminiRequestException::class, 'credits or quota');
});
