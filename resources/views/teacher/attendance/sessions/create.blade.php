<x-layouts::app :title="__('Take attendance')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div>
            <flux:heading size="xl">{{ __('Take attendance') }}</flux:heading>
        </div>

        <form method="GET" action="{{ route('teacher.attendance.sessions.create') }}" class="flex flex-wrap items-end gap-3">
            <flux:select name="academic_year_id" :label="__('Academic year')">
                @foreach ($academicYears as $year)
                    <flux:select.option :value="$year->id" :selected="(string) $selectedAcademicYearId === (string) $year->id">{{ $year->name }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:select name="school_class_id" :label="__('Class')">
                <flux:select.option value="">{{ __('Select class') }}</flux:select.option>
                @foreach ($schoolClasses as $class)
                    <flux:select.option :value="$class->id" :selected="(string) $selectedSchoolClassId === (string) $class->id">{{ $class->code }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:select name="subject_id" :label="__('Subject (optional)')">
                <flux:select.option value="">{{ __('Class attendance') }}</flux:select.option>
                @foreach ($subjects as $subject)
                    <flux:select.option :value="$subject->id" :selected="(string) $selectedSubjectId === (string) $subject->id">{{ $subject->name }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:input type="date" name="date" :label="__('Date')" :value="$date" />
            <flux:button type="submit" variant="filled">{{ __('Load') }}</flux:button>
        </form>

        @if ($schoolClass)
            <form method="POST" action="{{ route('teacher.attendance.sessions.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="academic_year_id" value="{{ $selectedAcademicYearId }}">
                <input type="hidden" name="school_class_id" value="{{ $schoolClass->id }}">
                <input type="hidden" name="date" value="{{ $date }}">
                @if ($selectedSubjectId)
                    <input type="hidden" name="subject_id" value="{{ $selectedSubjectId }}">
                @endif
                <flux:input name="notes" :label="__('Notes')" />
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="finalize" value="1">
                    {{ __('Finalize after save') }}
                </label>

                <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
                    <table class="min-w-full text-sm">
                        <thead class="bg-zinc-50 dark:bg-zinc-900">
                            <tr>
                                <th class="px-3 py-2 text-left">{{ __('Student') }}</th>
                                <th class="px-3 py-2 text-left">{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($students as $index => $student)
                                <tr class="border-t border-zinc-200 dark:border-zinc-700">
                                    <td class="px-3 py-2">
                                        {{ $student->user?->name }}
                                        <input type="hidden" name="records[{{ $index }}][student_id]" value="{{ $student->id }}">
                                    </td>
                                    <td class="px-3 py-2">
                                        <select name="records[{{ $index }}][status]" class="rounded border border-zinc-300 bg-transparent px-2 py-1 dark:border-zinc-600">
                                            @foreach ($statuses as $status)
                                                <option value="{{ $status->value }}" @selected($status === \App\Enums\AttendanceStatus::Present)>{{ $status->label() }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($students->isNotEmpty())
                    <flux:button type="submit" variant="primary">{{ __('Save') }}</flux:button>
                @endif
            </form>
        @endif
    </div>
</x-layouts::app>
