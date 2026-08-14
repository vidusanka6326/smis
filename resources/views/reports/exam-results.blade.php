<x-layouts::app :title="__('Exam results')">
    <x-report.page
        :title="__('Exam results')"
        :description="__('Student-level marks, grades, and pass/fail for a published exam.')"
        :catalog-route="$catalogRoute"
    >
        <x-slot:aside>
            <x-report.exports :query="$exportQuery" />
        </x-slot:aside>

        <x-list.filters :action="$action" :filters="$filters" :submit="__('Apply')">
            <flux:select name="exam_id" :label="__('Exam')">
                @foreach ($exams as $option)
                    <flux:select.option :value="$option->id" :selected="(string) $selectedExamId === (string) $option->id">{{ $option->name }}</flux:select.option>
                @endforeach
            </flux:select>
            @if ($exam)
                <flux:select name="subject_id" :label="__('Subject')" :placeholder="__('All subjects')">
                    @foreach ($exam->examSubjects as $examSubject)
                        <flux:select.option :value="$examSubject->subject_id" :selected="(string) $selectedSubjectId === (string) $examSubject->subject_id">{{ $examSubject->subject?->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            @endif
            <flux:select name="result" :label="__('Result')" :placeholder="__('All')">
                <flux:select.option value="pass" :selected="$selectedResult === 'pass'">{{ __('Pass') }}</flux:select.option>
                <flux:select.option value="fail" :selected="$selectedResult === 'fail'">{{ __('Fail') }}</flux:select.option>
            </flux:select>
        </x-list.filters>

        <div class="overflow-x-auto rounded-xl border border-border bg-card">
            <table class="min-w-full text-sm">
                <thead class="bg-muted/60">
                    <tr>
                        <th class="px-3 py-2 text-left">{{ __('Student') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('Class') }}</th>
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
                            <td class="px-3 py-2">{{ $row['student'] }}</td>
                            <td class="px-3 py-2">{{ $row['class'] }}</td>
                            <td class="px-3 py-2">{{ $row['subject'] }}</td>
                            <td class="px-3 py-2">{{ $row['marks_obtained'] }} / {{ $row['max_marks'] }}</td>
                            <td class="px-3 py-2">{{ $row['percentage'] }}%</td>
                            <td class="px-3 py-2">{{ $row['grade_letter'] }}</td>
                            <td class="px-3 py-2">{{ $row['result'] }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-3 py-6 text-muted-foreground">{{ __('No published marks for this filter.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-list.pagination :paginator="$rows" />
    </x-report.page>
</x-layouts::app>
