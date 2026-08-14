<x-layouts::app :title="__('Teacher attendance')">
    <x-report.page
        :title="__('Teacher attendance')"
        :description="__('Staff attendance totals for a selected month.')"
        :catalog-route="$catalogRoute"
    >
        <x-slot:aside>
            <x-report.exports :query="$exportQuery" />
        </x-slot:aside>

        <x-list.filters :action="$action" :filters="$filters" :submit="__('Apply')">
            <x-form.month-select :value="$month" />
            <flux:select name="teacher_id" :label="__('Teacher')" :placeholder="__('All teachers')">
                @foreach ($teachers as $teacher)
                    <flux:select.option :value="$teacher->id" :selected="(string) $selectedTeacherId === (string) $teacher->id">{{ $teacher->user?->name }}</flux:select.option>
                @endforeach
            </flux:select>
        </x-list.filters>

        <div class="overflow-x-auto rounded-xl border border-border bg-card">
            <table class="min-w-full text-sm">
                <thead class="bg-muted/60">
                    <tr>
                        <th class="px-3 py-2 text-left">{{ __('Teacher') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('Employee no.') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('%') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('P/A/L/E') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($staffRows as $row)
                        <tr class="border-t border-border">
                            <td class="px-3 py-2">{{ $row['name'] }}</td>
                            <td class="px-3 py-2">{{ $row['employee_no'] }}</td>
                            <td class="px-3 py-2">{{ $row['percentage'] }}%</td>
                            <td class="px-3 py-2">{{ $row['present'] }}/{{ $row['absent'] }}/{{ $row['late'] }}/{{ $row['excused'] }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-3 py-6 text-muted-foreground">{{ __('No teachers to report.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-list.pagination :paginator="$staffRows" />
    </x-report.page>
</x-layouts::app>
