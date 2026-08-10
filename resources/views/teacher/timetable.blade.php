<x-layouts::app :title="__('My timetable')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div>
            <flux:heading size="xl">{{ __('My timetable') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Weekly teaching grid derived from class timetables.') }}</flux:text>
        </div>

        <form method="GET" action="{{ route('teacher.timetable') }}" class="flex flex-wrap items-end gap-3">
            <flux:select name="academic_year_id" :label="__('Academic year')">
                @foreach ($academicYears as $year)
                    <flux:select.option :value="$year->id" :selected="(string) $selectedAcademicYearId === (string) $year->id">
                        {{ $year->name }}
                    </flux:select.option>
                @endforeach
            </flux:select>
            <flux:button type="submit" variant="filled">{{ __('Load') }}</flux:button>
        </form>

        <x-timetable.grid
            :days="$days"
            :periods="$periods"
            :grid="$grid"
            :period-times="$periodTimes"
            variant="teacher"
        />
    </div>
</x-layouts::app>
