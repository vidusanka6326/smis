<x-layouts::app :title="__('Classes')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <flux:heading size="xl">{{ __('Classes') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Class sections per academic year, grade, and stream (where applicable).') }}</flux:text>
            </div>
            <flux:button :href="route('admin.classes.create')" variant="primary" wire:navigate>{{ __('Add class') }}</flux:button>
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
                        <th class="px-4 py-3 font-medium">{{ __('Code') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('Year') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('Grade') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('Stream') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('Teacher') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($schoolClasses as $schoolClass)
                        <tr class="border-t border-zinc-200 dark:border-zinc-700">
                            <td class="px-4 py-3">{{ $schoolClass->code }}</td>
                            <td class="px-4 py-3">{{ $schoolClass->academicYear?->name }}</td>
                            <td class="px-4 py-3">{{ $schoolClass->grade?->name }}</td>
                            <td class="px-4 py-3">{{ $schoolClass->stream?->name ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $schoolClass->classTeacher?->name ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-2">
                                    <flux:button size="sm" :href="route('admin.classes.edit', $schoolClass)" variant="ghost" wire:navigate>{{ __('Edit') }}</flux:button>
                                    <form method="POST" action="{{ route('admin.classes.destroy', $schoolClass) }}" onsubmit="return confirm(@js(__('Delete this class?')))">
                                        @csrf
                                        @method('DELETE')
                                        <flux:button size="sm" type="submit" variant="danger">{{ __('Delete') }}</flux:button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="border-t border-zinc-200 dark:border-zinc-700">
                            <td colspan="6" class="px-4 py-6 text-zinc-500">{{ __('No classes yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $schoolClasses->links() }}
    </div>
</x-layouts::app>
