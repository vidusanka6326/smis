@props([
    'variant' => 'letters',
])

@php
    $current = \App\Enums\AppLocale::current();
@endphp

@if ($variant === 'pills')
    <div {{ $attributes->class('flex items-center gap-1 rounded-md bg-black/20 p-1') }} role="group" aria-label="{{ __('Language') }}">
        @foreach (\App\Enums\AppLocale::cases() as $locale)
            <form method="POST" action="{{ route('locale.update') }}">
                @csrf
                <input type="hidden" name="locale" value="{{ $locale->value }}">
                <button
                    type="submit"
                    class="rounded px-2.5 py-1 font-display text-xs font-semibold transition {{ $locale === $current ? 'bg-white text-[var(--home-ink)]' : 'text-white/80 hover:bg-white/10 hover:text-white' }}"
                    @if ($locale === $current) aria-current="true" @endif
                >
                    {{ $locale->nativeName() }}
                </button>
            </form>
        @endforeach
    </div>
@else
    <div
        {{ $attributes->class('inline-flex overflow-hidden rounded-lg border border-border bg-card') }}
        role="group"
        aria-label="{{ __('Language') }}"
        data-test="language-switcher"
    >
        @foreach (\App\Enums\AppLocale::cases() as $locale)
            <form method="POST" action="{{ route('locale.update') }}" class="min-w-0 flex-1 border-e border-border last:border-e-0">
                @csrf
                <input type="hidden" name="locale" value="{{ $locale->value }}">
                <button
                    type="submit"
                    title="{{ $locale->nativeName() }}"
                    aria-label="{{ $locale->nativeName() }}"
                    @if ($locale === $current) aria-current="true" @endif
                    class="min-h-9 w-full whitespace-nowrap px-2.5 py-1.5 text-center text-[11px] font-semibold tracking-wide {{ $locale === $current ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:bg-muted hover:text-foreground' }}"
                >{{ $locale->shortCode() }}</button>
            </form>
        @endforeach
    </div>
@endif
