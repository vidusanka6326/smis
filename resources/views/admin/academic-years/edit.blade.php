<x-layouts::app :title="__('Edit academic year')">
    <x-form.page
        :title="__('Edit academic year')"
        :description="$academicYear->name"
    >
        <form method="POST" action="{{ route('admin.academic-years.update', $academicYear) }}" class="flex flex-col gap-6">
            @csrf
            @method('PUT')

            <x-form.section :title="__('Year details')">
                <x-form.grid>
                    <x-form.full>
                        <flux:input name="name" :label="__('Name')" :value="old('name', $academicYear->name)" required autofocus />
                    </x-form.full>
                    <flux:input name="starts_on" type="date" :label="__('Starts on')" :value="old('starts_on', $academicYear->starts_on->toDateString())" required />
                    <flux:input name="ends_on" type="date" :label="__('Ends on')" :value="old('ends_on', $academicYear->ends_on->toDateString())" required />
                    <x-form.full>
                        <flux:checkbox name="is_current" value="1" :checked="old('is_current', $academicYear->is_current)" :label="__('Mark as current academic year')" />
                    </x-form.full>
                </x-form.grid>
            </x-form.section>

            <x-form.actions>
                <flux:button type="submit" variant="primary">{{ __('Save changes') }}</flux:button>
                <flux:button :href="route('admin.academic-years.index')" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
            </x-form.actions>
        </form>
    </x-form.page>
</x-layouts::app>
