<x-layouts::app :title="__('My exam results')">
    <x-report.page
        :title="__('My exam results')"
        :description="$student->user?->name.' — '.($student->currentClass?->code ?? '—')"
        :catalog-route="$catalogRoute"
    >
        <x-slot:aside>
            <x-report.exports :query="$exportQuery" />
        </x-slot:aside>

        <x-list.filters :action="$action" :filters="$filters" :submit="__('Apply')">
            <flux:select name="exam_id" :label="__('Exam')" :placeholder="__('All exams')">
                @foreach ($exams as $exam)
                    <flux:select.option :value="$exam->id" :selected="(string) $selectedExamId === (string) $exam->id">{{ $exam->name }}</flux:select.option>
                @endforeach
            </flux:select>
        </x-list.filters>

        <x-dashboard.stat
            :label="__('Overall exam average')"
            :value="$overallAverage !== null ? $overallAverage.'%' : '—'"
            class="max-w-xs"
        />

        <div class="overflow-x-auto rounded-xl border border-border bg-card">
            <table class="min-w-full text-sm">
                <thead class="bg-muted/60">
                    <tr>
                        <th class="px-3 py-2 text-left">{{ __('Exam') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('Subject') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('Marks') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('%') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('Grade') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('Result') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr class="border-t border-border">
                            <td class="px-3 py-2">{{ $row['exam'] }}</td>
                            <td class="px-3 py-2">{{ $row['subject'] }}</td>
                            <td class="px-3 py-2">{{ $row['marks_obtained'] }} / {{ $row['max_marks'] }}</td>
                            <td class="px-3 py-2">{{ $row['percentage'] }}%</td>
                            <td class="px-3 py-2">{{ $row['grade_letter'] }}</td>
                            <td class="px-3 py-2">{{ $row['is_pass'] ? __('Pass') : __('Fail') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-3 py-6 text-muted-foreground">{{ __('No published results.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-list.pagination :paginator="$rows" />
    </x-report.page>
</x-layouts::app>
