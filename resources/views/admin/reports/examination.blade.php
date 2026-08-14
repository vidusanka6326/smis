<x-layouts::app :title="__('Examination report')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div>
            <flux:heading size="xl">{{ __('Examination statistics') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Subject and class comparison for the selected exam.') }}</flux:text>
        </div>

        <x-list.filters :action="route('admin.reports.examination')" :filters="array_filter(['exam_id' => $selectedExamId, 'subject_id' => $selectedSubjectId])" :submit="__('Load')" :with-per-page="false" class="no-print">
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
        </x-list.filters>

        <x-report-toolbar :print="$print">
            <flux:button :href="route('admin.reports.examination', ['exam_id' => $selectedExamId, 'subject_id' => $selectedSubjectId, 'export' => 'csv'])" variant="filled">{{ __('CSV') }}</flux:button>
            <flux:button :href="route('admin.reports.examination', ['exam_id' => $selectedExamId, 'subject_id' => $selectedSubjectId, 'print' => 1])" variant="filled">{{ __('Print / PDF') }}</flux:button>
        </x-report-toolbar>

        @if ($stats)
            <div class="grid gap-4 md:grid-cols-4">
                <x-dashboard.stat :label="__('Entries')" :value="$stats['total_marks']" />
                <x-dashboard.stat :label="__('Pass rate')" :value="$stats['pass_rate'].'%'" tone="success" />
                <x-dashboard.stat :label="__('Avg marks')" :value="$stats['average_marks']" />
                <x-dashboard.stat :label="__('Avg %')" :value="$stats['average_percentage'].'%'" />
            </div>

            <div class="rounded-xl border border-border bg-card p-4">
                <flux:heading size="sm">{{ __('Grade letters') }}</flux:heading>
                <div class="mt-3 flex flex-wrap gap-3 text-sm">
                    @foreach ($stats['by_grade_letter'] as $letter => $count)
                        <span class="rounded-md bg-muted px-3 py-1.5">{{ $letter }}: <strong>{{ $count }}</strong></span>
                    @endforeach
                </div>
            </div>

            <div class="overflow-x-auto rounded-xl border border-border bg-card">
                <div class="border-b border-border px-3 py-3">
                    <flux:heading size="sm">{{ __('By subject') }}</flux:heading>
                </div>
                <table class="min-w-full text-sm">
                    <thead class="bg-muted/60">
                        <tr>
                            <th class="px-3 py-2 text-left">{{ __('Subject') }}</th>
                            <th class="px-3 py-2 text-left">{{ __('Entries') }}</th>
                            <th class="px-3 py-2 text-left">{{ __('Avg') }}</th>
                            <th class="px-3 py-2 text-left">{{ __('Pass %') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($stats['by_subject'] as $row)
                            <tr class="border-t border-border">
                                <td class="px-3 py-2">{{ $row['subject'] }}</td>
                                <td class="px-3 py-2">{{ $row['count'] }}</td>
                                <td class="px-3 py-2">{{ $row['average_marks'] }}</td>
                                <td class="px-3 py-2">{{ $row['pass_rate'] }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="overflow-x-auto rounded-xl border border-border bg-card">
                <div class="border-b border-border px-3 py-3">
                    <flux:heading size="sm">{{ __('By class') }}</flux:heading>
                </div>
                <table class="min-w-full text-sm">
                    <thead class="bg-muted/60">
                        <tr>
                            <th class="px-3 py-2 text-left">{{ __('Class') }}</th>
                            <th class="px-3 py-2 text-left">{{ __('Entries') }}</th>
                            <th class="px-3 py-2 text-left">{{ __('Avg %') }}</th>
                            <th class="px-3 py-2 text-left">{{ __('Pass %') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($stats['by_class'] as $row)
                            <tr class="border-t border-border">
                                <td class="px-3 py-2">{{ $row['code'] }}</td>
                                <td class="px-3 py-2">{{ $row['count'] }}</td>
                                <td class="px-3 py-2">{{ $row['average_percentage'] }}%</td>
                                <td class="px-3 py-2">{{ $row['pass_rate'] }}%</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-3 py-6 text-muted-foreground">{{ __('No class breakdown.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-layouts::app>
