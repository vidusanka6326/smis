<x-layouts::app :title="__('Edit academic year')">
    <div class="mx-auto flex w-full max-w-xl flex-col gap-6">
        <div>
            <flux:heading size="xl">{{ __('Edit academic year') }}</flux:heading>
            <flux:text class="mt-1">{{ $academicYear->name }}</flux:text>
        </div>

        <form method="POST" action="{{ route('admin.academic-years.update', $academicYear) }}" class="flex flex-col gap-6">
            @csrf
            @method('PUT')
            <flux:input name="name" :label="__('Name')" :value="old('name', $academicYear->name)" required autofocus />
            <flux:input name="starts_on" type="date" :label="__('Starts on')" :value="old('starts_on', $academicYear->starts_on->toDateString())" required />
            <flux:input name="ends_on" type="date" :label="__('Ends on')" :value="old('ends_on', $academicYear->ends_on->toDateString())" required />
            <flux:checkbox name="is_current" value="1" :checked="old('is_current', $academicYear->is_current)" :label="__('Mark as current academic year')" />

            <div class="flex gap-3">
                <flux:button type="submit" variant="primary">{{ __('Save changes') }}</flux:button>
                <flux:button :href="route('admin.academic-years.index')" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </div>
</x-layouts::app>
