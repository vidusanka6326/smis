<x-layouts::app :title="__('Student demographics')">
    <x-report.page
        :title="__('Student demographics')"
        :description="__('Grade, class, subject, and gender breakdowns.')"
        :catalog-route="$catalogRoute"
    >
        <x-slot:aside>
            <x-report.exports :query="$exportQuery" />
        </x-slot:aside>

        <x-list.filters :action="$action" :filters="$filters" :submit="__('Apply')" :with-per-page="false">
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
    </x-report.page>
</x-layouts::app>
