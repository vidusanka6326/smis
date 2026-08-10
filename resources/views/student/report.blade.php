<x-layouts::app :title="__('My report')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div>
            <flux:heading size="xl">{{ __('My report') }}</flux:heading>
            <flux:text class="mt-1">{{ $student->user?->name }} — {{ $student->currentClass?->code ?? '—' }}</flux:text>
        </div>

        <form method="GET" class="no-print flex flex-wrap items-end gap-3">
            <flux:input type="month" name="month" :label="__('Attendance month')" :value="$month" />
            <flux:button type="submit" variant="filled">{{ __('Load') }}</flux:button>
        </form>

        <x-report-toolbar :print="$print">
            <flux:button :href="route('student.report', ['month' => $month, 'export' => 'csv'])" variant="filled">{{ __('CSV results') }}</flux:button>
            <flux:button :href="route('student.report', ['month' => $month, 'print' => 1])" variant="filled">{{ __('Print / PDF') }}</flux:button>
        </x-report-toolbar>

        <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
            <flux:heading size="sm">{{ __('Attendance (:month)', ['month' => $month]) }}</flux:heading>
            <p class="mt-2 text-2xl font-semibold">{{ $attendancePercentage }}%</p>
        </div>

        <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
            <table class="min-w-full text-sm">
                <thead class="bg-zinc-50 dark:bg-zinc-900"><tr><th class="px-3 py-2 text-left">{{ __('Exam') }}</th><th class="px-3 py-2 text-left">{{ __('Subject') }}</th><th class="px-3 py-2 text-left">{{ __('Marks') }}</th><th class="px-3 py-2 text-left">{{ __('Grade') }}</th></tr></thead>
                <tbody>
                    @forelse ($marks as $mark)
                        <tr class="border-t border-zinc-200 dark:border-zinc-700">
                            <td class="px-3 py-2">{{ $mark->examSubject?->exam?->name }}</td>
                            <td class="px-3 py-2">{{ $mark->examSubject?->subject?->name }}</td>
                            <td class="px-3 py-2">{{ $mark->marks_obtained }}</td>
                            <td class="px-3 py-2">{{ $mark->grade_letter->value }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-3 py-6 text-zinc-500">{{ __('No published results.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts::app>
