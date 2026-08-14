<x-layouts::app :title="__('My results')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div>
            <flux:heading size="xl">{{ __('My results') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Published examination results only.') }}</flux:text>
        </div>

        <x-list.filters :action="route('student.results')" :filters="$filters">
            <flux:select name="exam_id" :label="__('Exam')" :placeholder="__('All')">
                @foreach ($exams as $exam)
                    <flux:select.option :value="$exam->id" :selected="(string) ($filters['exam_id'] ?? '') === (string) $exam->id">{{ $exam->name }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:select name="subject_id" :label="__('Subject')" :placeholder="__('All')">
                @foreach ($subjects as $subject)
                    <flux:select.option :value="$subject->id" :selected="(string) ($filters['subject_id'] ?? '') === (string) $subject->id">{{ $subject->name }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:select name="result" :label="__('Result')" :placeholder="__('All')">
                <flux:select.option value="pass" :selected="($filters['result'] ?? null) === 'pass'">{{ __('Pass') }}</flux:select.option>
                <flux:select.option value="fail" :selected="($filters['result'] ?? null) === 'fail'">{{ __('Fail') }}</flux:select.option>
            </flux:select>
        </x-list.filters>

        <x-list.table>
            <x-slot:head>
                <th class="px-4 py-3 font-medium">{{ __('Exam') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Subject') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Marks') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Grade') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Result') }}</th>
            </x-slot:head>
            @forelse ($marks as $mark)
                <tr class="border-t border-border">
                    <td class="px-4 py-3">{{ $mark->examSubject?->exam?->name }}</td>
                    <td class="px-4 py-3">{{ $mark->examSubject?->subject?->name }}</td>
                    <td class="px-4 py-3">{{ $mark->marks_obtained }} / {{ $mark->examSubject?->max_marks }}</td>
                    <td class="px-4 py-3">{{ $mark->grade_letter->value }}</td>
                    <td class="px-4 py-3">{{ $mark->is_pass ? __('Pass') : __('Fail') }}</td>
                </tr>
            @empty
                <tr class="border-t border-border">
                    <td colspan="5" class="px-4 py-10 text-center text-muted-foreground">{{ __('No published results match these filters.') }}</td>
                </tr>
            @endforelse
        </x-list.table>

        <x-list.pagination :paginator="$marks" />
    </div>
</x-layouts::app>
