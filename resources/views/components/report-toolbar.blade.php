@props(['print' => false])

@if ($print)
    <style>
        @media print {
            nav, aside, header, .no-print { display: none !important; }
            body { background: white !important; }
        }
    </style>
    <script>window.addEventListener('load', () => window.print())</script>
@endif

<div class="no-print mb-4 flex flex-wrap gap-2">
    {{ $slot }}
</div>
