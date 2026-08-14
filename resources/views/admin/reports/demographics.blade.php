<x-layouts::app :title="__('Demographics')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div>
            <flux:heading size="xl">{{ __('Student demographics') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Grade, class, subject, and gender breakdowns.') }}</flux:text>
        </div>

        <x-list.filters :action="route('admin.reports.demographics')" :filters="$filters" :submit="__('Apply')" :with-per-page="false" class="no-print">
            <flux:select name="school_class_id" :label="__('Class')" :placeholder="__('All classes')">
                @foreach ($schoolClasses as $class)
                    <flux:select.option :value="$class->id" :selected="(string) ($filters['school_class_id'] ?? '') === (string) $class->id">{{ $class->code }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:select name="subject_id" :label="__('Subject')" :placeholder="__('All subjects')">
                @foreach ($subjects as $subject)
                    <flux:select.option :value="$subject->id" :selected="(string) ($filters['subject_id'] ?? '') === (string) $subject->id">{{ $subject->name }}</flux:select.option>
                @endforeach
            </flux:select>
        </x-list.filters>

        <x-report-toolbar :print="$print">
            <flux:button :href="route('admin.reports.demographics', ['school_class_id' => $filters['school_class_id'] ?? null, 'subject_id' => $filters['subject_id'] ?? null, 'export' => 'csv'])" variant="filled">{{ __('CSV') }}</flux:button>
            <flux:button :href="route('admin.reports.demographics', ['school_class_id' => $filters['school_class_id'] ?? null, 'subject_id' => $filters['subject_id'] ?? null, 'print' => 1])" variant="filled">{{ __('Print / PDF') }}</flux:button>
            <flux:button :href="route('admin.reports.dashboard')" variant="ghost" wire:navigate>{{ __('Dashboard') }}</flux:button>
        </x-report-toolbar>

        <x-dashboard.stat :label="__('Total students')" :value="$data['total']" class="max-w-xs" />

        <div class="grid gap-4 lg:grid-cols-2">
            <div class="overflow-x-auto rounded-xl border border-border bg-card">
                <table class="min-w-full text-sm">
                    <thead class="bg-muted/60"><tr><th class="px-3 py-2 text-left">{{ __('Gender') }}</th><th class="px-3 py-2 text-left">{{ __('Count') }}</th></tr></thead>
                    <tbody>
                        <tr class="border-t border-border"><td class="px-3 py-2">{{ __('Boys') }}</td><td class="px-3 py-2">{{ $data['by_gender']['B'] ?? 0 }}</td></tr>
                        <tr class="border-t border-border"><td class="px-3 py-2">{{ __('Girls') }}</td><td class="px-3 py-2">{{ $data['by_gender']['G'] ?? 0 }}</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="overflow-x-auto rounded-xl border border-border bg-card">
                <table class="min-w-full text-sm">
                    <thead class="bg-muted/60"><tr><th class="px-3 py-2 text-left">{{ __('Class') }}</th><th class="px-3 py-2 text-left">{{ __('Count') }}</th></tr></thead>
                    <tbody>
                        @forelse ($data['by_class'] as $row)
                            <tr class="border-t border-border"><td class="px-3 py-2">{{ $row['code'] }}</td><td class="px-3 py-2">{{ $row['count'] }}</td></tr>
                        @empty
                            <tr><td colspan="2" class="px-3 py-4 text-muted-foreground">{{ __('No data.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts::app>
