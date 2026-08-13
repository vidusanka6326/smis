@props([
    'title',
    'description' => null,
])

<section {{ $attributes->class('rounded-xl border border-border bg-card p-5 shadow-sm sm:p-6') }}>
    <div class="mb-5 border-b border-border pb-4">
        <flux:heading size="sm">{{ $title }}</flux:heading>
        @if (filled($description))
            <flux:text class="mt-1 text-sm">{{ $description }}</flux:text>
        @endif
    </div>

    {{ $slot }}
</section>
