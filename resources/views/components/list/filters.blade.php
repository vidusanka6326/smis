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

<form method="GET" action="{{ $action }}" {{ $attributes->class('rounded-xl border border-border bg-card p-3') }}>
    <div class="flex flex-wrap items-end gap-3">
        <div class="flex min-w-0 flex-1 flex-wrap items-end gap-3 [&>*]:min-w-36 [&>*]:flex-1">
            {{ $slot }}
        </div>

        <div class="flex shrink-0 flex-wrap items-end gap-3">
            @if ($withPerPage)
                <div class="w-28 shrink-0">
                    <flux:select name="per_page" :label="__('Per page')">
                        @foreach (\App\Support\ListQuery::PER_PAGE_OPTIONS as $option)
                            <flux:select.option :value="$option" :selected="(int) request('per_page', \App\Support\ListQuery::DEFAULT_PER_PAGE) === $option">
                                {{ $option }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
            @endif

            <div class="flex h-10 items-center gap-2">
                <flux:button type="submit" variant="primary">{{ $submit }}</flux:button>

                @if ($activeCount > 0)
                    <flux:button :href="$clearUrl" variant="ghost" wire:navigate>{{ __('Clear') }}</flux:button>
                @endif

                {{ $actions ?? '' }}
            </div>
        </div>
    </div>
</form>
