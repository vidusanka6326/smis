@php
    $existing = old('assignments', $teacher->assignments->map(fn ($a) => [
        'school_class_id' => $a->school_class_id,
        'subject_id' => $a->subject_id,
        'role_in_assignment' => $a->role_in_assignment->value,
    ])->values()->all());
    $pageTitle = __('Assignments').' — '.($teacher->user?->name ?? '');
@endphp

<x-layouts::app :title="__('Teacher assignments')">
    <x-form.page
        :title="$pageTitle"
        :description="__('Class / subject / PT-PD roles for one academic year.')"
        wide
    >
        @if (session('status'))
            <flux:callout variant="success" icon="check-circle">
                <flux:callout.heading>{{ session('status') }}</flux:callout.heading>
            </flux:callout>
        @endif

        <x-form.section :title="__('Academic year')">
            <form method="GET" action="{{ route('admin.teachers.assignments.edit', $teacher) }}">
                <x-form.grid>
                    <flux:select name="academic_year_id" :label="__('Academic year')">
                        @foreach ($academicYears as $year)
                            <flux:select.option :value="$year->id" :selected="(string) $selectedAcademicYearId === (string) $year->id">
                                {{ $year->name }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                    <div class="flex items-end">
                        <flux:button type="submit" variant="filled">{{ __('Load year') }}</flux:button>
                    </div>
                </x-form.grid>
            </form>
        </x-form.section>

        <form method="POST" action="{{ route('admin.teachers.assignments.update', $teacher) }}" class="flex flex-col gap-6">
            @csrf
            @method('PUT')
            <input type="hidden" name="academic_year_id" value="{{ $selectedAcademicYearId }}">

            <x-form.section :title="__('Assignment rows')" :description="__('Each row is one class role, with an optional subject for subject teachers.')">
                <div class="space-y-4" x-data="{ rows: @js($existing ?: [['school_class_id' => '', 'subject_id' => '', 'role_in_assignment' => 'class_teacher']]) }">
                    <template x-for="(row, index) in rows" :key="index">
                        <div class="grid gap-3 rounded-xl border border-border bg-background/50 p-4 md:grid-cols-3">
                            <div>
                                <flux:select
                                    :label="__('Class')"
                                    x-bind:name="`assignments[${index}][school_class_id]`"
                                    x-model="row.school_class_id"
                                    required
                                >
                                    <flux:select.option value="">{{ __('Select class') }}</flux:select.option>
                                    @foreach ($schoolClasses as $class)
                                        <flux:select.option :value="$class->id">{{ $class->code }}</flux:select.option>
                                    @endforeach
                                </flux:select>
                            </div>
                            <div>
                                <flux:select
                                    :label="__('Role')"
                                    x-bind:name="`assignments[${index}][role_in_assignment]`"
                                    x-model="row.role_in_assignment"
                                    required
                                >
                                    @foreach ($roles as $role)
                                        <flux:select.option :value="$role->value">{{ $role->label() }}</flux:select.option>
                                    @endforeach
                                </flux:select>
                            </div>
                            <div>
                                <flux:select
                                    :label="__('Subject')"
                                    x-bind:name="`assignments[${index}][subject_id]`"
                                    x-model="row.subject_id"
                                >
                                    <flux:select.option value="">{{ __('None') }}</flux:select.option>
                                    @foreach ($subjects as $subject)
                                        <flux:select.option :value="$subject->id">{{ $subject->name }}</flux:select.option>
                                    @endforeach
                                </flux:select>
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
            </x-form.section>

            <x-form.actions>
                <flux:button type="submit" variant="primary">{{ __('Save assignments') }}</flux:button>
                <flux:button :href="route('admin.teachers.show', $teacher)" variant="ghost" wire:navigate>{{ __('Back') }}</flux:button>
            </x-form.actions>
        </form>
    </x-form.page>
</x-layouts::app>
