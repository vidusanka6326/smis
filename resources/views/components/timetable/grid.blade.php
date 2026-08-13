@props([
    'days',
    'periods',
    'grid',
    'periodTimes' => [],
    'variant' => 'student', // admin|teacher|student
])

@php
    $palette = [
        'bg-teal-50 text-teal-900 border-teal-200 dark:bg-teal-950/50 dark:text-teal-100 dark:border-teal-800',
        'bg-sky-50 text-sky-900 border-sky-200 dark:bg-sky-950/50 dark:text-sky-100 dark:border-sky-800',
        'bg-violet-50 text-violet-900 border-violet-200 dark:bg-violet-950/50 dark:text-violet-100 dark:border-violet-800',
        'bg-amber-50 text-amber-900 border-amber-200 dark:bg-amber-950/50 dark:text-amber-100 dark:border-amber-800',
        'bg-rose-50 text-rose-900 border-rose-200 dark:bg-rose-950/50 dark:text-rose-100 dark:border-rose-800',
        'bg-lime-50 text-lime-900 border-lime-200 dark:bg-lime-950/50 dark:text-lime-100 dark:border-lime-800',
        'bg-indigo-50 text-indigo-900 border-indigo-200 dark:bg-indigo-950/50 dark:text-indigo-100 dark:border-indigo-800',
        'bg-orange-50 text-orange-900 border-orange-200 dark:bg-orange-950/50 dark:text-orange-100 dark:border-orange-800',
    ];
@endphp

<div {{ $attributes->class('overflow-x-auto rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900') }}>
    <table class="min-w-[720px] w-full border-collapse text-sm">
        <thead>
            <tr>
                <th class="sticky left-0 z-10 w-28 border-b border-e border-zinc-200 bg-zinc-100 px-3 py-3 text-left font-semibold dark:border-zinc-700 dark:bg-zinc-800">
                    {{ __('Period') }}
                </th>
                @foreach ($days as $day)
                    <th class="border-b border-zinc-200 bg-zinc-100 px-2 py-3 text-center font-semibold dark:border-zinc-700 dark:bg-zinc-800">
                        <div>{{ $day->label() }}</div>
                        <div class="text-xs font-normal text-zinc-500">{{ __('Day :n', ['n' => $day->value]) }}</div>
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($periods as $period)
                <tr class="align-top">
                    <th class="sticky left-0 z-10 border-b border-e border-zinc-200 bg-zinc-50 px-3 py-3 text-left font-medium dark:border-zinc-700 dark:bg-zinc-900/90">
                        <div class="text-base">P{{ $period }}</div>
                        @if (! empty($periodTimes[$period]['label']))
                            <div class="mt-0.5 text-xs font-normal text-zinc-500">{{ $periodTimes[$period]['label'] }}</div>
                        @endif
                    </th>
                    @foreach ($days as $day)
                        @php($slot = $grid[$day->value][$period] ?? null)
                        <td class="h-24 border-b border-e border-zinc-100 p-1.5 last:border-e-0 dark:border-zinc-800">
                            @if ($slot)
                                @php($tone = $palette[($slot->subject_id ?? $slot->id) % count($palette)])
                                <div class="flex h-full min-h-20 flex-col justify-between rounded-xl border p-2 {{ $tone }}">
                                    <div>
                                        @if ($variant === 'teacher')
                                            <div class="font-semibold leading-tight">{{ $slot->schoolClass?->code }}</div>
                                            <div class="mt-1 text-xs opacity-80">{{ $slot->subject?->name }}</div>
                                        @else
                                            <div class="font-semibold leading-tight">{{ $slot->subject?->name }}</div>
                                            <div class="mt-1 text-xs opacity-80">
                                                @if ($variant === 'admin')
                                                    {{ $slot->teacher?->user?->name }}
                                                @else
                                                    {{ $slot->teacher?->user?->name }}
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                    @if ($variant === 'admin')
                                        <div class="mt-2 flex gap-2">
                                            <flux:button :href="route('admin.timetables.edit', $slot)" variant="ghost" size="xs">{{ __('Edit') }}</flux:button>
                                            <form method="POST" action="{{ route('admin.timetables.destroy', $slot) }}" onsubmit="return confirm(@js(__('Delete this slot?')))">
                                                @csrf
                                                @method('DELETE')
                                                <flux:button type="submit" variant="danger" size="xs">{{ __('Delete') }}</flux:button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="flex h-full min-h-20 items-center justify-center rounded-xl border border-dashed border-zinc-200 bg-zinc-50/60 text-zinc-400 dark:border-zinc-700 dark:bg-zinc-950/40">
                                    <span class="text-xs">{{ __('Free') }}</span>
                                </div>
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
