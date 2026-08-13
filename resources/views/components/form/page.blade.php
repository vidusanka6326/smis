@props([
    'title',
    'description' => null,
    'wide' => false,
])

<div {{ $attributes->class([
    'mx-auto flex w-full flex-col gap-6',
    'max-w-4xl' => (bool) $wide,
    'max-w-3xl' => ! (bool) $wide,
]) }}>
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <flux:heading size="xl">{{ $title }}</flux:heading>
            @if (filled($description))
                <flux:text class="mt-1">{{ $description }}</flux:text>
            @endif
        </div>
        @isset($aside)
            <div class="flex flex-wrap gap-2">
                {{ $aside }}
            </div>
        @endisset
    </div>

    {{ $slot }}
</div>
