<x-layouts::app :title="__('Monthly attendance')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div>
            <flux:heading size="xl">{{ __('Monthly attendance') }}</flux:heading>
        </div>

        <form method="GET" action="{{ route('teacher.attendance.monthly') }}" class="flex flex-wrap items-end gap-3">
            <x-form.month-select :value="$month" />
            <flux:select name="school_class_id" :label="__('Class')">
                <flux:select.option value="">{{ __('Select class') }}</flux:select.option>
                @foreach ($schoolClasses as $class)
                    <flux:select.option :value="$class->id" :selected="(string) $selectedSchoolClassId === (string) $class->id">{{ $class->code }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:button type="submit" variant="filled">{{ __('Load') }}</flux:button>
        </form>

        @if ($selectedSchoolClassId)
            <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
                <table class="min-w-full text-sm">
                    <thead class="bg-zinc-50 dark:bg-zinc-900">
                        <tr>
                            <th class="px-3 py-2 text-left">{{ __('Student') }}</th>
                            <th class="px-3 py-2 text-left">{{ __('%') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $row)
                            <tr class="border-t border-zinc-200 dark:border-zinc-700">
                                <td class="px-3 py-2">{{ $row['student']->user?->name }}</td>
                                <td class="px-3 py-2">{{ $row['percentage'] }}%</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="px-3 py-6 text-zinc-500">{{ __('No data.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-layouts::app>
