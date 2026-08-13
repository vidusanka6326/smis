<x-layouts::app :title="__('Edit exam')">
    <x-form.page :title="$exam->name" :description="$exam->isPublished() ? __('Published') : __('Draft')" wide>
        <x-slot:aside>
            <flux:button :href="route('admin.exams.subjects.edit', $exam)" variant="filled" wire:navigate>{{ __('Subjects') }}</flux:button>
            <flux:button :href="route('admin.marks.index')" variant="filled" wire:navigate>{{ __('Marks') }}</flux:button>
            @if ($exam->isPublished())
                <form method="POST" action="{{ route('admin.exams.unpublish', $exam) }}">
                    @csrf
                    <flux:button type="submit" variant="filled">{{ __('Unpublish') }}</flux:button>
                </form>
            @else
                <form method="POST" action="{{ route('admin.exams.publish', $exam) }}">
                    @csrf
                    <flux:button type="submit" variant="primary">{{ __('Publish results') }}</flux:button>
                </form>
            @endif
        </x-slot:aside>

        @if (session('status'))
            <flux:callout variant="success" icon="check-circle">
                <flux:callout.heading>{{ session('status') }}</flux:callout.heading>
            </flux:callout>
        @endif

        @if ($errors->any())
            <flux:callout variant="danger" icon="x-circle">
                <flux:callout.heading>{{ $errors->first() }}</flux:callout.heading>
            </flux:callout>
        @endif

        <form method="POST" action="{{ route('admin.exams.update', $exam) }}" class="flex flex-col gap-6">
            @csrf
            @method('PUT')

            <x-form.section :title="__('Exam details')">
                <x-form.grid>
                    <x-form.full>
                        <flux:input name="name" :label="__('Name')" :value="old('name', $exam->name)" :disabled="$exam->isPublished()" required />
                    </x-form.full>
                    <flux:select name="type" :label="__('Type')" :disabled="$exam->isPublished()">
                        @foreach ($types as $type)
                            <flux:select.option :value="$type->value" :selected="old('type', $exam->type->value) === $type->value">{{ $type->label() }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:select name="academic_year_id" :label="__('Academic year')" :disabled="$exam->isPublished()">
                        @foreach ($academicYears as $year)
                            <flux:select.option :value="$year->id" :selected="(string) old('academic_year_id', $exam->academic_year_id) === (string) $year->id">{{ $year->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </x-form.grid>
            </x-form.section>

            <x-form.section :title="__('Scope')">
                <x-form.grid>
                    <flux:select name="grade_id" :label="__('Grade')" :disabled="$exam->isPublished()">
                        <flux:select.option value="">{{ __('—') }}</flux:select.option>
                        @foreach ($grades as $grade)
                            <flux:select.option :value="$grade->id" :selected="(string) old('grade_id', $exam->grade_id) === (string) $grade->id">{{ $grade->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:select name="school_class_id" :label="__('Class')" :disabled="$exam->isPublished()">
                        <flux:select.option value="">{{ __('All classes in grade') }}</flux:select.option>
                        @foreach ($schoolClasses as $class)
                            <flux:select.option :value="$class->id" :selected="(string) old('school_class_id', $exam->school_class_id) === (string) $class->id">{{ $class->code }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </x-form.grid>
            </x-form.section>

            <x-form.section :title="__('Schedule')">
                <x-form.grid>
                    <flux:input type="date" name="starts_on" :label="__('Starts on')" :value="old('starts_on', $exam->starts_on->toDateString())" :disabled="$exam->isPublished()" />
                    <flux:input type="date" name="ends_on" :label="__('Ends on')" :value="old('ends_on', $exam->ends_on->toDateString())" :disabled="$exam->isPublished()" />
                </x-form.grid>
            </x-form.section>

            @unless ($exam->isPublished())
                <x-form.actions>
                    <flux:button type="submit" variant="primary">{{ __('Save') }}</flux:button>
                </x-form.actions>
            @endunless
        </form>

        <x-form.section :title="__('Subjects')">
            <ul class="space-y-2 text-sm">
                @forelse ($exam->examSubjects as $examSubject)
                    <li class="flex flex-wrap items-center justify-between gap-2 rounded-lg border border-border px-3 py-2">
                        <span>
                            {{ $examSubject->subject?->name }} —
                            {{ __('max :max / pass :pass', ['max' => $examSubject->max_marks, 'pass' => $examSubject->pass_mark]) }}
                        </span>
                        <a class="text-primary underline" href="{{ route('admin.marks.edit', $examSubject) }}">{{ __('Enter marks') }}</a>
                    </li>
                @empty
                    <li class="text-muted-foreground">{{ __('No subjects configured yet.') }}</li>
                @endforelse
            </ul>
        </x-form.section>
    </x-form.page>
</x-layouts::app>
