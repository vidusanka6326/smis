<x-layouts::app :title="__('Teacher assignments')">
    <x-report.page
        :title="__('Teacher assignments')"
        :description="__('Who teaches which class and subject.')"
        :catalog-route="$catalogRoute"
    >
        <x-slot:aside>
            <x-report.exports :query="$exportQuery" />
        </x-slot:aside>

        <x-list.filters :action="$action" :filters="$filters" :submit="__('Apply')">
            <flux:select name="academic_year_id" :label="__('Academic year')">
                @foreach ($academicYears as $year)
                    <flux:select.option :value="$year->id" :selected="(string) $selectedYearId === (string) $year->id">{{ $year->name }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:select name="school_class_id" :label="__('Class')" :placeholder="__('All classes')">
                @foreach ($schoolClasses as $class)
                    <flux:select.option :value="$class->id" :selected="(string) $selectedSchoolClassId === (string) $class->id">{{ $class->code }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:select name="role" :label="__('Role')" :placeholder="__('All roles')">
                @foreach ($roles as $role)
                    <flux:select.option :value="$role->value" :selected="(string) $selectedRole === $role->value">{{ $role->label() }}</flux:select.option>
                @endforeach
            </flux:select>
        </x-list.filters>

        <div class="overflow-x-auto rounded-xl border border-border bg-card">
            <table class="min-w-full text-sm">
                <thead class="bg-muted/60">
                    <tr>
                        <th class="px-3 py-2 text-left">{{ __('Teacher') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('Class') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('Subject') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('Role') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('Academic year') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr class="border-t border-border">
                            <td class="px-3 py-2">{{ $row['teacher'] }}</td>
                            <td class="px-3 py-2">{{ $row['class'] }}</td>
                            <td class="px-3 py-2">{{ $row['subject'] }}</td>
                            <td class="px-3 py-2">{{ $row['role'] }}</td>
                            <td class="px-3 py-2">{{ $row['academic_year'] }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-3 py-6 text-muted-foreground">{{ __('No assignments match these filters.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-list.pagination :paginator="$rows" />
    </x-report.page>
</x-layouts::app>
