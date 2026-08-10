<x-layouts::app :title="__('Class timetables')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <flux:heading size="xl">{{ __('Class timetables') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Weekly period grid with conflict detection (a teacher cannot be in two places).') }}</flux:text>
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
            <div class="flex flex-wrap items-end justify-between gap-2">
                <div>
                    <flux:heading size="lg">{{ $schoolClass->code }}</flux:heading>
                    <flux:text>{{ __('Mon–Fri · 8 periods · times are school defaults') }}</flux:text>
                </div>
            </div>

            <x-timetable.grid
                :days="$days"
                :periods="$periods"
                :grid="$grid"
                :period-times="$periodTimes"
                variant="admin"
            />

            <form method="POST" action="{{ route('admin.timetables.store') }}" class="grid gap-4 rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 md:grid-cols-3">
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
                            P{{ $period }}
                            @if (! empty($periodTimes[$period]['label']))
                                ({{ $periodTimes[$period]['label'] }})
                            @endif
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
