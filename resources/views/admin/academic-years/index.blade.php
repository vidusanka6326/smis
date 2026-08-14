<x-layouts::app :title="__('Academic years')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <flux:heading size="xl">{{ __('Academic years') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Configure school academic years and mark the current year.') }}</flux:text>
            </div>
            <flux:button :href="route('admin.academic-years.create')" variant="primary" wire:navigate>
                {{ __('Add academic year') }}
            </flux:button>
        </div>

        <x-list.flash />

        <x-list.filters :action="route('admin.academic-years.index')" :filters="$filters">
            <flux:input name="search" :label="__('Search')" :value="$filters['search'] ?? ''" placeholder="{{ __('Year name') }}" />
            <flux:select name="is_current" :label="__('Current')" :placeholder="__('All')">
                <flux:select.option value="1" :selected="($filters['is_current'] ?? null) === '1'">{{ __('Current only') }}</flux:select.option>
                <flux:select.option value="0" :selected="($filters['is_current'] ?? null) === '0'">{{ __('Not current') }}</flux:select.option>
            </flux:select>
        </x-list.filters>

        <x-list.table>
            <x-slot:head>
                <th class="px-4 py-3 font-medium">{{ __('Name') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Starts') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Ends') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Current') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Actions') }}</th>
            </x-slot:head>
            @forelse ($academicYears as $academicYear)
                <tr class="border-t border-border">
                    <td class="px-4 py-3">{{ $academicYear->name }}</td>
                    <td class="px-4 py-3">{{ $academicYear->starts_on->toDateString() }}</td>
                    <td class="px-4 py-3">{{ $academicYear->ends_on->toDateString() }}</td>
                    <td class="px-4 py-3">{{ $academicYear->is_current ? __('Yes') : __('No') }}</td>
                    <td class="px-4 py-3">
                        <div class="flex flex-wrap gap-2">
                            <flux:button size="sm" :href="route('admin.academic-years.edit', $academicYear)" variant="ghost" wire:navigate>
                                {{ __('Edit') }}
                            </flux:button>
                            <form method="POST" action="{{ route('admin.academic-years.destroy', $academicYear) }}" onsubmit="return confirm(@js(__('Delete this academic year?')))">
                                @csrf
                                @method('DELETE')
                                <flux:button size="sm" type="submit" variant="danger">{{ __('Delete') }}</flux:button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr class="border-t border-border">
                    <td colspan="5" class="px-4 py-10 text-center text-muted-foreground">{{ __('No academic years match these filters.') }}</td>
                </tr>
            @endforelse
        </x-list.table>

        <x-list.pagination :paginator="$academicYears" />
    </div>
</x-layouts::app>
