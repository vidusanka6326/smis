<x-layouts::app :title="__('Classes')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <flux:heading size="xl">{{ __('Classes') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Class sections per academic year, grade, and stream (where applicable).') }}</flux:text>
            </div>
            <flux:button :href="route('admin.classes.create')" variant="primary" wire:navigate>{{ __('Add class') }}</flux:button>
        </div>

        <x-list.flash />

        <x-list.filters :action="route('admin.classes.index')" :filters="$filters">
            <flux:input name="search" :label="__('Search')" :value="$filters['search'] ?? ''" placeholder="{{ __('Code or section') }}" />
            <flux:select name="academic_year_id" :label="__('Academic year')" :placeholder="__('All')">
                @foreach ($academicYears as $year)
                    <flux:select.option :value="$year->id" :selected="(string) ($filters['academic_year_id'] ?? '') === (string) $year->id">
                        {{ $year->name }}
                    </flux:select.option>
                @endforeach
            </flux:select>
            <flux:select name="grade_id" :label="__('Grade')" :placeholder="__('All')">
                @foreach ($grades as $grade)
                    <flux:select.option :value="$grade->id" :selected="(string) ($filters['grade_id'] ?? '') === (string) $grade->id">
                        {{ $grade->name }}
                    </flux:select.option>
                @endforeach
            </flux:select>
            <flux:select name="stream_id" :label="__('Stream')" :placeholder="__('All')">
                @foreach ($streams as $stream)
                    <flux:select.option :value="$stream->id" :selected="(string) ($filters['stream_id'] ?? '') === (string) $stream->id">
                        {{ $stream->name }}
                    </flux:select.option>
                @endforeach
            </flux:select>
        </x-list.filters>

        <x-list.table>
            <x-slot:head>
                <th class="px-4 py-3 font-medium">{{ __('Code') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Year') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Grade') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Stream') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Teacher') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Actions') }}</th>
            </x-slot:head>
            @forelse ($schoolClasses as $schoolClass)
                <tr class="border-t border-border">
                    <td class="px-4 py-3">{{ $schoolClass->code }}</td>
                    <td class="px-4 py-3">{{ $schoolClass->academicYear?->name }}</td>
                    <td class="px-4 py-3">{{ $schoolClass->grade?->name }}</td>
                    <td class="px-4 py-3">{{ $schoolClass->stream?->name ?? '—' }}</td>
                    <td class="px-4 py-3">{{ $schoolClass->classTeacher?->user?->name ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <div class="flex flex-wrap gap-2">
                            <flux:button size="sm" :href="route('admin.classes.edit', $schoolClass)" variant="ghost" wire:navigate>{{ __('Edit') }}</flux:button>
                            <form method="POST" action="{{ route('admin.classes.destroy', $schoolClass) }}" onsubmit="return confirm(@js(__('Delete this class?')))">
                                @csrf
                                @method('DELETE')
                                <flux:button size="sm" type="submit" variant="danger">{{ __('Delete') }}</flux:button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr class="border-t border-border">
                    <td colspan="6" class="px-4 py-10 text-center text-muted-foreground">{{ __('No classes match these filters.') }}</td>
                </tr>
            @endforelse
        </x-list.table>

        <x-list.pagination :paginator="$schoolClasses" />
    </div>
</x-layouts::app>
