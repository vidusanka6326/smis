<x-layouts::app :title="__('My results')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div>
            <flux:heading size="xl">{{ __('My results') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Published examination results only.') }}</flux:text>
        </div>

        <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
            <table class="min-w-full text-sm">
                <thead class="bg-zinc-50 dark:bg-zinc-900">
                    <tr>
                        <th class="px-3 py-2 text-left">{{ __('Exam') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('Subject') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('Marks') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('Grade') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('Result') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($marks as $mark)
                        <tr class="border-t border-zinc-200 dark:border-zinc-700">
                            <td class="px-3 py-2">{{ $mark->examSubject?->exam?->name }}</td>
                            <td class="px-3 py-2">{{ $mark->examSubject?->subject?->name }}</td>
                            <td class="px-3 py-2">{{ $mark->marks_obtained }} / {{ $mark->examSubject?->max_marks }}</td>
                            <td class="px-3 py-2">{{ $mark->grade_letter->value }}</td>
                            <td class="px-3 py-2">{{ $mark->is_pass ? __('Pass') : __('Fail') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-3 py-6 text-zinc-500">{{ __('No published results yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts::app>
