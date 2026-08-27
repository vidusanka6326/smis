<x-layouts::app :title="__('Exam schedule')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div>
            <flux:heading size="xl">{{ __('Exam schedule') }}</flux:heading>
            <flux:text class="mt-1">
                {{ __('Planned examinations for :class.', ['class' => $student->currentClass?->code ?? __('your class')]) }}
            </flux:text>
        </div>

        <x-list.flash />

        <x-dashboard.panel :title="__('Upcoming and planned exams')">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-muted-foreground">
                            <th class="px-3 py-2 font-medium">{{ __('Exam') }}</th>
                            <th class="px-3 py-2 font-medium">{{ __('Date') }}</th>
                            <th class="px-3 py-2 font-medium">{{ __('Subjects') }}</th>
                            <th class="px-3 py-2 font-medium">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($exams as $exam)
                            <tr class="border-t border-border align-top">
                                <td class="px-3 py-3 font-medium">{{ $exam->name }}</td>
                                <td class="whitespace-nowrap px-3 py-3">
                                    {{ $exam->starts_on?->format('d M Y') }}
                                    @if ($exam->ends_on && ! $exam->starts_on?->isSameDay($exam->ends_on))
                                        <span class="text-muted-foreground">&ndash; {{ $exam->ends_on->format('d M Y') }}</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3">
                                    {{ $exam->examSubjects->pluck('subject.name')->filter()->implode(', ') ?: __('Subjects to be announced') }}
                                </td>
                                <td class="px-3 py-3">
                                    @if ($exam->isPublished())
                                        <flux:badge color="green">{{ __('Published') }}</flux:badge>
                                    @else
                                        <flux:badge color="zinc">{{ __('Planned') }}</flux:badge>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-3 py-10 text-center text-muted-foreground">
                                    {{ __('No exam schedule has been published for your class yet.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-dashboard.panel>
    </div>
</x-layouts::app>
