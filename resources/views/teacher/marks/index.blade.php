<x-layouts::app :title="__('Marks')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div>
            <flux:heading size="xl">{{ __('Mark entry') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Subjects you can enter marks for.') }}</flux:text>
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
                        <th class="px-3 py-2 text-left">{{ __('Exam') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('Subject') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('Status') }}</th>
                        <th class="px-3 py-2 text-left"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($examSubjects as $examSubject)
                        <tr class="border-t border-zinc-200 dark:border-zinc-700">
                            <td class="px-3 py-2">{{ $examSubject->exam?->name }}</td>
                            <td class="px-3 py-2">{{ $examSubject->subject?->name }}</td>
                            <td class="px-3 py-2">{{ $examSubject->exam?->isPublished() ? __('Published') : __('Open') }}</td>
                            <td class="px-3 py-2">
                                @can('enterMarks', $examSubject)
                                    <a class="underline" href="{{ route('teacher.marks.edit', $examSubject) }}">{{ __('Enter') }}</a>
                                @else
                                    —
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-3 py-6 text-zinc-500">{{ __('No exam subjects available.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts::app>
