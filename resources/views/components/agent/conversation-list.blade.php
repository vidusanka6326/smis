@props([
    'conversations',
    'activeId' => null,
])

<nav {{ $attributes->merge(['class' => 'flex min-h-0 flex-1 flex-col gap-0.5 overflow-y-auto p-2']) }}>
    @forelse ($conversations as $conversation)
        <div wire:key="conversation-{{ $conversation->id }}" class="group flex items-start gap-0.5">
            <button
                type="button"
                wire:click="open({{ $conversation->id }})"
                @class([
                    'min-w-0 flex-1 rounded-lg px-3 py-2 text-start transition-colors',
                    'bg-primary/10 text-primary' => $activeId === $conversation->id,
                    'text-foreground hover:bg-accent/70' => $activeId !== $conversation->id,
                ])
            >
                <span class="block text-sm leading-snug font-medium line-clamp-2">{{ $conversation->title ?: __('New chat') }}</span>
                <span class="mt-0.5 block text-xs text-muted-foreground">{{ $conversation->updated_at?->diffForHumans() }}</span>
            </button>
            <flux:button
                size="sm"
                variant="ghost"
                icon="trash"
                class="mt-1 shrink-0 opacity-100 md:opacity-0 md:group-hover:opacity-100"
                wire:click="deleteConversation({{ $conversation->id }})"
                :title="__('Delete chat')"
            />
        </div>
    @empty
        <flux:text class="px-3 py-8 text-center text-sm">{{ __('No chats yet.') }}</flux:text>
    @endforelse
</nav>
