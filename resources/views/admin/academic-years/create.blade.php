<x-layouts::app :title="__('Add academic year')">
    <x-form.page
        :title="__('Add academic year')"
        :description="__('Only one academic year should be marked current at a time.')"
    >
        <form method="POST" action="{{ route('admin.academic-years.store') }}" class="flex flex-col gap-6">
            @csrf

            <x-form.section :title="__('Year details')">
                <x-form.grid>
                    <x-form.full>
                        <flux:input name="name" :label="__('Name')" :value="old('name')" placeholder="2025/2026" required autofocus />
                    </x-form.full>
                    <flux:input name="starts_on" type="date" :label="__('Starts on')" :value="old('starts_on')" required />
                    <flux:input name="ends_on" type="date" :label="__('Ends on')" :value="old('ends_on')" required />
                    <x-form.full>
                        <flux:checkbox name="is_current" value="1" :checked="old('is_current')" :label="__('Mark as current academic year')" />
                    </x-form.full>
                </x-form.grid>
            </x-form.section>

            <x-form.actions>
                <flux:button type="submit" variant="primary">{{ __('Save') }}</flux:button>
                <flux:button :href="route('admin.academic-years.index')" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
            </x-form.actions>
        </form>
    </x-form.page>
</x-layouts::app>
