<x-layouts::app :title="__('Student Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <flux:heading size="xl">{{ __('Student Dashboard') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Your attendance, results, and timetable for today.') }}</flux:text>
            </div>
            <div class="flex flex-wrap gap-2">
                <flux:button :href="route('student.timetable')" variant="primary" wire:navigate>{{ __('My timetable') }}</flux:button>
                <flux:button :href="route('student.results')" variant="filled" wire:navigate>{{ __('My results') }}</flux:button>
                <flux:button :href="route('student.attendance')" variant="filled" wire:navigate>{{ __('My attendance') }}</flux:button>
            </div>
        </div>

        @if (! $student)
            <flux:callout variant="warning" icon="exclamation-triangle">
                <flux:callout.heading>{{ __('No student profile is linked to this account yet.') }}</flux:callout.heading>
            </flux:callout>
        @else
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <x-dashboard.stat
                    :label="__('Attendance (month)')"
                    :value="$stats['attendance_percent'] !== null ? $stats['attendance_percent'].'%' : '—'"
                    :hint="__(':count sessions recorded', ['count' => $stats['sessions_this_month']])"
                />
                <x-dashboard.stat :label="__('Published marks')" :value="$stats['published_marks']" />
                <x-dashboard.stat :label="__('Subjects')" :value="$stats['subjects']" />
                <x-dashboard.stat :label="__('Class')" :value="$student->currentClass?->code ?? '—'" :hint="$student->currentClass?->grade?->name" />
            </div>

            <div class="grid gap-4 lg:grid-cols-3">
                <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                    <flux:heading size="sm">{{ __('Profile') }}</flux:heading>
                    <dl class="mt-3 space-y-2 text-sm">
                        <div><dt class="text-zinc-500">{{ __('Admission no.') }}</dt><dd>{{ $student->admission_no }}</dd></div>
                        <div><dt class="text-zinc-500">{{ __('Gender') }}</dt><dd>{{ $student->gender->label() }}</dd></div>
                        <div><dt class="text-zinc-500">{{ __('Grade') }}</dt><dd>{{ $student->currentClass?->grade?->name ?? '—' }}</dd></div>
                    </dl>
                </div>

                <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                    <flux:heading size="sm">{{ __('Timetable today') }}</flux:heading>
                    <ul class="mt-3 space-y-2 text-sm">
                        @forelse ($todaySlots as $slot)
                            <li class="rounded-lg border border-zinc-100 px-3 py-2 dark:border-zinc-800">
                                <div class="font-medium">P{{ $slot->period_number }} — {{ $slot->subject?->name }}</div>
                                <div class="text-zinc-500">{{ $slot->teacher?->user?->name }}</div>
                            </li>
                        @empty
                            <li class="text-zinc-500">{{ __('No lessons scheduled for today.') }}</li>
                        @endforelse
                    </ul>
                </div>

                <x-dashboard.chart-card :title="__('My grade letters')" canvas-id="studentLettersChart" />
            </div>

            <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="sm">{{ __('Recent published results') }}</flux:heading>
                <div class="mt-3 overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-zinc-500">
                                <th class="py-2 pe-3">{{ __('Exam') }}</th>
                                <th class="py-2 pe-3">{{ __('Subject') }}</th>
                                <th class="py-2 pe-3">{{ __('Marks') }}</th>
                                <th class="py-2">{{ __('Grade') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentMarks as $mark)
                                <tr class="border-t border-zinc-100 dark:border-zinc-800">
                                    <td class="py-2 pe-3">{{ $mark->examSubject?->exam?->name }}</td>
                                    <td class="py-2 pe-3">{{ $mark->examSubject?->subject?->name }}</td>
                                    <td class="py-2 pe-3">{{ $mark->marks_obtained }}</td>
                                    <td class="py-2">{{ $mark->grade_letter->value }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-4 text-zinc-500">{{ __('No published results yet.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <x-charts.render :charts="[
                [
                    'id' => 'studentLettersChart',
                    'type' => 'doughnut',
                    'data' => $charts['letters'],
                    'colors' => ['#0f766e', '#2563eb', '#ca8a04', '#db2777', '#7c3aed'],
                ],
            ]" />
        @endif
    </div>
</x-layouts::app>
