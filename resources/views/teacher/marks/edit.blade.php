<x-layouts::app :title="__('Enter marks')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div>
            <flux:heading size="xl">{{ __('Enter marks') }}</flux:heading>
            <flux:text class="mt-1">
                {{ $examSubject->exam?->name }} — {{ $examSubject->subject?->name }}
            </flux:text>
        </div>

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

        <form method="POST" action="{{ route('teacher.marks.update', $examSubject) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
                <table class="min-w-full text-sm">
                    <thead class="bg-zinc-50 dark:bg-zinc-900">
                        <tr>
                            <th class="px-3 py-2 text-left">{{ __('Student') }}</th>
                            <th class="px-3 py-2 text-left">{{ __('Marks') }}</th>
                            <th class="px-3 py-2 text-left">{{ __('Grade') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($students as $index => $student)
                            @php($mark = $existing->get($student->id))
                            <tr class="border-t border-zinc-200 dark:border-zinc-700">
                                <td class="px-3 py-2">
                                    {{ $student->user?->name }}
                                    <input type="hidden" name="records[{{ $index }}][student_id]" value="{{ $student->id }}">
                                </td>
                                <td class="px-3 py-2">
                                    <input type="number" step="0.01" min="0" max="{{ $examSubject->max_marks }}" name="records[{{ $index }}][marks_obtained]" value="{{ old('records.'.$index.'.marks_obtained', $mark?->marks_obtained ?? 0) }}" class="w-28 rounded border border-zinc-300 bg-transparent px-2 py-1 dark:border-zinc-600">
                                </td>
                                <td class="px-3 py-2">{{ $mark?->grade_letter?->value ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($students->isNotEmpty())
                <flux:button type="submit" variant="primary">{{ __('Save') }}</flux:button>
            @endif
        </form>
    </div>
</x-layouts::app>
