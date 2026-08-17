@props([
    'variant' => 'menu',
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
    <flux:dropdown {{ $attributes }}>
        <flux:button variant="ghost" size="sm" icon="globe-alt" :aria-label="__('Language')" data-test="language-switcher">
            {{ $current->nativeName() }}
        </flux:button>

        <flux:menu>
            @foreach (\App\Enums\AppLocale::cases() as $locale)
                <form method="POST" action="{{ route('locale.update') }}">
                    @csrf
                    <input type="hidden" name="locale" value="{{ $locale->value }}">
                    <flux:menu.item
                        as="button"
                        type="submit"
                        class="w-full cursor-pointer"
                        :icon="$locale === $current ? 'check' : null"
                    >
                        {{ $locale->nativeName() }}
                    </flux:menu.item>
                </form>
            @endforeach
        </flux:menu>
    </flux:dropdown>
@endif
