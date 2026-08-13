@props([
    'name' => 'month',
    'label' => null,
    'value' => null,
    'count' => 36,
])

@php
    $label ??= __('Month');
    $selected = is_string($value) && $value !== '' ? $value : now()->format('Y-m');
    $months = collect(range(0, (int) $count - 1))
        ->map(fn (int $i) => now()->startOfMonth()->subMonths($i));

    if ($months->doesntContain(fn ($month) => $month->format('Y-m') === $selected)) {
        try {
            $extra = \Illuminate\Support\Carbon::createFromFormat('Y-m', $selected)->startOfMonth();
            $months = $months->prepend($extra)->unique(fn ($month) => $month->format('Y-m'));
        } catch (\Throwable) {
            // Keep the generated range when the current value is not a valid Y-m month.
        }
    }
@endphp

<flux:select :name="$name" :label="$label" {{ $attributes }}>
    @foreach ($months as $monthOption)
        <flux:select.option :value="$monthOption->format('Y-m')" :selected="$selected === $monthOption->format('Y-m')">
            {{ $monthOption->translatedFormat('F Y') }}
        </flux:select.option>
    @endforeach
</flux:select>
