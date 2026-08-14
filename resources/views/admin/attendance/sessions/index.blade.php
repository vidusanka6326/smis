<x-layouts::app :title="__('Student attendance')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <flux:heading size="xl">{{ __('Student attendance') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Class and subject sessions with present / absent / late / excused.') }}</flux:text>
            </div>
            <div class="flex flex-wrap gap-2">
                <flux:button :href="route('admin.attendance.monthly')" variant="filled" wire:navigate>{{ __('Monthly summary') }}</flux:button>
                <flux:button :href="route('admin.attendance.teachers.index')" variant="filled" wire:navigate>{{ __('Teacher attendance') }}</flux:button>
                <flux:button :href="route('admin.attendance.sessions.create')" variant="primary" wire:navigate>{{ __('Take attendance') }}</flux:button>
            </div>
        </div>

        <x-list.flash />

        <x-list.filters :action="route('admin.attendance.sessions.index')" :filters="$filters">
            <flux:select name="academic_year_id" :label="__('Academic year')" :placeholder="__('All')">
                @foreach ($academicYears as $year)
                    <flux:select.option :value="$year->id" :selected="(string) ($filters['academic_year_id'] ?? '') === (string) $year->id">{{ $year->name }}</flux:select.option>
                @endforeach
            </flux:select>
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
                <th class="px-4 py-3 font-medium">{{ __('Taken by') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Status') }}</th>
                <th class="px-4 py-3 font-medium"></th>
            </x-slot:head>
            @forelse ($sessions as $session)
                <tr class="border-t border-border">
                    <td class="px-4 py-3">{{ $session->date->toDateString() }}</td>
                    <td class="px-4 py-3">{{ $session->schoolClass?->code }}</td>
                    <td class="px-4 py-3">{{ $session->subject?->name ?? __('Class') }}</td>
                    <td class="px-4 py-3">{{ $session->takenByTeacher?->user?->name ?? '—' }}</td>
                    <td class="px-4 py-3">{{ $session->isFinalized() ? __('Finalized') : __('Open') }}</td>
                    <td class="px-4 py-3">
                        <flux:button size="sm" :href="route('admin.attendance.sessions.edit', $session)" variant="ghost" wire:navigate>{{ __('Open') }}</flux:button>
                    </td>
                </tr>
            @empty
                <tr class="border-t border-border">
                    <td colspan="6" class="px-4 py-10 text-center text-muted-foreground">{{ __('No attendance sessions match these filters.') }}</td>
                </tr>
            @endforelse
        </x-list.table>

        <x-list.pagination :paginator="$sessions" />
    </div>
</x-layouts::app>
