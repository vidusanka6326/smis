@props([
    'charts' => [],
])

{{--
    @param array<int, array{id: string, type: string, data: array{labels: list<string>, data: list<int|float>}, label?: string, colors?: list<string>}> $charts
--}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    (() => {
        const charts = @json($charts);
        const fallbackColors = ['#0f766e', '#2563eb', '#db2777', '#ca8a04', '#7c3aed', '#ea580c', '#0891b2', '#4d7c0f'];

        charts.forEach((chart) => {
            const canvas = document.getElementById(chart.id);
            if (! canvas || typeof Chart === 'undefined') {
                return;
            }

            const colors = chart.colors?.length
                ? chart.colors
                : fallbackColors.slice(0, Math.max(chart.data.data.length, 1));

            new Chart(canvas, {
                type: chart.type,
                data: {
                    labels: chart.data.labels,
                    datasets: [{
                        label: chart.label ?? '',
                        data: chart.data.data,
                        backgroundColor: colors,
                        borderWidth: 0,
                        borderRadius: chart.type === 'bar' ? 6 : 0,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: chart.type !== 'bar',
                            position: 'bottom',
                        },
                    },
                    scales: chart.type === 'bar' ? {
                        y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(113,113,122,0.15)' } },
                        x: { grid: { display: false } },
                    } : {},
                },
            });
        });
    })();
</script>
