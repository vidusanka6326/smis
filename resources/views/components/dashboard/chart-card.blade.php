@props([
    'title',
    'canvasId',
])

<div {{ $attributes->class('rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900') }}>
    <flux:heading size="sm">{{ $title }}</flux:heading>
    <div class="mt-4 relative h-56">
        <canvas id="{{ $canvasId }}"></canvas>
    </div>
</div>
