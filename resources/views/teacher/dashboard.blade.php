<x-layouts::app :title="__('Teacher Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <flux:heading size="xl">{{ __('Teacher Dashboard') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Your assignments and homeroom classes.') }}</flux:text>
            </div>
            <flux:button :href="route('teacher.students.index')" variant="primary" wire:navigate>{{ __('My students') }}</flux:button>
        </div>

        @if (! $teacher)
            <flux:callout variant="warning" icon="exclamation-triangle">
                <flux:callout.heading>{{ __('No teacher profile is linked to this account yet.') }}</flux:callout.heading>
            </flux:callout>
        @else
            <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                <flux:heading size="sm">{{ __('Homeroom') }}</flux:heading>
                <ul class="mt-3 space-y-1 text-sm">
                    @forelse ($teacher->homeroomClasses as $class)
                        <li>{{ $class->code }} — {{ $class->grade?->name }}</li>
                    @empty
                        <li class="text-zinc-500">{{ __('No homeroom classes assigned.') }}</li>
                    @endforelse
                </ul>
            </div>

            <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                <flux:heading size="sm">{{ __('Teaching assignments') }}</flux:heading>
                <ul class="mt-3 space-y-1 text-sm">
                    @forelse ($teacher->assignments as $assignment)
                        <li>
                            {{ $assignment->role_in_assignment->label() }} —
                            {{ $assignment->schoolClass?->code }}
                            @if ($assignment->subject)
                                — {{ $assignment->subject->name }}
                            @endif
                            ({{ $assignment->academicYear?->name }})
                        </li>
                    @empty
                        <li class="text-zinc-500">{{ __('No assignments yet.') }}</li>
                    @endforelse
                </ul>
            </div>
        @endif
    </div>
</x-layouts::app>
