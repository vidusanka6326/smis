<x-layouts::app :title="__('Edit subject')">
    <div class="mx-auto flex w-full max-w-xl flex-col gap-6">
        <div>
            <flux:heading size="xl">{{ __('Edit subject') }}</flux:heading>
        </div>

        <form method="POST" action="{{ route('admin.subjects.update', $subject) }}" class="flex flex-col gap-6">
            @csrf
            @method('PUT')
            <flux:input name="name" :label="__('Name')" :value="old('name', $subject->name)" required autofocus />
            <flux:input name="code" :label="__('Code')" :value="old('code', $subject->code)" required />
            <flux:input name="min_grade" type="number" min="1" max="13" :label="__('Minimum grade')" :value="old('min_grade', $subject->min_grade)" required />
            <flux:input name="max_grade" type="number" min="1" max="13" :label="__('Maximum grade')" :value="old('max_grade', $subject->max_grade)" required />

            <div class="flex gap-3">
                <flux:button type="submit" variant="primary">{{ __('Save changes') }}</flux:button>
                <flux:button :href="route('admin.subjects.index')" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </div>
</x-layouts::app>
