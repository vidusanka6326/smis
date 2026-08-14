<x-layouts::app :title="__('Attendance at risk')">
    <x-report.page
        :title="__('Attendance at risk')"
        :description="__('Students below :pct% monthly attendance.', ['pct' => (int) $data['summary']['threshold']])"
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

        <x-dashboard.stat
            :label="__('Needs attention')"
            :value="$data['summary']['at_risk_count']"
            :hint="__('Of :count students tracked', ['count' => $data['summary']['tracked_students']])"
            :tone="$data['summary']['at_risk_count'] > 0 ? 'warning' : 'default'"
            class="max-w-xs"
        />

        <div class="overflow-x-auto rounded-xl border border-border bg-card">
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
                    @forelse ($rows as $row)
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

        <x-list.pagination :paginator="$rows" />
    </x-report.page>
</x-layouts::app>
