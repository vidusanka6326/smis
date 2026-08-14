<x-layouts::app :title="__('Class enrollment')">
    <x-report.page
        :title="$heading ?? __('Class enrollment')"
        :description="$description ?? __('Student register with class, gender, and guardian details.')"
        :catalog-route="$catalogRoute"
    >
        <x-slot:aside>
            <x-report.exports :query="$exportQuery" />
        </x-slot:aside>

        <x-list.filters :action="$action" :filters="$filters" :submit="__('Apply')">
            <flux:select name="grade_id" :label="__('Grade')" :placeholder="__('All grades')">
                @foreach ($grades as $grade)
                    <flux:select.option :value="$grade->id" :selected="(string) $selectedGradeId === (string) $grade->id">{{ $grade->name }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:select name="school_class_id" :label="__('Class')" :placeholder="__('All classes')">
                @foreach ($schoolClasses as $class)
                    <flux:select.option :value="$class->id" :selected="(string) $selectedSchoolClassId === (string) $class->id">{{ $class->code }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:select name="gender" :label="__('Gender')" :placeholder="__('All')">
                @foreach ($genders as $gender)
                    <flux:select.option :value="$gender->value" :selected="(string) $selectedGender === $gender->value">{{ $gender->label() }}</flux:select.option>
                @endforeach
            </flux:select>
        </x-list.filters>

        <div class="overflow-x-auto rounded-xl border border-border bg-card">
            <table class="min-w-full text-sm">
                <thead class="bg-muted/60">
                    <tr>
                        <th class="px-3 py-2 text-left">{{ __('Admission no.') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('Name') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('Gender') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('Class') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('Grade') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('Guardian') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('Phone') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr class="border-t border-border">
                            <td class="px-3 py-2">{{ $row['admission_no'] }}</td>
                            <td class="px-3 py-2">{{ $row['name'] }}</td>
                            <td class="px-3 py-2">{{ $row['gender'] }}</td>
                            <td class="px-3 py-2">{{ $row['class'] }}</td>
                            <td class="px-3 py-2">{{ $row['grade'] }}</td>
                            <td class="px-3 py-2">{{ $row['guardian_name'] }}</td>
                            <td class="px-3 py-2">{{ $row['guardian_phone'] }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-3 py-6 text-muted-foreground">{{ __('No students match these filters.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-list.pagination :paginator="$rows" />
    </x-report.page>
</x-layouts::app>
