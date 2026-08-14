<x-layouts::app :title="__('Teacher attendance')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div>
            <flux:heading size="xl">{{ __('Teacher attendance') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Record daily attendance for teachers.') }}</flux:text>
        </div>

        <x-list.flash />

        <form method="POST" action="{{ route('admin.attendance.teachers.store') }}" class="grid gap-3 rounded-xl border border-border bg-card p-4 md:grid-cols-4">
            @csrf
            <flux:select name="teacher_id" :label="__('Teacher')">
                @foreach ($teachers as $teacher)
                    <flux:select.option :value="$teacher->id">{{ $teacher->user?->name }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:input type="date" name="date" :label="__('Date')" :value="now()->toDateString()" />
            <flux:select name="status" :label="__('Status')">
                @foreach ($statuses as $status)
                    <flux:select.option :value="$status->value">{{ $status->label() }}</flux:select.option>
                @endforeach
            </flux:select>
            <div class="flex items-end">
                <flux:button type="submit" variant="primary">{{ __('Save') }}</flux:button>
            </div>
        </form>

        <x-list.filters :action="route('admin.attendance.teachers.index')" :filters="$filters">
            <flux:select name="teacher_id" :label="__('Teacher')" :placeholder="__('All')">
                @foreach ($teachers as $teacher)
                    <flux:select.option :value="$teacher->id" :selected="(string) ($filters['teacher_id'] ?? '') === (string) $teacher->id">{{ $teacher->user?->name }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:select name="status" :label="__('Status')" :placeholder="__('All')">
                @foreach ($statuses as $status)
                    <flux:select.option :value="$status->value" :selected="($filters['status'] ?? null) === $status->value">{{ $status->label() }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:input type="date" name="date_from" :label="__('From')" :value="$filters['date_from'] ?? ''" />
            <flux:input type="date" name="date_to" :label="__('To')" :value="$filters['date_to'] ?? ''" />
        </x-list.filters>

        <x-list.table>
            <x-slot:head>
                <th class="px-4 py-3 font-medium">{{ __('Date') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Teacher') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Status') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Recorded by') }}</th>
                <th class="px-4 py-3 font-medium"></th>
            </x-slot:head>
            @forelse ($records as $record)
                <tr class="border-t border-border">
                    <td class="px-4 py-3">{{ $record->date->toDateString() }}</td>
                    <td class="px-4 py-3">{{ $record->teacher?->user?->name }}</td>
                    <td class="px-4 py-3">{{ $record->status->label() }}</td>
                    <td class="px-4 py-3">{{ $record->recordedBy?->name ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <form method="POST" action="{{ route('admin.attendance.teachers.destroy', $record) }}" onsubmit="return confirm(@js(__('Delete?')))">
                            @csrf
                            @method('DELETE')
                            <flux:button type="submit" variant="danger" size="sm">{{ __('Delete') }}</flux:button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr class="border-t border-border">
                    <td colspan="5" class="px-4 py-10 text-center text-muted-foreground">{{ __('No teacher attendance matches these filters.') }}</td>
                </tr>
            @endforelse
        </x-list.table>

        <x-list.pagination :paginator="$records" />
    </div>
</x-layouts::app>
