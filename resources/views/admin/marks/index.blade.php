<x-layouts::app :title="__('Mark entry')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div>
            <flux:heading size="xl">{{ __('Mark entry') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Enter marks per exam subject. Results lock after publish.') }}</flux:text>
        </div>

        <div class="space-y-4">
            @forelse ($exams as $exam)
                <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <flux:heading size="sm">{{ $exam->name }}</flux:heading>
                            <flux:text>{{ $exam->type->label() }} · {{ $exam->isPublished() ? __('Published') : __('Draft') }}</flux:text>
                        </div>
                    </div>
                    <ul class="mt-3 space-y-1 text-sm">
                        @forelse ($exam->examSubjects as $examSubject)
                            <li>
                                {{ $examSubject->subject?->name }}
                                @unless ($exam->isPublished())
                                    — <a class="underline" href="{{ route('admin.marks.edit', $examSubject) }}">{{ __('Enter marks') }}</a>
                                @else
                                    — {{ __('Locked') }}
                                @endunless
                            </li>
                        @empty
                            <li class="text-zinc-500">{{ __('No subjects configured.') }}</li>
                        @endforelse
                    </ul>
                </div>
            @empty
                <p class="text-zinc-500">{{ __('No exams yet.') }}</p>
            @endforelse
        </div>

        {{ $exams->links() }}
    </div>
</x-layouts::app>
