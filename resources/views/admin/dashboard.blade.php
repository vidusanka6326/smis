<x-layouts::app :title="__('Admin Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div>
            <flux:heading size="xl">{{ __('Admin Dashboard') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Manage users and academic structure.') }}</flux:text>
        </div>

        @if (session('status'))
            <flux:callout variant="success" icon="check-circle">
                <flux:callout.heading>{{ session('status') }}</flux:callout.heading>
            </flux:callout>
        @endif

        <div class="flex flex-wrap gap-3">
            <flux:button :href="route('admin.users.create')" variant="primary" wire:navigate>
                {{ __('Create user') }}
            </flux:button>
            <flux:button :href="route('admin.academic-years.index')" variant="filled" wire:navigate>
                {{ __('Academic years') }}
            </flux:button>
            <flux:button :href="route('admin.grades.index')" variant="filled" wire:navigate>
                {{ __('Grades') }}
            </flux:button>
            <flux:button :href="route('admin.streams.index')" variant="filled" wire:navigate>
                {{ __('Streams') }}
            </flux:button>
            <flux:button :href="route('admin.subjects.index')" variant="filled" wire:navigate>
                {{ __('Subjects') }}
            </flux:button>
            <flux:button :href="route('admin.classes.index')" variant="filled" wire:navigate>
                {{ __('Classes') }}
            </flux:button>
        </div>
    </div>
</x-layouts::app>
