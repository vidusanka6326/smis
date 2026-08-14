@php
    $hasResults = method_exists($paginator, 'total') && $paginator->total() > 0;
@endphp

@if ($hasResults || $paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="@container flex flex-wrap items-center justify-between gap-3">
        @if ($hasResults)
            <p class="text-xs font-medium whitespace-nowrap text-muted-foreground">
                {{ __('Showing') }}
                <span class="text-foreground">{{ $paginator->firstItem() }}</span>
                {{ __('to') }}
                <span class="text-foreground">{{ $paginator->lastItem() }}</span>
                {{ __('of') }}
                <span class="text-foreground">{{ $paginator->total() }}</span>
                {{ __('results') }}
            </p>
        @else
            <div></div>
        @endif

        @if ($paginator->hasPages())
            <div class="flex items-center rounded-lg border border-border bg-card p-px">
                @if ($paginator->onFirstPage())
                    <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}" class="flex size-8 items-center justify-center rounded-md text-muted-foreground/50 sm:size-7">
                        <flux:icon.chevron-left variant="micro" class="rtl:hidden" />
                        <flux:icon.chevron-right variant="micro" class="hidden rtl:inline" />
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" wire:navigate aria-label="{{ __('pagination.previous') }}" class="flex size-8 items-center justify-center rounded-md text-muted-foreground hover:bg-muted hover:text-foreground sm:size-7">
                        <flux:icon.chevron-left variant="micro" class="rtl:hidden" />
                        <flux:icon.chevron-right variant="micro" class="hidden rtl:inline" />
                    </a>
                @endif

                <div class="hidden items-center @[28rem]:flex">
                    @foreach ($elements as $element)
                        @if (is_string($element))
                            <span aria-disabled="true" class="flex size-7 cursor-default items-center justify-center text-xs font-medium text-muted-foreground">{{ $element }}</span>
                        @endif

                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page" class="flex h-7 min-w-7 items-center justify-center rounded-md px-2 text-xs font-medium text-foreground">{{ $page }}</span>
                                @else
                                    <a href="{{ $url }}" wire:navigate aria-label="{{ __('Go to page :page', ['page' => $page]) }}" class="flex h-7 min-w-7 items-center justify-center rounded-md px-2 text-xs font-medium text-muted-foreground hover:bg-muted hover:text-foreground">{{ $page }}</a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach
                </div>

                <span class="px-2 text-xs font-medium text-muted-foreground @[28rem]:hidden">
                    {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}
                </span>

                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" wire:navigate aria-label="{{ __('pagination.next') }}" class="flex size-8 items-center justify-center rounded-md text-muted-foreground hover:bg-muted hover:text-foreground sm:size-7">
                        <flux:icon.chevron-right variant="micro" class="rtl:hidden" />
                        <flux:icon.chevron-left variant="micro" class="hidden rtl:inline" />
                    </a>
                @else
                    <span aria-disabled="true" aria-label="{{ __('pagination.next') }}" class="flex size-8 items-center justify-center rounded-md text-muted-foreground/50 sm:size-7">
                        <flux:icon.chevron-right variant="micro" class="rtl:hidden" />
                        <flux:icon.chevron-left variant="micro" class="hidden rtl:inline" />
                    </span>
                @endif
            </div>
        @endif
    </nav>
@endif
