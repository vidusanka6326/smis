<x-layouts::app :title="__('Edit teacher')">
    <div class="mx-auto flex w-full max-w-xl flex-col gap-6">
        <div>
            <flux:heading size="xl">{{ __('Edit teacher') }}</flux:heading>
            <flux:text class="mt-1">{{ $teacher->employee_no }}</flux:text>
        </div>

        <form method="POST" action="{{ route('admin.teachers.update', $teacher) }}" class="flex flex-col gap-6">
            @csrf
            @method('PUT')
            <flux:input name="name" :label="__('Name')" :value="old('name', $teacher->user->name)" required autofocus />
            <flux:input name="email" type="email" :label="__('Email')" :value="old('email', $teacher->user->email)" required />
            <flux:input name="password" type="password" :label="__('New password')" />
            <flux:input name="password_confirmation" type="password" :label="__('Confirm password')" />
            <flux:input name="employee_no" :label="__('Employee number')" :value="old('employee_no', $teacher->employee_no)" required />
            <flux:input name="phone" :label="__('Phone')" :value="old('phone', $teacher->phone)" />
            <flux:select name="status" :label="__('Status')" required>
                @foreach ($statuses as $status)
                    <flux:select.option :value="$status->value" :selected="old('status', $teacher->user->status->value) === $status->value">
                        {{ $status->label() }}
                    </flux:select.option>
                @endforeach
            </flux:select>

            <div class="flex gap-3">
                <flux:button type="submit" variant="primary">{{ __('Save changes') }}</flux:button>
                <flux:button :href="route('admin.teachers.show', $teacher)" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </div>
</x-layouts::app>
