@props([
    'title',
    'description' => null,
    'catalogRoute',
])

<div {{ $attributes->class('flex h-full w-full flex-1 flex-col gap-6') }}>
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <flux:button :href="route($catalogRoute)" variant="ghost" icon="arrow-left" size="sm" class="mb-2 -ms-2" wire:navigate>
                {{ __('All reports') }}
            </flux:button>
            <flux:heading size="xl">{{ $title }}</flux:heading>
            @if (filled($description))
                <flux:text class="mt-1">{{ $description }}</flux:text>
            @endif
        </div>
        <div class="flex flex-wrap items-center gap-2">
            {{ $aside ?? '' }}
        </div>
    </div>

    {{ $slot }}
</div>
