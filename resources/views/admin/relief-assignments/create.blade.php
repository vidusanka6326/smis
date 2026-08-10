<x-layouts::app :title="__('Assign relief teacher')">
    <div class="mx-auto flex w-full max-w-xl flex-col gap-6">
        <div>
            <flux:heading size="xl">{{ __('Assign relief teacher') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Date must match the timetable entry weekday.') }}</flux:text>
        </div>

        <form method="POST" action="{{ route('admin.relief-assignments.store') }}" class="flex flex-col gap-6">
            @csrf
            <flux:select name="timetable_entry_id" :label="__('Timetable slot')" required>
                @foreach ($entries as $entry)
                    <flux:select.option :value="$entry->id" :selected="(string) old('timetable_entry_id') === (string) $entry->id">
                        {{ $entry->schoolClass?->code }} · {{ $entry->day_of_week->label() }} P{{ $entry->period_number }} · {{ $entry->subject?->name }} ({{ $entry->teacher?->user?->name }})
                    </flux:select.option>
                @endforeach
            </flux:select>
            <flux:select name="relief_teacher_id" :label="__('Relief teacher')" required>
                @foreach ($teachers as $teacher)
                    <flux:select.option :value="$teacher->id" :selected="(string) old('relief_teacher_id') === (string) $teacher->id">
                        {{ $teacher->user?->name }} ({{ $teacher->employee_no }})
                    </flux:select.option>
                @endforeach
            </flux:select>
            <flux:input name="date" type="date" :label="__('Date')" :value="old('date')" required />
            <flux:input name="reason" :label="__('Reason')" :value="old('reason')" />

            <div class="flex gap-3">
                <flux:button type="submit" variant="primary">{{ __('Assign') }}</flux:button>
                <flux:button :href="route('admin.relief-assignments.index')" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </div>
</x-layouts::app>
