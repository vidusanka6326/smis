<x-layouts::app :title="__('Take attendance')">
    <x-form.page
        :title="__('Take attendance')"
        :description="__('Select class and optionally a subject, then mark each student.')"
        wide
    >
        <x-form.section :title="__('Session filters')">
            <form method="GET" action="{{ route('admin.attendance.sessions.create') }}">
                <x-form.grid cols="3">
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
                    <flux:input type="date" name="date" :label="__('Date')" :value="$date" />
                    <x-form.full>
                        <flux:button type="submit" variant="filled">{{ __('Load students') }}</flux:button>
                    </x-form.full>
                </x-form.grid>
            </form>
        </x-form.section>

        @if ($schoolClass)
            <form method="POST" action="{{ route('admin.attendance.sessions.store') }}" class="flex flex-col gap-6">
                @csrf
                <input type="hidden" name="academic_year_id" value="{{ $selectedAcademicYearId }}">
                <input type="hidden" name="school_class_id" value="{{ $schoolClass->id }}">
                <input type="hidden" name="date" value="{{ $date }}">

                <x-form.section :title="__('Session details')">
                    <x-form.grid>
                        <flux:select name="subject_id" :label="__('Subject (optional)')">
                            <flux:select.option value="">{{ __('Class attendance') }}</flux:select.option>
                            @foreach ($schoolClass->subjects as $subject)
                                <flux:select.option :value="$subject->id">{{ $subject->name }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:select name="taken_by_teacher_id" :label="__('Taken by')">
                            <flux:select.option value="">{{ __('—') }}</flux:select.option>
                            @foreach ($teachers as $teacher)
                                <flux:select.option :value="$teacher->id">{{ $teacher->user?->name }} ({{ $teacher->employee_no }})</flux:select.option>
                            @endforeach
                        </flux:select>
                        <x-form.full>
                            <flux:input name="notes" :label="__('Notes')" />
                        </x-form.full>
                        <x-form.full>
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" name="finalize" value="1">
                                {{ __('Finalize after save') }}
                            </label>
                        </x-form.full>
                    </x-form.grid>
                </x-form.section>

                <x-form.section :title="__('Roster')">
                    <div class="overflow-x-auto rounded-xl border border-border">
                        <table class="min-w-full text-sm">
                            <thead class="bg-muted/50">
                                <tr>
                                    <th class="px-3 py-2 text-left">{{ __('Student') }}</th>
                                    <th class="px-3 py-2 text-left">{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($students as $index => $student)
                                    <tr class="border-t border-border">
                                        <td class="px-3 py-2">
                                            {{ $student->user?->name }}
                                            <div class="text-muted-foreground">{{ $student->admission_no }}</div>
                                            <input type="hidden" name="records[{{ $index }}][student_id]" value="{{ $student->id }}">
                                        </td>
                                        <td class="px-3 py-2">
                                            <select name="records[{{ $index }}][status]" class="rounded border border-border bg-transparent px-2 py-1">
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
                </x-form.section>

                @if ($students->isEmpty())
                    <flux:callout variant="warning" icon="exclamation-triangle">
                        <flux:callout.heading>{{ __('No students are enrolled in this class.') }}</flux:callout.heading>
                    </flux:callout>
                @else
                    <x-form.actions>
                        <flux:button type="submit" variant="primary">{{ __('Save attendance') }}</flux:button>
                    </x-form.actions>
                @endif
            </form>
        @endif
    </x-form.page>
</x-layouts::app>
