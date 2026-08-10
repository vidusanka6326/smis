<x-layouts::app :title="__('Examination report')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div>
            <flux:heading size="xl">{{ __('Examination statistics') }}</flux:heading>
        </div>

        <form method="GET" action="{{ route('admin.reports.examination') }}" class="no-print flex flex-wrap items-end gap-3">
            <flux:select name="exam_id" :label="__('Exam')">
                @foreach ($exams as $option)
                    <flux:select.option :value="$option->id" :selected="(string) $selectedExamId === (string) $option->id">{{ $option->name }}</flux:select.option>
                @endforeach
            </flux:select>
            @if ($exam)
                <flux:select name="subject_id" :label="__('Subject')">
                    <flux:select.option value="">{{ __('All subjects') }}</flux:select.option>
                    @foreach ($exam->examSubjects as $examSubject)
                        <flux:select.option :value="$examSubject->subject_id" :selected="(string) $selectedSubjectId === (string) $examSubject->subject_id">{{ $examSubject->subject?->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            @endif
            <flux:button type="submit" variant="filled">{{ __('Load') }}</flux:button>
        </form>

        <x-report-toolbar :print="$print">
            <flux:button :href="route('admin.reports.examination', ['exam_id' => $selectedExamId, 'subject_id' => $selectedSubjectId, 'export' => 'csv'])" variant="filled">{{ __('CSV') }}</flux:button>
            <flux:button :href="route('admin.reports.examination', ['exam_id' => $selectedExamId, 'subject_id' => $selectedSubjectId, 'print' => 1])" variant="filled">{{ __('Print / PDF') }}</flux:button>
        </x-report-toolbar>

        @if ($stats)
            <div class="grid gap-4 md:grid-cols-4">
                <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700"><flux:text>{{ __('Entries') }}</flux:text><p class="text-2xl font-semibold">{{ $stats['total_marks'] }}</p></div>
                <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700"><flux:text>{{ __('Pass rate') }}</flux:text><p class="text-2xl font-semibold">{{ $stats['pass_rate'] }}%</p></div>
                <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700"><flux:text>{{ __('Avg marks') }}</flux:text><p class="text-2xl font-semibold">{{ $stats['average_marks'] }}</p></div>
                <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700"><flux:text>{{ __('Avg %') }}</flux:text><p class="text-2xl font-semibold">{{ $stats['average_percentage'] }}%</p></div>
            </div>

            <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
                <table class="min-w-full text-sm">
                    <thead class="bg-zinc-50 dark:bg-zinc-900"><tr><th class="px-3 py-2 text-left">{{ __('Subject') }}</th><th class="px-3 py-2 text-left">{{ __('Entries') }}</th><th class="px-3 py-2 text-left">{{ __('Avg') }}</th><th class="px-3 py-2 text-left">{{ __('Pass %') }}</th></tr></thead>
                    <tbody>
                        @foreach ($stats['by_subject'] as $row)
                            <tr class="border-t border-zinc-200 dark:border-zinc-700">
                                <td class="px-3 py-2">{{ $row['subject'] }}</td>
                                <td class="px-3 py-2">{{ $row['count'] }}</td>
                                <td class="px-3 py-2">{{ $row['average_marks'] }}</td>
                                <td class="px-3 py-2">{{ $row['pass_rate'] }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-layouts::app>
