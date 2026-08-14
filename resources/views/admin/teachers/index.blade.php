<x-layouts::app :title="__('Teachers')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <flux:heading size="xl">{{ __('Teachers') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Search by name or employee number, or filter by class and assignment.') }}</flux:text>
            </div>
            <flux:button :href="route('admin.teachers.create')" variant="primary" wire:navigate>{{ __('Add teacher') }}</flux:button>
        </div>

        <x-list.flash />

        <x-list.filters :action="route('admin.teachers.index')" :filters="$filters">
            <flux:input name="search" :label="__('Search')" :value="$filters['search'] ?? ''" placeholder="{{ __('Name / employee no. / email') }}" />
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
            <flux:select name="role" :label="__('Assignment')" :placeholder="__('All')">
                @foreach ($roles as $role)
                    <flux:select.option :value="$role->value" :selected="($filters['role'] ?? null) === $role->value">
                        {{ $role->label() }}
                    </flux:select.option>
                @endforeach
            </flux:select>
        </x-list.filters>

        <x-list.table>
            <x-slot:head>
                <th class="px-4 py-3 font-medium">{{ __('Name') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Employee no.') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Email') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Actions') }}</th>
            </x-slot:head>
            @forelse ($teachers as $teacher)
                <tr class="border-t border-border">
                    <td class="px-4 py-3">{{ $teacher->user?->name }}</td>
                    <td class="px-4 py-3">{{ $teacher->employee_no }}</td>
                    <td class="px-4 py-3">{{ $teacher->user?->email }}</td>
                    <td class="px-4 py-3">
                        <div class="flex flex-wrap gap-2">
                            <flux:button size="sm" :href="route('admin.teachers.show', $teacher)" variant="ghost" wire:navigate>{{ __('View') }}</flux:button>
                            <flux:button size="sm" :href="route('admin.teachers.edit', $teacher)" variant="ghost" wire:navigate>{{ __('Edit') }}</flux:button>
                            <flux:button size="sm" :href="route('admin.teachers.assignments.edit', $teacher)" variant="ghost" wire:navigate>{{ __('Assignments') }}</flux:button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr class="border-t border-border">
                    <td colspan="4" class="px-4 py-10 text-center text-muted-foreground">{{ __('No teachers match these filters.') }}</td>
                </tr>
            @endforelse
        </x-list.table>

        <x-list.pagination :paginator="$teachers" />
    </div>
</x-layouts::app>
