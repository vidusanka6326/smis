<x-layouts::app :title="__('Officers')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <flux:heading size="xl">{{ __('Officers') }}</flux:heading>
                <flux:text class="mt-1">{{ __('School office staff who enter operational data. Only admins manage this section.') }}</flux:text>
            </div>
            <flux:button :href="route('admin.officers.create')" variant="primary" wire:navigate>{{ __('Add officer') }}</flux:button>
        </div>

        <x-list.flash />

        <x-list.filters :action="route('admin.officers.index')" :filters="$filters">
            <flux:input name="search" :label="__('Search')" :value="$filters['search'] ?? ''" placeholder="{{ __('Name / email') }}" />
            <flux:select name="status" :label="__('Status')" :placeholder="__('All')">
                @foreach ($statuses as $status)
                    <flux:select.option :value="$status->value" :selected="($filters['status'] ?? null) === $status->value">
                        {{ $status->label() }}
                    </flux:select.option>
                @endforeach
            </flux:select>
        </x-list.filters>

        <x-list.table>
            <x-slot:head>
                <th class="px-4 py-3 font-medium">{{ __('Name') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Email') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Status') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Actions') }}</th>
            </x-slot:head>
            @forelse ($officers as $officer)
                <tr class="border-t border-border">
                    <td class="px-4 py-3">{{ $officer->name }}</td>
                    <td class="px-4 py-3">{{ $officer->email }}</td>
                    <td class="px-4 py-3">{{ $officer->status->label() }}</td>
                    <td class="px-4 py-3">
                        <div class="flex flex-wrap gap-2">
                            <flux:button size="sm" :href="route('admin.officers.edit', $officer)" variant="ghost" wire:navigate>{{ __('Edit') }}</flux:button>
                            <form method="POST" action="{{ route('admin.officers.destroy', $officer) }}" onsubmit="return confirm(@js(__('Remove this officer?')))">
                                @csrf
                                @method('DELETE')
                                <flux:button size="sm" type="submit" variant="danger">{{ __('Remove') }}</flux:button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr class="border-t border-border">
                    <td colspan="4" class="px-4 py-10 text-center text-muted-foreground">{{ __('No officers match these filters.') }}</td>
                </tr>
            @endforelse
        </x-list.table>

        <x-list.pagination :paginator="$officers" />
    </div>
</x-layouts::app>
