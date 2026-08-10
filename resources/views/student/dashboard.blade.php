<x-layouts::app :title="__('Student Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div>
            <flux:heading size="xl">{{ __('Student Dashboard') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Your class and enrollment details (read-only).') }}</flux:text>
        </div>

        @if (! $student)
            <flux:callout variant="warning" icon="exclamation-triangle">
                <flux:callout.heading>{{ __('No student profile is linked to this account yet.') }}</flux:callout.heading>
            </flux:callout>
        @else
            <div class="grid gap-4 md:grid-cols-2">
                <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                    <flux:heading size="sm">{{ __('Profile') }}</flux:heading>
                    <dl class="mt-3 space-y-2 text-sm">
                        <div><dt class="text-zinc-500">{{ __('Admission no.') }}</dt><dd>{{ $student->admission_no }}</dd></div>
                        <div><dt class="text-zinc-500">{{ __('Gender') }}</dt><dd>{{ $student->gender->label() }}</dd></div>
                        <div><dt class="text-zinc-500">{{ __('Class') }}</dt><dd>{{ $student->currentClass?->code ?? '—' }}</dd></div>
                        <div><dt class="text-zinc-500">{{ __('Grade') }}</dt><dd>{{ $student->currentClass?->grade?->name ?? '—' }}</dd></div>
                    </dl>
                </div>
                <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                    <flux:heading size="sm">{{ __('Subjects') }}</flux:heading>
                    <ul class="mt-3 space-y-1 text-sm">
                        @forelse ($student->currentClass?->subjects ?? [] as $subject)
                            <li>{{ $subject->name }}</li>
                        @empty
                            <li class="text-zinc-500">{{ __('No subjects linked to your class yet.') }}</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        @endif
    </div>
</x-layouts::app>
