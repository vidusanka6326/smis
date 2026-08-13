@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand :name="config('app.name', 'SMIS')" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center overflow-hidden rounded-lg bg-white shadow-xs ring-1 ring-border">
            <x-app-logo-icon class="size-7" />
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand :name="config('app.name', 'SMIS')" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center overflow-hidden rounded-lg bg-white shadow-xs ring-1 ring-border">
            <x-app-logo-icon class="size-7" />
        </x-slot>
    </flux:brand>
@endif
