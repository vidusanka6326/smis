<x-layouts::app :title="__('Performance')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div>
            <flux:heading size="xl">{{ __('Best & poor performers') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Ranked by average percentage for students in your scope.') }}</flux:text>
        </div>

        <x-list.filters :action="route('teacher.reports.performance')" :filters="array_filter(['exam_id' => $selectedExamId])" :submit="__('Load')" :with-per-page="false" class="no-print">
            <flux:select name="exam_id" :label="__('Exam')">
                @foreach ($exams as $option)
                    <flux:select.option :value="$option->id" :selected="(string) $selectedExamId === (string) $option->id">{{ $option->name }}</flux:select.option>
                @endforeach
            </flux:select>
        </x-list.filters>

        <x-report-toolbar :print="$print">
            <flux:button :href="route('teacher.reports.performance', ['exam_id' => $selectedExamId, 'export' => 'csv'])" variant="filled">{{ __('CSV') }}</flux:button>
            <flux:button :href="route('teacher.reports.performance', ['exam_id' => $selectedExamId, 'print' => 1])" variant="filled">{{ __('Print / PDF') }}</flux:button>
        </x-report-toolbar>

        <div class="grid gap-4 lg:grid-cols-2">
            <div class="rounded-xl border border-border bg-card p-4 shadow-sm">
                <flux:heading size="sm">{{ __('Best') }}</flux:heading>
                <ul class="mt-3 space-y-2 text-sm">
                    @forelse ($ranks['best'] as $row)
                        <li class="flex items-baseline justify-between gap-3 border-b border-border/60 pb-2 last:border-0">
                            <span>#{{ $row['rank'] }} {{ $row['name'] }} <span class="text-muted-foreground">({{ $row['class'] }})</span></span>
                            <span class="font-medium text-emerald-700 dark:text-emerald-400">{{ $row['percentage'] }}%</span>
                        </li>
                    @empty
                        <li class="text-muted-foreground">{{ __('No data.') }}</li>
                    @endforelse
                </ul>
            </div>
            <div class="rounded-xl border border-border bg-card p-4 shadow-sm">
                <flux:heading size="sm">{{ __('Needs improvement') }}</flux:heading>
                <ul class="mt-3 space-y-2 text-sm">
                    @forelse ($ranks['poor'] as $row)
                        <li class="flex items-baseline justify-between gap-3 border-b border-border/60 pb-2 last:border-0">
                            <span>#{{ $row['rank'] }} {{ $row['name'] }} <span class="text-muted-foreground">({{ $row['class'] }})</span></span>
                            <span class="font-medium text-amber-600 dark:text-amber-400">{{ $row['percentage'] }}%</span>
                        </li>
                    @empty
                        <li class="text-muted-foreground">{{ __('No data.') }}</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</x-layouts::app>
