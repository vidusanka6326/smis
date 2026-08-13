<x-layouts::app :title="__('Assign relief teacher')">
    <x-form.page
        :title="__('Assign relief teacher')"
        :description="__('Date must match the timetable entry weekday.')"
    >
        <form method="POST" action="{{ route('admin.relief-assignments.store') }}" class="flex flex-col gap-6">
            @csrf

            <x-form.section :title="__('Cover details')">
                <x-form.grid>
                    <x-form.full>
                        <flux:select name="timetable_entry_id" :label="__('Timetable slot')" required>
                            @foreach ($entries as $entry)
                                <flux:select.option :value="$entry->id" :selected="(string) old('timetable_entry_id') === (string) $entry->id">
                                    {{ $entry->schoolClass?->code }} · {{ $entry->day_of_week->label() }} P{{ $entry->period_number }} · {{ $entry->subject?->name }} ({{ $entry->teacher?->user?->name }})
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                    </x-form.full>
                    <flux:select name="relief_teacher_id" :label="__('Relief teacher')" required>
                        @foreach ($teachers as $teacher)
                            <flux:select.option :value="$teacher->id" :selected="(string) old('relief_teacher_id') === (string) $teacher->id">
                                {{ $teacher->user?->name }} ({{ $teacher->employee_no }})
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:input name="date" type="date" :label="__('Date')" :value="old('date')" required />
                    <x-form.full>
                        <flux:input name="reason" :label="__('Reason')" :value="old('reason')" />
                    </x-form.full>
                </x-form.grid>
            </x-form.section>

            <x-form.actions>
                <flux:button type="submit" variant="primary">{{ __('Assign') }}</flux:button>
                <flux:button :href="route('admin.relief-assignments.index')" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
            </x-form.actions>
        </form>
    </x-form.page>
</x-layouts::app>
