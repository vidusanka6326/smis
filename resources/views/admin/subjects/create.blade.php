<x-layouts::app :title="__('Add subject')">
    <div class="mx-auto flex w-full max-w-xl flex-col gap-6">
        <div>
            <flux:heading size="xl">{{ __('Add subject') }}</flux:heading>
        </div>

        <form method="POST" action="{{ route('admin.subjects.store') }}" class="flex flex-col gap-6">
            @csrf
            <flux:input name="name" :label="__('Name')" :value="old('name')" required autofocus />
            <flux:input name="code" :label="__('Code')" :value="old('code')" placeholder="MATH" required />
            <flux:input name="min_grade" type="number" min="1" max="13" :label="__('Minimum grade')" :value="old('min_grade', 1)" required />
            <flux:input name="max_grade" type="number" min="1" max="13" :label="__('Maximum grade')" :value="old('max_grade', 13)" required />

            <div class="flex gap-3">
                <flux:button type="submit" variant="primary">{{ __('Save') }}</flux:button>
                <flux:button :href="route('admin.subjects.index')" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </div>
</x-layouts::app>
