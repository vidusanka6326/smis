<x-layouts::app :title="__('Edit grade')">
    <div class="mx-auto flex w-full max-w-xl flex-col gap-6">
        <div>
            <flux:heading size="xl">{{ __('Edit grade') }}</flux:heading>
        </div>

        <form method="POST" action="{{ route('admin.grades.update', $grade) }}" class="flex flex-col gap-6">
            @csrf
            @method('PUT')
            <flux:input name="number" type="number" min="1" max="13" :label="__('Number')" :value="old('number', $grade->number)" required autofocus />
            <flux:input name="name" :label="__('Name')" :value="old('name', $grade->name)" required />

            <div class="flex gap-3">
                <flux:button type="submit" variant="primary">{{ __('Save changes') }}</flux:button>
                <flux:button :href="route('admin.grades.index')" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </div>
</x-layouts::app>
