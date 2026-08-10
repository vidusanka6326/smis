<x-layouts::app :title="__('My timetable')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div>
            <flux:heading size="xl">{{ __('My timetable') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Derived from class timetables for your teaching slots.') }}</flux:text>
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
                                        <div>{{ $slot->schoolClass?->code }}</div>
                                        <div class="text-zinc-500">{{ $slot->subject?->name }}</div>
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
    </div>
</x-layouts::app>
