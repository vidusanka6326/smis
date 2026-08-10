<x-layouts::app :title="__('Class timetables')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <flux:heading size="xl">{{ __('Class timetables') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Build weekly grids with conflict detection (teacher cannot be in two places).') }}</flux:text>
            </div>
            <flux:button :href="route('admin.relief-assignments.index')" variant="filled" wire:navigate>
                {{ __('Relief assignments') }}
            </flux:button>
        </div>

        @if (session('status'))
            <flux:callout variant="success" icon="check-circle">
                <flux:callout.heading>{{ session('status') }}</flux:callout.heading>
            </flux:callout>
        @endif

        @if ($errors->any())
            <flux:callout variant="danger" icon="x-circle">
                <flux:callout.heading>{{ $errors->first() }}</flux:callout.heading>
            </flux:callout>
        @endif

        <form method="GET" action="{{ route('admin.timetables.index') }}" class="flex flex-wrap items-end gap-3">
            <flux:select name="academic_year_id" :label="__('Academic year')">
                @foreach ($academicYears as $year)
                    <flux:select.option :value="$year->id" :selected="(string) $selectedAcademicYearId === (string) $year->id">
                        {{ $year->name }}
                    </flux:select.option>
                @endforeach
            </flux:select>
            <flux:select name="school_class_id" :label="__('Class')">
                @foreach ($schoolClasses as $class)
                    <flux:select.option :value="$class->id" :selected="(string) $selectedSchoolClassId === (string) $class->id">
                        {{ $class->code }}
                    </flux:select.option>
                @endforeach
            </flux:select>
            <flux:button type="submit" variant="filled">{{ __('Load') }}</flux:button>
        </form>

        @if ($schoolClass)
            <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
                <table class="min-w-full text-sm">
                    <thead class="bg-zinc-50 dark:bg-zinc-900">
                        <tr>
                            <th class="px-3 py-2 text-left">{{ __('Period') }}</th>
                            @foreach ($days as $day)
                                <th class="px-3 py-2 text-left">{{ $day->label() }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($periods as $period)
                            <tr class="border-t border-zinc-200 dark:border-zinc-700 align-top">
                                <td class="px-3 py-3 font-medium">{{ $period }}</td>
                                @foreach ($days as $day)
                                    @php($slot = $grid[$day->value][$period] ?? null)
                                    <td class="px-3 py-3">
                                        @if ($slot)
                                            <div class="space-y-1">
                                                <div>{{ $slot->subject?->name }}</div>
                                                <div class="text-zinc-500">{{ $slot->teacher?->user?->name }}</div>
                                                <div class="flex gap-2">
                                                    <a class="text-xs underline" href="{{ route('admin.timetables.edit', $slot) }}">{{ __('Edit') }}</a>
                                                    <form method="POST" action="{{ route('admin.timetables.destroy', $slot) }}" onsubmit="return confirm(@js(__('Delete this slot?')))">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-xs text-red-600 underline">{{ __('Delete') }}</button>
                                                    </form>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-zinc-400">—</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <form method="POST" action="{{ route('admin.timetables.store') }}" class="grid gap-4 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700 md:grid-cols-3">
                @csrf
                <input type="hidden" name="academic_year_id" value="{{ $selectedAcademicYearId }}">
                <input type="hidden" name="school_class_id" value="{{ $selectedSchoolClassId }}">
                <flux:heading size="sm" class="md:col-span-3">{{ __('Add slot for :class', ['class' => $schoolClass->code]) }}</flux:heading>

                <flux:select name="day_of_week" :label="__('Day')" required>
                    @foreach ($days as $day)
                        <flux:select.option :value="$day->value" :selected="(string) old('day_of_week') === (string) $day->value">
                            {{ $day->label() }}
                        </flux:select.option>
                    @endforeach
                </flux:select>
                <flux:select name="period_number" :label="__('Period')" required>
                    @foreach ($periods as $period)
                        <flux:select.option :value="$period" :selected="(string) old('period_number') === (string) $period">
                            {{ $period }}
                        </flux:select.option>
                    @endforeach
                </flux:select>
                <flux:select name="subject_id" :label="__('Subject')" required>
                    @foreach ($schoolClass->subjects as $subject)
                        <flux:select.option :value="$subject->id" :selected="(string) old('subject_id') === (string) $subject->id">
                            {{ $subject->name }}
                        </flux:select.option>
                    @endforeach
                </flux:select>
                <flux:select name="teacher_id" :label="__('Teacher')" required class="md:col-span-2">
                    @foreach ($teachers as $teacher)
                        <flux:select.option :value="$teacher->id" :selected="(string) old('teacher_id') === (string) $teacher->id">
                            {{ $teacher->user?->name }} ({{ $teacher->employee_no }})
                        </flux:select.option>
                    @endforeach
                </flux:select>
                <div class="flex items-end">
                    <flux:button type="submit" variant="primary">{{ __('Add slot') }}</flux:button>
                </div>
            </form>
        @endif
    </div>
</x-layouts::app>
