@php
    $selectedSubjects = old('subject_ids', $schoolClass->subjects->pluck('id')->all());
@endphp

<x-layouts::app :title="__('Edit class')">
    <x-form.page
        :title="__('Edit class')"
        :description="$schoolClass->code"
        wide
    >
        <form method="POST" action="{{ route('admin.classes.update', $schoolClass) }}" class="flex flex-col gap-6">
            @csrf
            @method('PUT')

            <x-form.section :title="__('Class identity')">
                <x-form.grid>
                    <flux:select name="academic_year_id" :label="__('Academic year')" required>
                        @foreach ($academicYears as $year)
                            <flux:select.option :value="$year->id" :selected="(string) old('academic_year_id', $schoolClass->academic_year_id) === (string) $year->id">
                                {{ $year->name }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:select name="grade_id" :label="__('Grade')" required>
                        @foreach ($grades as $grade)
                            <flux:select.option :value="$grade->id" :selected="(string) old('grade_id', $schoolClass->grade_id) === (string) $grade->id">
                                {{ $grade->name }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:select name="stream_id" :label="__('Stream')" :placeholder="__('None (grades 1–11)')">
                        @foreach ($streams as $stream)
                            <flux:select.option :value="$stream->id" :selected="(string) old('stream_id', $schoolClass->stream_id) === (string) $stream->id">
                                {{ $stream->name }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:input name="name" :label="__('Section name')" :value="old('name', $schoolClass->name)" required />
                    <x-form.full>
                        <flux:select name="class_teacher_id" :label="__('Class teacher')" :placeholder="__('Unassigned')">
                            @foreach ($teachers as $teacher)
                                <flux:select.option :value="$teacher->id" :selected="(string) old('class_teacher_id', $schoolClass->class_teacher_id) === (string) $teacher->id">
                                    {{ $teacher->user?->name }} ({{ $teacher->employee_no }})
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                    </x-form.full>
                </x-form.grid>
            </x-form.section>

            <x-form.section :title="__('Subjects')" :description="__('Subjects offered by this class.')">
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($subjects as $subject)
                        <flux:checkbox
                            name="subject_ids[]"
                            :value="$subject->id"
                            :checked="in_array($subject->id, $selectedSubjects, false)"
                            :label="$subject->name.' ('.$subject->code.')'"
                        />
                    @endforeach
                </div>
            </x-form.section>

            <x-form.actions>
                <flux:button type="submit" variant="primary">{{ __('Save changes') }}</flux:button>
                <flux:button :href="route('admin.classes.index')" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
            </x-form.actions>
        </form>
    </x-form.page>
</x-layouts::app>
