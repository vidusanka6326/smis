<x-layouts::app :title="__('Teacher Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <flux:heading size="xl">{{ __('Teacher Dashboard') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Your classes, lessons today, and scoped analytics.') }}</flux:text>
            </div>
            <div class="flex flex-wrap gap-2">
                <flux:button :href="route('teacher.students.index')" variant="primary" wire:navigate>{{ __('My students') }}</flux:button>
                <flux:button :href="route('teacher.timetable')" variant="filled" wire:navigate>{{ __('My timetable') }}</flux:button>
                <flux:button :href="route('teacher.reports.dashboard')" variant="filled" wire:navigate>{{ __('Reports') }}</flux:button>
            </div>
        </div>

        @if (! $teacher)
            <flux:callout variant="warning" icon="exclamation-triangle">
                <flux:callout.heading>{{ __('No teacher profile is linked to this account yet.') }}</flux:callout.heading>
            </flux:callout>
        @else
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <x-dashboard.stat :label="__('Students in scope')" :value="$stats['students']" />
                <x-dashboard.stat :label="__('Classes')" :value="$stats['classes']" />
                <x-dashboard.stat :label="__('Assignments')" :value="$stats['assignments']" />
                <x-dashboard.stat
                    :label="__('Avg attendance (month)')"
                    :value="$stats['avg_attendance'] !== null ? $stats['avg_attendance'].'%' : '—'"
                />
            </div>

            <div class="grid gap-4 lg:grid-cols-3">
                <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 lg:col-span-1">
                    <flux:heading size="sm">{{ __('Lessons today') }}</flux:heading>
                    <ul class="mt-3 space-y-2 text-sm">
                        @forelse ($todaySlots as $slot)
                            <li class="rounded-lg border border-zinc-100 px-3 py-2 dark:border-zinc-800">
                                <div class="font-medium">P{{ $slot->period_number }} — {{ $slot->schoolClass?->code }}</div>
                                <div class="text-zinc-500">{{ $slot->subject?->name }}</div>
                            </li>
                        @empty
                            <li class="text-zinc-500">{{ __('No lessons scheduled for today.') }}</li>
                        @endforelse
                    </ul>
                </div>

                <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 lg:col-span-1">
                    <flux:heading size="sm">{{ __('Homeroom') }}</flux:heading>
                    <ul class="mt-3 space-y-1 text-sm">
                        @forelse ($teacher->homeroomClasses as $class)
                            <li>{{ $class->code }} — {{ $class->grade?->name }}</li>
                        @empty
                            <li class="text-zinc-500">{{ __('No homeroom classes assigned.') }}</li>
                        @endforelse
                    </ul>
                </div>

                <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 lg:col-span-1">
                    <flux:heading size="sm">{{ __('Teaching assignments') }}</flux:heading>
                    <ul class="mt-3 max-h-48 space-y-1 overflow-y-auto text-sm">
                        @forelse ($teacher->assignments as $assignment)
                            <li>
                                {{ $assignment->role_in_assignment->label() }} —
                                {{ $assignment->schoolClass?->code }}
                                @if ($assignment->subject)
                                    — {{ $assignment->subject->name }}
                                @endif
                            </li>
                        @empty
                            <li class="text-zinc-500">{{ __('No assignments yet.') }}</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <div class="grid gap-4 lg:grid-cols-3">
                <x-dashboard.chart-card :title="__('Gender mix (my classes)')" canvas-id="teacherGenderChart" />
                <x-dashboard.chart-card :title="__('Attendance % by class')" canvas-id="teacherAttendanceChart" />
                <x-dashboard.chart-card :title="__('Grade letters (latest exam)')" canvas-id="teacherLettersChart" />
            </div>

            <x-charts.render :charts="[
                [
                    'id' => 'teacherGenderChart',
                    'type' => 'doughnut',
                    'data' => $charts['gender'],
                    'colors' => ['#2563eb', '#db2777'],
                ],
                [
                    'id' => 'teacherAttendanceChart',
                    'type' => 'bar',
                    'label' => __('%'),
                    'data' => $charts['attendance_by_class'],
                    'colors' => ['#0f766e'],
                ],
                [
                    'id' => 'teacherLettersChart',
                    'type' => 'bar',
                    'label' => __('Count'),
                    'data' => $charts['letters'],
                    'colors' => ['#ca8a04'],
                ],
            ]" />
        @endif
    </div>
</x-layouts::app>
