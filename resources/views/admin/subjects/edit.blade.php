<x-layouts::app :title="__('Edit subject')">
    <x-form.page :title="__('Edit subject')">
        <form method="POST" action="{{ route('admin.subjects.update', $subject) }}" class="flex flex-col gap-6">
            @csrf
            @method('PUT')

            <x-form.section :title="__('Subject details')">
                <x-form.grid>
                    <flux:input name="name" :label="__('Name')" :value="old('name', $subject->name)" required autofocus />
                    <flux:input name="code" :label="__('Code')" :value="old('code', $subject->code)" required />
                </x-form.grid>
            </x-form.section>

            <x-form.section :title="__('Grade range')" :description="__('Which grades this subject can be offered to.')">
                <x-form.grid>
                    <flux:input name="min_grade" type="number" min="1" max="13" :label="__('Minimum grade')" :value="old('min_grade', $subject->min_grade)" required />
                    <flux:input name="max_grade" type="number" min="1" max="13" :label="__('Maximum grade')" :value="old('max_grade', $subject->max_grade)" required />
                </x-form.grid>
            </x-form.section>

            <x-form.actions>
                <flux:button type="submit" variant="primary">{{ __('Save changes') }}</flux:button>
                <flux:button :href="route('admin.subjects.index')" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
            </x-form.actions>
        </form>
    </x-form.page>
</x-layouts::app>
