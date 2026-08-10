<x-layouts::app :title="__('Admin Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <flux:heading size="xl">{{ __('Admin Dashboard') }}</flux:heading>
                <flux:text class="mt-1">{{ __('School-wide analytics, attendance, and exam pulse.') }}</flux:text>
            </div>
            <div class="flex flex-wrap gap-2">
                <flux:button :href="route('admin.reports.dashboard')" variant="primary" wire:navigate>{{ __('Full reports') }}</flux:button>
                <flux:button :href="route('admin.timetables.index')" variant="filled" wire:navigate>{{ __('Timetables') }}</flux:button>
                <flux:button :href="route('admin.activity-logs.index')" variant="filled" wire:navigate>{{ __('Activity log') }}</flux:button>
            </div>
        </div>

        @if (session('status'))
            <flux:callout variant="success" icon="check-circle">
                <flux:callout.heading>{{ session('status') }}</flux:callout.heading>
            </flux:callout>
        @endif

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-dashboard.stat :label="__('Students')" :value="$stats['students']" />
            <x-dashboard.stat :label="__('Teachers')" :value="$stats['teachers']" />
            <x-dashboard.stat :label="__('Classes')" :value="$stats['classes']" />
            <x-dashboard.stat
                :label="__('Avg attendance (month)')"
                :value="$stats['avg_attendance'] !== null ? $stats['avg_attendance'].'%' : '—'"
                :hint="__('Across :count students tracked', ['count' => $stats['attendance_tracked']])"
            />
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <x-dashboard.stat :label="__('Published exams')" :value="$stats['published_exams']" />
            <x-dashboard.stat :label="__('Draft exams')" :value="$stats['draft_exams']" />
            <x-dashboard.stat
                :label="__('Latest exam pass rate')"
                :value="$stats['pass_rate'] !== null ? $stats['pass_rate'].'%' : '—'"
                :hint="$exam?->name"
            />
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <x-dashboard.chart-card :title="__('Gender mix')" canvas-id="adminGenderChart" />
            <x-dashboard.chart-card :title="__('Students by grade')" canvas-id="adminGradesChart" />
            <x-dashboard.chart-card :title="__('Attendance % by class (month)')" canvas-id="adminAttendanceChart" />
            <x-dashboard.chart-card :title="__('Grade letters (latest published exam)')" canvas-id="adminLettersChart" />
        </div>

        <div class="flex flex-wrap gap-2">
            <flux:button :href="route('admin.users.create')" variant="filled" wire:navigate>{{ __('Create user') }}</flux:button>
            <flux:button :href="route('admin.teachers.index')" variant="filled" wire:navigate>{{ __('Teachers') }}</flux:button>
            <flux:button :href="route('admin.students.index')" variant="filled" wire:navigate>{{ __('Students') }}</flux:button>
            <flux:button :href="route('admin.attendance.sessions.index')" variant="filled" wire:navigate>{{ __('Attendance') }}</flux:button>
            <flux:button :href="route('admin.exams.index')" variant="filled" wire:navigate>{{ __('Exams') }}</flux:button>
            <flux:button :href="route('admin.academic-years.index')" variant="ghost" wire:navigate>{{ __('Academic years') }}</flux:button>
        </div>

        <x-charts.render :charts="[
            [
                'id' => 'adminGenderChart',
                'type' => 'doughnut',
                'data' => $charts['gender'],
                'colors' => ['#2563eb', '#db2777'],
            ],
            [
                'id' => 'adminGradesChart',
                'type' => 'bar',
                'label' => __('Students'),
                'data' => $charts['grades'],
                'colors' => ['#0f766e'],
            ],
            [
                'id' => 'adminAttendanceChart',
                'type' => 'bar',
                'label' => __('%'),
                'data' => $charts['attendance_by_class'],
                'colors' => ['#2563eb'],
            ],
            [
                'id' => 'adminLettersChart',
                'type' => 'bar',
                'label' => __('Count'),
                'data' => $charts['letters'],
                'colors' => ['#ca8a04'],
            ],
        ]" />
    </div>
</x-layouts::app>
