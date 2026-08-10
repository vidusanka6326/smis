@php
    $existing = old('assignments', $teacher->assignments->map(fn ($a) => [
        'school_class_id' => $a->school_class_id,
        'subject_id' => $a->subject_id,
        'role_in_assignment' => $a->role_in_assignment->value,
    ])->values()->all());
@endphp

<x-layouts::app :title="__('Teacher assignments')">
    <div class="mx-auto flex w-full max-w-3xl flex-col gap-6">
        <div>
            <flux:heading size="xl">{{ __('Assignments') }} — {{ $teacher->user?->name }}</flux:heading>
            <flux:text class="mt-1">{{ __('Class / subject / PT-PD roles for one academic year.') }}</flux:text>
        </div>

        @if (session('status'))
            <flux:callout variant="success" icon="check-circle">
                <flux:callout.heading>{{ session('status') }}</flux:callout.heading>
            </flux:callout>
        @endif

        <form method="GET" action="{{ route('admin.teachers.assignments.edit', $teacher) }}" class="flex flex-wrap items-end gap-3">
            <flux:select name="academic_year_id" :label="__('Academic year')">
                @foreach ($academicYears as $year)
                    <flux:select.option :value="$year->id" :selected="(string) $selectedAcademicYearId === (string) $year->id">
                        {{ $year->name }}
                    </flux:select.option>
                @endforeach
            </flux:select>
            <flux:button type="submit" variant="filled">{{ __('Load year') }}</flux:button>
        </form>

        <form method="POST" action="{{ route('admin.teachers.assignments.update', $teacher) }}" class="flex flex-col gap-6">
            @csrf
            @method('PUT')
            <input type="hidden" name="academic_year_id" value="{{ $selectedAcademicYearId }}">

            <div class="space-y-4" x-data="{ rows: @js($existing ?: [['school_class_id' => '', 'subject_id' => '', 'role_in_assignment' => 'class_teacher']]) }">
                <template x-for="(row, index) in rows" :key="index">
                    <div class="grid gap-3 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700 md:grid-cols-3">
                        <div>
                            <flux:label>{{ __('Class') }}</flux:label>
                            <select class="mt-1 w-full rounded-lg border border-zinc-300 bg-transparent px-3 py-2 text-sm dark:border-zinc-600" :name="`assignments[${index}][school_class_id]`" x-model="row.school_class_id" required>
                                <option value="">{{ __('Select class') }}</option>
                                @foreach ($schoolClasses as $class)
                                    <option value="{{ $class->id }}">{{ $class->code }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <flux:label>{{ __('Role') }}</flux:label>
                            <select class="mt-1 w-full rounded-lg border border-zinc-300 bg-transparent px-3 py-2 text-sm dark:border-zinc-600" :name="`assignments[${index}][role_in_assignment]`" x-model="row.role_in_assignment" required>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->value }}">{{ $role->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <flux:label>{{ __('Subject') }}</flux:label>
                            <select class="mt-1 w-full rounded-lg border border-zinc-300 bg-transparent px-3 py-2 text-sm dark:border-zinc-600" :name="`assignments[${index}][subject_id]`" x-model="row.subject_id">
                                <option value="">{{ __('None') }}</option>
                                @foreach ($subjects as $subject)
                                    <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </template>

                <div class="flex gap-2">
                    <flux:button type="button" variant="ghost" x-on:click="rows.push({ school_class_id: '', subject_id: '', role_in_assignment: 'class_teacher' })">
                        {{ __('Add assignment') }}
                    </flux:button>
                    <flux:button type="button" variant="ghost" x-on:click="rows = []" x-show="rows.length">
                        {{ __('Clear all') }}
                    </flux:button>
                </div>
            </div>

            <div class="flex gap-3">
                <flux:button type="submit" variant="primary">{{ __('Save assignments') }}</flux:button>
                <flux:button :href="route('admin.teachers.show', $teacher)" variant="ghost" wire:navigate>{{ __('Back') }}</flux:button>
            </div>
        </form>
    </div>
</x-layouts::app>
