@props(['paginator'])

@if ($paginator instanceof \Illuminate\Contracts\Pagination\Paginator)
    <div {{ $attributes->class('pt-1') }}>
        {{ $paginator->links() }}
    </div>
@endif
