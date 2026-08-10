<x-layouts::app :title="__('New exam')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div>
            <flux:heading size="xl">{{ __('New exam') }}</flux:heading>
        </div>

        <form method="POST" action="{{ route('admin.exams.store') }}" class="grid max-w-3xl gap-4">
            @csrf
            <flux:input name="name" :label="__('Name')" required />
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
            <div class="grid gap-4 md:grid-cols-2">
                <flux:input type="date" name="starts_on" :label="__('Starts on')" required />
                <flux:input type="date" name="ends_on" :label="__('Ends on')" required />
            </div>
            <flux:button type="submit" variant="primary">{{ __('Create') }}</flux:button>
        </form>
    </div>
</x-layouts::app>
