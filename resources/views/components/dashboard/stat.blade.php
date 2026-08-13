@props([
    'label',
    'value',
    'hint' => null,
    'tone' => 'default',
])

@php
    $valueClass = match ($tone) {
        'warning' => 'text-amber-600 dark:text-amber-400',
        'danger' => 'text-destructive',
        'success' => 'text-emerald-700 dark:text-emerald-400',
        default => 'text-foreground',
    };
@endphp

<div {{ $attributes->class('rounded-xl border border-border bg-card p-4 shadow-sm') }}>
    <flux:text class="text-muted-foreground">{{ $label }}</flux:text>
    <p class="mt-2 text-3xl font-semibold tracking-tight {{ $valueClass }}">{{ $value }}</p>
    @if ($hint)
        <flux:text class="mt-1 text-xs text-muted-foreground">{{ $hint }}</flux:text>
    @endif
</div>
