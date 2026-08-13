<x-layouts::app :title="__('Edit student')">
    <x-form.page
        :title="__('Edit student')"
        :description="__('Limited fields for class teachers.')"
        wide
    >
        <form method="POST" action="{{ route('teacher.students.update', $student) }}" class="flex flex-col gap-6">
            @csrf
            @method('PUT')

            <x-form.section :title="__('Account')">
                <x-form.grid>
                    <flux:input name="name" :label="__('Name')" :value="old('name', $student->user->name)" required autofocus />
                    <flux:input name="email" type="email" :label="__('Email')" :value="old('email', $student->user->email)" required />
                    <flux:input name="admission_no" :label="__('Admission number')" :value="old('admission_no', $student->admission_no)" required />
                </x-form.grid>
            </x-form.section>

            <x-form.section :title="__('Student profile')">
                <x-form.grid>
                    <flux:input name="date_of_birth" type="date" :label="__('Date of birth')" :value="old('date_of_birth', $student->date_of_birth?->toDateString())" />
                    <flux:select name="gender" :label="__('Gender')" required>
                        @foreach ($genders as $gender)
                            <flux:select.option :value="$gender->value" :selected="old('gender', $student->gender->value) === $gender->value">{{ $gender->label() }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </x-form.grid>
            </x-form.section>

            <x-form.section :title="__('Guardian')">
                <x-form.grid>
                    <flux:input name="guardian_name" :label="__('Guardian name')" :value="old('guardian_name', $student->guardian_name)" />
                    <flux:input name="guardian_relationship" :label="__('Relationship')" :value="old('guardian_relationship', $student->guardian_relationship)" />
                    <flux:input name="guardian_phone" :label="__('Phone')" :value="old('guardian_phone', $student->guardian_phone)" />
                    <flux:input name="guardian_email" type="email" :label="__('Email')" :value="old('guardian_email', $student->guardian_email)" />
                </x-form.grid>
            </x-form.section>

            <x-form.actions>
                <flux:button type="submit" variant="primary">{{ __('Save changes') }}</flux:button>
                <flux:button :href="route('teacher.students.index')" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
            </x-form.actions>
        </form>
    </x-form.page>
</x-layouts::app>
