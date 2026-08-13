<x-layouts::app :title="__('Add grade')">
    <x-form.page :title="__('Add grade')">
        <form method="POST" action="{{ route('admin.grades.store') }}" class="flex flex-col gap-6">
            @csrf

            <x-form.section :title="__('Grade details')">
                <x-form.grid>
                    <flux:input name="number" type="number" min="1" max="13" :label="__('Number')" :value="old('number')" required autofocus />
                    <flux:input name="name" :label="__('Name')" :value="old('name')" placeholder="Grade 10" required />
                </x-form.grid>
            </x-form.section>

            <x-form.actions>
                <flux:button type="submit" variant="primary">{{ __('Save') }}</flux:button>
                <flux:button :href="route('admin.grades.index')" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
            </x-form.actions>
        </form>
    </x-form.page>
</x-layouts::app>
