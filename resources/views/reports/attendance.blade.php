<x-layouts::app :title="__('Attendance report')">
    <x-report.page
        :title="__('Student attendance')"
        :description="__('Class averages, students below :pct%, and full monthly detail.', ['pct' => (int) $data['summary']['threshold']])"
        :catalog-route="$catalogRoute"
    >
        <x-slot:aside>
            <x-report.exports :query="$exportQuery" />
        </x-slot:aside>

        <x-list.filters :action="$action" :filters="$filters" :submit="__('Apply')">
            <x-form.month-select :value="$month" />
            <flux:select name="school_class_id" :label="__('Class')" :placeholder="__('All classes')">
                @foreach ($schoolClasses as $class)
                    <flux:select.option :value="$class->id" :selected="(string) $selectedSchoolClassId === (string) $class->id">{{ $class->code }}</flux:select.option>
                @endforeach
            </flux:select>
        </x-list.filters>

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
                :hint="__('Below :pct%', ['pct' => (int) $data['summary']['threshold']])"
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
                        <tr><td colspan="3" class="px-3 py-6 text-muted-foreground">{{ __('No class attendance this month.') }}</td></tr>
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
                        <tr><td colspan="4" class="px-3 py-6 text-muted-foreground">{{ __('No attendance this month.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-list.pagination :paginator="$studentRows" />
    </x-report.page>
</x-layouts::app>
