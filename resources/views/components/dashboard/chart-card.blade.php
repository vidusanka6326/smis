@props([
    'title',
    'canvasId',
])

<div {{ $attributes->class('rounded-xl border border-border bg-card p-4 shadow-sm') }}>
    <flux:heading size="sm">{{ $title }}</flux:heading>
    <div class="relative mt-4 h-56">
        <canvas id="{{ $canvasId }}"></canvas>
    </div>
</div>
