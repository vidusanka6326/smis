<x-layouts::app :title="__('Attendance')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <flux:heading size="xl">{{ __('Attendance') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Sessions for your classes and subjects.') }}</flux:text>
            </div>
            <div class="flex flex-wrap gap-2">
                <flux:button :href="route('teacher.attendance.monthly')" variant="filled" wire:navigate>{{ __('Monthly') }}</flux:button>
                <flux:button :href="route('teacher.attendance.self.index')" variant="filled" wire:navigate>{{ __('My attendance') }}</flux:button>
                <flux:button :href="route('teacher.attendance.sessions.create')" variant="primary" wire:navigate>{{ __('Take attendance') }}</flux:button>
            </div>
        </div>

        <x-list.flash />

        <x-list.filters :action="route('teacher.attendance.sessions.index')" :filters="$filters">
            <flux:select name="school_class_id" :label="__('Class')" :placeholder="__('All')">
                @foreach ($schoolClasses as $class)
                    <flux:select.option :value="$class->id" :selected="(string) ($filters['school_class_id'] ?? '') === (string) $class->id">{{ $class->code }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:select name="subject_id" :label="__('Subject')" :placeholder="__('All')">
                @foreach ($subjects as $subject)
                    <flux:select.option :value="$subject->id" :selected="(string) ($filters['subject_id'] ?? '') === (string) $subject->id">{{ $subject->name }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:select name="scope" :label="__('Scope')" :placeholder="__('All')">
                <flux:select.option value="class" :selected="($filters['scope'] ?? null) === 'class'">{{ __('Class') }}</flux:select.option>
                <flux:select.option value="subject" :selected="($filters['scope'] ?? null) === 'subject'">{{ __('Subject') }}</flux:select.option>
            </flux:select>
            <flux:select name="status" :label="__('Status')" :placeholder="__('All')">
                <flux:select.option value="open" :selected="($filters['status'] ?? null) === 'open'">{{ __('Open') }}</flux:select.option>
                <flux:select.option value="finalized" :selected="($filters['status'] ?? null) === 'finalized'">{{ __('Finalized') }}</flux:select.option>
            </flux:select>
            <flux:input type="date" name="date_from" :label="__('From')" :value="$filters['date_from'] ?? ''" />
            <flux:input type="date" name="date_to" :label="__('To')" :value="$filters['date_to'] ?? ''" />
        </x-list.filters>

        <x-list.table>
            <x-slot:head>
                <th class="px-4 py-3 font-medium">{{ __('Date') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Class') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Scope') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Status') }}</th>
                <th class="px-4 py-3 font-medium"></th>
            </x-slot:head>
            @forelse ($sessions as $session)
                <tr class="border-t border-border">
                    <td class="px-4 py-3">{{ $session->date->toDateString() }}</td>
                    <td class="px-4 py-3">{{ $session->schoolClass?->code }}</td>
                    <td class="px-4 py-3">{{ $session->subject?->name ?? __('Class') }}</td>
                    <td class="px-4 py-3">{{ $session->isFinalized() ? __('Finalized') : __('Open') }}</td>
                    <td class="px-4 py-3">
                        @can('update', $session)
                            <flux:button size="sm" :href="route('teacher.attendance.sessions.edit', $session)" variant="ghost" wire:navigate>{{ __('Open') }}</flux:button>
                        @else
                            —
                        @endcan
                    </td>
                </tr>
            @empty
                <tr class="border-t border-border">
                    <td colspan="5" class="px-4 py-10 text-center text-muted-foreground">{{ __('No sessions match these filters.') }}</td>
                </tr>
            @endforelse
        </x-list.table>

        <x-list.pagination :paginator="$sessions" />
    </div>
</x-layouts::app>
