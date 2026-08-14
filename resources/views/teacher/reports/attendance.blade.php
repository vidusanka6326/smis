<x-layouts::app :title="__('Attendance report')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div>
            <flux:heading size="xl">{{ __('Attendance report') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Students in your scope below :pct%, plus full monthly detail.', ['pct' => (int) $data['summary']['threshold']]) }}</flux:text>
        </div>

        <x-list.filters :action="route('teacher.reports.attendance')" :filters="$filters" :submit="__('Load')" class="no-print">
            <x-form.month-select :value="$month" />
            <flux:select name="school_class_id" :label="__('Class')" :placeholder="__('All classes')">
                @foreach ($schoolClasses as $class)
                    <flux:select.option :value="$class->id" :selected="(string) $selectedSchoolClassId === (string) $class->id">{{ $class->code }}</flux:select.option>
                @endforeach
            </flux:select>
        </x-list.filters>

        <x-report-toolbar :print="$print">
            <flux:button :href="route('teacher.reports.attendance', ['month' => $month, 'school_class_id' => $selectedSchoolClassId, 'export' => 'csv'])" variant="filled">{{ __('CSV') }}</flux:button>
            <flux:button :href="route('teacher.reports.attendance', ['month' => $month, 'school_class_id' => $selectedSchoolClassId, 'print' => 1])" variant="filled">{{ __('Print / PDF') }}</flux:button>
        </x-report-toolbar>

        <div class="grid gap-4 sm:grid-cols-3">
            <x-dashboard.stat :label="__('Students tracked')" :value="$data['summary']['tracked_students']" />
            <x-dashboard.stat
                :label="__('Class average')"
                :value="$data['summary']['class_average'] !== null ? $data['summary']['class_average'].'%' : '—'"
                tone="success"
            />
            <x-dashboard.stat
                :label="__('Needs attention')"
                :value="$data['summary']['at_risk_count']"
                :tone="$data['summary']['at_risk_count'] > 0 ? 'warning' : 'default'"
            />
        </div>

        <div class="overflow-x-auto rounded-xl border border-border bg-card">
            <div class="border-b border-border px-3 py-3">
                <flux:heading size="sm">{{ __('By class') }}</flux:heading>
            </div>
            <table class="min-w-full text-sm">
                <thead class="bg-muted/60">
                    <tr>
                        <th class="px-3 py-2 text-left">{{ __('Class') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('%') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('P/A/L/E') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($data['class_rows'] as $row)
                        <tr class="border-t border-border">
                            <td class="px-3 py-2">{{ $row['code'] }}</td>
                            <td class="px-3 py-2">{{ $row['percentage'] }}%</td>
                            <td class="px-3 py-2">{{ $row['present'] }}/{{ $row['absent'] }}/{{ $row['late'] }}/{{ $row['excused'] }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-3 py-6 text-muted-foreground">{{ __('No class attendance.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="overflow-x-auto rounded-xl border border-border bg-card">
            <div class="border-b border-border px-3 py-3">
                <flux:heading size="sm">{{ __('Needs attention (below :pct%)', ['pct' => (int) $data['summary']['threshold']]) }}</flux:heading>
            </div>
            <table class="min-w-full text-sm">
                <thead class="bg-muted/60">
                    <tr>
                        <th class="px-3 py-2 text-left">{{ __('Student') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('Class') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('%') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('P/A/L/E') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($data['at_risk'] as $row)
                        <tr class="border-t border-border">
                            <td class="px-3 py-2">{{ $row['name'] }}</td>
                            <td class="px-3 py-2">{{ $row['class'] }}</td>
                            <td class="px-3 py-2 font-medium text-amber-600 dark:text-amber-400">{{ $row['percentage'] }}%</td>
                            <td class="px-3 py-2">{{ $row['present'] }}/{{ $row['absent'] }}/{{ $row['late'] }}/{{ $row['excused'] }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-3 py-6 text-muted-foreground">{{ __('No students below the threshold.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="overflow-x-auto rounded-xl border border-border bg-card">
            <div class="border-b border-border px-3 py-3">
                <flux:heading size="sm">{{ __('All students') }}</flux:heading>
            </div>
            <table class="min-w-full text-sm">
                <thead class="bg-muted/60">
                    <tr>
                        <th class="px-3 py-2 text-left">{{ __('Student') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('Class') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('%') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('P/A/L/E') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($studentRows as $row)
                        <tr class="border-t border-border">
                            <td class="px-3 py-2">{{ $row['name'] }}</td>
                            <td class="px-3 py-2">{{ $row['class'] }}</td>
                            <td class="px-3 py-2">{{ $row['percentage'] }}%</td>
                            <td class="px-3 py-2">{{ $row['present'] }}/{{ $row['absent'] }}/{{ $row['late'] }}/{{ $row['excused'] }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-3 py-6 text-muted-foreground">{{ __('No data.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-list.pagination :paginator="$studentRows" class="no-print" />
    </div>
</x-layouts::app>
