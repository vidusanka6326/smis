<x-layouts::app :title="__('Add student')">
    <x-form.page
        :title="__('Add student')"
        :description="__('Creates login, profile, and current-year enrollment together.')"
        wide
    >
        <form method="POST" action="{{ route('admin.students.store') }}" class="flex flex-col gap-6">
            @csrf

            <x-form.section :title="__('Account')" :description="__('Login credentials for the student portal.')">
                <x-form.grid>
                    <flux:input name="name" :label="__('Name')" :value="old('name')" required autofocus />
                    <flux:input name="email" type="email" :label="__('Email')" :value="old('email')" required />
                    <flux:input name="password" type="password" :label="__('Password')" required />
                    <flux:input name="password_confirmation" type="password" :label="__('Confirm password')" required />
                    <flux:select name="status" :label="__('Status')" required>
                        @foreach ($statuses as $status)
                            <flux:select.option :value="$status->value" :selected="old('status', 'active') === $status->value">{{ $status->label() }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </x-form.grid>
            </x-form.section>

            <x-form.section :title="__('Student profile')">
                <x-form.grid>
                    <flux:input name="admission_no" :label="__('Admission number')" :value="old('admission_no')" required />
                    <flux:input name="date_of_birth" type="date" :label="__('Date of birth')" :value="old('date_of_birth')" />
                    <flux:select name="gender" :label="__('Gender')" required>
                        @foreach ($genders as $gender)
                            <flux:select.option :value="$gender->value" :selected="old('gender') === $gender->value">{{ $gender->label() }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </x-form.grid>
            </x-form.section>

            <x-form.section :title="__('Guardian')" :description="__('Optional contact details for parents or guardians.')">
                <x-form.grid>
                    <flux:input name="guardian_name" :label="__('Guardian name')" :value="old('guardian_name')" />
                    <flux:input name="guardian_relationship" :label="__('Relationship')" :value="old('guardian_relationship')" />
                    <flux:input name="guardian_phone" :label="__('Phone')" :value="old('guardian_phone')" />
                    <flux:input name="guardian_email" type="email" :label="__('Email')" :value="old('guardian_email')" />
                </x-form.grid>
            </x-form.section>

            <x-form.section :title="__('Enrollment')">
                <x-form.grid>
                    <flux:select name="academic_year_id" :label="__('Academic year')" required>
                        @foreach ($academicYears as $year)
                            <flux:select.option :value="$year->id" :selected="(string) old('academic_year_id') === (string) $year->id">{{ $year->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:select name="school_class_id" :label="__('Class')" required>
                        @foreach ($schoolClasses as $class)
                            <flux:select.option :value="$class->id" :selected="(string) old('school_class_id') === (string) $class->id">
                                {{ $class->code }} ({{ $class->academicYear?->name }})
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                </x-form.grid>
            </x-form.section>

            <x-form.actions>
                <flux:button type="submit" variant="primary">{{ __('Save') }}</flux:button>
                <flux:button :href="route('admin.students.index')" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
            </x-form.actions>
        </form>
    </x-form.page>
</x-layouts::app>
