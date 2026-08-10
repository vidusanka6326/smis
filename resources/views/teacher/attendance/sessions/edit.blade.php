<x-layouts::app :title="__('Edit attendance')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <flux:heading size="xl">{{ __('Edit attendance') }}</flux:heading>
                <flux:text class="mt-1">
                    {{ $session->schoolClass?->code }} — {{ $session->date->toDateString() }} —
                    {{ $session->subject?->name ?? __('Class') }}
                </flux:text>
            </div>
            @unless ($session->isFinalized())
                <form method="POST" action="{{ route('teacher.attendance.sessions.finalize', $session) }}">
                    @csrf
                    <flux:button type="submit" variant="filled">{{ __('Finalize') }}</flux:button>
                </form>
            @endunless
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

        <form method="POST" action="{{ route('teacher.attendance.sessions.update', $session) }}" class="space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" name="academic_year_id" value="{{ $session->academic_year_id }}">
            <input type="hidden" name="school_class_id" value="{{ $session->school_class_id }}">
            <input type="hidden" name="date" value="{{ $session->date->toDateString() }}">
            @if ($session->subject_id)
                <input type="hidden" name="subject_id" value="{{ $session->subject_id }}">
            @endif
            <flux:input name="notes" :label="__('Notes')" :value="old('notes', $session->notes)" />

            <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
                <table class="min-w-full text-sm">
                    <thead class="bg-zinc-50 dark:bg-zinc-900">
                        <tr>
                            <th class="px-3 py-2 text-left">{{ __('Student') }}</th>
                            <th class="px-3 py-2 text-left">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($students as $index => $student)
                            @php($current = $existing->get($student->id)?->status)
                            <tr class="border-t border-zinc-200 dark:border-zinc-700">
                                <td class="px-3 py-2">
                                    {{ $student->user?->name }}
                                    <input type="hidden" name="records[{{ $index }}][student_id]" value="{{ $student->id }}">
                                </td>
                                <td class="px-3 py-2">
                                    <select name="records[{{ $index }}][status]" class="rounded border border-zinc-300 bg-transparent px-2 py-1 dark:border-zinc-600" @disabled($session->isFinalized())>
                                        @foreach ($statuses as $status)
                                            <option value="{{ $status->value }}" @selected(($current ?? \App\Enums\AttendanceStatus::Present) === $status)>{{ $status->label() }}</option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @unless ($session->isFinalized())
                <flux:button type="submit" variant="primary">{{ __('Update') }}</flux:button>
            @endunless
        </form>
    </div>
</x-layouts::app>
