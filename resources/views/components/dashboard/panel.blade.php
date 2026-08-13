@props([
    'title',
])

<div {{ $attributes->class('rounded-xl border border-border bg-card p-4 shadow-sm') }}>
    <flux:heading size="sm">{{ $title }}</flux:heading>
    <div class="mt-3">
        {{ $slot }}
    </div>
</div>
