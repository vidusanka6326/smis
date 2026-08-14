<x-layouts::app :title="__('Monthly attendance')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div>
            <flux:heading size="xl">{{ __('Monthly attendance') }}</flux:heading>
        </div>

        <x-list.filters :action="route('teacher.attendance.monthly')" :filters="$filters" :submit="__('Load')">
            <x-form.month-select :value="$month" />
            <flux:select name="school_class_id" :label="__('Class')" :placeholder="__('Select class')">
                @foreach ($schoolClasses as $class)
                    <flux:select.option :value="$class->id" :selected="(string) $selectedSchoolClassId === (string) $class->id">{{ $class->code }}</flux:select.option>
                @endforeach
            </flux:select>
        </x-list.filters>

        @if ($selectedSchoolClassId)
            <x-list.table>
                <x-slot:head>
                    <th class="px-4 py-3 font-medium">{{ __('Student') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('%') }}</th>
                </x-slot:head>
                @forelse ($rows as $row)
                    <tr class="border-t border-border">
                        <td class="px-4 py-3">{{ $row['student']->user?->name }}</td>
                        <td class="px-4 py-3">{{ $row['percentage'] }}%</td>
                    </tr>
                @empty
                    <tr class="border-t border-border">
                        <td colspan="2" class="px-4 py-10 text-center text-muted-foreground">{{ __('No data.') }}</td>
                    </tr>
                @endforelse
            </x-list.table>

            <x-list.pagination :paginator="$rows" />
        @endif
    </div>
</x-layouts::app>
