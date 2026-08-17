<?php

use App\Contracts\AgentLlm;
use App\Enums\AgentMessageRole;
use App\Enums\DayOfWeek;
use App\Models\AcademicYear;
use App\Models\AgentConversation;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TimetableEntry;
use App\Models\User;
use App\Services\Agent\AgentLlmEvent;
use App\Services\Agent\AgentLlmException;
use App\Services\Agent\AgentOrchestrator;
use Tests\Support\ScriptedAgentLlm;

test('orchestrator calls tools then stores markdown and choices', function () {
    $year = AcademicYear::factory()->current()->create();
    $grade = Grade::factory()->number(10)->create();
    $schoolClass = SchoolClass::factory()->create([
        'academic_year_id' => $year->id,
        'grade_id' => $grade->id,
        'code' => '10-A',
        'name' => 'A',
    ]);
    $subject = Subject::factory()->forGradeRange(1, 13)->create();
    $schoolClass->subjects()->sync([$subject->id]);
    TimetableEntry::factory()->create([
        'academic_year_id' => $year->id,
        'school_class_id' => $schoolClass->id,
        'subject_id' => $subject->id,
        'teacher_id' => Teacher::factory(),
        'day_of_week' => DayOfWeek::Monday,
        'period_number' => 1,
    ]);

    $this->app->instance(AgentLlm::class, new ScriptedAgentLlm([
        [new AgentLlmEvent(functionCalls: [[
            'name' => 'find_free_periods',
            'args' => ['class_code' => '10-A'],
        ]], complete: true)],
        [
            new AgentLlmEvent(textDelta: 'Monday period 1 is taken.', complete: false),
            new AgentLlmEvent(functionCalls: [[
                'name' => 'offer_choices',
                'args' => [
                    'choices' => [[
                        'id' => 'teachers',
                        'label' => 'Show free teachers',
                        'message' => 'Show teachers free on Monday period 2',
                    ]],
                ],
            ]], complete: true),
        ],
        [new AgentLlmEvent(textDelta: 'Here are the free periods in **10-A**.', complete: true)],
    ]));

    $admin = User::factory()->admin()->create();
    $conversation = AgentConversation::factory()->create(['user_id' => $admin->id, 'title' => null]);
    $deltas = [];

    $result = app(AgentOrchestrator::class)->run(
        $admin,
        $conversation,
        'What are the free periods in 10-A?',
        function (string $markdown) use (&$deltas): void {
            $deltas[] = $markdown;
        },
        function (string $status): void {},
    );

    expect($result->markdown)->toContain('10-A')
        ->and($result->choices)->not->toBeEmpty()
        ->and($result->toolTrace[0]['name'])->toBe('find_free_periods')
        ->and($deltas)->not->toBeEmpty()
        ->and($conversation->fresh()->title)->not->toBeNull()
        ->and($conversation->messages()->count())->toBe(2);
});

test('orchestrator surfaces llm request errors in the chat', function () {
    $this->app->instance(AgentLlm::class, new class implements AgentLlm
    {
        public function isConfigured(): bool
        {
            return true;
        }

        public function streamTurn(array $contents, array $tools, string $systemInstruction): iterable
        {
            throw new AgentLlmException('Gemini credits or quota are exhausted. Add billing in Google AI Studio and retry.');
        }
    });

    $admin = User::factory()->admin()->create();
    $conversation = AgentConversation::factory()->create(['user_id' => $admin->id]);

    $result = app(AgentOrchestrator::class)->run(
        $admin,
        $conversation,
        'Hello',
        function (string $markdown): void {},
        function (string $status): void {},
    );

    expect($result->markdown)->toContain('credits');
});

test('orchestrator keeps streamed answer text when offer_choices follows', function () {
    $this->app->instance(AgentLlm::class, new ScriptedAgentLlm([
        [
            new AgentLlmEvent(textDelta: "| Subject | Attention |\n| Math | High |", complete: false),
            new AgentLlmEvent(functionCalls: [[
                'name' => 'offer_choices',
                'args' => [
                    'choices' => [[
                        'id' => 'math',
                        'label' => 'View Grade 10 Math Results',
                        'message' => 'Show Grade 10 Math results',
                    ]],
                ],
            ]], complete: true),
        ],
        [new AgentLlmEvent(textDelta: 'I have highlighted the key subjects above.', complete: true)],
    ]));

    $admin = User::factory()->admin()->create();
    $conversation = AgentConversation::factory()->create(['user_id' => $admin->id]);
    $deltas = [];

    $result = app(AgentOrchestrator::class)->run(
        $admin,
        $conversation,
        'Which subjects need more attention?',
        function (string $markdown) use (&$deltas): void {
            $deltas[] = $markdown;
        },
        function (string $status): void {},
    );

    expect($result->markdown)->toContain('Math')
        ->and($result->markdown)->toContain('highlighted the key subjects')
        ->and($result->choices[0]['label'])->toBe('View Grade 10 Math Results')
        ->and($deltas[0])->toContain('Math')
        ->and($conversation->messages()->where('role', AgentMessageRole::Assistant)->value('content'))->toContain('Math');
});
