<x-layouts::app :title="__('Add teacher')">
    <x-form.page
        :title="__('Add teacher')"
        :description="__('Creates a login account and teacher profile together.')"
    >
        <form method="POST" action="{{ route('admin.teachers.store') }}" class="flex flex-col gap-6">
            @csrf

            <x-form.section :title="__('Account')" :description="__('Portal login for this teacher.')">
                <x-form.grid>
                    <flux:input name="name" :label="__('Name')" :value="old('name')" required autofocus />
                    <flux:input name="email" type="email" :label="__('Email')" :value="old('email')" required />
                    <flux:input name="password" type="password" :label="__('Password')" required />
                    <flux:input name="password_confirmation" type="password" :label="__('Confirm password')" required />
                    <flux:select name="status" :label="__('Status')" required>
                        @foreach ($statuses as $status)
                            <flux:select.option :value="$status->value" :selected="old('status', 'active') === $status->value">
                                {{ $status->label() }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                </x-form.grid>
            </x-form.section>

            <x-form.section :title="__('Employment')">
                <x-form.grid>
                    <flux:input name="employee_no" :label="__('Employee number')" :value="old('employee_no')" required />
                    <flux:input name="phone" :label="__('Phone')" :value="old('phone')" />
                </x-form.grid>
            </x-form.section>

            <x-form.actions>
                <flux:button type="submit" variant="primary">{{ __('Save') }}</flux:button>
                <flux:button :href="route('admin.teachers.index')" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
            </x-form.actions>
        </form>
    </x-form.page>
</x-layouts::app>
