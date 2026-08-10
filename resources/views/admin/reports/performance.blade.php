<x-layouts::app :title="__('Performance')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div>
            <flux:heading size="xl">{{ __('Best & poor performers') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Ranked by average percentage across selected exam subjects.') }}</flux:text>
        </div>

        <form method="GET" action="{{ route('admin.reports.performance') }}" class="no-print flex flex-wrap items-end gap-3">
            <flux:select name="exam_id" :label="__('Exam')">
                @foreach ($exams as $option)
                    <flux:select.option :value="$option->id" :selected="(string) $selectedExamId === (string) $option->id">{{ $option->name }}</flux:select.option>
                @endforeach
            </flux:select>
            @if ($exam)
                <flux:select name="subject_id" :label="__('Subject')">
                    <flux:select.option value="">{{ __('All subjects') }}</flux:select.option>
                    @foreach ($exam->examSubjects as $examSubject)
                        <flux:select.option :value="$examSubject->subject_id" :selected="(string) $selectedSubjectId === (string) $examSubject->subject_id">{{ $examSubject->subject?->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            @endif
            <flux:input type="number" name="limit" :label="__('Top/bottom N')" :value="$limit" min="1" max="50" />
            <flux:button type="submit" variant="filled">{{ __('Load') }}</flux:button>
        </form>

        <x-report-toolbar :print="$print">
            <flux:button :href="route('admin.reports.performance', ['exam_id' => $selectedExamId, 'subject_id' => $selectedSubjectId, 'limit' => $limit, 'export' => 'csv'])" variant="filled">{{ __('CSV') }}</flux:button>
            <flux:button :href="route('admin.reports.performance', ['exam_id' => $selectedExamId, 'subject_id' => $selectedSubjectId, 'limit' => $limit, 'print' => 1])" variant="filled">{{ __('Print / PDF') }}</flux:button>
        </x-report-toolbar>

        <div class="grid gap-4 lg:grid-cols-2">
            <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                <flux:heading size="sm">{{ __('Best') }}</flux:heading>
                <ul class="mt-3 space-y-1 text-sm">
                    @forelse ($ranks['best'] as $row)
                        <li>#{{ $row['rank'] }} {{ $row['name'] }} — {{ $row['percentage'] }}%</li>
                    @empty
                        <li class="text-zinc-500">{{ __('No data.') }}</li>
                    @endforelse
                </ul>
            </div>
            <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                <flux:heading size="sm">{{ __('Poor') }}</flux:heading>
                <ul class="mt-3 space-y-1 text-sm">
                    @forelse ($ranks['poor'] as $row)
                        <li>#{{ $row['rank'] }} {{ $row['name'] }} — {{ $row['percentage'] }}%</li>
                    @empty
                        <li class="text-zinc-500">{{ __('No data.') }}</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</x-layouts::app>
