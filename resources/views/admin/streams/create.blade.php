<x-layouts::app :title="__('Add stream')">
    <div class="mx-auto flex w-full max-w-xl flex-col gap-6">
        <div>
            <flux:heading size="xl">{{ __('Add stream') }}</flux:heading>
        </div>

        <form method="POST" action="{{ route('admin.streams.store') }}" class="flex flex-col gap-6">
            @csrf
            <flux:input name="name" :label="__('Name')" :value="old('name')" required autofocus />
            <flux:input name="code" :label="__('Code')" :value="old('code')" placeholder="SCI" required />

            <div class="flex gap-3">
                <flux:button type="submit" variant="primary">{{ __('Save') }}</flux:button>
                <flux:button :href="route('admin.streams.index')" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </div>
</x-layouts::app>
