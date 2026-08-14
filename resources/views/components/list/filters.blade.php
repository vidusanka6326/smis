@props([
    'action',
    'filters' => [],
    'clearUrl' => null,
    'submit' => null,
    'withPerPage' => true,
])

@php
    $submit ??= __('Apply');
    $clearUrl ??= $action;
    $activeCount = collect($filters)->filter(fn (mixed $value): bool => filled($value))->count();
@endphp

<form method="GET" action="{{ $action }}" {{ $attributes->class('rounded-xl border border-border bg-card p-4') }}>
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <flux:heading size="sm">{{ __('Filters') }}</flux:heading>
            <flux:text class="mt-0.5">
                @if ($activeCount > 0)
                    {{ trans_choice(':count filter applied|:count filters applied', $activeCount, ['count' => $activeCount]) }}
                @else
                    {{ __('Narrow the list, then apply.') }}
                @endif
            </flux:text>
        </div>
    </div>

    <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5">
        {{ $slot }}
        @if ($withPerPage)
            <flux:select name="per_page" :label="__('Per page')">
                @foreach (\App\Support\ListQuery::PER_PAGE_OPTIONS as $option)
                    <flux:select.option :value="$option" :selected="(int) request('per_page', \App\Support\ListQuery::DEFAULT_PER_PAGE) === $option">
                        {{ $option }}
                    </flux:select.option>
                @endforeach
            </flux:select>
        @endif
    </div>

    <div class="mt-4 flex flex-wrap items-center gap-2">
        <flux:button type="submit" variant="primary">{{ $submit }}</flux:button>
        @if ($activeCount > 0)
            <flux:button :href="$clearUrl" variant="ghost" wire:navigate>{{ __('Clear') }}</flux:button>
        @endif
        {{ $actions ?? '' }}
    </div>
</form>
