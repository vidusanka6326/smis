<x-layouts::app :title="__('My class students')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <flux:heading size="xl">{{ __('My class students') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Students in classes where you are the class teacher.') }}</flux:text>
            </div>
            <flux:button :href="route('teacher.students.create')" variant="primary" wire:navigate>{{ __('Add student') }}</flux:button>
        </div>

        <x-list.flash />

        <x-list.filters :action="route('teacher.students.index')" :filters="$filters">
            <flux:input name="search" :label="__('Search')" :value="$filters['search'] ?? ''" placeholder="{{ __('Name / admission') }}" />
            <flux:select name="gender" :label="__('Gender')" :placeholder="__('All')">
                @foreach ($genders as $gender)
                    <flux:select.option :value="$gender->value" :selected="($filters['gender'] ?? null) === $gender->value">{{ $gender->label() }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:select name="class_id" :label="__('Class')" :placeholder="__('All')">
                @foreach ($classes as $class)
                    <flux:select.option :value="$class->id" :selected="(string) ($filters['class_id'] ?? '') === (string) $class->id">{{ $class->code }}</flux:select.option>
                @endforeach
            </flux:select>
        </x-list.filters>

        <x-list.table>
            <x-slot:head>
                <th class="px-4 py-3 font-medium">{{ __('Name') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Admission') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Class') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Actions') }}</th>
            </x-slot:head>
            @forelse ($students as $student)
                <tr class="border-t border-border">
                    <td class="px-4 py-3">{{ $student->user?->name }}</td>
                    <td class="px-4 py-3">{{ $student->admission_no }}</td>
                    <td class="px-4 py-3">{{ $student->currentClass?->code }}</td>
                    <td class="px-4 py-3">
                        <flux:button size="sm" :href="route('teacher.students.edit', $student)" variant="ghost" wire:navigate>{{ __('Edit') }}</flux:button>
                    </td>
                </tr>
            @empty
                <tr class="border-t border-border">
                    <td colspan="4" class="px-4 py-10 text-center text-muted-foreground">{{ __('No students match these filters.') }}</td>
                </tr>
            @endforelse
        </x-list.table>

        <x-list.pagination :paginator="$students" />
    </div>
</x-layouts::app>
