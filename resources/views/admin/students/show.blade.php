<x-layouts::app :title="__('Student')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <flux:heading size="xl">{{ $student->user?->name }}</flux:heading>
                <flux:text class="mt-1">{{ $student->admission_no }} · {{ $student->gender->label() }}</flux:text>
            </div>
            <div class="flex flex-wrap gap-2">
                <flux:button :href="route('admin.students.edit', $student)" variant="primary" wire:navigate>{{ __('Edit') }}</flux:button>
                <form method="POST" action="{{ route('admin.students.destroy', $student) }}" onsubmit="return confirm(@js(__('Delete this student?')))">
                    @csrf
                    @method('DELETE')
                    <flux:button type="submit" variant="danger">{{ __('Delete') }}</flux:button>
                </form>
            </div>
        </div>

        @if (session('status'))
            <flux:callout variant="success" icon="check-circle">
                <flux:callout.heading>{{ session('status') }}</flux:callout.heading>
            </flux:callout>
        @endif

        <div class="grid gap-4 md:grid-cols-2">
            <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                <flux:heading size="sm">{{ __('Current class') }}</flux:heading>
                <p class="mt-2 text-sm">{{ $student->currentClass?->code ?? __('Unassigned') }}</p>
                <p class="text-sm text-zinc-500">{{ $student->currentClass?->grade?->name }}</p>
            </div>
            <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                <flux:heading size="sm">{{ __('Guardian') }}</flux:heading>
                <dl class="mt-2 space-y-1 text-sm">
                    <div>{{ $student->guardian_name ?: '—' }}</div>
                    <div>{{ $student->guardian_phone ?: '—' }}</div>
                    <div>{{ $student->guardian_email ?: '—' }}</div>
                    <div>{{ $student->guardian_relationship ?: '—' }}</div>
                </dl>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
            <flux:heading size="sm">{{ __('Enrollment history') }}</flux:heading>
            <ul class="mt-3 space-y-2 text-sm">
                @forelse ($student->enrollments as $enrollment)
                    <li>
                        {{ $enrollment->academicYear?->name }} —
                        {{ $enrollment->schoolClass?->code }} —
                        {{ $enrollment->status->label() }}
                    </li>
                @empty
                    <li class="text-zinc-500">{{ __('No enrollments.') }}</li>
                @endforelse
            </ul>
        </div>
    </div>
</x-layouts::app>
