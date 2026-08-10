<x-layouts::app :title="__('Admin Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div>
            <flux:heading size="xl">{{ __('Admin Dashboard') }}</flux:heading>
            <flux:text class="mt-1">{{ __('System administration overview. Module management arrives in later phases.') }}</flux:text>
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
        </div>

        <div class="relative h-48 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
        </div>
    </div>
</x-layouts::app>
