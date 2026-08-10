<x-layouts::app :title="__('Edit student')">
    <div class="mx-auto flex w-full max-w-xl flex-col gap-6">
        <div>
            <flux:heading size="xl">{{ __('Edit student') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Limited fields for class teachers.') }}</flux:text>
        </div>

        <form method="POST" action="{{ route('teacher.students.update', $student) }}" class="flex flex-col gap-6">
            @csrf
            @method('PUT')
            <flux:input name="name" :label="__('Name')" :value="old('name', $student->user->name)" required autofocus />
            <flux:input name="email" type="email" :label="__('Email')" :value="old('email', $student->user->email)" required />
            <flux:input name="admission_no" :label="__('Admission number')" :value="old('admission_no', $student->admission_no)" required />
            <flux:input name="date_of_birth" type="date" :label="__('Date of birth')" :value="old('date_of_birth', $student->date_of_birth?->toDateString())" />
            <flux:select name="gender" :label="__('Gender')" required>
                @foreach ($genders as $gender)
                    <flux:select.option :value="$gender->value" :selected="old('gender', $student->gender->value) === $gender->value">{{ $gender->label() }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:input name="guardian_name" :label="__('Guardian name')" :value="old('guardian_name', $student->guardian_name)" />
            <flux:input name="guardian_phone" :label="__('Guardian phone')" :value="old('guardian_phone', $student->guardian_phone)" />
            <flux:input name="guardian_email" type="email" :label="__('Guardian email')" :value="old('guardian_email', $student->guardian_email)" />
            <flux:input name="guardian_relationship" :label="__('Guardian relationship')" :value="old('guardian_relationship', $student->guardian_relationship)" />

            <div class="flex gap-3">
                <flux:button type="submit" variant="primary">{{ __('Save changes') }}</flux:button>
                <flux:button :href="route('teacher.students.index')" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </div>
</x-layouts::app>
