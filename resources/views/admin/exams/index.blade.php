<x-layouts::app :title="__('Exams')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <flux:heading size="xl">{{ __('Exams') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Create term tests, scholarship, O/L and A/L exams.') }}</flux:text>
            </div>
            <div class="flex flex-wrap gap-2">
                <flux:button :href="route('admin.marks.index')" variant="filled" wire:navigate>{{ __('Mark entry') }}</flux:button>
                <flux:button :href="route('admin.exams.create')" variant="primary" wire:navigate>{{ __('New exam') }}</flux:button>
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
                        <th class="px-3 py-2 text-left">{{ __('Name') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('Type') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('Scope') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('Dates') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('Status') }}</th>
                        <th class="px-3 py-2 text-left"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($exams as $exam)
                        <tr class="border-t border-zinc-200 dark:border-zinc-700">
                            <td class="px-3 py-2">{{ $exam->name }}</td>
                            <td class="px-3 py-2">{{ $exam->type->label() }}</td>
                            <td class="px-3 py-2">
                                {{ $exam->schoolClass?->code ?? $exam->grade?->name ?? '—' }}
                            </td>
                            <td class="px-3 py-2">{{ $exam->starts_on->toDateString() }} → {{ $exam->ends_on->toDateString() }}</td>
                            <td class="px-3 py-2">{{ $exam->isPublished() ? __('Published') : __('Draft') }}</td>
                            <td class="px-3 py-2">
                                <a class="underline" href="{{ route('admin.exams.edit', $exam) }}">{{ __('Open') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-3 py-6 text-zinc-500">{{ __('No exams yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $exams->links() }}
    </div>
</x-layouts::app>
