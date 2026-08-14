<x-layouts::app.sidebar :title="$title ?? null">
    <flux:main class="!overflow-hidden !p-0">
        {{ $slot }}
    </flux:main>
</x-layouts::app.sidebar>
