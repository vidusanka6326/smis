<x-layouts::app :title="__('My attendance')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div>
            <flux:heading size="xl">{{ __('My attendance') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Your attendance history and monthly percentage.') }}</flux:text>
        </div>

        <x-list.filters :action="route('student.attendance')" :filters="$filters" :submit="__('Load')">
            <x-form.month-select :value="$month" />
            <flux:select name="scope" :label="__('Scope')" :placeholder="__('All')">
                <flux:select.option value="class" :selected="($filters['scope'] ?? null) === 'class'">{{ __('Class') }}</flux:select.option>
                <flux:select.option value="subject" :selected="($filters['scope'] ?? null) === 'subject'">{{ __('Subject') }}</flux:select.option>
            </flux:select>
            <flux:select name="status" :label="__('Status')" :placeholder="__('All')">
                @foreach ($statuses as $status)
                    <flux:select.option :value="$status->value" :selected="($filters['status'] ?? null) === $status->value">{{ $status->label() }}</flux:select.option>
                @endforeach
            </flux:select>
        </x-list.filters>

        <div class="rounded-xl border border-border bg-card p-4">
            <flux:heading size="sm">{{ __('Summary for :month', ['month' => $month]) }}</flux:heading>
            <p class="mt-2 text-2xl font-semibold">{{ $percentage }}%</p>
            <p class="mt-1 text-sm text-muted-foreground">
                {{ __('Present :p · Absent :a · Late :l · Excused :e', [
                    'p' => $counts['present'],
                    'a' => $counts['absent'],
                    'l' => $counts['late'],
                    'e' => $counts['excused'],
                ]) }}
            </p>
        </div>

        <x-list.table>
            <x-slot:head>
                <th class="px-4 py-3 font-medium">{{ __('Date') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Scope') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Status') }}</th>
            </x-slot:head>
            @forelse ($records as $record)
                <tr class="border-t border-border">
                    <td class="px-4 py-3">{{ $record->attendanceSession?->date?->toDateString() }}</td>
                    <td class="px-4 py-3">{{ $record->attendanceSession?->subject?->name ?? __('Class') }}</td>
                    <td class="px-4 py-3">{{ $record->status->label() }}</td>
                </tr>
            @empty
                <tr class="border-t border-border">
                    <td colspan="3" class="px-4 py-10 text-center text-muted-foreground">{{ __('No attendance records match these filters.') }}</td>
                </tr>
            @endforelse
        </x-list.table>

        <x-list.pagination :paginator="$records" />
    </div>
</x-layouts::app>
