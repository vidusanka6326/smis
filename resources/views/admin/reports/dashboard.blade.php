@php
    $chartColors = ['#ff7e5f', '#feb47b', '#ffcaa7', '#ffad8f', '#ce6a57'];
@endphp

<x-layouts::app :title="__('Reports')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <flux:heading size="xl">{{ __('Analytics dashboard') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Demographics, attendance risk, and examination overview.') }}</flux:text>
            </div>
            <div class="flex flex-wrap gap-2">
                <flux:button :href="route('admin.reports.demographics')" variant="filled" wire:navigate>{{ __('Demographics') }}</flux:button>
                <flux:button :href="route('admin.reports.attendance')" variant="filled" wire:navigate>{{ __('Attendance') }}</flux:button>
                <flux:button :href="route('admin.reports.examination')" variant="filled" wire:navigate>{{ __('Exams') }}</flux:button>
                <flux:button :href="route('admin.reports.performance')" variant="primary" wire:navigate>{{ __('Best / poor') }}</flux:button>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-dashboard.stat :label="__('Students')" :value="$studentCount" />
            <x-dashboard.stat
                :label="__('Avg attendance (month)')"
                :value="$attendance['summary']['class_average'] !== null ? $attendance['summary']['class_average'].'%' : '—'"
                :hint="__(':count students tracked', ['count' => $attendance['summary']['tracked_students']])"
                tone="success"
            />
            <x-dashboard.stat
                :label="__('Attendance at risk')"
                :value="$attendance['summary']['at_risk_count']"
                :hint="__('Below :pct%', ['pct' => (int) $attendance['summary']['threshold']])"
                :tone="$attendance['summary']['at_risk_count'] > 0 ? 'warning' : 'default'"
            />
            <x-dashboard.stat
                :label="__('Latest exam pass rate')"
                :value="($examStats['pass_rate'] ?? null) !== null ? $examStats['pass_rate'].'%' : '—'"
                :hint="$exam?->name"
            />
        </div>

        <div class="grid gap-4 lg:grid-cols-3">
            <x-dashboard.chart-card :title="__('Gender mix')" canvas-id="genderChart" />
            <x-dashboard.chart-card :title="__('Attendance % by class')" canvas-id="attendanceChart" />
            <x-dashboard.chart-card :title="__('Grade letters (latest exam)')" canvas-id="lettersChart" />
        </div>

        <x-charts.render :charts="[
            [
                'id' => 'genderChart',
                'type' => 'doughnut',
                'data' => $chartGender,
                'colors' => ['#feb47b', '#ffad8f'],
            ],
            [
                'id' => 'attendanceChart',
                'type' => 'bar',
                'label' => __('%'),
                'data' => $chartAttendanceByClass,
                'colors' => ['#ff7e5f'],
            ],
            [
                'id' => 'lettersChart',
                'type' => 'bar',
                'label' => __('Count'),
                'data' => $chartGradeLetters,
                'colors' => $chartColors,
            ],
        ]" />
    </div>
</x-layouts::app>
