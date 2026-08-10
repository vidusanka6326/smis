<x-layouts::app :title="__('Attendance')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <flux:heading size="xl">{{ __('Attendance') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Sessions for your classes and subjects.') }}</flux:text>
            </div>
            <div class="flex flex-wrap gap-2">
                <flux:button :href="route('teacher.attendance.monthly')" variant="filled" wire:navigate>{{ __('Monthly') }}</flux:button>
                <flux:button :href="route('teacher.attendance.self.index')" variant="filled" wire:navigate>{{ __('My attendance') }}</flux:button>
                <flux:button :href="route('teacher.attendance.sessions.create')" variant="primary" wire:navigate>{{ __('Take attendance') }}</flux:button>
            </div>
        </div>

        @if (session('status'))
            <flux:callout variant="success" icon="check-circle">
                <flux:callout.heading>{{ session('status') }}</flux:callout.heading>
            </flux:callout>
        @endif

        <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
            <table class="min-w-full text-sm">
                <thead class="bg-zinc-50 dark:bg-zinc-900">
                    <tr>
                        <th class="px-3 py-2 text-left">{{ __('Date') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('Class') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('Scope') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('Status') }}</th>
                        <th class="px-3 py-2 text-left"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sessions as $session)
                        <tr class="border-t border-zinc-200 dark:border-zinc-700">
                            <td class="px-3 py-2">{{ $session->date->toDateString() }}</td>
                            <td class="px-3 py-2">{{ $session->schoolClass?->code }}</td>
                            <td class="px-3 py-2">{{ $session->subject?->name ?? __('Class') }}</td>
                            <td class="px-3 py-2">{{ $session->isFinalized() ? __('Finalized') : __('Open') }}</td>
                            <td class="px-3 py-2">
                                @can('update', $session)
                                    <a class="underline" href="{{ route('teacher.attendance.sessions.edit', $session) }}">{{ __('Open') }}</a>
                                @else
                                    —
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-3 py-6 text-zinc-500">{{ __('No sessions yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $sessions->links() }}
    </div>
</x-layouts::app>
