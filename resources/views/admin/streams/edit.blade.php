<x-layouts::app :title="__('Edit stream')">
    <x-form.page :title="__('Edit stream')">
        <form method="POST" action="{{ route('admin.streams.update', $stream) }}" class="flex flex-col gap-6">
            @csrf
            @method('PUT')

            <x-form.section :title="__('Stream details')">
                <x-form.grid>
                    <flux:input name="name" :label="__('Name')" :value="old('name', $stream->name)" required autofocus />
                    <flux:input name="code" :label="__('Code')" :value="old('code', $stream->code)" required />
                </x-form.grid>
            </x-form.section>

            <x-form.actions>
                <flux:button type="submit" variant="primary">{{ __('Save changes') }}</flux:button>
                <flux:button :href="route('admin.streams.index')" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
            </x-form.actions>
        </form>
    </x-form.page>
</x-layouts::app>
