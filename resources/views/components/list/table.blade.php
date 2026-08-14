@props([
    'empty' => null,
    'colspan' => 1,
])

<div {{ $attributes->class('overflow-x-auto rounded-xl border border-border bg-card') }}>
    <table class="min-w-full text-sm">
        @isset($head)
            <thead class="bg-muted/50 text-left">
                <tr>
                    {{ $head }}
                </tr>
            </thead>
        @endisset
        <tbody>
            {{ $slot }}
            @if ($empty !== null)
                <tr class="border-t border-border">
                    <td colspan="{{ $colspan }}" class="px-4 py-10 text-center text-muted-foreground">{{ $empty }}</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>
