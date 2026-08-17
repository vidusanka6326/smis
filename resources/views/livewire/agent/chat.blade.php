<div class="agent-shell flex h-[calc(100dvh-3.5rem)] min-h-0 flex-col overflow-hidden bg-background lg:h-dvh">
    <div class="flex min-h-0 flex-1">
        <aside class="hidden w-80 shrink-0 flex-col border-e border-border bg-muted/30 lg:flex">
            <div class="flex items-center justify-between gap-2 border-b border-border px-3 py-2.5">
                <flux:heading size="sm">{{ __('Chats') }}</flux:heading>
                <flux:button size="sm" variant="primary" icon="plus" wire:click="newChat" wire:loading.attr="disabled">
                    {{ __('New') }}
                </flux:button>
            </div>
            <x-agent.conversation-list :conversations="$this->conversationList" :active-id="$conversationId" />
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <header class="flex items-center justify-between gap-3 border-b border-border px-3 py-2.5 sm:px-4">
                <div class="flex min-w-0 items-center gap-3">
                    <flux:button
                        class="lg:hidden"
                        size="sm"
                        variant="ghost"
                        icon="chat-bubble-left-right"
                        wire:click="$toggle('showHistory')"
                    >
                        {{ __('Chats') }}
                    </flux:button>
                    <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary">
                        <flux:icon.bot class="size-4" />
                    </span>
                    <div class="min-w-0">
                        <flux:heading size="sm" class="truncate">{{ $this->currentTitle() }}</flux:heading>
                        <flux:text class="hidden text-xs sm:block">{{ __('Ask about school data your role allows.') }}</flux:text>
                    </div>
                </div>
                <flux:button class="lg:hidden" size="sm" variant="ghost" icon="plus" wire:click="newChat">
                    {{ __('New') }}
                </flux:button>
            </header>

            <div
                class="min-h-0 flex-1 overflow-y-auto"
                x-data="{ pin() { $nextTick(() => { $el.scrollTop = $el.scrollHeight }) } }"
                x-init="pin(); new MutationObserver(() => pin()).observe($el, { childList: true, subtree: true, characterData: true })"
            >
                <div @class([
                    'mx-auto flex w-full max-w-3xl flex-col gap-5 px-4 py-5 sm:px-6',
                    'min-h-full justify-center' => $this->thread->isEmpty() && ! $isStreaming,
                ])>
                    @if ($this->thread->isEmpty() && ! $isStreaming)
                        <div class="flex flex-col items-center gap-5 text-center">
                            <span class="flex size-14 items-center justify-center rounded-full bg-primary/10 text-primary">
                                <flux:icon.bot class="size-7" />
                            </span>
                            <div class="space-y-1">
                                <flux:heading>{{ __('How can I help?') }}</flux:heading>
                                <flux:text>{{ __('Look up school data or take actions your account already allows.') }}</flux:text>
                            </div>
                            <div class="grid w-full gap-2 sm:grid-cols-2">
                                @foreach ($this->suggestions as $suggestion)
                                    <button
                                        type="button"
                                        wire:key="suggestion-{{ $suggestion['label'] }}"
                                        wire:click="useSuggestion(@js($suggestion['message']))"
                                        wire:loading.attr="disabled"
                                        class="flex items-start gap-3 rounded-xl border border-border bg-card px-3 py-3 text-start transition-colors hover:border-primary/40 hover:bg-accent/40"
                                    >
                                        <span class="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
                                            <flux:icon :icon="$suggestion['icon']" class="size-4" />
                                        </span>
                                        <span class="text-sm font-medium text-foreground">{{ $suggestion['label'] }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @foreach ($this->thread as $message)
                        <div wire:key="message-{{ $message->id }}" class="flex w-full gap-3 {{ $message->role->value === 'user' ? 'justify-end' : 'justify-start' }}">
                            @if ($message->role->value === 'assistant' && ! $message->isServiceNotice())
                                <span class="mt-1 flex size-8 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary">
                                    <flux:icon.bot class="size-4" />
                                </span>
                            @endif

                            <div @class([
                                'space-y-2',
                                'max-w-[min(100%,36rem)]' => $message->role->value === 'user',
                                'min-w-0 flex-1' => $message->role->value === 'assistant',
                            ])>
                                @if ($message->isServiceNotice())
                                    <flux:callout
                                        :variant="$message->serviceNoticeVariant()"
                                        :icon="$message->serviceNoticeVariant() === 'danger' ? 'x-circle' : 'exclamation-triangle'"
                                    >
                                        <flux:callout.heading>{{ $message->content }}</flux:callout.heading>
                                    </flux:callout>
                                    @if (str_contains($message->content, 'Gemini') || str_contains($message->content, 'Google AI Studio') || str_contains($message->content, 'GEMINI_API_KEY'))
                                        <flux:button href="https://aistudio.google.com/" target="_blank" size="sm" icon="arrow-top-right-on-square">
                                            {{ __('Open Google AI Studio') }}
                                        </flux:button>
                                    @endif
                                @elseif ($message->role->value === 'assistant')
                                    <div class="text-sm text-foreground">
                                        <x-agent.markdown :content="$message->content" />
                                    </div>
                                @else
                                    <div class="rounded-2xl rounded-br-md bg-primary px-4 py-2.5 text-sm text-primary-foreground">
                                        <p class="whitespace-pre-wrap">{{ $message->content }}</p>
                                    </div>
                                @endif

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
                        <div class="flex w-full gap-3">
                            <span class="mt-1 flex size-8 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary">
                                <flux:icon.bot class="size-4" />
                            </span>
                            <div class="min-w-0 flex-1 space-y-2">
                                <p wire:stream="agent-status" class="text-xs text-muted-foreground">{{ $status }}</p>
                                <div class="text-sm text-foreground">
                                    <div wire:stream="assistant-stream" class="agent-markdown">
                                        {!! $streamingHtml !!}
                                    </div>
                                    <span class="mt-1 inline-block h-4 w-1.5 animate-pulse bg-primary align-middle"></span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <form
                wire:submit="send"
                class="border-t border-border bg-background px-3 py-3 sm:px-4"
                x-data
                x-on:keydown.enter="
                    if (!$event.shiftKey && $event.target.tagName === 'TEXTAREA') {
                        $event.preventDefault();
                        $wire.send();
                    }
                "
            >
                <div class="agent-composer mx-auto flex max-w-3xl items-end gap-2 rounded-xl border border-border bg-card px-2 py-1.5 shadow-sm">
                    <flux:textarea
                        wire:model="draft"
                        rows="1"
                        :placeholder="__('Message SMIS Agent…')"
                        class="min-h-10 flex-1 border-0 bg-transparent shadow-none focus:ring-0"
                        :disabled="$isStreaming"
                    />
                    <flux:button
                        type="submit"
                        variant="primary"
                        icon="paper-airplane"
                        :disabled="$isStreaming"
                        wire:loading.attr="disabled"
                        :title="__('Send')"
                    />
                </div>
                <flux:error name="draft" class="mx-auto mt-2 max-w-3xl" />
                <p class="mx-auto mt-1.5 max-w-3xl text-center text-xs text-muted-foreground">
                    {{ __('Actions follow your role. Shift+Enter for a new line.') }}
                </p>
            </form>
        </div>
    </div>

    <flux:modal wire:model="showHistory" class="max-w-sm lg:hidden">
        <div class="mb-3 flex items-center justify-between gap-2">
            <flux:heading size="sm">{{ __('Chats') }}</flux:heading>
            <flux:button size="sm" variant="primary" icon="plus" wire:click="newChat">
                {{ __('New') }}
            </flux:button>
        </div>
        <x-agent.conversation-list class="max-h-[70vh] p-0" :conversations="$this->conversationList" :active-id="$conversationId" />
    </flux:modal>
</div>
