<x-layouts::app :title="__('Class timetable')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div>
            <flux:heading size="xl">{{ __('Class timetable') }}</flux:heading>
            <flux:text class="mt-1">{{ $student->currentClass?->code }} · {{ __('Weekly period grid') }}</flux:text>
        </div>

        <x-timetable.grid
            :days="$days"
            :periods="$periods"
            :grid="$grid"
            :period-times="$periodTimes"
            variant="student"
        />
    </div>
</x-layouts::app>
