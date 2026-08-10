<x-layouts::app :title="__('Add class')">
    <div class="mx-auto flex w-full max-w-2xl flex-col gap-6">
        <div>
            <flux:heading size="xl">{{ __('Add class') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Streams are required for grades 12–13 and forbidden for grades 1–11.') }}</flux:text>
        </div>

        <form method="POST" action="{{ route('admin.classes.store') }}" class="flex flex-col gap-6">
            @csrf

            <flux:select name="academic_year_id" :label="__('Academic year')" :placeholder="__('Select year')" required>
                @foreach ($academicYears as $year)
                    <flux:select.option :value="$year->id" :selected="(string) old('academic_year_id') === (string) $year->id">
                        {{ $year->name }}
                    </flux:select.option>
                @endforeach
            </flux:select>

            <flux:select name="grade_id" :label="__('Grade')" :placeholder="__('Select grade')" required>
                @foreach ($grades as $grade)
                    <flux:select.option :value="$grade->id" :selected="(string) old('grade_id') === (string) $grade->id">
                        {{ $grade->name }}
                    </flux:select.option>
                @endforeach
            </flux:select>

            <flux:select name="stream_id" :label="__('Stream')" :placeholder="__('None (grades 1–11)')">
                @foreach ($streams as $stream)
                    <flux:select.option :value="$stream->id" :selected="(string) old('stream_id') === (string) $stream->id">
                        {{ $stream->name }}
                    </flux:select.option>
                @endforeach
            </flux:select>

            <flux:input name="name" :label="__('Section name')" :value="old('name')" placeholder="A" required />

            <flux:select name="class_teacher_id" :label="__('Class teacher')" :placeholder="__('Unassigned')">
                @foreach ($teachers as $teacher)
                    <flux:select.option :value="$teacher->id" :selected="(string) old('class_teacher_id') === (string) $teacher->id">
                        {{ $teacher->user?->name }} ({{ $teacher->employee_no }})
                    </flux:select.option>
                @endforeach
            </flux:select>

            <div class="flex flex-col gap-2">
                <flux:heading size="sm">{{ __('Subjects') }}</flux:heading>
                <div class="grid gap-2 sm:grid-cols-2">
                    @foreach ($subjects as $subject)
                        <flux:checkbox
                            name="subject_ids[]"
                            :value="$subject->id"
                            :checked="in_array($subject->id, old('subject_ids', []), false)"
                            :label="$subject->name.' ('.$subject->code.')'"
                        />
                    @endforeach
                </div>
            </div>

            <div class="flex gap-3">
                <flux:button type="submit" variant="primary">{{ __('Save') }}</flux:button>
                <flux:button :href="route('admin.classes.index')" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </div>
</x-layouts::app>
