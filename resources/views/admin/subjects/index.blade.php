<x-layouts::app :title="__('Subjects')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <flux:heading size="xl">{{ __('Subjects') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Subjects with the grade range they apply to.') }}</flux:text>
            </div>
            <flux:button :href="route('admin.subjects.create')" variant="primary" wire:navigate>{{ __('Add subject') }}</flux:button>
        </div>

        <x-list.flash />

        <x-list.filters :action="route('admin.subjects.index')" :filters="$filters">
            <flux:input name="search" :label="__('Search')" :value="$filters['search'] ?? ''" placeholder="{{ __('Name or code') }}" />
            <flux:select name="grade" :label="__('Grade')" :placeholder="__('All')">
                @foreach ($grades as $grade)
                    <flux:select.option :value="$grade->number" :selected="(string) ($filters['grade'] ?? '') === (string) $grade->number">
                        {{ $grade->name }}
                    </flux:select.option>
                @endforeach
            </flux:select>
        </x-list.filters>

        <x-list.table>
            <x-slot:head>
                <th class="px-4 py-3 font-medium">{{ __('Name') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Code') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Grades') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Actions') }}</th>
            </x-slot:head>
            @forelse ($subjects as $subject)
                <tr class="border-t border-border">
                    <td class="px-4 py-3">{{ $subject->name }}</td>
                    <td class="px-4 py-3">{{ $subject->code }}</td>
                    <td class="px-4 py-3">{{ $subject->min_grade }}–{{ $subject->max_grade }}</td>
                    <td class="px-4 py-3">
                        <div class="flex flex-wrap gap-2">
                            <flux:button size="sm" :href="route('admin.subjects.edit', $subject)" variant="ghost" wire:navigate>{{ __('Edit') }}</flux:button>
                            <form method="POST" action="{{ route('admin.subjects.destroy', $subject) }}" onsubmit="return confirm(@js(__('Delete this subject?')))">
                                @csrf
                                @method('DELETE')
                                <flux:button size="sm" type="submit" variant="danger">{{ __('Delete') }}</flux:button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr class="border-t border-border">
                    <td colspan="4" class="px-4 py-10 text-center text-muted-foreground">{{ __('No subjects match these filters.') }}</td>
                </tr>
            @endforelse
        </x-list.table>

        <x-list.pagination :paginator="$subjects" />
    </div>
</x-layouts::app>
