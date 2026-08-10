<x-layouts::app :title="__('Monthly attendance')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div>
            <flux:heading size="xl">{{ __('Monthly attendance summary') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Present + late count as attended; excused days are excluded from the percentage.') }}</flux:text>
        </div>

        <form method="GET" action="{{ route('admin.attendance.monthly') }}" class="flex flex-wrap items-end gap-3">
            <flux:input type="month" name="month" :label="__('Month')" :value="$month" />
            <flux:select name="school_class_id" :label="__('Class')">
                <flux:select.option value="">{{ __('Select class') }}</flux:select.option>
                @foreach ($schoolClasses as $class)
                    <flux:select.option :value="$class->id" :selected="(string) $selectedSchoolClassId === (string) $class->id">{{ $class->code }}</flux:select.option>
                @endforeach
            </flux:select>
            @if ($schoolClass)
                <flux:select name="subject_id" :label="__('Scope')">
                    <flux:select.option value="">{{ __('Class attendance') }}</flux:select.option>
                    @foreach ($schoolClass->subjects as $subject)
                        <flux:select.option :value="$subject->id" :selected="(string) $selectedSubjectId === (string) $subject->id">{{ $subject->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            @endif
            <flux:button type="submit" variant="filled">{{ __('Load') }}</flux:button>
        </form>

        @if ($selectedSchoolClassId)
            <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
                <table class="min-w-full text-sm">
                    <thead class="bg-zinc-50 dark:bg-zinc-900">
                        <tr>
                            <th class="px-3 py-2 text-left">{{ __('Student') }}</th>
                            <th class="px-3 py-2 text-left">{{ __('Present') }}</th>
                            <th class="px-3 py-2 text-left">{{ __('Absent') }}</th>
                            <th class="px-3 py-2 text-left">{{ __('Late') }}</th>
                            <th class="px-3 py-2 text-left">{{ __('Excused') }}</th>
                            <th class="px-3 py-2 text-left">{{ __('%') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $row)
                            <tr class="border-t border-zinc-200 dark:border-zinc-700">
                                <td class="px-3 py-2">{{ $row['student']->user?->name }}</td>
                                <td class="px-3 py-2">{{ $row['counts']['present'] }}</td>
                                <td class="px-3 py-2">{{ $row['counts']['absent'] }}</td>
                                <td class="px-3 py-2">{{ $row['counts']['late'] }}</td>
                                <td class="px-3 py-2">{{ $row['counts']['excused'] }}</td>
                                <td class="px-3 py-2">{{ $row['percentage'] }}%</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-3 py-6 text-zinc-500">{{ __('No students or attendance for this month.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-layouts::app>
