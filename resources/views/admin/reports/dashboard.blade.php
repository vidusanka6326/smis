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
            <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                <flux:text>{{ __('Students') }}</flux:text>
                <p class="mt-2 text-3xl font-semibold">{{ $studentCount }}</p>
            </div>
            <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                <flux:text>{{ __('Attendance rows (month)') }}</flux:text>
                <p class="mt-2 text-3xl font-semibold">{{ count($attendance['student_rows']) }}</p>
            </div>
            <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                <flux:text>{{ __('Latest exam pass rate') }}</flux:text>
                <p class="mt-2 text-3xl font-semibold">{{ $examStats['pass_rate'] ?? 0 }}%</p>
            </div>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                <flux:heading size="sm">{{ __('Gender mix') }}</flux:heading>
                <canvas id="genderChart" class="mt-4 max-h-64"></canvas>
            </div>
            <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                <flux:heading size="sm">{{ __('Grade letters (latest exam)') }}</flux:heading>
                <canvas id="lettersChart" class="mt-4 max-h-64"></canvas>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
        <script>
            const gender = @json($chartGender);
            const letters = @json($chartGradeLetters);
            new Chart(document.getElementById('genderChart'), {
                type: 'doughnut',
                data: { labels: gender.labels, datasets: [{ data: gender.data, backgroundColor: ['#2563eb', '#db2777'] }] },
            });
            new Chart(document.getElementById('lettersChart'), {
                type: 'bar',
                data: { labels: letters.labels, datasets: [{ label: @json(__('Count')), data: letters.data, backgroundColor: '#0f766e' }] },
                options: { scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } },
            });
        </script>
    </div>
</x-layouts::app>
