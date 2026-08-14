<x-layouts::app :title="__('Exams')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <flux:heading size="xl">{{ __('Exams') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Create term tests, scholarship, O/L and A/L exams.') }}</flux:text>
            </div>
            <div class="flex flex-wrap gap-2">
                <flux:button :href="route('admin.marks.index')" variant="filled" wire:navigate>{{ __('Mark entry') }}</flux:button>
                <flux:button :href="route('admin.exams.create')" variant="primary" wire:navigate>{{ __('New exam') }}</flux:button>
            </div>
        </div>

        <x-list.flash />

        <x-list.filters :action="route('admin.exams.index')" :filters="$filters">
            <flux:input name="search" :label="__('Search')" :value="$filters['search'] ?? ''" placeholder="{{ __('Exam name') }}" />
            <flux:select name="academic_year_id" :label="__('Academic year')" :placeholder="__('All')">
                @foreach ($academicYears as $year)
                    <flux:select.option :value="$year->id" :selected="(string) ($filters['academic_year_id'] ?? '') === (string) $year->id">{{ $year->name }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:select name="type" :label="__('Type')" :placeholder="__('All')">
                @foreach ($types as $type)
                    <flux:select.option :value="$type->value" :selected="($filters['type'] ?? null) === $type->value">{{ $type->label() }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:select name="grade_id" :label="__('Grade')" :placeholder="__('All')">
                @foreach ($grades as $grade)
                    <flux:select.option :value="$grade->id" :selected="(string) ($filters['grade_id'] ?? '') === (string) $grade->id">{{ $grade->name }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:select name="school_class_id" :label="__('Class')" :placeholder="__('All')">
                @foreach ($schoolClasses as $class)
                    <flux:select.option :value="$class->id" :selected="(string) ($filters['school_class_id'] ?? '') === (string) $class->id">{{ $class->code }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:select name="status" :label="__('Status')" :placeholder="__('All')">
                <flux:select.option value="draft" :selected="($filters['status'] ?? null) === 'draft'">{{ __('Draft') }}</flux:select.option>
                <flux:select.option value="published" :selected="($filters['status'] ?? null) === 'published'">{{ __('Published') }}</flux:select.option>
            </flux:select>
        </x-list.filters>

        <x-list.table>
            <x-slot:head>
                <th class="px-4 py-3 font-medium">{{ __('Name') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Type') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Scope') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Dates') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Status') }}</th>
                <th class="px-4 py-3 font-medium"></th>
            </x-slot:head>
            @forelse ($exams as $exam)
                <tr class="border-t border-border">
                    <td class="px-4 py-3">{{ $exam->name }}</td>
                    <td class="px-4 py-3">{{ $exam->type->label() }}</td>
                    <td class="px-4 py-3">{{ $exam->schoolClass?->code ?? $exam->grade?->name ?? '—' }}</td>
                    <td class="px-4 py-3">{{ $exam->starts_on->toDateString() }} → {{ $exam->ends_on->toDateString() }}</td>
                    <td class="px-4 py-3">{{ $exam->isPublished() ? __('Published') : __('Draft') }}</td>
                    <td class="px-4 py-3">
                        <flux:button size="sm" :href="route('admin.exams.edit', $exam)" variant="ghost" wire:navigate>{{ __('Open') }}</flux:button>
                    </td>
                </tr>
            @empty
                <tr class="border-t border-border">
                    <td colspan="6" class="px-4 py-10 text-center text-muted-foreground">{{ __('No exams match these filters.') }}</td>
                </tr>
            @endforelse
        </x-list.table>

        <x-list.pagination :paginator="$exams" />
    </div>
</x-layouts::app>
