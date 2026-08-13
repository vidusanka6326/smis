@php
    $chartColors = ['#72e3ad', '#3b82f6', '#8b5cf6', '#f59e0b', '#10b981'];
@endphp

<x-layouts::app :title="__('Admin Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <flux:heading size="xl">{{ __('Admin Dashboard') }}</flux:heading>
                <flux:text class="mt-1">{{ __('School-wide people, attendance, exams, and recent activity.') }}</flux:text>
            </div>
            <div class="flex flex-wrap gap-2">
                <flux:button :href="route('admin.reports.dashboard')" variant="primary" wire:navigate>{{ __('Full reports') }}</flux:button>
                <flux:button :href="route('admin.timetables.index')" variant="filled" wire:navigate>{{ __('Timetables') }}</flux:button>
                <flux:button :href="route('admin.activity-logs.index')" variant="filled" wire:navigate>{{ __('Activity log') }}</flux:button>
            </div>
        </div>

        @if (session('status'))
            <flux:callout variant="success" icon="check-circle">
                <flux:callout.heading>{{ session('status') }}</flux:callout.heading>
            </flux:callout>
        @endif

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4 2xl:grid-cols-8">
            <x-dashboard.stat :label="__('Students')" :value="$stats['students']" />
            <x-dashboard.stat :label="__('Teachers')" :value="$stats['teachers']" />
            <x-dashboard.stat :label="__('Classes')" :value="$stats['classes']" />
            <x-dashboard.stat :label="__('Boys / Girls')" :value="$stats['boys'].' / '.$stats['girls']" />
            <x-dashboard.stat
                :label="__('Avg attendance')"
                :value="$stats['avg_attendance'] !== null ? $stats['avg_attendance'].'%' : '—'"
                :hint="__(':count tracked', ['count' => $stats['attendance_tracked']])"
                tone="success"
            />
            <x-dashboard.stat
                :label="__('Attendance at risk')"
                :value="$stats['at_risk_count']"
                :hint="__('Below 80%')"
                :tone="$stats['at_risk_count'] > 0 ? 'warning' : 'default'"
            />
            <x-dashboard.stat
                :label="__('Exam pass rate')"
                :value="$stats['pass_rate'] !== null ? $stats['pass_rate'].'%' : '—'"
                :hint="$exam?->name"
                tone="success"
            />
            <x-dashboard.stat
                :label="__('Exam avg %')"
                :value="$stats['average_percentage'] !== null ? $stats['average_percentage'].'%' : '—'"
                :hint="__('Pass :p · Fail :f', ['p' => $stats['pass_count'], 'f' => $stats['fail_count']])"
            />
        </div>

        <div class="grid gap-4 lg:grid-cols-2 xl:grid-cols-3">
            <x-dashboard.chart-card :title="__('Gender mix')" canvas-id="adminGenderChart" />
            <x-dashboard.chart-card :title="__('Students by grade')" canvas-id="adminGradesChart" />
            <x-dashboard.chart-card :title="__('Students by class')" canvas-id="adminClassesChart" />
            <x-dashboard.chart-card :title="__('Attendance % by class')" canvas-id="adminAttendanceChart" />
            <x-dashboard.chart-card :title="__('Grade letters (latest exam)')" canvas-id="adminLettersChart" />
            <x-dashboard.chart-card :title="__('Pass vs fail (latest exam)')" canvas-id="adminPassFailChart" />
            <x-dashboard.chart-card :title="__('Subject pass rates')" canvas-id="adminSubjectPassChart" class="xl:col-span-2" />
            <x-dashboard.chart-card :title="__('Class exam averages %')" canvas-id="adminClassExamChart" />
        </div>

        <div class="grid gap-4 lg:grid-cols-2 xl:grid-cols-4">
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
                    <flux:button size="sm" :href="route('admin.reports.attendance')" variant="ghost" wire:navigate>{{ __('Open attendance report') }}</flux:button>
                </div>
            </x-dashboard.panel>

            <x-dashboard.panel :title="__('Top performers (latest exam)')">
                <ul class="space-y-2 text-sm">
                    @forelse ($bestPreview as $row)
                        <li class="flex items-baseline justify-between gap-2 border-b border-border/70 pb-2 last:border-0">
                            <span>#{{ $row['rank'] }} {{ $row['name'] }} <span class="text-muted-foreground">({{ $row['class'] }})</span></span>
                            <span class="font-medium text-emerald-700 dark:text-emerald-400">{{ $row['percentage'] }}%</span>
                        </li>
                    @empty
                        <li class="text-muted-foreground">{{ __('No published exam rankings yet.') }}</li>
                    @endforelse
                </ul>
            </x-dashboard.panel>

            <x-dashboard.panel :title="__('Needs improvement (latest exam)')">
                <ul class="space-y-2 text-sm">
                    @forelse ($poorPreview as $row)
                        <li class="flex items-baseline justify-between gap-2 border-b border-border/70 pb-2 last:border-0">
                            <span>#{{ $row['rank'] }} {{ $row['name'] }} <span class="text-muted-foreground">({{ $row['class'] }})</span></span>
                            <span class="font-medium text-amber-600 dark:text-amber-400">{{ $row['percentage'] }}%</span>
                        </li>
                    @empty
                        <li class="text-muted-foreground">{{ __('No published exam rankings yet.') }}</li>
                    @endforelse
                </ul>
                <div class="mt-3">
                    <flux:button size="sm" :href="route('admin.reports.performance')" variant="ghost" wire:navigate>{{ __('Full rankings') }}</flux:button>
                </div>
            </x-dashboard.panel>

            <x-dashboard.panel :title="__('Draft exams')">
                <ul class="space-y-2 text-sm">
                    @forelse ($draftExams as $draft)
                        <li class="border-b border-border/70 pb-2 last:border-0">
                            <div class="font-medium">{{ $draft->name }}</div>
                            <div class="text-muted-foreground">{{ $draft->starts_on?->format('Y-m-d') ?? '—' }}</div>
                        </li>
                    @empty
                        <li class="text-muted-foreground">{{ __('No draft exams.') }}</li>
                    @endforelse
                </ul>
                <div class="mt-3">
                    <flux:button size="sm" :href="route('admin.exams.index')" variant="ghost" wire:navigate>{{ __('Manage exams') }}</flux:button>
                </div>
            </x-dashboard.panel>
        </div>

        <x-dashboard.panel :title="__('Recent activity')">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-muted-foreground">
                            <th class="py-2 pe-3">{{ __('When') }}</th>
                            <th class="py-2 pe-3">{{ __('Who') }}</th>
                            <th class="py-2 pe-3">{{ __('Action') }}</th>
                            <th class="py-2">{{ __('Detail') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentActivity as $log)
                            <tr class="border-t border-border">
                                <td class="py-2 pe-3 whitespace-nowrap">{{ $log->created_at?->diffForHumans() }}</td>
                                <td class="py-2 pe-3">{{ $log->causer?->name ?? '—' }}</td>
                                <td class="py-2 pe-3">{{ $log->action instanceof \App\Enums\ActivityAction ? $log->action->label() : $log->action }}</td>
                                <td class="py-2">{{ $log->description }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-4 text-muted-foreground">{{ __('No activity logged yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-dashboard.panel>

        <div class="flex flex-wrap gap-2">
            <flux:button :href="route('admin.users.create')" variant="filled" wire:navigate>{{ __('Create user') }}</flux:button>
            <flux:button :href="route('admin.teachers.index')" variant="filled" wire:navigate>{{ __('Teachers') }}</flux:button>
            <flux:button :href="route('admin.students.index')" variant="filled" wire:navigate>{{ __('Students') }}</flux:button>
            <flux:button :href="route('admin.attendance.sessions.index')" variant="filled" wire:navigate>{{ __('Attendance') }}</flux:button>
            <flux:button :href="route('admin.exams.index')" variant="filled" wire:navigate>{{ __('Exams') }}</flux:button>
            <flux:button :href="route('admin.academic-years.index')" variant="ghost" wire:navigate>{{ __('Academic years') }}</flux:button>
        </div>

        <x-charts.render :charts="[
            [
                'id' => 'adminGenderChart',
                'type' => 'doughnut',
                'data' => $charts['gender'],
                'colors' => ['#3b82f6', '#f59e0b'],
            ],
            [
                'id' => 'adminGradesChart',
                'type' => 'bar',
                'label' => __('Students'),
                'data' => $charts['grades'],
                'colors' => ['#72e3ad'],
            ],
            [
                'id' => 'adminClassesChart',
                'type' => 'bar',
                'label' => __('Students'),
                'data' => $charts['classes'],
                'colors' => ['#8b5cf6'],
            ],
            [
                'id' => 'adminAttendanceChart',
                'type' => 'bar',
                'label' => __('%'),
                'data' => $charts['attendance_by_class'],
                'colors' => ['#3b82f6'],
            ],
            [
                'id' => 'adminLettersChart',
                'type' => 'bar',
                'label' => __('Count'),
                'data' => $charts['letters'],
                'colors' => $chartColors,
            ],
            [
                'id' => 'adminPassFailChart',
                'type' => 'doughnut',
                'data' => $charts['pass_fail'],
                'colors' => ['#10b981', '#f59e0b'],
            ],
            [
                'id' => 'adminSubjectPassChart',
                'type' => 'bar',
                'label' => __('Pass %'),
                'data' => $charts['subject_pass_rates'],
                'colors' => ['#72e3ad'],
            ],
            [
                'id' => 'adminClassExamChart',
                'type' => 'bar',
                'label' => __('Avg %'),
                'data' => $charts['class_exam_averages'],
                'colors' => ['#3b82f6'],
            ],
        ]" />
    </div>
</x-layouts::app>
