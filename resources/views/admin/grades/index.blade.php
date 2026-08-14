<x-layouts::app :title="__('Grades')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <flux:heading size="xl">{{ __('Grades') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Grades 1–13. Streams apply only to grades 12 and 13.') }}</flux:text>
            </div>
            <flux:button :href="route('admin.grades.create')" variant="primary" wire:navigate>{{ __('Add grade') }}</flux:button>
        </div>

        <x-list.flash />

        <x-list.filters :action="route('admin.grades.index')" :filters="$filters">
            <flux:input name="search" :label="__('Search')" :value="$filters['search'] ?? ''" placeholder="{{ __('Name or number') }}" />
        </x-list.filters>

        <x-list.table>
            <x-slot:head>
                <th class="px-4 py-3 font-medium">{{ __('Number') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Name') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Actions') }}</th>
            </x-slot:head>
            @forelse ($grades as $grade)
                <tr class="border-t border-border">
                    <td class="px-4 py-3">{{ $grade->number }}</td>
                    <td class="px-4 py-3">{{ $grade->name }}</td>
                    <td class="px-4 py-3">
                        <div class="flex flex-wrap gap-2">
                            <flux:button size="sm" :href="route('admin.grades.edit', $grade)" variant="ghost" wire:navigate>{{ __('Edit') }}</flux:button>
                            <form method="POST" action="{{ route('admin.grades.destroy', $grade) }}" onsubmit="return confirm(@js(__('Delete this grade?')))">
                                @csrf
                                @method('DELETE')
                                <flux:button size="sm" type="submit" variant="danger">{{ __('Delete') }}</flux:button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr class="border-t border-border">
                    <td colspan="3" class="px-4 py-10 text-center text-muted-foreground">{{ __('No grades match these filters.') }}</td>
                </tr>
            @endforelse
        </x-list.table>

        <x-list.pagination :paginator="$grades" />
    </div>
</x-layouts::app>
