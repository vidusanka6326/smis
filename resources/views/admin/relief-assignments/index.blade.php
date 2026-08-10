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

        @if (session('status'))
            <flux:callout variant="success" icon="check-circle">
                <flux:callout.heading>{{ session('status') }}</flux:callout.heading>
            </flux:callout>
        @endif

        <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
            <table class="min-w-full text-sm">
                <thead class="bg-zinc-50 text-left dark:bg-zinc-900">
                    <tr>
                        <th class="px-4 py-3">{{ __('Date') }}</th>
                        <th class="px-4 py-3">{{ __('Class / slot') }}</th>
                        <th class="px-4 py-3">{{ __('Original') }}</th>
                        <th class="px-4 py-3">{{ __('Relief') }}</th>
                        <th class="px-4 py-3">{{ __('Reason') }}</th>
                        <th class="px-4 py-3">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($assignments as $assignment)
                        <tr class="border-t border-zinc-200 dark:border-zinc-700">
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
                        <tr class="border-t border-zinc-200 dark:border-zinc-700">
                            <td colspan="6" class="px-4 py-6 text-zinc-500">{{ __('No relief assignments yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $assignments->links() }}
    </div>
</x-layouts::app>
