@php
    $chartColors = ['#0f6b6d', '#2da8a8', '#7ed3b2', '#256396', '#5a787e'];
@endphp

<x-layouts::app :title="__('Teacher Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <flux:heading size="xl">{{ __('Teacher Dashboard') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Your classes, attendance risk, exam pulse, and today’s lessons.') }}</flux:text>
            </div>
            <div class="flex flex-wrap gap-2">
                <flux:button :href="route('teacher.students.index')" variant="primary" wire:navigate>{{ __('My students') }}</flux:button>
                <flux:button :href="route('teacher.timetable')" variant="filled" wire:navigate>{{ __('My timetable') }}</flux:button>
                <flux:button :href="route('teacher.reports.dashboard')" variant="filled" wire:navigate>{{ __('Reports') }}</flux:button>
                <flux:button :href="route('teacher.attendance.sessions.index')" variant="filled" wire:navigate>{{ __('Attendance') }}</flux:button>
            </div>
        </div>

        @if (! $teacher)
            <flux:callout variant="warning" icon="exclamation-triangle">
                <flux:callout.heading>{{ __('No teacher profile is linked to this account yet.') }}</flux:callout.heading>
            </flux:callout>
        @else
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4 2xl:grid-cols-8">
                <x-dashboard.stat :label="__('Students in scope')" :value="$stats['students']" />
                <x-dashboard.stat :label="__('Classes')" :value="$stats['classes']" />
                <x-dashboard.stat :label="__('Homerooms')" :value="$stats['homerooms']" />
                <x-dashboard.stat :label="__('Boys / Girls')" :value="$stats['boys'].' / '.$stats['girls']" />
                <x-dashboard.stat
                    :label="__('Avg attendance')"
                    :value="$stats['avg_attendance'] !== null ? $stats['avg_attendance'].'%' : '—'"
                    tone="success"
                />
                <x-dashboard.stat
                    :label="__('Attendance at risk')"
                    :value="$stats['at_risk_count']"
                    :tone="$stats['at_risk_count'] > 0 ? 'warning' : 'default'"
                />
                <x-dashboard.stat
                    :label="__('Exam pass rate')"
                    :value="$stats['pass_rate'] !== null ? $stats['pass_rate'].'%' : '—'"
                    :hint="$exam?->name"
                    tone="success"
                />
                <x-dashboard.stat
                    :label="__('Lessons today')"
                    :value="$stats['lessons_today']"
                    :hint="__('Exam avg :pct', ['pct' => $stats['average_percentage'] !== null ? $stats['average_percentage'].'%' : '—'])"
                />
            </div>

            <div class="grid gap-4 lg:grid-cols-2 xl:grid-cols-3">
                <x-dashboard.chart-card :title="__('Gender mix (my classes)')" canvas-id="teacherGenderChart" />
                <x-dashboard.chart-card :title="__('Students by class')" canvas-id="teacherClassesChart" />
                <x-dashboard.chart-card :title="__('Attendance % by class')" canvas-id="teacherAttendanceChart" />
                <x-dashboard.chart-card :title="__('Grade letters (latest exam)')" canvas-id="teacherLettersChart" />
                <x-dashboard.chart-card :title="__('Pass vs fail')" canvas-id="teacherPassFailChart" />
                <x-dashboard.chart-card :title="__('Subject pass rates')" canvas-id="teacherSubjectPassChart" />
            </div>

            <div class="grid gap-4 lg:grid-cols-2 xl:grid-cols-4">
                <x-dashboard.panel :title="__('Lessons today')">
                    <ul class="space-y-2 text-sm">
                        @forelse ($todaySlots as $slot)
                            <li class="rounded-lg border border-border px-3 py-2">
                                <div class="font-medium">P{{ $slot->period_number }} — {{ $slot->schoolClass?->code }}</div>
                                <div class="text-muted-foreground">{{ $slot->subject?->name }}</div>
                            </li>
                        @empty
                            <li class="text-muted-foreground">{{ __('No lessons scheduled for today.') }}</li>
                        @endforelse
                    </ul>
                </x-dashboard.panel>

                <x-dashboard.panel :title="__('Attendance needing attention')">
                    <ul class="space-y-2 text-sm">
                        @forelse ($atRiskPreview as $row)
                            <li class="flex items-baseline justify-between gap-2 border-b border-border/70 pb-2 last:border-0">
                                <span>{{ $row['name'] }} <span class="text-muted-foreground">({{ $row['class'] }})</span></span>
                                <span class="font-medium text-amber-600 dark:text-amber-400">{{ $row['percentage'] }}%</span>
                            </li>
                        @empty
                            <li class="text-muted-foreground">{{ __('No students below 80% this month.') }}</li>
                        @endforelse
                    </ul>
                    <div class="mt-3">
                        <flux:button size="sm" :href="route('teacher.reports.attendance')" variant="ghost" wire:navigate>{{ __('Attendance report') }}</flux:button>
                    </div>
                </x-dashboard.panel>

                <x-dashboard.panel :title="__('Top performers')">
                    <ul class="space-y-2 text-sm">
                        @forelse ($bestPreview as $row)
                            <li class="flex items-baseline justify-between gap-2 border-b border-border/70 pb-2 last:border-0">
                                <span>#{{ $row['rank'] }} {{ $row['name'] }}</span>
                                <span class="font-medium text-emerald-700 dark:text-emerald-400">{{ $row['percentage'] }}%</span>
                            </li>
                        @empty
                            <li class="text-muted-foreground">{{ __('No rankings yet.') }}</li>
                        @endforelse
                    </ul>
                </x-dashboard.panel>

                <x-dashboard.panel :title="__('Needs improvement')">
                    <ul class="space-y-2 text-sm">
                        @forelse ($poorPreview as $row)
                            <li class="flex items-baseline justify-between gap-2 border-b border-border/70 pb-2 last:border-0">
                                <span>#{{ $row['rank'] }} {{ $row['name'] }}</span>
                                <span class="font-medium text-amber-600 dark:text-amber-400">{{ $row['percentage'] }}%</span>
                            </li>
                        @empty
                            <li class="text-muted-foreground">{{ __('No rankings yet.') }}</li>
                        @endforelse
                    </ul>
                    <div class="mt-3">
                        <flux:button size="sm" :href="route('teacher.reports.performance')" variant="ghost" wire:navigate>{{ __('Full rankings') }}</flux:button>
                    </div>
                </x-dashboard.panel>
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <x-dashboard.panel :title="__('Homeroom')">
                    <ul class="space-y-1 text-sm">
                        @forelse ($teacher->homeroomClasses as $class)
                            <li>{{ $class->code }} — {{ $class->grade?->name }}</li>
                        @empty
                            <li class="text-muted-foreground">{{ __('No homeroom classes assigned.') }}</li>
                        @endforelse
                    </ul>
                </x-dashboard.panel>

                <x-dashboard.panel :title="__('Teaching assignments')">
                    <ul class="max-h-48 space-y-1 overflow-y-auto text-sm">
                        @forelse ($teacher->assignments as $assignment)
                            <li>
                                {{ $assignment->role_in_assignment->label() }} —
                                {{ $assignment->schoolClass?->code }}
                                @if ($assignment->subject)
                                    — {{ $assignment->subject->name }}
                                @endif
                            </li>
                        @empty
                            <li class="text-muted-foreground">{{ __('No assignments yet.') }}</li>
                        @endforelse
                    </ul>
                </x-dashboard.panel>
            </div>

            <x-charts.render :charts="[
                [
                    'id' => 'teacherGenderChart',
                    'type' => 'doughnut',
                    'data' => $charts['gender'],
                    'colors' => ['#2da8a8', '#5a787e'],
                ],
                [
                    'id' => 'teacherClassesChart',
                    'type' => 'bar',
                    'label' => __('Students'),
                    'data' => $charts['classes'],
                    'colors' => ['#7ed3b2'],
                ],
                [
                    'id' => 'teacherAttendanceChart',
                    'type' => 'bar',
                    'label' => __('%'),
                    'data' => $charts['attendance_by_class'],
                    'colors' => ['#0f6b6d'],
                ],
                [
                    'id' => 'teacherLettersChart',
                    'type' => 'bar',
                    'label' => __('Count'),
                    'data' => $charts['letters'],
                    'colors' => $chartColors,
                ],
                [
                    'id' => 'teacherPassFailChart',
                    'type' => 'doughnut',
                    'data' => $charts['pass_fail'],
                    'colors' => ['#2da8a8', '#c83737'],
                ],
                [
                    'id' => 'teacherSubjectPassChart',
                    'type' => 'bar',
                    'label' => __('Pass %'),
                    'data' => $charts['subject_pass_rates'],
                    'colors' => ['#2da8a8'],
                ],
            ]" />
        @endif
    </div>
</x-layouts::app>
