<x-layouts::app :title="__('Create user')">
    <div class="mx-auto flex w-full max-w-xl flex-col gap-6">
        <div>
            <flux:heading size="xl">{{ __('Create user') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Only administrators can create accounts. There is no public registration.') }}</flux:text>
        </div>

        <form method="POST" action="{{ route('admin.users.store') }}" class="flex flex-col gap-6">
            @csrf

            <flux:input name="name" :label="__('Name')" :value="old('name')" required autofocus />
            <flux:input name="email" type="email" :label="__('Email')" :value="old('email')" required />
            <flux:input name="password" type="password" :label="__('Password')" required />
            <flux:input name="password_confirmation" type="password" :label="__('Confirm password')" required />

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

            <div class="flex gap-3">
                <flux:button type="submit" variant="primary">{{ __('Create user') }}</flux:button>
                <flux:button :href="route('admin.dashboard')" variant="ghost" wire:navigate>
                    {{ __('Cancel') }}
                </flux:button>
            </div>
        </form>
    </div>
</x-layouts::app>
