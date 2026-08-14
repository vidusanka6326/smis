<?php

use App\Services\Agent\AgentMarkdown;
use App\Services\Agent\GeminiSseDecoder;
use Tests\TestCase;

uses(TestCase::class);

test('markdown renderer allows headings and strips scripts', function () {
    $html = app(AgentMarkdown::class)->render("# Title\n\n<script>alert(1)</script>\n\nHello **world**");

    expect($html)->toContain('<h1>')
        ->and($html)->toContain('<strong>world</strong>')
        ->and($html)->not->toContain('<script>');
});

test('sse decoder reads text and function calls', function () {
    $event = app(GeminiSseDecoder::class)->eventFromPayload([
        'candidates' => [[
            'content' => [
                'parts' => [
                    ['text' => 'Hello '],
                    ['functionCall' => ['name' => 'find_free_periods', 'args' => ['class_code' => '10-A']]],
                ],
            ],
            'finishReason' => 'STOP',
        ]],
    ]);

    expect($event->textDelta)->toBe('Hello ')
        ->and($event->functionCalls[0]['name'])->toBe('find_free_periods')
        ->and($event->complete)->toBeTrue();
});
