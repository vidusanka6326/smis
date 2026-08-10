<x-layouts::app :title="__('Reports')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <flux:heading size="xl">{{ __('Analytics dashboard') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Demographics, attendance, and examination overview.') }}</flux:text>
            </div>
            <div class="flex flex-wrap gap-2">
                <flux:button :href="route('admin.reports.demographics')" variant="filled" wire:navigate>{{ __('Demographics') }}</flux:button>
                <flux:button :href="route('admin.reports.attendance')" variant="filled" wire:navigate>{{ __('Attendance') }}</flux:button>
                <flux:button :href="route('admin.reports.examination')" variant="filled" wire:navigate>{{ __('Exams') }}</flux:button>
                <flux:button :href="route('admin.reports.performance')" variant="primary" wire:navigate>{{ __('Best / poor') }}</flux:button>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <x-dashboard.stat :label="__('Students')" :value="$studentCount" />
            <x-dashboard.stat :label="__('Attendance rows (month)')" :value="count($attendance['student_rows'])" />
            <x-dashboard.stat :label="__('Latest exam pass rate')" :value="($examStats['pass_rate'] ?? 0).'%'" />
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <x-dashboard.chart-card :title="__('Gender mix')" canvas-id="genderChart" />
            <x-dashboard.chart-card :title="__('Grade letters (latest exam)')" canvas-id="lettersChart" />
        </div>

        <x-charts.render :charts="[
            [
                'id' => 'genderChart',
                'type' => 'doughnut',
                'data' => $chartGender,
                'colors' => ['#2563eb', '#db2777'],
            ],
            [
                'id' => 'lettersChart',
                'type' => 'bar',
                'label' => __('Count'),
                'data' => $chartGradeLetters,
                'colors' => ['#0f766e'],
            ],
        ]" />
    </div>
</x-layouts::app>
