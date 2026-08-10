<x-layouts::app :title="__('Teacher')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <flux:heading size="xl">{{ $teacher->user?->name }}</flux:heading>
                <flux:text class="mt-1">{{ $teacher->employee_no }} · {{ $teacher->user?->email }}</flux:text>
            </div>
            <div class="flex flex-wrap gap-2">
                <flux:button :href="route('admin.teachers.edit', $teacher)" variant="filled" wire:navigate>{{ __('Edit') }}</flux:button>
                <flux:button :href="route('admin.teachers.assignments.edit', $teacher)" variant="primary" wire:navigate>{{ __('Assignments') }}</flux:button>
            </div>
        </div>

        @if (session('status'))
            <flux:callout variant="success" icon="check-circle">
                <flux:callout.heading>{{ session('status') }}</flux:callout.heading>
            </flux:callout>
        @endif

        <div class="grid gap-4 md:grid-cols-2">
            <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                <flux:heading size="sm">{{ __('Profile') }}</flux:heading>
                <dl class="mt-3 space-y-2 text-sm">
                    <div><dt class="text-zinc-500">{{ __('Phone') }}</dt><dd>{{ $teacher->phone ?: '—' }}</dd></div>
                    <div><dt class="text-zinc-500">{{ __('Status') }}</dt><dd>{{ $teacher->user?->status?->label() }}</dd></div>
                </dl>
            </div>
            <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                <flux:heading size="sm">{{ __('Homeroom classes') }}</flux:heading>
                <ul class="mt-3 space-y-1 text-sm">
                    @forelse ($teacher->homeroomClasses as $class)
                        <li>{{ $class->code }} ({{ $class->academicYear?->name }})</li>
                    @empty
                        <li class="text-zinc-500">{{ __('None') }}</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
            <flux:heading size="sm">{{ __('Assignments') }}</flux:heading>
            <div class="mt-3 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left">
                        <tr>
                            <th class="py-2 pe-4">{{ __('Year') }}</th>
                            <th class="py-2 pe-4">{{ __('Class') }}</th>
                            <th class="py-2 pe-4">{{ __('Subject') }}</th>
                            <th class="py-2">{{ __('Role') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($teacher->assignments as $assignment)
                            <tr class="border-t border-zinc-200 dark:border-zinc-700">
                                <td class="py-2 pe-4">{{ $assignment->academicYear?->name }}</td>
                                <td class="py-2 pe-4">{{ $assignment->schoolClass?->code }}</td>
                                <td class="py-2 pe-4">{{ $assignment->subject?->name ?? '—' }}</td>
                                <td class="py-2">{{ $assignment->role_in_assignment->label() }}</td>
                            </tr>
                        @empty
                            <tr class="border-t border-zinc-200 dark:border-zinc-700">
                                <td colspan="4" class="py-4 text-zinc-500">{{ __('No assignments yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts::app>
