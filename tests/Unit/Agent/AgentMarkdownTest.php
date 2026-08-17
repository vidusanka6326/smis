<?php

use App\Enums\AgentMessageRole;
use App\Models\AgentMessage;
use App\Services\Agent\AgentMarkdown;
use Tests\TestCase;

uses(TestCase::class);

test('markdown renderer allows headings and strips scripts', function () {
    $html = app(AgentMarkdown::class)->render("# Title\n\n<script>alert(1)</script>\n\nHello **world**");

    expect($html)->toContain('<h1>')
        ->and($html)->toContain('<strong>world</strong>')
        ->and($html)->not->toContain('<script>');
});

test('quota replies are warning service notices', function () {
    $message = new AgentMessage([
        'role' => AgentMessageRole::Assistant,
        'content' => 'Gemini credits or quota are exhausted. Add billing in Google AI Studio and retry.',
    ]);

    expect($message->isServiceNotice())->toBeTrue()
        ->and($message->serviceNoticeVariant())->toBe('warning');
});

test('user messages are not service notices', function () {
    $message = new AgentMessage([
        'role' => AgentMessageRole::User,
        'content' => 'Gemini credits or quota are exhausted. Add billing in Google AI Studio and retry.',
    ]);

    expect($message->isServiceNotice())->toBeFalse();
});
