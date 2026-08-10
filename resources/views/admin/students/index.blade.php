<x-layouts::app :title="__('Students')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <flux:heading size="xl">{{ __('Students') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Filter by grade, class, subject, or gender.') }}</flux:text>
            </div>
            <flux:button :href="route('admin.students.create')" variant="primary" wire:navigate>{{ __('Add student') }}</flux:button>
        </div>

        @if (session('status'))
            <flux:callout variant="success" icon="check-circle">
                <flux:callout.heading>{{ session('status') }}</flux:callout.heading>
            </flux:callout>
        @endif

        <form method="GET" action="{{ route('admin.students.index') }}" class="grid gap-3 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700 md:grid-cols-5">
            <flux:input name="search" :label="__('Search')" :value="$filters['search'] ?? ''" placeholder="{{ __('Name / admission') }}" />
            <flux:select name="gender" :label="__('Gender')" :placeholder="__('All')">
                @foreach ($genders as $gender)
                    <flux:select.option :value="$gender->value" :selected="($filters['gender'] ?? null) === $gender->value">
                        {{ $gender->label() }}
                    </flux:select.option>
                @endforeach
            </flux:select>
            <flux:select name="grade_id" :label="__('Grade')" :placeholder="__('All')">
                @foreach ($grades as $grade)
                    <flux:select.option :value="$grade->id" :selected="(string) ($filters['grade_id'] ?? '') === (string) $grade->id">
                        {{ $grade->name }}
                    </flux:select.option>
                @endforeach
            </flux:select>
            <flux:select name="class_id" :label="__('Class')" :placeholder="__('All')">
                @foreach ($classes as $class)
                    <flux:select.option :value="$class->id" :selected="(string) ($filters['class_id'] ?? '') === (string) $class->id">
                        {{ $class->code }}
                    </flux:select.option>
                @endforeach
            </flux:select>
            <flux:select name="subject_id" :label="__('Subject')" :placeholder="__('All')">
                @foreach ($subjects as $subject)
                    <flux:select.option :value="$subject->id" :selected="(string) ($filters['subject_id'] ?? '') === (string) $subject->id">
                        {{ $subject->name }}
                    </flux:select.option>
                @endforeach
            </flux:select>
            <div class="md:col-span-5">
                <flux:button type="submit" variant="filled">{{ __('Apply filters') }}</flux:button>
            </div>
        </form>

        <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
            <table class="min-w-full text-sm">
                <thead class="bg-zinc-50 text-left dark:bg-zinc-900">
                    <tr>
                        <th class="px-4 py-3 font-medium">{{ __('Name') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('Admission') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('Gender') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('Class') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($students as $student)
                        <tr class="border-t border-zinc-200 dark:border-zinc-700">
                            <td class="px-4 py-3">{{ $student->user?->name }}</td>
                            <td class="px-4 py-3">{{ $student->admission_no }}</td>
                            <td class="px-4 py-3">{{ $student->gender->label() }}</td>
                            <td class="px-4 py-3">{{ $student->currentClass?->code ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-2">
                                    <flux:button size="sm" :href="route('admin.students.show', $student)" variant="ghost" wire:navigate>{{ __('View') }}</flux:button>
                                    <flux:button size="sm" :href="route('admin.students.edit', $student)" variant="ghost" wire:navigate>{{ __('Edit') }}</flux:button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="border-t border-zinc-200 dark:border-zinc-700">
                            <td colspan="5" class="px-4 py-6 text-zinc-500">{{ __('No students match these filters.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $students->links() }}
    </div>
</x-layouts::app>
