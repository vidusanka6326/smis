<x-layouts::app :title="__('Exam report')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <flux:heading size="xl">{{ __('Examination statistics') }}</flux:heading>

        <form method="GET" class="no-print flex flex-wrap items-end gap-3">
            <flux:select name="exam_id" :label="__('Exam')">
                @foreach ($exams as $option)
                    <flux:select.option :value="$option->id" :selected="(string) $selectedExamId === (string) $option->id">{{ $option->name }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:button type="submit" variant="filled">{{ __('Load') }}</flux:button>
        </form>

        <x-report-toolbar :print="$print">
            <flux:button :href="route('teacher.reports.examination', ['exam_id' => $selectedExamId, 'export' => 'csv'])" variant="filled">{{ __('CSV') }}</flux:button>
            <flux:button :href="route('teacher.reports.examination', ['exam_id' => $selectedExamId, 'print' => 1])" variant="filled">{{ __('Print / PDF') }}</flux:button>
        </x-report-toolbar>

        @if ($stats)
            <p class="text-sm">{{ __('Pass rate: :p% · Average: :a', ['p' => $stats['pass_rate'], 'a' => $stats['average_marks']]) }}</p>
            <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
                <table class="min-w-full text-sm">
                    <thead class="bg-zinc-50 dark:bg-zinc-900"><tr><th class="px-3 py-2 text-left">{{ __('Subject') }}</th><th class="px-3 py-2 text-left">{{ __('Pass %') }}</th><th class="px-3 py-2 text-left">{{ __('Avg') }}</th></tr></thead>
                    <tbody>
                        @foreach ($stats['by_subject'] as $row)
                            <tr class="border-t border-zinc-200 dark:border-zinc-700"><td class="px-3 py-2">{{ $row['subject'] }}</td><td class="px-3 py-2">{{ $row['pass_rate'] }}%</td><td class="px-3 py-2">{{ $row['average_marks'] }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-layouts::app>
