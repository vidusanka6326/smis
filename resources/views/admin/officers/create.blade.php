<x-layouts::app :title="__('Add officer')">
    <x-form.page
        :title="__('Add officer')"
        :description="__('Office staff can enter school data and view the activity log. They cannot manage other officers.')"
    >
        <form method="POST" action="{{ route('admin.officers.store') }}" class="flex flex-col gap-6">
            @csrf

            <x-form.section :title="__('Account')">
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

            <x-form.actions>
                <flux:button type="submit" variant="primary">{{ __('Save') }}</flux:button>
                <flux:button :href="route('admin.officers.index')" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
            </x-form.actions>
        </form>
    </x-form.page>
</x-layouts::app>
