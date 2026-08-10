<x-layouts::app :title="__('Teachers')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <flux:heading size="xl">{{ __('Teachers') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Manage teacher profiles and teaching assignments.') }}</flux:text>
            </div>
            <flux:button :href="route('admin.teachers.create')" variant="primary" wire:navigate>{{ __('Add teacher') }}</flux:button>
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
                        <th class="px-4 py-3 font-medium">{{ __('Name') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('Employee no.') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('Email') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($teachers as $teacher)
                        <tr class="border-t border-zinc-200 dark:border-zinc-700">
                            <td class="px-4 py-3">{{ $teacher->user?->name }}</td>
                            <td class="px-4 py-3">{{ $teacher->employee_no }}</td>
                            <td class="px-4 py-3">{{ $teacher->user?->email }}</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-2">
                                    <flux:button size="sm" :href="route('admin.teachers.show', $teacher)" variant="ghost" wire:navigate>{{ __('View') }}</flux:button>
                                    <flux:button size="sm" :href="route('admin.teachers.edit', $teacher)" variant="ghost" wire:navigate>{{ __('Edit') }}</flux:button>
                                    <flux:button size="sm" :href="route('admin.teachers.assignments.edit', $teacher)" variant="ghost" wire:navigate>{{ __('Assignments') }}</flux:button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="border-t border-zinc-200 dark:border-zinc-700">
                            <td colspan="4" class="px-4 py-6 text-zinc-500">{{ __('No teachers yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $teachers->links() }}
    </div>
</x-layouts::app>
