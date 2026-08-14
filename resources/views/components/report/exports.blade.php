@props(['query' => []])

@php
    $routeName = request()->route()?->getName();
    $params = collect($query)->filter(fn (mixed $value): bool => filled($value))->all();
@endphp

@if ($routeName)
    <div {{ $attributes->class('flex flex-wrap gap-2') }}>
        <flux:button :href="route($routeName, [...$params, 'export' => 'csv'])" icon="arrow-down-tray" variant="filled">
            {{ __('Download CSV') }}
        </flux:button>
        <flux:button :href="route($routeName, [...$params, 'export' => 'pdf'])" icon="document-text" variant="primary">
            {{ __('Download PDF') }}
        </flux:button>
    </div>
@endif
