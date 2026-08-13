@php
    $sessionLabel = ($session->schoolClass?->code ?? '').' — '.$session->date->toDateString().' — '.($session->subject?->name ?? __('Class'));
@endphp

<x-layouts::app :title="__('Edit attendance')">
    <x-form.page
        :title="__('Edit attendance')"
        :description="$sessionLabel"
        wide
    >
        <x-slot:aside>
            @unless ($session->isFinalized())
                <form method="POST" action="{{ route('teacher.attendance.sessions.finalize', $session) }}">
                    @csrf
                    <flux:button type="submit" variant="filled">{{ __('Finalize') }}</flux:button>
                </form>
            @endunless
        </x-slot:aside>

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

        <form method="POST" action="{{ route('teacher.attendance.sessions.update', $session) }}" class="flex flex-col gap-6">
            @csrf
            @method('PUT')
            <input type="hidden" name="academic_year_id" value="{{ $session->academic_year_id }}">
            <input type="hidden" name="school_class_id" value="{{ $session->school_class_id }}">
            <input type="hidden" name="date" value="{{ $session->date->toDateString() }}">
            @if ($session->subject_id)
                <input type="hidden" name="subject_id" value="{{ $session->subject_id }}">
            @endif

            <x-form.section :title="__('Session details')">
                <x-form.grid>
                    <x-form.full>
                        <flux:input name="notes" :label="__('Notes')" :value="old('notes', $session->notes)" />
                    </x-form.full>
                </x-form.grid>
            </x-form.section>

            <x-form.section :title="__('Roster')">
                <div class="overflow-x-auto rounded-xl border border-border">
                    <table class="min-w-full text-sm">
                        <thead class="bg-muted/50">
                            <tr>
                                <th class="px-3 py-2 text-left">{{ __('Student') }}</th>
                                <th class="px-3 py-2 text-left">{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($students as $index => $student)
                                @php($current = $existing->get($student->id)?->status)
                                <tr class="border-t border-border">
                                    <td class="px-3 py-2">
                                        {{ $student->user?->name }}
                                        <input type="hidden" name="records[{{ $index }}][student_id]" value="{{ $student->id }}">
                                    </td>
                                    <td class="px-3 py-2">
                                        <flux:select name="records[{{ $index }}][status]" size="sm" :disabled="$session->isFinalized()">
                                            @foreach ($statuses as $status)
                                                <flux:select.option :value="$status->value" :selected="($current ?? \App\Enums\AttendanceStatus::Present) === $status">
                                                    {{ $status->label() }}
                                                </flux:select.option>
                                            @endforeach
                                        </flux:select>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-form.section>

            @unless ($session->isFinalized())
                <x-form.actions>
                    <flux:button type="submit" variant="primary">{{ __('Update') }}</flux:button>
                </x-form.actions>
            @endunless
        </form>
    </x-form.page>
</x-layouts::app>
