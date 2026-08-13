<x-layouts::app :title="__('My report')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div>
            <flux:heading size="xl">{{ __('My report') }}</flux:heading>
            <flux:text class="mt-1">{{ $student->user?->name }} — {{ $student->currentClass?->code ?? '—' }}</flux:text>
        </div>

        <form method="GET" class="no-print flex flex-wrap items-end gap-3">
            <x-form.month-select :label="__('Attendance month')" :value="$month" />
            <flux:button type="submit" variant="filled">{{ __('Load') }}</flux:button>
        </form>

        <x-report-toolbar :print="$print">
            <flux:button :href="route('student.report', ['month' => $month, 'export' => 'csv'])" variant="filled">{{ __('CSV results') }}</flux:button>
            <flux:button :href="route('student.report', ['month' => $month, 'print' => 1])" variant="filled">{{ __('Print / PDF') }}</flux:button>
        </x-report-toolbar>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <x-dashboard.stat
                :label="__('Attendance (:month)', ['month' => $month])"
                :value="$attendancePercentage.'%'"
                tone="success"
            />
            <x-dashboard.stat
                :label="__('Present / Absent / Late / Excused')"
                :value="$attendanceCounts[$presentKey].' / '.$attendanceCounts[$absentKey].' / '.$attendanceCounts[$lateKey].' / '.$attendanceCounts[$excusedKey]"
            />
            <x-dashboard.stat
                :label="__('Overall exam average')"
                :value="$overallAverage !== null ? $overallAverage.'%' : '—'"
            />
        </div>

        @forelse ($marksByExam as $examBlock)
            <div class="overflow-x-auto rounded-xl border border-border bg-card">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-border px-3 py-3">
                    <flux:heading size="sm">{{ $examBlock['exam_name'] }}</flux:heading>
                    <flux:text class="text-sm">{{ __('Exam average: :pct%', ['pct' => $examBlock['average_percentage']]) }}</flux:text>
                </div>
                <table class="min-w-full text-sm">
                    <thead class="bg-muted/60">
                        <tr>
                            <th class="px-3 py-2 text-left">{{ __('Subject') }}</th>
                            <th class="px-3 py-2 text-left">{{ __('Marks') }}</th>
                            <th class="px-3 py-2 text-left">{{ __('%') }}</th>
                            <th class="px-3 py-2 text-left">{{ __('Grade') }}</th>
                            <th class="px-3 py-2 text-left">{{ __('Result') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($examBlock['rows'] as $row)
                            <tr class="border-t border-border">
                                <td class="px-3 py-2">{{ $row['subject'] }}</td>
                                <td class="px-3 py-2">{{ $row['marks_obtained'] }} / {{ $row['max_marks'] }}</td>
                                <td class="px-3 py-2">{{ $row['percentage'] }}%</td>
                                <td class="px-3 py-2">{{ $row['grade_letter'] }}</td>
                                <td class="px-3 py-2">{{ $row['is_pass'] ? __('Pass') : __('Fail') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @empty
            <div class="rounded-xl border border-border bg-card px-3 py-6 text-muted-foreground">
                {{ __('No published results.') }}
            </div>
        @endforelse
    </div>
</x-layouts::app>
