<?php

use App\Contracts\AgentLlm;
use App\Livewire\Agent\Chat;
use App\Models\AgentConversation;
use App\Models\AgentMessage;
use App\Models\User;
use App\Services\Agent\AgentLlmEvent;
use App\Services\Agent\AgentLlmException;
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

test('unconfigured llm explains how to enable the agent', function () {
    $this->app->instance(AgentLlm::class, new ScriptedAgentLlm([], configured: false));

    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(Chat::class)
        ->set('draft', 'Hello')
        ->call('send')
        ->assertSee('GEMINI_API_KEY')
        ->assertDontSee('OPENROUTER_API_KEY');
});

test('empty chat shows suggested prompts', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(Chat::class)
        ->assertSee('How can I help?')
        ->assertSee('Free periods in 10-A')
        ->assertSee('Free teachers')
        ->assertSee('Thinking…')
        ->assertSeeHtml('wire:stream="agent-status"')
        ->assertSeeHtml('wire:stream="assistant-stream"');
});

test('chat history lists conversation titles', function () {
    $admin = User::factory()->admin()->create();
    AgentConversation::factory()->create([
        'user_id' => $admin->id,
        'title' => 'Show teachers who are free on those 10-A timeslots.',
    ]);

    Livewire::actingAs($admin)
        ->test(Chat::class)
        ->assertSee('Show teachers who are free on those 10-A timeslots.');
});

test('quota errors render as a notice with a billing link', function () {
    $this->app->instance(AgentLlm::class, new class implements AgentLlm
    {
        public function isConfigured(): bool
        {
            return true;
        }

        public function streamTurn(array $contents, array $tools, string $systemInstruction): iterable
        {
            throw new AgentLlmException(
                'Gemini credits or quota are exhausted. Add billing in Google AI Studio and retry.',
            );
        }
    });

    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(Chat::class)
        ->set('draft', 'Hello')
        ->call('send')
        ->assertSee('credits')
        ->assertSee('Open Google AI Studio');
});
