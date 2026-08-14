<?php

use App\Contracts\AgentLlm;
use App\Livewire\Agent\Chat;
use App\Models\AgentConversation;
use App\Models\AgentMessage;
use App\Models\User;
use App\Services\Agent\AgentLlmEvent;
use Livewire\Livewire;
use Tests\Support\ScriptedAgentLlm;

test('admin can send a chat message and persist the reply', function () {
    $this->app->instance(AgentLlm::class, new ScriptedAgentLlm([
        [new AgentLlmEvent(textDelta: "## Hello\nI can help with **10-A**.", complete: true)],
    ]));

    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(Chat::class)
        ->set('draft', 'Hello')
        ->call('send')
        ->assertHasNoErrors()
        ->assertSee('I can help with');

    expect(AgentConversation::query()->where('user_id', $admin->id)->count())->toBe(1)
        ->and(AgentMessage::query()->count())->toBe(2);
});

test('clicking a choice sends that follow-up', function () {
    $this->app->instance(AgentLlm::class, new ScriptedAgentLlm([
        [
            new AgentLlmEvent(textDelta: 'Here are free periods.', complete: false),
            new AgentLlmEvent(functionCalls: [[
                'name' => 'offer_choices',
                'args' => [
                    'choices' => [[
                        'id' => 'teachers',
                        'label' => 'Show free teachers',
                        'message' => 'Show teachers free on Monday period 1',
                    ]],
                ],
            ]], complete: true),
        ],
        [new AgentLlmEvent(textDelta: 'Pick a next step.', complete: true)],
        [new AgentLlmEvent(textDelta: 'Nimal Perera is free.', complete: true)],
    ]));

    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(Chat::class)
        ->set('draft', 'Free periods in 10-A')
        ->call('send')
        ->assertSee('Show free teachers')
        ->call('choose', 'Show teachers free on Monday period 1')
        ->assertSee('Nimal Perera is free.');
});

test('unconfigured gemini explains how to enable the agent', function () {
    $this->app->instance(AgentLlm::class, new ScriptedAgentLlm([], configured: false));

    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(Chat::class)
        ->set('draft', 'Hello')
        ->call('send')
        ->assertSee('GEMINI_API_KEY');
});
