@php
    $marksSubtitle = ($examSubject->exam?->name ?? '').' — '.($examSubject->subject?->name ?? '').' ('.__('max :max / pass :pass', ['max' => $examSubject->max_marks, 'pass' => $examSubject->pass_mark]).')';
@endphp

<x-layouts::app :title="__('Enter marks')">
    <x-form.page
        :title="__('Enter marks')"
        :description="$marksSubtitle"
        wide
    >
        @if (session('status'))
            <flux:callout variant="success" icon="check-circle">
                <flux:callout.heading>{{ session('status') }}</flux:callout.heading>
            </flux:callout>
        @endif

        @if ($errors->any())
            <flux:callout variant="danger" icon="x-circle">
                <flux:callout.heading>{{ $errors->first() }}</flux:callout.heading>
            </flux:callout>
        @endif

        <form method="POST" action="{{ route('admin.marks.update', $examSubject) }}" class="flex flex-col gap-6">
            @csrf
            @method('PUT')

            <x-form.section :title="__('Class marks')">
                <div class="overflow-x-auto rounded-xl border border-border">
                    <table class="min-w-full text-sm">
                        <thead class="bg-muted/50">
                            <tr>
                                <th class="px-3 py-2 text-left">{{ __('Student') }}</th>
                                <th class="px-3 py-2 text-left">{{ __('Marks') }}</th>
                                <th class="px-3 py-2 text-left">{{ __('Current') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($students as $index => $student)
                                @php($mark = $existing->get($student->id))
                                <tr class="border-t border-border">
                                    <td class="px-3 py-2">
                                        {{ $student->user?->name }}
                                        <input type="hidden" name="records[{{ $index }}][student_id]" value="{{ $student->id }}">
                                    </td>
                                    <td class="px-3 py-2">
                                        <flux:input
                                            class="w-28"
                                            type="number"
                                            size="sm"
                                            step="0.01"
                                            min="0"
                                            :max="$examSubject->max_marks"
                                            name="records[{{ $index }}][marks_obtained]"
                                            :value="old('records.'.$index.'.marks_obtained', $mark?->marks_obtained ?? 0)"
                                        />
                                    </td>
                                    <td class="px-3 py-2">
                                        @if ($mark)
                                            {{ $mark->grade_letter->value }} / {{ $mark->is_pass ? __('Pass') : __('Fail') }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-form.section>

            @if ($students->isNotEmpty())
                <x-form.actions>
                    <flux:button type="submit" variant="primary">{{ __('Save marks') }}</flux:button>
                </x-form.actions>
            @endif
        </form>
    </x-form.page>
</x-layouts::app>
