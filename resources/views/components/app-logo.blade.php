@props([
    'sidebar' => false,
])

@php
    $name = config('app.name', 'SMIS');
    $subtitle = __('Never miss a class');
@endphp

@if($sidebar)
    <flux:sidebar.brand :name="$name" :subtitle="$subtitle" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center overflow-hidden rounded-lg bg-white shadow-xs ring-1 ring-border">
            <x-app-logo-icon class="size-7" />
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand :name="$name" :subtitle="$subtitle" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center overflow-hidden rounded-lg bg-white shadow-xs ring-1 ring-border">
            <x-app-logo-icon class="size-7" />
        </x-slot>
    </flux:brand>
@endif
