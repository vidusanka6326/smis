<x-layouts::app :title="__('Edit teacher')">
    <x-form.page
        :title="__('Edit teacher')"
        :description="$teacher->employee_no"
    >
        <form method="POST" action="{{ route('admin.teachers.update', $teacher) }}" class="flex flex-col gap-6">
            @csrf
            @method('PUT')

            <x-form.section :title="__('Account')" :description="__('Leave password blank to keep the current one.')">
                <x-form.grid>
                    <flux:input name="name" :label="__('Name')" :value="old('name', $teacher->user->name)" required autofocus />
                    <flux:input name="email" type="email" :label="__('Email')" :value="old('email', $teacher->user->email)" required />
                    <flux:input name="password" type="password" :label="__('New password')" />
                    <flux:input name="password_confirmation" type="password" :label="__('Confirm password')" />
                    <flux:select name="status" :label="__('Status')" required>
                        @foreach ($statuses as $status)
                            <flux:select.option :value="$status->value" :selected="old('status', $teacher->user->status->value) === $status->value">
                                {{ $status->label() }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                </x-form.grid>
            </x-form.section>

            <x-form.section :title="__('Employment')">
                <x-form.grid>
                    <flux:input name="employee_no" :label="__('Employee number')" :value="old('employee_no', $teacher->employee_no)" required />
                    <flux:input name="phone" :label="__('Phone')" :value="old('phone', $teacher->phone)" />
                </x-form.grid>
            </x-form.section>

            <x-form.actions>
                <flux:button type="submit" variant="primary">{{ __('Save changes') }}</flux:button>
                <flux:button :href="route('admin.teachers.show', $teacher)" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
            </x-form.actions>
        </form>
    </x-form.page>
</x-layouts::app>
