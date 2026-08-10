<x-layouts::app :title="__('Reports')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <flux:heading size="xl">{{ __('My class reports') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Scoped to your assignments and homeroom.') }}</flux:text>
            </div>
            <div class="flex flex-wrap gap-2">
                <flux:button :href="route('teacher.reports.attendance')" variant="filled" wire:navigate>{{ __('Attendance') }}</flux:button>
                <flux:button :href="route('teacher.reports.examination')" variant="filled" wire:navigate>{{ __('Exams') }}</flux:button>
                <flux:button :href="route('teacher.reports.performance')" variant="primary" wire:navigate>{{ __('Best / poor') }}</flux:button>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <x-dashboard.stat :label="__('Students in scope')" :value="$demographics['total']" />
            <x-dashboard.stat :label="__('Attendance rows')" :value="count($attendance['student_rows'])" />
            <x-dashboard.stat :label="__('Exam pass rate')" :value="($examStats['pass_rate'] ?? 0).'%'" />
        </div>

        <x-dashboard.chart-card :title="__('Gender mix')" canvas-id="genderChart" class="max-w-xl" />

        <x-charts.render :charts="[
            [
                'id' => 'genderChart',
                'type' => 'doughnut',
                'data' => $chartGender,
                'colors' => ['#2563eb', '#db2777'],
            ],
        ]" />
    </div>
</x-layouts::app>
