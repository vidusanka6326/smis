<x-layouts::app :title="__('Exam subjects')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div>
            <flux:heading size="xl">{{ __('Subjects for :name', ['name' => $exam->name]) }}</flux:heading>
            <flux:text class="mt-1">{{ __('Set max marks and pass mark per subject. Leave unused rows blank.') }}</flux:text>
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

        @php
            $rows = $existing->values();
            $blankCount = max(1, 3 - $rows->count());
        @endphp

        <form method="POST" action="{{ route('admin.exams.subjects.update', $exam) }}" class="space-y-4">
            @csrf
            @method('PUT')

            @foreach ($rows as $index => $row)
                <div class="grid gap-3 rounded-xl border border-zinc-200 p-4 md:grid-cols-3 dark:border-zinc-700">
                    <flux:select name="subjects[{{ $index }}][subject_id]" :label="__('Subject')">
                        @foreach ($subjects as $subject)
                            <flux:select.option :value="$subject->id" :selected="(string) $row->subject_id === (string) $subject->id">{{ $subject->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:input type="number" step="0.01" name="subjects[{{ $index }}][max_marks]" :label="__('Max marks')" :value="$row->max_marks" />
                    <flux:input type="number" step="0.01" name="subjects[{{ $index }}][pass_mark]" :label="__('Pass mark')" :value="$row->pass_mark" />
                </div>
            @endforeach

            @for ($i = 0; $i < $blankCount; $i++)
                @php($index = $rows->count() + $i)
                <div class="grid gap-3 rounded-xl border border-dashed border-zinc-300 p-4 md:grid-cols-3 dark:border-zinc-600">
                    <flux:select name="subjects[{{ $index }}][subject_id]" :label="__('Subject')">
                        <flux:select.option value="">{{ __('— skip —') }}</flux:select.option>
                        @foreach ($subjects as $subject)
                            <flux:select.option :value="$subject->id">{{ $subject->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:input type="number" step="0.01" name="subjects[{{ $index }}][max_marks]" :label="__('Max marks')" value="100" />
                    <flux:input type="number" step="0.01" name="subjects[{{ $index }}][pass_mark]" :label="__('Pass mark')" value="40" />
                </div>
            @endfor

            <div class="flex flex-wrap gap-2">
                <flux:button type="submit" variant="primary">{{ __('Save subjects') }}</flux:button>
                <flux:button :href="route('admin.exams.edit', $exam)" variant="ghost" wire:navigate>{{ __('Back') }}</flux:button>
            </div>
        </form>
    </div>
</x-layouts::app>
