<x-layouts::app :title="__('My attendance')">
    <x-report.page
        :title="__('My attendance')"
        :description="$student->user?->name.' — '.($student->currentClass?->code ?? '—')"
        :catalog-route="$catalogRoute"
    >
        <x-slot:aside>
            <x-report.exports :query="$exportQuery" />
        </x-slot:aside>

        <x-list.filters :action="$action" :filters="$filters" :submit="__('Apply')">
            <x-form.month-select :value="$month" />
        </x-list.filters>

        <x-dashboard.stat :label="__('Attendance')" :value="$percentage.'%'" tone="success" class="max-w-xs" />

        <div class="overflow-x-auto rounded-xl border border-border bg-card">
            <table class="min-w-full text-sm">
                <thead class="bg-muted/60">
                    <tr>
                        <th class="px-3 py-2 text-left">{{ __('Date') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('Scope') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('Subject') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('Status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr class="border-t border-border">
                            <td class="px-3 py-2">{{ $row['date'] }}</td>
                            <td class="px-3 py-2">{{ $row['scope'] }}</td>
                            <td class="px-3 py-2">{{ $row['subject'] }}</td>
                            <td class="px-3 py-2">{{ $row['status'] }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-3 py-6 text-muted-foreground">{{ __('No attendance this month.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-list.pagination :paginator="$rows" />
    </x-report.page>
</x-layouts::app>
