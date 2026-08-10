<x-layouts::app :title="__('Student attendance')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <flux:heading size="xl">{{ __('Student attendance') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Class and subject sessions with present / absent / late / excused.') }}</flux:text>
            </div>
            <div class="flex flex-wrap gap-2">
                <flux:button :href="route('admin.attendance.monthly')" variant="filled" wire:navigate>{{ __('Monthly summary') }}</flux:button>
                <flux:button :href="route('admin.attendance.teachers.index')" variant="filled" wire:navigate>{{ __('Teacher attendance') }}</flux:button>
                <flux:button :href="route('admin.attendance.sessions.create')" variant="primary" wire:navigate>{{ __('Take attendance') }}</flux:button>
            </div>
        </div>

        @if (session('status'))
            <flux:callout variant="success" icon="check-circle">
                <flux:callout.heading>{{ session('status') }}</flux:callout.heading>
            </flux:callout>
        @endif

        <form method="GET" action="{{ route('admin.attendance.sessions.index') }}" class="flex flex-wrap items-end gap-3">
            <flux:select name="academic_year_id" :label="__('Academic year')">
                @foreach ($academicYears as $year)
                    <flux:select.option :value="$year->id" :selected="(string) $selectedAcademicYearId === (string) $year->id">{{ $year->name }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:select name="school_class_id" :label="__('Class')">
                <flux:select.option value="">{{ __('All classes') }}</flux:select.option>
                @foreach ($schoolClasses as $class)
                    <flux:select.option :value="$class->id" :selected="(string) $selectedSchoolClassId === (string) $class->id">{{ $class->code }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:button type="submit" variant="filled">{{ __('Filter') }}</flux:button>
        </form>

        <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
            <table class="min-w-full text-sm">
                <thead class="bg-zinc-50 dark:bg-zinc-900">
                    <tr>
                        <th class="px-3 py-2 text-left">{{ __('Date') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('Class') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('Scope') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('Taken by') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('Status') }}</th>
                        <th class="px-3 py-2 text-left"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sessions as $session)
                        <tr class="border-t border-zinc-200 dark:border-zinc-700">
                            <td class="px-3 py-2">{{ $session->date->toDateString() }}</td>
                            <td class="px-3 py-2">{{ $session->schoolClass?->code }}</td>
                            <td class="px-3 py-2">{{ $session->subject?->name ?? __('Class') }}</td>
                            <td class="px-3 py-2">{{ $session->takenByTeacher?->user?->name ?? '—' }}</td>
                            <td class="px-3 py-2">{{ $session->isFinalized() ? __('Finalized') : __('Open') }}</td>
                            <td class="px-3 py-2">
                                <a class="underline" href="{{ route('admin.attendance.sessions.edit', $session) }}">{{ __('Open') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-6 text-zinc-500">{{ __('No attendance sessions yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $sessions->links() }}
    </div>
</x-layouts::app>
