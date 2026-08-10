<x-layouts::app :title="__('Attendance report')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <flux:heading size="xl">{{ __('Attendance report') }}</flux:heading>

        <form method="GET" class="no-print flex flex-wrap items-end gap-3">
            <flux:input type="month" name="month" :label="__('Month')" :value="$month" />
            <flux:button type="submit" variant="filled">{{ __('Load') }}</flux:button>
        </form>

        <x-report-toolbar :print="$print">
            <flux:button :href="route('teacher.reports.attendance', ['month' => $month, 'export' => 'csv'])" variant="filled">{{ __('CSV') }}</flux:button>
            <flux:button :href="route('teacher.reports.attendance', ['month' => $month, 'print' => 1])" variant="filled">{{ __('Print / PDF') }}</flux:button>
        </x-report-toolbar>

        <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
            <table class="min-w-full text-sm">
                <thead class="bg-zinc-50 dark:bg-zinc-900"><tr><th class="px-3 py-2 text-left">{{ __('Student') }}</th><th class="px-3 py-2 text-left">{{ __('Class') }}</th><th class="px-3 py-2 text-left">{{ __('%') }}</th></tr></thead>
                <tbody>
                    @forelse ($data['student_rows'] as $row)
                        <tr class="border-t border-zinc-200 dark:border-zinc-700"><td class="px-3 py-2">{{ $row['name'] }}</td><td class="px-3 py-2">{{ $row['class'] }}</td><td class="px-3 py-2">{{ $row['percentage'] }}%</td></tr>
                    @empty
                        <tr><td colspan="3" class="px-3 py-6 text-zinc-500">{{ __('No data.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts::app>
