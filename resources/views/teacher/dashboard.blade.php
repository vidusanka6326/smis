@php
    $greeting = now()->format('l');
    $bothRoles = $isClassTeacher && $isSubjectTeacher;
@endphp

<x-layouts::app :title="__('Teacher Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-8">
        @if (! $teacher)
            <flux:callout variant="warning" icon="exclamation-triangle">
                <flux:callout.heading>{{ __('No teacher profile is linked to this account yet.') }}</flux:callout.heading>
            </flux:callout>
        @else

            {{-- ════════════════════════════════════════
                 CLASS TEACHER / HOMEROOM DASHBOARD
                 Shown when teacher has homeroom class(es)
                 or holds the ClassTeacher role.
                 ════════════════════════════════════════ --}}
            @if ($isClassTeacher)
                {{-- Hero --}}
                <section class="relative overflow-hidden rounded-3xl bg-primary px-6 py-8 text-primary-foreground sm:px-8">
                    <div class="pointer-events-none absolute -right-10 -top-10 size-48 rounded-full bg-white/10"></div>
                    <div class="pointer-events-none absolute -bottom-16 right-20 size-56 rounded-full bg-white/5"></div>

                    <div class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                        <div class="max-w-xl">
                            <p class="text-sm font-medium text-primary-foreground/75">{{ __('Happy :day', ['day' => $greeting]) }}</p>
                            <h1 class="mt-1 text-3xl font-semibold tracking-tight sm:text-4xl">{{ __("Here's your class pulse") }}</h1>
                            <p class="mt-2 text-sm text-primary-foreground/80">
                                {{ __(':students students across :classes classes · :homerooms homeroom', [
                                    'students' => $stats['students'],
                                    'classes'  => $stats['classes'],
                                    'homerooms' => $stats['homerooms'],
                                ]) }}
                            </p>
                            <div class="mt-5 flex flex-wrap gap-2">
                                <flux:button :href="route('teacher.students.index')" variant="filled" class="!bg-white !text-primary" wire:navigate>{{ __('My students') }}</flux:button>
                                <flux:button :href="route('teacher.attendance.sessions.index')" variant="ghost" class="!text-white hover:!bg-white/10" wire:navigate>{{ __('Take attendance') }}</flux:button>
                                <flux:button :href="route('teacher.reports.dashboard')" variant="ghost" class="!text-white hover:!bg-white/10" wire:navigate>{{ __('Reports') }}</flux:button>
                                <flux:button :href="route('teacher.data-sheet.index')" variant="ghost" class="!text-white hover:!bg-white/10" wire:navigate>{{ __('EMIS Data Sheet') }}</flux:button>
                            </div>
                        </div>

                        <div class="rounded-2xl bg-white/10 px-6 py-5 backdrop-blur-sm">
                            <p class="text-xs font-medium uppercase tracking-wide text-primary-foreground/70">{{ __('Month attendance') }}</p>
                            <p class="mt-1 text-5xl font-semibold tabular-nums">
                                {{ $stats['avg_attendance'] !== null ? $stats['avg_attendance'].'%' : '—' }}
                            </p>
                            <p class="mt-1 text-sm text-primary-foreground/75">
                                @if ($stats['at_risk_count'] > 0)
                                    {{ __(':count need attention', ['count' => $stats['at_risk_count']]) }}
                                @else
                                    {{ __('No students below 80%') }}
                                @endif
                            </p>
                        </div>
                    </div>
                </section>

                {{-- Stats --}}
                <div class="grid gap-4 sm:grid-cols-3">
                    <x-dashboard.stat
                        :label="__('Exam pass rate')"
                        :value="$stats['pass_rate'] !== null ? $stats['pass_rate'].'%' : '—'"
                        :hint="$exam?->name ?? __('No published exam yet')"
                        tone="success"
                    />
                    <x-dashboard.stat
                        :label="__('At risk')"
                        :value="$stats['at_risk_count']"
                        :hint="__('Attendance under 80%')"
                        :tone="$stats['at_risk_count'] > 0 ? 'warning' : 'default'"
                    />
                    <x-dashboard.stat
                        :label="__('Lessons today')"
                        :value="$stats['lessons_today']"
                        :hint="__('Open timetable for the full week')"
                    />
                </div>

                {{-- Chart + Today --}}
                <div class="grid gap-4 lg:grid-cols-12">
                    <x-dashboard.chart-card
                        :title="__('Attendance by class')"
                        canvas-id="teacherAttendanceChart"
                        class="lg:col-span-7"
                    />

                    <x-dashboard.panel :title="__('Today')" class="lg:col-span-5">
                        <ul class="space-y-2 text-sm">
                            @forelse ($todaySlots as $slot)
                                <li class="flex items-start justify-between gap-3 rounded-xl bg-muted/60 px-3 py-2.5">
                                    <div>
                                        <div class="font-medium">{{ $slot->subject?->name ?? __('Lesson') }}</div>
                                        <div class="text-muted-foreground">{{ $slot->schoolClass?->code }}</div>
                                    </div>
                                    <span class="shrink-0 rounded-md bg-background px-2 py-0.5 text-xs font-medium">P{{ $slot->period_number }}</span>
                                </li>
                            @empty
                                <li class="rounded-xl border border-dashed border-border px-3 py-8 text-center text-muted-foreground">
                                    {{ __('No lessons on the timetable for today.') }}
                                    <div class="mt-3">
                                        <flux:button size="sm" :href="route('teacher.timetable')" variant="ghost" wire:navigate>{{ __('View timetable') }}</flux:button>
                                    </div>
                                </li>
                            @endforelse
                        </ul>
                    </x-dashboard.panel>
                </div>

                <div class="grid gap-4 lg:grid-cols-2">
                    <x-dashboard.panel :title="__('Who needs you')">
                        <ul class="space-y-2 text-sm">
                            @forelse ($atRiskPreview as $row)
                                <li class="flex items-baseline justify-between gap-2 border-b border-border/60 pb-2 last:border-0">
                                    <span>{{ $row['name'] }} <span class="text-muted-foreground">· {{ $row['class'] }}</span></span>
                                    <span class="font-semibold text-amber-600 dark:text-amber-400">{{ $row['percentage'] }}%</span>
                                </li>
                            @empty
                                <li class="text-muted-foreground">{{ __('Everyone is above the attendance threshold.') }}</li>
                            @endforelse
                        </ul>
                        @if ($poorPreview !== [])
                            <div class="mt-4 border-t border-border pt-4">
                                <p class="mb-2 text-xs font-medium uppercase tracking-wide text-muted-foreground">{{ __('Exam — needs improvement') }}</p>
                                <ul class="space-y-2 text-sm">
                                    @foreach (array_slice($poorPreview, 0, 3) as $row)
                                        <li class="flex items-baseline justify-between gap-2">
                                            <span>{{ $row['name'] }}</span>
                                            <span class="font-medium text-amber-600 dark:text-amber-400">{{ $row['percentage'] }}%</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="mt-4">
                            <flux:button size="sm" :href="route('teacher.reports.dashboard')" variant="ghost" wire:navigate>{{ __('Open reports') }}</flux:button>
                        </div>
                    </x-dashboard.panel>

                    <x-dashboard.panel :title="__('Standing out')">
                        <ul class="space-y-2 text-sm">
                            @forelse ($bestPreview as $row)
                                <li class="flex items-baseline justify-between gap-2 border-b border-border/60 pb-2 last:border-0">
                                    <span><span class="text-muted-foreground">#{{ $row['rank'] }}</span> {{ $row['name'] }}</span>
                                    <span class="font-semibold text-primary">{{ $row['percentage'] }}%</span>
                                </li>
                            @empty
                                <li class="text-muted-foreground">{{ __('Publish an exam to see rankings.') }}</li>
                            @endforelse
                        </ul>
                        @if ($teacher->homeroomClasses->isNotEmpty() || $teacher->assignments->isNotEmpty())
                            <div class="mt-4 border-t border-border pt-4">
                                <p class="mb-2 text-xs font-medium uppercase tracking-wide text-muted-foreground">{{ __('Your remit') }}</p>
                                <ul class="space-y-1 text-sm text-muted-foreground">
                                    @foreach ($teacher->homeroomClasses as $class)
                                        <li>{{ __('Homeroom') }} · {{ $class->code }}</li>
                                    @endforeach
                                    @foreach ($teacher->assignments->take(4) as $assignment)
                                        <li>
                                            {{ $assignment->schoolClass?->code }}
                                            @if ($assignment->subject)
                                                · {{ $assignment->subject->name }}
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </x-dashboard.panel>
                </div>

                <x-charts.render :charts="[
                    [
                        'id'     => 'teacherAttendanceChart',
                        'type'   => 'bar',
                        'label'  => __('%'),
                        'data'   => $charts['attendance_by_class'],
                        'colors' => ['#0f6b6d'],
                    ],
                ]" />
            @endif

            {{-- ════════════════════════════════════════
                 SUBJECT TEACHER DASHBOARD
                 Shown when teacher has subject assignments
                 (and NOT a class teacher, OR as second panel when both)
                 ════════════════════════════════════════ --}}
            @if ($isSubjectTeacher)
                {{-- Divider when teacher holds both roles --}}
                @if ($bothRoles)
                    <div class="flex items-center gap-4 py-2">
                        <div class="h-px flex-1 bg-border"></div>
                        <span class="shrink-0 rounded-full border border-border bg-muted px-4 py-1 text-xs font-semibold uppercase tracking-widest text-muted-foreground">
                            {{ __('Subject teaching overview') }}
                        </span>
                        <div class="h-px flex-1 bg-border"></div>
                    </div>
                @else
                    {{-- Hero for subject-only teacher --}}
                    <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-violet-600 to-indigo-600 px-6 py-8 text-white sm:px-8">
                        <div class="pointer-events-none absolute -right-10 -top-10 size-48 rounded-full bg-white/10"></div>
                        <div class="pointer-events-none absolute -bottom-16 right-20 size-56 rounded-full bg-white/5"></div>

                        <div class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                            <div class="max-w-xl">
                                <p class="text-sm font-medium text-white/75">{{ __('Happy :day', ['day' => $greeting]) }}</p>
                                <h1 class="mt-1 text-3xl font-semibold tracking-tight sm:text-4xl">{{ __("Here's your subject pulse") }}</h1>
                                <p class="mt-2 text-sm text-white/80">
                                    {{ __(':subjects subject(s) · :students students', [
                                        'subjects' => count($subjectMetrics),
                                        'students' => $stats['students'],
                                    ]) }}
                                </p>
                                <div class="mt-5 flex flex-wrap gap-2">
                                    <flux:button :href="route('teacher.marks.index')" variant="filled" class="!bg-white !text-violet-700" wire:navigate>{{ __('Marks') }}</flux:button>
                                    <flux:button :href="route('teacher.reports.dashboard')" variant="ghost" class="!text-white hover:!bg-white/10" wire:navigate>{{ __('Reports') }}</flux:button>
                                    <flux:button :href="route('teacher.timetable')" variant="ghost" class="!text-white hover:!bg-white/10" wire:navigate>{{ __('Timetable') }}</flux:button>
                                </div>
                            </div>

                            <div class="rounded-2xl bg-white/10 px-6 py-5 backdrop-blur-sm">
                                <p class="text-xs font-medium uppercase tracking-wide text-white/70">{{ __('Overall pass rate') }}</p>
                                <p class="mt-1 text-5xl font-semibold tabular-nums">
                                    {{ $stats['pass_rate'] !== null ? $stats['pass_rate'].'%' : '—' }}
                                </p>
                                <p class="mt-1 text-sm text-white/75">
                                    {{ $exam?->name ?? __('No published exam yet') }}
                                </p>
                            </div>
                        </div>
                    </section>

                    {{-- Today's slots for pure subject teacher --}}
                    @if (count($todaySlots) > 0)
                        @php $todayPanelTitle = __("Today's periods"); @endphp
                        <x-dashboard.panel :title="$todayPanelTitle">
                            <ul class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                                @foreach ($todaySlots as $slot)
                                    <li class="flex items-start justify-between gap-3 rounded-xl bg-muted/60 px-3 py-2.5">
                                        <div>
                                            <div class="font-medium">{{ $slot->subject?->name ?? __('Lesson') }}</div>
                                            <div class="text-sm text-muted-foreground">{{ $slot->schoolClass?->code }}</div>
                                        </div>
                                        <span class="shrink-0 rounded-md bg-background px-2 py-0.5 text-xs font-medium">P{{ $slot->period_number }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </x-dashboard.panel>
                    @endif
                @endif

                {{-- ── Per-subject cards ─────────────────────────── --}}
                @if (count($subjectMetrics) > 0)
                    @if (count($subjectMetrics) === 1)
                        {{-- Single subject → rich detail view --}}
                        @php $sm = $subjectMetrics[0]; @endphp
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            <x-dashboard.stat
                                :label="__('Students')"
                                :value="$sm['student_count']"
                                :hint="implode(', ', $sm['classes']) ?: '—'"
                            />
                            <x-dashboard.stat
                                :label="__('Pass rate')"
                                :value="$sm['pass_rate'].'%'"
                                :hint="$exam?->name ?? '—'"
                                tone="success"
                            />
                            <x-dashboard.stat
                                :label="__('Average score')"
                                :value="$sm['average'].'%'"
                                :hint="__('Across all marks')"
                            />
                            <x-dashboard.stat
                                :label="__('Failed')"
                                :value="$sm['fail_count']"
                                :hint="__(':p passed', ['p' => $sm['pass_count']])"
                                :tone="$sm['fail_count'] > 0 ? 'warning' : 'default'"
                            />
                        </div>

                        <div class="grid gap-4 lg:grid-cols-2">
                            <x-dashboard.chart-card
                                :title="__('Grade distribution — :subject', ['subject' => $sm['subject_name']])"
                                :canvas-id="$sm['chart_id']"
                            />
                            <x-dashboard.panel :title="__('Top performers')">
                                <ul class="space-y-2 text-sm">
                                    @forelse ($bestPreview as $row)
                                        <li class="flex items-baseline justify-between gap-2 border-b border-border/60 pb-2 last:border-0">
                                            <span><span class="text-muted-foreground">#{{ $row['rank'] }}</span> {{ $row['name'] }}</span>
                                            <span class="font-semibold text-primary">{{ $row['percentage'] }}%</span>
                                        </li>
                                    @empty
                                        <li class="text-muted-foreground">{{ __('No marks published yet.') }}</li>
                                    @endforelse
                                </ul>
                            </x-dashboard.panel>
                        </div>

                        <x-charts.render :charts="[
                            [
                                'id'     => $sm['chart_id'],
                                'type'   => 'doughnut',
                                'label'  => __('Students'),
                                'data'   => [
                                    'labels' => array_keys($sm['by_grade_letter']),
                                    'data'   => array_values($sm['by_grade_letter']),
                                ],
                                'colors' => ['#22c55e','#3b82f6','#f59e0b','#a78bfa','#ef4444'],
                            ],
                        ]" />

                    @else
                        {{-- Multiple subjects → comparison view --}}
                        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-{{ min(count($subjectMetrics), 3) }}">
                            @foreach ($subjectMetrics as $sm)
                                <div class="rounded-2xl border border-border bg-card p-5 shadow-sm">
                                    <div class="flex items-start justify-between gap-2">
                                        <div>
                                            <h3 class="font-semibold text-foreground">{{ $sm['subject_name'] }}</h3>
                                            <p class="mt-0.5 text-xs text-muted-foreground">
                                                {{ implode(', ', $sm['classes']) ?: '—' }}
                                                · {{ $sm['student_count'] }} {{ __('students') }}
                                            </p>
                                        </div>
                                        <flux:badge
                                            :color="$sm['pass_rate'] >= 70 ? 'green' : ($sm['pass_rate'] >= 50 ? 'yellow' : 'red')"
                                            size="sm"
                                        >
                                            {{ $sm['pass_rate'] }}%
                                        </flux:badge>
                                    </div>

                                    <div class="mt-4 grid grid-cols-3 gap-3 text-center">
                                        <div class="rounded-lg bg-muted/50 px-2 py-2">
                                            <p class="text-xs text-muted-foreground">{{ __('Avg') }}</p>
                                            <p class="text-lg font-semibold tabular-nums">{{ $sm['average'] }}%</p>
                                        </div>
                                        <div class="rounded-lg bg-green-50 px-2 py-2 dark:bg-green-900/20">
                                            <p class="text-xs text-green-700 dark:text-green-400">{{ __('Passed') }}</p>
                                            <p class="text-lg font-semibold tabular-nums text-green-700 dark:text-green-400">{{ $sm['pass_count'] }}</p>
                                        </div>
                                        <div class="rounded-lg bg-red-50 px-2 py-2 dark:bg-red-900/20">
                                            <p class="text-xs text-red-700 dark:text-red-400">{{ __('Failed') }}</p>
                                            <p class="text-lg font-semibold tabular-nums text-red-700 dark:text-red-400">{{ $sm['fail_count'] }}</p>
                                        </div>
                                    </div>

                                    {{-- Mini grade bar --}}
                                    @php
                                        $totalLetters = array_sum($sm['by_grade_letter']) ?: 1;
                                        $gradeColors  = ['A' => 'bg-green-500','B' => 'bg-blue-500','C' => 'bg-yellow-400','S' => 'bg-purple-400','F' => 'bg-red-500'];
                                    @endphp
                                    <div class="mt-4 flex h-2 w-full overflow-hidden rounded-full">
                                        @foreach ($sm['by_grade_letter'] as $letter => $count)
                                            @if ($count > 0)
                                                <div
                                                    class="{{ $gradeColors[$letter] ?? 'bg-zinc-400' }}"
                                                    style="width: {{ round($count / $totalLetters * 100) }}%"
                                                    title="{{ $letter }}: {{ $count }}"
                                                ></div>
                                            @endif
                                        @endforeach
                                    </div>
                                    <div class="mt-1 flex flex-wrap gap-x-3 gap-y-1">
                                        @foreach ($sm['by_grade_letter'] as $letter => $count)
                                            <span class="text-xs text-muted-foreground">{{ $letter }}: {{ $count }}</span>
                                        @endforeach
                                    </div>

                                    <div class="mt-4">
                                        <flux:button size="sm" :href="route('teacher.marks.index')" variant="ghost" wire:navigate>{{ __('View marks') }}</flux:button>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Comparative pass-rate chart --}}
                        <x-dashboard.chart-card
                            :title="__('Pass rate comparison by subject')"
                            canvas-id="subjectPassRateChart"
                        />

                        <x-charts.render :charts="[
                            [
                                'id'     => 'subjectPassRateChart',
                                'type'   => 'bar',
                                'label'  => __('Pass rate (%)'),
                                'data'   => [
                                    'labels' => collect($subjectMetrics)->pluck('subject_name')->all(),
                                    'data'   => collect($subjectMetrics)->pluck('pass_rate')->all(),
                                ],
                                'colors' => ['#7c3aed'],
                            ],
                        ]" />
                    @endif

                @else
                    <flux:callout variant="info" icon="information-circle">
                        <flux:callout.heading>{{ __('No exam data yet') }}</flux:callout.heading>
                        <flux:callout.text>{{ __('Once an exam is published and marks are entered for your subjects, performance data will appear here.') }}</flux:callout.text>
                    </flux:callout>
                @endif
            @endif

        @endif
    </div>
</x-layouts::app>
