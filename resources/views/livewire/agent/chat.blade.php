<div class="flex min-h-[calc(100dvh-8rem)] overflow-hidden rounded-xl border border-border bg-card">
    <aside class="hidden w-64 shrink-0 flex-col border-e border-border bg-muted/40 md:flex">
        <div class="flex items-center justify-between gap-2 border-b border-border p-3">
            <flux:heading size="sm">{{ __('Chats') }}</flux:heading>
            <flux:button size="sm" variant="ghost" icon="plus" wire:click="newChat" wire:loading.attr="disabled">
                {{ __('New') }}
            </flux:button>
        </div>
        <nav class="flex-1 space-y-1 overflow-y-auto p-2">
            @forelse ($this->conversationList as $conversation)
                <div wire:key="conversation-{{ $conversation->id }}" class="group flex items-center gap-1">
                    <button
                        type="button"
                        wire:click="open({{ $conversation->id }})"
                        class="min-w-0 flex-1 rounded-lg px-3 py-2 text-start text-sm {{ $conversationId === $conversation->id ? 'bg-accent text-accent-foreground' : 'text-foreground hover:bg-accent/60' }}"
                    >
                        <span class="block truncate">{{ $conversation->title ?: __('New chat') }}</span>
                    </button>
                    <flux:button
                        size="sm"
                        variant="ghost"
                        icon="trash"
                        class="opacity-0 group-hover:opacity-100"
                        wire:click="deleteConversation({{ $conversation->id }})"
                    />
                </div>
            @empty
                <flux:text class="px-3 py-6 text-center text-sm">{{ __('No chats yet.') }}</flux:text>
            @endforelse
        </nav>
    </aside>

    <div class="flex min-w-0 flex-1 flex-col">
        <header class="flex items-center justify-between gap-3 border-b border-border px-4 py-3">
            <div class="flex items-center gap-3">
                <span class="flex size-10 items-center justify-center rounded-full bg-primary/10 text-primary">
                    <flux:icon.bot class="size-5" />
                </span>
                <div>
                    <flux:heading size="sm">{{ __('SMIS Agent') }}</flux:heading>
                    <flux:text class="text-xs">{{ __('Ask about timetables, attendance, exams, and assignments your role allows.') }}</flux:text>
                </div>
            </div>
            <flux:button class="md:hidden" size="sm" variant="ghost" icon="plus" wire:click="newChat">
                {{ __('New chat') }}
            </flux:button>
        </header>

        <div class="flex-1 space-y-6 overflow-y-auto px-4 py-6 sm:px-8" id="agent-thread">
            @if ($this->thread->isEmpty() && ! $isStreaming)
                <div class="mx-auto flex max-w-2xl flex-col items-center gap-6 py-12 text-center">
                    <span class="flex size-16 items-center justify-center rounded-full bg-primary/10 text-primary">
                        <flux:icon.bot class="size-8" />
                    </span>
                    <div class="space-y-2">
                        <flux:heading>{{ __('How can I help?') }}</flux:heading>
                        <flux:text>{{ __('I can look up school data and take actions your account is allowed to perform.') }}</flux:text>
                    </div>
                    <div class="flex w-full flex-wrap justify-center gap-2">
                        @foreach ($this->suggestions as $suggestion)
                            <flux:button
                                wire:key="suggestion-{{ $suggestion['label'] }}"
                                variant="ghost"
                                class="border border-border"
                                wire:click="useSuggestion(@js($suggestion['message']))"
                                wire:loading.attr="disabled"
                            >
                                {{ $suggestion['label'] }}
                            </flux:button>
                        @endforeach
                    </div>
                </div>
            @endif

            @foreach ($this->thread as $message)
                <div wire:key="message-{{ $message->id }}" class="mx-auto flex w-full max-w-3xl gap-3 {{ $message->role->value === 'user' ? 'justify-end' : 'justify-start' }}">
                    @if ($message->role->value === 'assistant')
                        <span class="mt-1 flex size-8 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary">
                            <flux:icon.bot class="size-4" />
                        </span>
                    @endif

                    <div class="max-w-[85%] space-y-3">
                        <div @class([
                            'rounded-2xl px-4 py-3 text-sm',
                            'bg-primary text-primary-foreground' => $message->role->value === 'user',
                            'bg-muted text-foreground' => $message->role->value === 'assistant',
                        ])>
                            @if ($message->role->value === 'assistant')
                                <x-agent.markdown :content="$message->content" />
                            @else
                                <p class="whitespace-pre-wrap">{{ $message->content }}</p>
                            @endif
                        </div>

                        @if ($message->role->value === 'assistant' && is_array($message->choices) && $message->choices !== [] && $loop->last && ! $isStreaming)
                            <div class="flex flex-wrap gap-2">
                                @foreach ($message->choices as $choice)
                                    <flux:button
                                        wire:key="choice-{{ $message->id }}-{{ $choice['id'] }}"
                                        size="sm"
                                        variant="ghost"
                                        class="border border-border"
                                        wire:click="choose(@js($choice['message']))"
                                        wire:loading.attr="disabled"
                                    >
                                        {{ $choice['label'] }}
                                    </flux:button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach

            @if ($isStreaming)
                <div class="mx-auto flex w-full max-w-3xl gap-3">
                    <span class="mt-1 flex size-8 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary">
                        <flux:icon.bot class="size-4" />
                    </span>
                    <div class="max-w-[85%] space-y-2">
                        <p wire:stream="agent-status" class="text-xs text-muted-foreground">{{ $status }}</p>
                        <div class="rounded-2xl bg-muted px-4 py-3 text-sm text-foreground">
                            <div wire:stream="assistant-stream" class="agent-markdown">
                                {!! $streamingHtml !!}
                            </div>
                            <span class="mt-1 inline-block h-4 w-1.5 animate-pulse bg-primary align-middle"></span>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <form
            wire:submit="send"
            class="border-t border-border p-4"
            x-data
            x-on:keydown.enter="
                if (!$event.shiftKey && $event.target.tagName === 'TEXTAREA') {
                    $event.preventDefault();
                    $wire.send();
                }
            "
        >
            <div class="mx-auto flex max-w-3xl items-end gap-2 rounded-2xl border border-border bg-background p-2 shadow-sm">
                <flux:textarea
                    wire:model="draft"
                    rows="1"
                    :placeholder="__('Message SMIS Agent…')"
                    class="min-h-11 flex-1 border-0 bg-transparent shadow-none focus:ring-0"
                    :disabled="$isStreaming"
                />
                <flux:button
                    type="submit"
                    variant="primary"
                    icon="paper-airplane"
                    :disabled="$isStreaming"
                    wire:loading.attr="disabled"
                >
                    {{ __('Send') }}
                </flux:button>
            </div>
            <flux:error name="draft" class="mx-auto mt-2 max-w-3xl" />
            <p class="mx-auto mt-2 max-w-3xl text-center text-xs text-muted-foreground">
                {{ __('Actions follow your role and permissions. Shift+Enter for a new line.') }}
            </p>
        </form>
    </div>
</div>
