@props([
    'cols' => 2,
])

@php
    $cols = (int) $cols;
@endphp

<div {{ $attributes->class([
    'grid gap-4',
    'sm:grid-cols-2' => $cols === 2,
    'sm:grid-cols-2 lg:grid-cols-3' => $cols === 3,
]) }}>
    {{ $slot }}
</div>
