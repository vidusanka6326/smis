@php
    $greeting = now()->format('l');
@endphp

<x-layouts::app :title="__('Student Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-8">
        @if (! $student)
            <flux:callout variant="warning" icon="exclamation-triangle">
                <flux:callout.heading>{{ __('No student profile is linked to this account yet.') }}</flux:callout.heading>
            </flux:callout>
        @else
            <section class="relative overflow-hidden rounded-3xl bg-primary px-6 py-8 text-primary-foreground sm:px-8">
                <div class="pointer-events-none absolute -right-10 -top-10 size-48 rounded-full bg-white/10"></div>
                <div class="pointer-events-none absolute -bottom-16 right-24 size-56 rounded-full bg-white/5"></div>

                <div class="relative flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-sm font-medium text-primary-foreground/75">{{ $greeting }}</p>
                        <h1 class="mt-1 text-3xl font-semibold tracking-tight sm:text-4xl">
                            {{ $student->user?->name ?? __('Your day') }}
                        </h1>
                        <p class="mt-2 text-sm text-primary-foreground/80">
                            {{ $student->currentClass?->code ?? __('No class') }}
                            @if ($student->currentClass?->grade)
                                · {{ $student->currentClass->grade->name }}
                            @endif
                            · {{ $student->admission_no }}
                        </p>
                        <div class="mt-5 flex flex-wrap gap-2">
                            <flux:button :href="route('student.timetable')" variant="filled" class="!bg-white !text-primary" wire:navigate>{{ __('Timetable') }}</flux:button>
                            <flux:button :href="route('student.exam-schedule')" variant="ghost" class="!text-white hover:!bg-white/10" wire:navigate>{{ __('Exam schedule') }}</flux:button>
                            <flux:button :href="route('student.reports')" variant="ghost" class="!text-white hover:!bg-white/10" wire:navigate>{{ __('Reports') }}</flux:button>
                            <flux:button :href="route('student.results')" variant="ghost" class="!text-white hover:!bg-white/10" wire:navigate>{{ __('Results') }}</flux:button>
                        </div>
                    </div>

                    <div class="rounded-2xl bg-white/10 px-6 py-5 backdrop-blur-sm">
                        <p class="text-xs font-medium uppercase tracking-wide text-primary-foreground/70">{{ __('This month') }}</p>
                        <p class="mt-1 text-5xl font-semibold tabular-nums">
                            {{ $stats['attendance_percent'] !== null ? $stats['attendance_percent'].'%' : '—' }}
                        </p>
                        <p class="mt-1 text-sm text-primary-foreground/75">
                            {{ __('Present :p · Absent :a', ['p' => $stats['present'], 'a' => $stats['absent']]) }}
                        </p>
                    </div>
                </div>
            </section>

            <div class="grid gap-4 sm:grid-cols-3">
                <x-dashboard.stat
                    :label="__('Exam average')"
                    :value="$stats['overall_average'] !== null ? $stats['overall_average'].'%' : '—'"
                    :hint="__('Pass :p · Fail :f', ['p' => $stats['pass_count'], 'f' => $stats['fail_count']])"
                    tone="success"
                />
                <x-dashboard.stat
                    :label="__('Published marks')"
                    :value="$stats['published_marks']"
                />
                <x-dashboard.stat
                    :label="__('Lessons today')"
                    :value="$stats['lessons_today']"
                />
            </div>

            <div class="grid gap-4 lg:grid-cols-12">
                <x-dashboard.panel :title="__('Today')" class="lg:col-span-5">
                    <ul class="space-y-2 text-sm">
                        @forelse ($todaySlots as $slot)
                            <li class="flex items-start justify-between gap-3 rounded-xl bg-muted/60 px-3 py-2.5">
                                <div>
                                    <div class="font-medium">{{ $slot->subject?->name }}</div>
                                    <div class="text-muted-foreground">{{ $slot->teacher?->user?->name }}</div>
                                </div>
                                <span class="shrink-0 rounded-md bg-background px-2 py-0.5 text-xs font-medium">P{{ $slot->period_number }}</span>
                            </li>
                        @empty
                            <li class="rounded-xl border border-dashed border-border px-3 py-8 text-center text-muted-foreground">
                                {{ __('Nothing scheduled for today.') }}
                            </li>
                        @endforelse
                    </ul>
                </x-dashboard.panel>

                <x-dashboard.chart-card
                    :title="__('Subject averages')"
                    canvas-id="studentSubjectsChart"
                    class="lg:col-span-7"
                />
            </div>

            <x-dashboard.panel :title="__('Latest results')">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-muted-foreground">
                                <th class="py-2 pe-3">{{ __('Exam') }}</th>
                                <th class="py-2 pe-3">{{ __('Subject') }}</th>
                                <th class="py-2 pe-3">{{ __('Marks') }}</th>
                                <th class="py-2">{{ __('Grade') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentMarks->take(6) as $mark)
                                <tr class="border-t border-border">
                                    <td class="py-2.5 pe-3">{{ $mark->examSubject?->exam?->name }}</td>
                                    <td class="py-2.5 pe-3">{{ $mark->examSubject?->subject?->name }}</td>
                                    <td class="py-2.5 pe-3">{{ $mark->marks_obtained }} / {{ $mark->examSubject?->max_marks }}</td>
                                    <td class="py-2.5 font-medium">{{ $mark->grade_letter->value }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-6 text-muted-foreground">{{ __('No published results yet.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($failedMarks->isNotEmpty())
                    <div class="mt-4 rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:bg-amber-950/40 dark:text-amber-200">
                        {{ __('Focus on: :subjects', [
                            'subjects' => $failedMarks->take(3)->map(fn ($m) => $m->examSubject?->subject?->name)->filter()->implode(', '),
                        ]) }}
                    </div>
                @endif
            </x-dashboard.panel>

            <x-charts.render :charts="[
                [
                    'id' => 'studentSubjectsChart',
                    'type' => 'bar',
                    'label' => __('Avg %'),
                    'data' => $charts['subject_averages'],
                    'colors' => ['#0f6b6d'],
                ],
            ]" />
        @endif
    </div>
</x-layouts::app>
