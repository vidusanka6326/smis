<?php

namespace App\Livewire\Agent;

use App\Models\AgentConversation;
use App\Models\AgentMessage;
use App\Models\User;
use App\Services\Agent\AgentOrchestrator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.agent')]
#[Title('SMIS Agent')]
class Chat extends Component
{
    #[Url(as: 'c')]
    public ?int $conversationId = null;

    public string $draft = '';

    public string $streamingHtml = '';

    public string $status = '';

    public bool $isStreaming = false;

    public bool $showHistory = false;

    public function mount(): void
    {
        Gate::authorize('viewAny', AgentConversation::class);

        if ($this->conversationId !== null) {
            $conversation = AgentConversation::query()->find($this->conversationId);

            if ($conversation === null || Gate::denies('view', $conversation)) {
                $this->conversationId = null;
            }
        }
    }

    public function send(AgentOrchestrator $orchestrator): void
    {
        $this->submit($this->draft, $orchestrator);
    }

    public function choose(string $message, AgentOrchestrator $orchestrator): void
    {
        $this->submit($message, $orchestrator);
    }

    public function useSuggestion(string $message, AgentOrchestrator $orchestrator): void
    {
        $this->submit($message, $orchestrator);
    }

    public function newChat(): void
    {
        $this->conversationId = null;
        $this->showHistory = false;
        $this->resetComposer();
        unset($this->conversationList, $this->thread);
    }

    public function open(int $conversationId): void
    {
        $conversation = AgentConversation::query()->findOrFail($conversationId);
        Gate::authorize('view', $conversation);

        $this->conversationId = $conversation->id;
        $this->showHistory = false;
        $this->resetComposer();
        unset($this->thread);
    }

    public function deleteConversation(int $conversationId): void
    {
        $conversation = AgentConversation::query()->findOrFail($conversationId);
        Gate::authorize('delete', $conversation);
        $conversation->delete();

        if ($this->conversationId === $conversationId) {
            $this->newChat();
        }

        unset($this->conversationList);
    }

    /**
     * @return Collection<int, AgentConversation>
     */
    #[Computed]
    public function conversationList(): Collection
    {
        return AgentConversation::query()
            ->where('user_id', Auth::id())
            ->latest('updated_at')
            ->limit(30)
            ->get();
    }

    /**
     * @return Collection<int, AgentMessage>
     */
    #[Computed]
    public function thread(): Collection
    {
        if ($this->conversationId === null) {
            return collect();
        }

        return AgentMessage::query()
            ->where('agent_conversation_id', $this->conversationId)
            ->orderBy('id')
            ->get();
    }

    /**
     * @return list<array{label: string, message: string, icon: string}>
     */
    #[Computed]
    public function suggestions(): array
    {
        $user = Auth::user();

        if ($user instanceof User && $user->isTeacher()) {
            return [
                ['label' => __('My timetable'), 'message' => __('Show my timetable for this week.'), 'icon' => 'table-cells'],
                ['label' => __('At-risk students'), 'message' => __('Which of my students are below 80% attendance this month?'), 'icon' => 'exclamation-triangle'],
                ['label' => __('Take attendance'), 'message' => __('Help me take today’s class attendance.'), 'icon' => 'clipboard-document-check'],
                ['label' => __('Enter marks'), 'message' => __('Help me enter marks for the latest exam in my classes.'), 'icon' => 'pencil-square'],
            ];
        }

        return [
            ['label' => __('Free periods in 10-A'), 'message' => __('What are the free periods in 10-A?'), 'icon' => 'clock'],
            ['label' => __('Free teachers'), 'message' => __('Show teachers who are free on those 10-A timeslots.'), 'icon' => 'user-group'],
            ['label' => __('At-risk attendance'), 'message' => __('Which students are below 80% attendance this month?'), 'icon' => 'exclamation-triangle'],
            ['label' => __('What can you do?'), 'message' => __('What can you do for me in this school system?'), 'icon' => 'sparkles'],
        ];
    }

    public function currentTitle(): string
    {
        if ($this->conversationId === null) {
            return __('New chat');
        }

        $title = AgentConversation::query()
            ->whereKey($this->conversationId)
            ->value('title');

        return is_string($title) && $title !== '' ? $title : __('New chat');
    }

    private function submit(string $text, AgentOrchestrator $orchestrator): void
    {
        $this->draft = $text;

        $this->validate([
            'draft' => ['required', 'string', 'max:4000'],
        ]);

        /** @var User $user */
        $user = Auth::user();
        Gate::authorize('create', AgentConversation::class);

        $rateKey = 'smis-agent:'.$user->id;

        if (RateLimiter::tooManyAttempts($rateKey, 20)) {
            $this->addError('draft', __('Please wait a moment before sending another message.'));

            return;
        }

        RateLimiter::hit($rateKey, 60);

        $timeout = max(1, (int) config('services.gemini.timeout', 90));

        if ((int) ini_get('max_execution_time') > 0) {
            set_time_limit(max(120, ($timeout + 20) * 3));
        }

        $message = trim($this->draft);
        $conversation = $this->conversationFor($user);
        $this->conversationId = $conversation->id;
        $this->draft = '';
        $this->isStreaming = true;
        $this->streamingHtml = '';
        $this->status = __('Thinking…');
        unset($this->thread, $this->conversationList);
        $this->stream(e($this->status), replace: true, name: 'agent-status');

        try {
            $orchestrator->run(
                $user,
                $conversation->fresh() ?? $conversation,
                $message,
                function (string $markdown) use ($orchestrator): void {
                    $this->streamingHtml = $orchestrator->renderMarkdown($markdown);
                    $this->stream($this->streamingHtml, replace: true, name: 'assistant-stream');
                },
                function (string $label): void {
                    $this->status = $label;
                    $this->stream(e($label), replace: true, name: 'agent-status');
                },
            );
        } finally {
            $this->isStreaming = false;
            $this->streamingHtml = '';
            $this->status = '';
            unset($this->thread, $this->conversationList);
        }
    }

    private function conversationFor(User $user): AgentConversation
    {
        if ($this->conversationId !== null) {
            $conversation = AgentConversation::query()->findOrFail($this->conversationId);
            Gate::authorize('update', $conversation);

            return $conversation;
        }

        return AgentConversation::query()->create([
            'user_id' => $user->id,
            'title' => null,
        ]);
    }

    private function resetComposer(): void
    {
        $this->draft = '';
        $this->streamingHtml = '';
        $this->status = '';
        $this->isStreaming = false;
        $this->resetErrorBag();
    }
}
