<x-layouts::app :title="__('My class students')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <flux:heading size="xl">{{ __('My class students') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Students in classes where you are the class teacher.') }}</flux:text>
            </div>
            <flux:button :href="route('teacher.students.create')" variant="primary" wire:navigate>{{ __('Add student') }}</flux:button>
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
                        <th class="px-4 py-3 font-medium">{{ __('Admission') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('Class') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($students as $student)
                        <tr class="border-t border-zinc-200 dark:border-zinc-700">
                            <td class="px-4 py-3">{{ $student->user?->name }}</td>
                            <td class="px-4 py-3">{{ $student->admission_no }}</td>
                            <td class="px-4 py-3">{{ $student->currentClass?->code }}</td>
                            <td class="px-4 py-3">
                                <flux:button size="sm" :href="route('teacher.students.edit', $student)" variant="ghost" wire:navigate>{{ __('Edit') }}</flux:button>
                            </td>
                        </tr>
                    @empty
                        <tr class="border-t border-zinc-200 dark:border-zinc-700">
                            <td colspan="4" class="px-4 py-6 text-zinc-500">{{ __('No students in your classes yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $students->links() }}
    </div>
</x-layouts::app>
