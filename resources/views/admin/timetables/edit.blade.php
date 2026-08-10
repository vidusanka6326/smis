<x-layouts::app :title="__('Edit timetable slot')">
    <div class="mx-auto flex w-full max-w-xl flex-col gap-6">
        <div>
            <flux:heading size="xl">{{ __('Edit timetable slot') }}</flux:heading>
        </div>

        <form method="POST" action="{{ route('admin.timetables.update', $entry) }}" class="flex flex-col gap-6">
            @csrf
            @method('PUT')
            <flux:select name="academic_year_id" :label="__('Academic year')" required>
                @foreach ($academicYears as $year)
                    <flux:select.option :value="$year->id" :selected="(string) old('academic_year_id', $entry->academic_year_id) === (string) $year->id">
                        {{ $year->name }}
                    </flux:select.option>
                @endforeach
            </flux:select>
            <flux:select name="school_class_id" :label="__('Class')" required>
                @foreach ($schoolClasses as $class)
                    <flux:select.option :value="$class->id" :selected="(string) old('school_class_id', $entry->school_class_id) === (string) $class->id">
                        {{ $class->code }}
                    </flux:select.option>
                @endforeach
            </flux:select>
            <flux:select name="day_of_week" :label="__('Day')" required>
                @foreach ($days as $day)
                    <flux:select.option :value="$day->value" :selected="(string) old('day_of_week', $entry->day_of_week->value) === (string) $day->value">
                        {{ $day->label() }}
                    </flux:select.option>
                @endforeach
            </flux:select>
            <flux:select name="period_number" :label="__('Period')" required>
                @foreach ($periods as $period)
                    <flux:select.option :value="$period" :selected="(string) old('period_number', $entry->period_number) === (string) $period">
                        {{ $period }}
                    </flux:select.option>
                @endforeach
            </flux:select>
            <flux:select name="subject_id" :label="__('Subject')" required>
                @foreach ($subjects as $subject)
                    <flux:select.option :value="$subject->id" :selected="(string) old('subject_id', $entry->subject_id) === (string) $subject->id">
                        {{ $subject->name }}
                    </flux:select.option>
                @endforeach
            </flux:select>
            <flux:select name="teacher_id" :label="__('Teacher')" required>
                @foreach ($teachers as $teacher)
                    <flux:select.option :value="$teacher->id" :selected="(string) old('teacher_id', $entry->teacher_id) === (string) $teacher->id">
                        {{ $teacher->user?->name }}
                    </flux:select.option>
                @endforeach
            </flux:select>

            <div class="flex gap-3">
                <flux:button type="submit" variant="primary">{{ __('Save') }}</flux:button>
                <flux:button :href="route('admin.timetables.index', ['academic_year_id' => $entry->academic_year_id, 'school_class_id' => $entry->school_class_id])" variant="ghost" wire:navigate>
                    {{ __('Cancel') }}
                </flux:button>
            </div>
        </form>
    </div>
</x-layouts::app>
