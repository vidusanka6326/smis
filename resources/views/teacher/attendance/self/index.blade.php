<x-layouts::app :title="__('My attendance')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div>
            <flux:heading size="xl">{{ __('My attendance') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Record your own daily attendance.') }}</flux:text>
        </div>

        <x-list.flash />

        <form method="POST" action="{{ route('teacher.attendance.self.store') }}" class="grid gap-3 rounded-xl border border-border bg-card p-4 md:grid-cols-3">
            @csrf
            <input type="hidden" name="teacher_id" value="{{ $teacher->id }}">
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

        <x-list.filters :action="route('teacher.attendance.self.index')" :filters="$filters">
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
                <th class="px-4 py-3 font-medium">{{ __('Status') }}</th>
            </x-slot:head>
            @forelse ($records as $record)
                <tr class="border-t border-border">
                    <td class="px-4 py-3">{{ $record->date->toDateString() }}</td>
                    <td class="px-4 py-3">{{ $record->status->label() }}</td>
                </tr>
            @empty
                <tr class="border-t border-border">
                    <td colspan="2" class="px-4 py-10 text-center text-muted-foreground">{{ __('No records match these filters.') }}</td>
                </tr>
            @endforelse
        </x-list.table>

        <x-list.pagination :paginator="$records" />
    </div>
</x-layouts::app>
