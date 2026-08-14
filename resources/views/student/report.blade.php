<x-layouts::app :title="__('My report')">
    <x-report.page
        :title="__('Report card')"
        :description="$student->user?->name.' — '.($student->currentClass?->code ?? '—')"
        :catalog-route="$catalogRoute"
    >
        <x-slot:aside>
            <x-report.exports :query="$exportQuery" />
        </x-slot:aside>

        <x-list.filters :action="route('student.report')" :filters="['month' => $month]" :submit="__('Apply')" :with-per-page="false">
            <x-form.month-select :label="__('Attendance month')" :value="$month" />
        </x-list.filters>

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
    </x-report.page>
</x-layouts::app>
