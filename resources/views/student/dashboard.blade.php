@php
    $chartColors = ['#7033ff', '#3276e4', '#fd822b', '#747474', '#4ac885'];
@endphp

<x-layouts::app :title="__('Student Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <flux:heading size="xl">{{ __('Student Dashboard') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Your attendance, subject performance, results, and timetable.') }}</flux:text>
            </div>
            <div class="flex flex-wrap gap-2">
                <flux:button :href="route('student.timetable')" variant="primary" wire:navigate>{{ __('My timetable') }}</flux:button>
                <flux:button :href="route('student.results')" variant="filled" wire:navigate>{{ __('My results') }}</flux:button>
                <flux:button :href="route('student.report')" variant="filled" wire:navigate>{{ __('My report') }}</flux:button>
                <flux:button :href="route('student.attendance')" variant="filled" wire:navigate>{{ __('My attendance') }}</flux:button>
            </div>
        </div>

        @if (! $student)
            <flux:callout variant="warning" icon="exclamation-triangle">
                <flux:callout.heading>{{ __('No student profile is linked to this account yet.') }}</flux:callout.heading>
            </flux:callout>
        @else
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4 2xl:grid-cols-8">
                <x-dashboard.stat
                    :label="__('Attendance (month)')"
                    :value="$stats['attendance_percent'] !== null ? $stats['attendance_percent'].'%' : '—'"
                    :hint="__(':count sessions', ['count' => $stats['sessions_this_month']])"
                    tone="success"
                />
                <x-dashboard.stat :label="__('Present')" :value="$stats['present']" />
                <x-dashboard.stat :label="__('Absent')" :value="$stats['absent']" :tone="$stats['absent'] > 0 ? 'warning' : 'default'" />
                <x-dashboard.stat :label="__('Late / Excused')" :value="$stats['late'].' / '.$stats['excused']" />
                <x-dashboard.stat
                    :label="__('Overall exam avg')"
                    :value="$stats['overall_average'] !== null ? $stats['overall_average'].'%' : '—'"
                />
                <x-dashboard.stat :label="__('Pass / Fail')" :value="$stats['pass_count'].' / '.$stats['fail_count']" />
                <x-dashboard.stat :label="__('Published marks')" :value="$stats['published_marks']" />
                <x-dashboard.stat :label="__('Class')" :value="$student->currentClass?->code ?? '—'" :hint="$student->currentClass?->grade?->name" />
            </div>

            <div class="grid gap-4 lg:grid-cols-2 xl:grid-cols-4">
                <x-dashboard.chart-card :title="__('Attendance this month')" canvas-id="studentAttendanceChart" />
                <x-dashboard.chart-card :title="__('My grade letters')" canvas-id="studentLettersChart" />
                <x-dashboard.chart-card :title="__('Pass vs fail')" canvas-id="studentPassFailChart" />
                <x-dashboard.chart-card :title="__('Subject averages %')" canvas-id="studentSubjectsChart" />
            </div>

            <div class="grid gap-4 lg:grid-cols-3">
                <x-dashboard.panel :title="__('Profile')">
                    <dl class="space-y-2 text-sm">
                        <div><dt class="text-muted-foreground">{{ __('Admission no.') }}</dt><dd>{{ $student->admission_no }}</dd></div>
                        <div><dt class="text-muted-foreground">{{ __('Gender') }}</dt><dd>{{ $student->gender->label() }}</dd></div>
                        <div><dt class="text-muted-foreground">{{ __('Grade') }}</dt><dd>{{ $student->currentClass?->grade?->name ?? '—' }}</dd></div>
                        <div><dt class="text-muted-foreground">{{ __('Subjects') }}</dt><dd>{{ $stats['subjects'] }}</dd></div>
                        <div><dt class="text-muted-foreground">{{ __('Lessons today') }}</dt><dd>{{ $stats['lessons_today'] }}</dd></div>
                    </dl>
                </x-dashboard.panel>

                <x-dashboard.panel :title="__('Timetable today')">
                    <ul class="space-y-2 text-sm">
                        @forelse ($todaySlots as $slot)
                            <li class="rounded-lg border border-border px-3 py-2">
                                <div class="font-medium">P{{ $slot->period_number }} — {{ $slot->subject?->name }}</div>
                                <div class="text-muted-foreground">{{ $slot->teacher?->user?->name }}</div>
                            </li>
                        @empty
                            <li class="text-muted-foreground">{{ __('No lessons scheduled for today.') }}</li>
                        @endforelse
                    </ul>
                </x-dashboard.panel>

                <x-dashboard.panel :title="__('Subjects to improve')">
                    <ul class="space-y-2 text-sm">
                        @forelse ($failedMarks as $mark)
                            <li class="flex items-baseline justify-between gap-2 border-b border-border/70 pb-2 last:border-0">
                                <span>{{ $mark->examSubject?->subject?->name }} <span class="text-muted-foreground">({{ $mark->examSubject?->exam?->name }})</span></span>
                                <span class="font-medium text-amber-600 dark:text-amber-400">{{ $mark->grade_letter->value }}</span>
                            </li>
                        @empty
                            <li class="text-muted-foreground">{{ __('No failed published subjects.') }}</li>
                        @endforelse
                    </ul>
                </x-dashboard.panel>
            </div>

            <x-dashboard.panel :title="__('Recent published results')">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-muted-foreground">
                                <th class="py-2 pe-3">{{ __('Exam') }}</th>
                                <th class="py-2 pe-3">{{ __('Subject') }}</th>
                                <th class="py-2 pe-3">{{ __('Marks') }}</th>
                                <th class="py-2 pe-3">{{ __('Grade') }}</th>
                                <th class="py-2">{{ __('Result') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentMarks as $mark)
                                <tr class="border-t border-border">
                                    <td class="py-2 pe-3">{{ $mark->examSubject?->exam?->name }}</td>
                                    <td class="py-2 pe-3">{{ $mark->examSubject?->subject?->name }}</td>
                                    <td class="py-2 pe-3">{{ $mark->marks_obtained }} / {{ $mark->examSubject?->max_marks }}</td>
                                    <td class="py-2 pe-3">{{ $mark->grade_letter->value }}</td>
                                    <td class="py-2">{{ $mark->is_pass ? __('Pass') : __('Fail') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-4 text-muted-foreground">{{ __('No published results yet.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-dashboard.panel>

            <x-charts.render :charts="[
                [
                    'id' => 'studentAttendanceChart',
                    'type' => 'doughnut',
                    'data' => $charts['attendance_status'],
                    'colors' => ['#7033ff', '#747474', '#3276e4', '#fd822b'],
                ],
                [
                    'id' => 'studentLettersChart',
                    'type' => 'doughnut',
                    'data' => $charts['letters'],
                    'colors' => $chartColors,
                ],
                [
                    'id' => 'studentPassFailChart',
                    'type' => 'doughnut',
                    'data' => $charts['pass_fail'],
                    'colors' => ['#4ac885', '#e54b4f'],
                ],
                [
                    'id' => 'studentSubjectsChart',
                    'type' => 'bar',
                    'label' => __('Avg %'),
                    'data' => $charts['subject_averages'],
                    'colors' => ['#3276e4'],
                ],
            ]" />
        @endif
    </div>
</x-layouts::app>
