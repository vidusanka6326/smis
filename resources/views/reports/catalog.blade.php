@props([
    'title',
    'description',
    'reports',
])

<x-layouts::app :title="$title">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div>
            <flux:heading size="xl">{{ $title }}</flux:heading>
            <flux:text class="mt-1">{{ $description }}</flux:text>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($reports as $report)
                <a
                    href="{{ route($report['route']) }}"
                    wire:navigate
                    class="group flex flex-col gap-3 rounded-xl border border-border bg-card p-5 shadow-sm transition hover:border-primary hover:shadow-md"
                >
                    <div class="flex size-10 items-center justify-center rounded-lg bg-primary/10 text-primary">
                        <flux:icon :icon="$report['icon']" variant="outline" class="size-5" />
                    </div>
                    <div class="flex flex-1 flex-col gap-1">
                        <flux:heading size="sm">{{ $report['title'] }}</flux:heading>
                        <flux:text class="text-sm">{{ $report['description'] }}</flux:text>
                    </div>
                    <span class="text-sm font-medium text-primary group-hover:underline">{{ __('Open') }}</span>
                </a>
            @endforeach
        </div>
    </div>
</x-layouts::app>
