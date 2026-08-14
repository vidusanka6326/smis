<x-layouts::app :title="__('Relief assignments')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <flux:heading size="xl">{{ __('Relief teacher assignments') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Manual relief allocation with conflict checks.') }}</flux:text>
            </div>
            <div class="flex gap-2">
                <flux:button :href="route('admin.timetables.index')" variant="ghost" wire:navigate>{{ __('Timetables') }}</flux:button>
                <flux:button :href="route('admin.relief-assignments.create')" variant="primary" wire:navigate>{{ __('Assign relief') }}</flux:button>
            </div>
        </div>

        <x-list.flash />

        <x-list.filters :action="route('admin.relief-assignments.index')" :filters="$filters">
            <flux:input name="search" :label="__('Reason')" :value="$filters['search'] ?? ''" placeholder="{{ __('Search reason') }}" />
            <flux:select name="school_class_id" :label="__('Class')" :placeholder="__('All')">
                @foreach ($schoolClasses as $class)
                    <flux:select.option :value="$class->id" :selected="(string) ($filters['school_class_id'] ?? '') === (string) $class->id">{{ $class->code }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:select name="teacher_id" :label="__('Teacher')" :placeholder="__('All')">
                @foreach ($teachers as $teacher)
                    <flux:select.option :value="$teacher->id" :selected="(string) ($filters['teacher_id'] ?? '') === (string) $teacher->id">{{ $teacher->user?->name }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:input type="date" name="date_from" :label="__('From')" :value="$filters['date_from'] ?? ''" />
            <flux:input type="date" name="date_to" :label="__('To')" :value="$filters['date_to'] ?? ''" />
        </x-list.filters>

        <x-list.table>
            <x-slot:head>
                <th class="px-4 py-3 font-medium">{{ __('Date') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Class / slot') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Original') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Relief') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Reason') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Actions') }}</th>
            </x-slot:head>
            @forelse ($assignments as $assignment)
                <tr class="border-t border-border">
                    <td class="px-4 py-3">{{ $assignment->date->toDateString() }}</td>
                    <td class="px-4 py-3">
                        {{ $assignment->timetableEntry?->schoolClass?->code }}
                        · {{ $assignment->timetableEntry?->day_of_week?->label() }}
                        P{{ $assignment->timetableEntry?->period_number }}
                        · {{ $assignment->timetableEntry?->subject?->name }}
                    </td>
                    <td class="px-4 py-3">{{ $assignment->timetableEntry?->teacher?->user?->name }}</td>
                    <td class="px-4 py-3">{{ $assignment->reliefTeacher?->user?->name }}</td>
                    <td class="px-4 py-3">{{ $assignment->reason ?: '—' }}</td>
                    <td class="px-4 py-3">
                        <form method="POST" action="{{ route('admin.relief-assignments.destroy', $assignment) }}" onsubmit="return confirm(@js(__('Remove relief assignment?')))">
                            @csrf
                            @method('DELETE')
                            <flux:button size="sm" type="submit" variant="danger">{{ __('Remove') }}</flux:button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr class="border-t border-border">
                    <td colspan="6" class="px-4 py-10 text-center text-muted-foreground">{{ __('No relief assignments match these filters.') }}</td>
                </tr>
            @endforelse
        </x-list.table>

        <x-list.pagination :paginator="$assignments" />
    </div>
</x-layouts::app>
