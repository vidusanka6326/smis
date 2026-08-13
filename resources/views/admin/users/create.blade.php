<x-layouts::app :title="__('Create user')">
    <x-form.page
        :title="__('Create user')"
        :description="__('Only administrators can create accounts. There is no public registration.')"
    >
        <form method="POST" action="{{ route('admin.users.store') }}" class="flex flex-col gap-6">
            @csrf

            <x-form.section :title="__('Account details')">
                <x-form.grid>
                    <flux:input name="name" :label="__('Name')" :value="old('name')" required autofocus />
                    <flux:input name="email" type="email" :label="__('Email')" :value="old('email')" required />
                    <flux:input name="password" type="password" :label="__('Password')" required />
                    <flux:input name="password_confirmation" type="password" :label="__('Confirm password')" required />
                </x-form.grid>
            </x-form.section>

            <x-form.section :title="__('Access')">
                <x-form.grid>
                    <flux:select name="role" :label="__('Role')" :placeholder="__('Select a role')" required>
                        @foreach ($roles as $role)
                            <flux:select.option :value="$role->value" :selected="old('role') === $role->value">
                                {{ str($role->value)->headline() }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
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
                <flux:button type="submit" variant="primary">{{ __('Create user') }}</flux:button>
                <flux:button :href="route('admin.dashboard')" variant="ghost" wire:navigate>
                    {{ __('Cancel') }}
                </flux:button>
            </x-form.actions>
        </form>
    </x-form.page>
</x-layouts::app>
