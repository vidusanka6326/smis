<x-layouts::app :title="__('New exam')">
    <x-form.page :title="__('New exam')" wide>
        <form method="POST" action="{{ route('admin.exams.store') }}" class="flex flex-col gap-6">
            @csrf

            <x-form.section :title="__('Exam details')">
                <x-form.grid>
                    <x-form.full>
                        <flux:input name="name" :label="__('Name')" required />
                    </x-form.full>
                    <flux:select name="type" :label="__('Type')">
                        @foreach ($types as $type)
                            <flux:select.option :value="$type->value">{{ $type->label() }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:select name="academic_year_id" :label="__('Academic year')">
                        @foreach ($academicYears as $year)
                            <flux:select.option :value="$year->id" :selected="$year->is_current">{{ $year->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </x-form.grid>
            </x-form.section>

            <x-form.section :title="__('Scope')" :description="__('Limit the exam to a grade, or optionally a single class.')">
                <x-form.grid>
                    <flux:select name="grade_id" :label="__('Grade')">
                        <flux:select.option value="">{{ __('—') }}</flux:select.option>
                        @foreach ($grades as $grade)
                            <flux:select.option :value="$grade->id">{{ $grade->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:select name="school_class_id" :label="__('Class (optional)')">
                        <flux:select.option value="">{{ __('All classes in grade') }}</flux:select.option>
                        @foreach ($schoolClasses as $class)
                            <flux:select.option :value="$class->id">{{ $class->code }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </x-form.grid>
            </x-form.section>

            <x-form.section :title="__('Schedule')">
                <x-form.grid>
                    <flux:input type="date" name="starts_on" :label="__('Starts on')" required />
                    <flux:input type="date" name="ends_on" :label="__('Ends on')" required />
                </x-form.grid>
            </x-form.section>

            <x-form.actions>
                <flux:button type="submit" variant="primary">{{ __('Create') }}</flux:button>
                <flux:button :href="route('admin.exams.index')" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
            </x-form.actions>
        </form>
    </x-form.page>
</x-layouts::app>
