<x-layouts::app :title="__('My Relief Assignments')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <flux:heading size="xl">{{ __('My Relief Assignments') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Periods you have been assigned to cover.') }}</flux:text>
            </div>
        </div>

        <x-list.table>
            <x-slot:head>
                <th class="px-4 py-3 font-medium">{{ __('Date') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Class / Slot') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Original Teacher') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Reason') }}</th>
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
                    <td class="px-4 py-3">{{ $assignment->reason ?: '—' }}</td>
                </tr>
            @empty
                <tr class="border-t border-border">
                    <td colspan="4" class="px-4 py-10 text-center text-muted-foreground">{{ __('No relief assignments found.') }}</td>
                </tr>
            @endforelse
        </x-list.table>

        <x-list.pagination :paginator="$assignments" />
    </div>
</x-layouts::app>
