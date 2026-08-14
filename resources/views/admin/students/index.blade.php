<x-layouts::app :title="__('Students')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <flux:heading size="xl">{{ __('Students') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Search and filter by grade, class, subject, gender, or status.') }}</flux:text>
            </div>
            <flux:button :href="route('admin.students.create')" variant="primary" wire:navigate>{{ __('Add student') }}</flux:button>
        </div>

        <x-list.flash />

        <x-list.filters :action="route('admin.students.index')" :filters="$filters">
            <flux:input name="search" :label="__('Search')" :value="$filters['search'] ?? ''" placeholder="{{ __('Name / admission / email') }}" />
            <flux:select name="gender" :label="__('Gender')" :placeholder="__('All')">
                @foreach ($genders as $gender)
                    <flux:select.option :value="$gender->value" :selected="($filters['gender'] ?? null) === $gender->value">
                        {{ $gender->label() }}
                    </flux:select.option>
                @endforeach
            </flux:select>
            <flux:select name="status" :label="__('Status')" :placeholder="__('All')">
                @foreach ($statuses as $status)
                    <flux:select.option :value="$status->value" :selected="($filters['status'] ?? null) === $status->value">
                        {{ $status->label() }}
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
            <flux:select name="class_id" :label="__('Class')" :placeholder="__('All')">
                @foreach ($classes as $class)
                    <flux:select.option :value="$class->id" :selected="(string) ($filters['class_id'] ?? '') === (string) $class->id">
                        {{ $class->code }}
                    </flux:select.option>
                @endforeach
            </flux:select>
            <flux:select name="subject_id" :label="__('Subject')" :placeholder="__('All')">
                @foreach ($subjects as $subject)
                    <flux:select.option :value="$subject->id" :selected="(string) ($filters['subject_id'] ?? '') === (string) $subject->id">
                        {{ $subject->name }}
                    </flux:select.option>
                @endforeach
            </flux:select>
        </x-list.filters>

        <x-list.table>
            <x-slot:head>
                <th class="px-4 py-3 font-medium">{{ __('Name') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Admission') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Gender') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Class') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Actions') }}</th>
            </x-slot:head>
            @forelse ($students as $student)
                <tr class="border-t border-border">
                    <td class="px-4 py-3">{{ $student->user?->name }}</td>
                    <td class="px-4 py-3">{{ $student->admission_no }}</td>
                    <td class="px-4 py-3">{{ $student->gender->label() }}</td>
                    <td class="px-4 py-3">{{ $student->currentClass?->code ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <div class="flex flex-wrap gap-2">
                            <flux:button size="sm" :href="route('admin.students.show', $student)" variant="ghost" wire:navigate>{{ __('View') }}</flux:button>
                            <flux:button size="sm" :href="route('admin.students.edit', $student)" variant="ghost" wire:navigate>{{ __('Edit') }}</flux:button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr class="border-t border-border">
                    <td colspan="5" class="px-4 py-10 text-center text-muted-foreground">{{ __('No students match these filters.') }}</td>
                </tr>
            @endforelse
        </x-list.table>

        <x-list.pagination :paginator="$students" />
    </div>
</x-layouts::app>
