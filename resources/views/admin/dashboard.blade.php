@php
    $greeting = now()->format('l');
@endphp

<x-layouts::app :title="__('Admin Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-8">
        @if (session('status'))
            <flux:callout variant="success" icon="check-circle">
                <flux:callout.heading>{{ session('status') }}</flux:callout.heading>
            </flux:callout>
        @endif

        <section class="relative overflow-hidden rounded-3xl bg-primary px-6 py-8 text-primary-foreground sm:px-8">
            <div class="pointer-events-none absolute -right-8 -top-12 size-52 rounded-full bg-white/10"></div>
            <div class="pointer-events-none absolute -bottom-20 left-1/3 size-64 rounded-full bg-white/5"></div>

            <div class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-xl">
                    <p class="text-sm font-medium text-primary-foreground/75">{{ $greeting }}</p>
                    <h1 class="mt-1 text-3xl font-semibold tracking-tight sm:text-4xl">{{ __('School at a glance') }}</h1>
                    <p class="mt-2 text-sm text-primary-foreground/80">
                        {{ __(':students students · :teachers teachers · :classes classes', [
                            'students' => $stats['students'],
                            'teachers' => $stats['teachers'],
                            'classes' => $stats['classes'],
                        ]) }}
                    </p>
                    <div class="mt-5 flex flex-wrap gap-2">
                        <flux:button :href="route('admin.reports.dashboard')" variant="filled" class="!bg-white !text-primary" wire:navigate>{{ __('Reports') }}</flux:button>
                        <flux:button :href="route('admin.students.index')" variant="ghost" class="!text-white hover:!bg-white/10" wire:navigate>{{ __('Students') }}</flux:button>
                        <flux:button :href="route('admin.activity-logs.index')" variant="ghost" class="!text-white hover:!bg-white/10" wire:navigate>{{ __('Activity') }}</flux:button>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-2xl bg-white/10 px-5 py-4 backdrop-blur-sm">
                        <p class="text-xs uppercase tracking-wide text-primary-foreground/70">{{ __('Attendance') }}</p>
                        <p class="mt-1 text-3xl font-semibold tabular-nums">
                            {{ $stats['avg_attendance'] !== null ? $stats['avg_attendance'].'%' : '—' }}
                        </p>
                    </div>
                    <div class="rounded-2xl bg-white/10 px-5 py-4 backdrop-blur-sm">
                        <p class="text-xs uppercase tracking-wide text-primary-foreground/70">{{ __('At risk') }}</p>
                        <p class="mt-1 text-3xl font-semibold tabular-nums">{{ $stats['at_risk_count'] }}</p>
                    </div>
                </div>
            </div>
        </section>

        <div class="grid gap-4 sm:grid-cols-3">
            <x-dashboard.stat
                :label="__('Exam pass rate')"
                :value="$stats['pass_rate'] !== null ? $stats['pass_rate'].'%' : '—'"
                :hint="$exam?->name ?? __('No published exam yet')"
                tone="success"
            />
            <x-dashboard.stat
                :label="__('Draft exams')"
                :value="$stats['draft_exams']"
                :hint="__(':count published', ['count' => $stats['published_exams']])"
            />
            <x-dashboard.stat
                :label="__('Boys / Girls')"
                :value="$stats['boys'].' / '.$stats['girls']"
            />
        </div>

        <div class="grid gap-4 lg:grid-cols-12">
            <x-dashboard.chart-card
                :title="__('Attendance by class')"
                canvas-id="adminAttendanceChart"
                class="lg:col-span-7"
            />
            <x-dashboard.chart-card
                :title="__('Latest exam letters')"
                canvas-id="adminLettersChart"
                class="lg:col-span-5"
            />
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <x-dashboard.panel :title="__('Needs attention')">
                <ul class="space-y-2 text-sm">
                    @forelse ($atRiskPreview as $row)
                        <li class="flex items-baseline justify-between gap-2 border-b border-border/60 pb-2 last:border-0">
                            <span>{{ $row['name'] }} <span class="text-muted-foreground">· {{ $row['class'] }}</span></span>
                            <span class="font-semibold text-amber-600 dark:text-amber-400">{{ $row['percentage'] }}%</span>
                        </li>
                    @empty
                        <li class="text-muted-foreground">{{ __('Attendance looks healthy this month.') }}</li>
                    @endforelse
                </ul>
                <div class="mt-4 flex flex-wrap gap-2">
                    <flux:button size="sm" :href="route('admin.reports.attendance')" variant="ghost" wire:navigate>{{ __('Attendance report') }}</flux:button>
                    @if ($draftExams->isNotEmpty())
                        <flux:button size="sm" :href="route('admin.exams.index')" variant="ghost" wire:navigate>
                            {{ __(':count drafts waiting', ['count' => $draftExams->count()]) }}
                        </flux:button>
                    @endif
                </div>
            </x-dashboard.panel>

            <x-dashboard.panel :title="__('Recent activity')">
                <ul class="space-y-3 text-sm">
                    @forelse ($recentActivity->take(5) as $log)
                        <li class="border-b border-border/60 pb-3 last:border-0">
                            <div class="font-medium">{{ $log->description }}</div>
                            <div class="mt-0.5 text-muted-foreground">
                                {{ $log->causer?->name ?? '—' }}
                                · {{ $log->created_at?->diffForHumans() }}
                            </div>
                        </li>
                    @empty
                        <li class="text-muted-foreground">{{ __('No activity logged yet.') }}</li>
                    @endforelse
                </ul>
            </x-dashboard.panel>
        </div>

        <x-charts.render :charts="[
            [
                'id' => 'adminAttendanceChart',
                'type' => 'bar',
                'label' => __('%'),
                'data' => $charts['attendance_by_class'],
                'colors' => ['#0f6b6d'],
            ],
            [
                'id' => 'adminLettersChart',
                'type' => 'bar',
                'label' => __('Count'),
                'data' => $charts['letters'],
                'colors' => ['#2da8a8', '#0f6b6d', '#7ed3b2', '#256396', '#5a787e'],
            ],
        ]" />
    </div>
</x-layouts::app>
