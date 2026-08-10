<x-layouts::app :title="__('Add teacher')">
    <div class="mx-auto flex w-full max-w-xl flex-col gap-6">
        <div>
            <flux:heading size="xl">{{ __('Add teacher') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Creates a login account and teacher profile together.') }}</flux:text>
        </div>

        <form method="POST" action="{{ route('admin.teachers.store') }}" class="flex flex-col gap-6">
            @csrf
            <flux:input name="name" :label="__('Name')" :value="old('name')" required autofocus />
            <flux:input name="email" type="email" :label="__('Email')" :value="old('email')" required />
            <flux:input name="password" type="password" :label="__('Password')" required />
            <flux:input name="password_confirmation" type="password" :label="__('Confirm password')" required />
            <flux:input name="employee_no" :label="__('Employee number')" :value="old('employee_no')" required />
            <flux:input name="phone" :label="__('Phone')" :value="old('phone')" />
            <flux:select name="status" :label="__('Status')" required>
                @foreach ($statuses as $status)
                    <flux:select.option :value="$status->value" :selected="old('status', 'active') === $status->value">
                        {{ $status->label() }}
                    </flux:select.option>
                @endforeach
            </flux:select>

            <div class="flex gap-3">
                <flux:button type="submit" variant="primary">{{ __('Save') }}</flux:button>
                <flux:button :href="route('admin.teachers.index')" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </div>
</x-layouts::app>
