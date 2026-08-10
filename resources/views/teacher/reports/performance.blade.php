<x-layouts::app :title="__('Performance')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <flux:heading size="xl">{{ __('Best & poor performers') }}</flux:heading>

        <form method="GET" class="no-print flex flex-wrap items-end gap-3">
            <flux:select name="exam_id" :label="__('Exam')">
                @foreach ($exams as $option)
                    <flux:select.option :value="$option->id" :selected="(string) $selectedExamId === (string) $option->id">{{ $option->name }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:button type="submit" variant="filled">{{ __('Load') }}</flux:button>
        </form>

        <x-report-toolbar :print="$print">
            <flux:button :href="route('teacher.reports.performance', ['exam_id' => $selectedExamId, 'export' => 'csv'])" variant="filled">{{ __('CSV') }}</flux:button>
            <flux:button :href="route('teacher.reports.performance', ['exam_id' => $selectedExamId, 'print' => 1])" variant="filled">{{ __('Print / PDF') }}</flux:button>
        </x-report-toolbar>

        <div class="grid gap-4 lg:grid-cols-2">
            <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                <flux:heading size="sm">{{ __('Best') }}</flux:heading>
                <ul class="mt-3 space-y-1 text-sm">
                    @forelse ($ranks['best'] as $row)
                        <li>#{{ $row['rank'] }} {{ $row['name'] }} — {{ $row['percentage'] }}%</li>
                    @empty
                        <li class="text-zinc-500">{{ __('No data.') }}</li>
                    @endforelse
                </ul>
            </div>
            <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                <flux:heading size="sm">{{ __('Poor') }}</flux:heading>
                <ul class="mt-3 space-y-1 text-sm">
                    @forelse ($ranks['poor'] as $row)
                        <li>#{{ $row['rank'] }} {{ $row['name'] }} — {{ $row['percentage'] }}%</li>
                    @empty
                        <li class="text-zinc-500">{{ __('No data.') }}</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</x-layouts::app>
