@props([
    'label',
    'value',
    'hint' => null,
])

<div {{ $attributes->class('rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900') }}>
    <flux:text class="text-zinc-500">{{ $label }}</flux:text>
    <p class="mt-2 text-3xl font-semibold tracking-tight text-zinc-900 dark:text-zinc-50">{{ $value }}</p>
    @if ($hint)
        <flux:text class="mt-1 text-xs">{{ $hint }}</flux:text>
    @endif
</div>
