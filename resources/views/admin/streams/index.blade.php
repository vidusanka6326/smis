<x-layouts::app :title="__('Streams')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <flux:heading size="xl">{{ __('Streams') }}</flux:heading>
                <flux:text class="mt-1">{{ __('A/L streams for grades 12–13 (Science, Commerce, Arts, Technology).') }}</flux:text>
            </div>
            <flux:button :href="route('admin.streams.create')" variant="primary" wire:navigate>{{ __('Add stream') }}</flux:button>
        </div>

        <x-list.flash />

        <x-list.filters :action="route('admin.streams.index')" :filters="$filters">
            <flux:input name="search" :label="__('Search')" :value="$filters['search'] ?? ''" placeholder="{{ __('Name or code') }}" />
        </x-list.filters>

        <x-list.table>
            <x-slot:head>
                <th class="px-4 py-3 font-medium">{{ __('Name') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Code') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Actions') }}</th>
            </x-slot:head>
            @forelse ($streams as $stream)
                <tr class="border-t border-border">
                    <td class="px-4 py-3">{{ $stream->name }}</td>
                    <td class="px-4 py-3">{{ $stream->code }}</td>
                    <td class="px-4 py-3">
                        <div class="flex flex-wrap gap-2">
                            <flux:button size="sm" :href="route('admin.streams.edit', $stream)" variant="ghost" wire:navigate>{{ __('Edit') }}</flux:button>
                            <form method="POST" action="{{ route('admin.streams.destroy', $stream) }}" onsubmit="return confirm(@js(__('Delete this stream?')))">
                                @csrf
                                @method('DELETE')
                                <flux:button size="sm" type="submit" variant="danger">{{ __('Delete') }}</flux:button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr class="border-t border-border">
                    <td colspan="3" class="px-4 py-10 text-center text-muted-foreground">{{ __('No streams match these filters.') }}</td>
                </tr>
            @endforelse
        </x-list.table>

        <x-list.pagination :paginator="$streams" />
    </div>
</x-layouts::app>
