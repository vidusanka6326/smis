<x-layouts::app :title="__('Marks')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div>
            <flux:heading size="xl">{{ __('Mark entry') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Subjects you can enter marks for.') }}</flux:text>
        </div>

        <x-list.flash />

        <x-list.filters :action="route('teacher.marks.index')" :filters="$filters">
            <flux:input name="search" :label="__('Search')" :value="$filters['search'] ?? ''" placeholder="{{ __('Exam or subject') }}" />
            <flux:select name="academic_year_id" :label="__('Academic year')" :placeholder="__('All')">
                @foreach ($academicYears as $year)
                    <flux:select.option :value="$year->id" :selected="(string) ($filters['academic_year_id'] ?? '') === (string) $year->id">{{ $year->name }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:select name="status" :label="__('Status')" :placeholder="__('All')">
                <flux:select.option value="draft" :selected="($filters['status'] ?? null) === 'draft'">{{ __('Open') }}</flux:select.option>
                <flux:select.option value="published" :selected="($filters['status'] ?? null) === 'published'">{{ __('Published') }}</flux:select.option>
            </flux:select>
        </x-list.filters>

        <x-list.table>
            <x-slot:head>
                <th class="px-4 py-3 font-medium">{{ __('Exam') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Subject') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Status') }}</th>
                <th class="px-4 py-3 font-medium"></th>
            </x-slot:head>
            @forelse ($examSubjects as $examSubject)
                <tr class="border-t border-border">
                    <td class="px-4 py-3">{{ $examSubject->exam?->name }}</td>
                    <td class="px-4 py-3">{{ $examSubject->subject?->name }}</td>
                    <td class="px-4 py-3">{{ $examSubject->exam?->isPublished() ? __('Published') : __('Open') }}</td>
                    <td class="px-4 py-3">
                        @can('enterMarks', $examSubject)
                            <flux:button size="sm" :href="route('teacher.marks.edit', $examSubject)" variant="ghost" wire:navigate>{{ __('Enter') }}</flux:button>
                        @else
                            —
                        @endcan
                    </td>
                </tr>
            @empty
                <tr class="border-t border-border">
                    <td colspan="4" class="px-4 py-10 text-center text-muted-foreground">{{ __('No exam subjects match these filters.') }}</td>
                </tr>
            @endforelse
        </x-list.table>

        <x-list.pagination :paginator="$examSubjects" />
    </div>
</x-layouts::app>
