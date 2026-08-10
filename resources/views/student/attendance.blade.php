<x-layouts::app :title="__('My attendance')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div>
            <flux:heading size="xl">{{ __('My attendance') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Your attendance history and monthly percentage.') }}</flux:text>
        </div>

        <form method="GET" action="{{ route('student.attendance') }}" class="flex flex-wrap items-end gap-3">
            <flux:input type="month" name="month" :label="__('Month')" :value="$month" />
            <flux:button type="submit" variant="filled">{{ __('Load') }}</flux:button>
        </form>

        <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
            <flux:heading size="sm">{{ __('Summary for :month', ['month' => $month]) }}</flux:heading>
            <p class="mt-2 text-2xl font-semibold">{{ $percentage }}%</p>
            <p class="mt-1 text-sm text-zinc-500">
                {{ __('Present :p · Absent :a · Late :l · Excused :e', [
                    'p' => $counts['present'],
                    'a' => $counts['absent'],
                    'l' => $counts['late'],
                    'e' => $counts['excused'],
                ]) }}
            </p>
        </div>

        <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
            <table class="min-w-full text-sm">
                <thead class="bg-zinc-50 dark:bg-zinc-900">
                    <tr>
                        <th class="px-3 py-2 text-left">{{ __('Date') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('Scope') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('Status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($records as $record)
                        <tr class="border-t border-zinc-200 dark:border-zinc-700">
                            <td class="px-3 py-2">{{ $record->attendanceSession?->date?->toDateString() }}</td>
                            <td class="px-3 py-2">{{ $record->attendanceSession?->subject?->name ?? __('Class') }}</td>
                            <td class="px-3 py-2">{{ $record->status->label() }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-3 py-6 text-zinc-500">{{ __('No attendance records this month.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts::app>
