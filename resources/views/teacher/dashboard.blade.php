@php
    $greeting = now()->format('l');
@endphp

<x-layouts::app :title="__('Teacher Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-8">
        @if (! $teacher)
            <flux:callout variant="warning" icon="exclamation-triangle">
                <flux:callout.heading>{{ __('No teacher profile is linked to this account yet.') }}</flux:callout.heading>
            </flux:callout>
        @else
            {{-- Hero --}}
            <section class="relative overflow-hidden rounded-3xl bg-primary px-6 py-8 text-primary-foreground sm:px-8">
                <div class="pointer-events-none absolute -right-10 -top-10 size-48 rounded-full bg-white/10"></div>
                <div class="pointer-events-none absolute -bottom-16 right-20 size-56 rounded-full bg-white/5"></div>

                <div class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                    <div class="max-w-xl">
                        <p class="text-sm font-medium text-primary-foreground/75">{{ __('Happy :day', ['day' => $greeting]) }}</p>
                        <h1 class="mt-1 text-3xl font-semibold tracking-tight sm:text-4xl">{{ __('Here’s your class pulse') }}</h1>
                        <p class="mt-2 text-sm text-primary-foreground/80">
                            {{ __(':students students across :classes classes · :homerooms homeroom', [
                                'students' => $stats['students'],
                                'classes' => $stats['classes'],
                                'homerooms' => $stats['homerooms'],
                            ]) }}
                        </p>
                        <div class="mt-5 flex flex-wrap gap-2">
                            <flux:button :href="route('teacher.students.index')" variant="filled" class="!bg-white !text-primary" wire:navigate>{{ __('My students') }}</flux:button>
                            <flux:button :href="route('teacher.attendance.sessions.index')" variant="ghost" class="!text-white hover:!bg-white/10" wire:navigate>{{ __('Take attendance') }}</flux:button>
                            <flux:button :href="route('teacher.reports.dashboard')" variant="ghost" class="!text-white hover:!bg-white/10" wire:navigate>{{ __('Reports') }}</flux:button>
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

            {{-- Three signals only --}}
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

            {{-- One chart + today + focus --}}
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
                    'id' => 'teacherAttendanceChart',
                    'type' => 'bar',
                    'label' => __('%'),
                    'data' => $charts['attendance_by_class'],
                    'colors' => ['#0f6b6d'],
                ],
            ]" />
        @endif
    </div>
</x-layouts::app>
