import Chart from 'chart.js/auto';

document.addEventListener('DOMContentLoaded', () => {
    const cfg = window.adminAnalyticsConfig || {};
    const byCategory = Array.isArray(cfg.byCategory) ? cfg.byCategory : [];
    const byClass = Array.isArray(cfg.byClass) ? cfg.byClass : [];

    const colors = ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4', '#ec4899'];

    if (byCategory.length && document.getElementById('chart-category')) {
        const sessionsList = byCategory.map(c => Number(c.sessions ?? 0));
        const maxSessions = Math.max(1, ...sessionsList);

        new Chart(document.getElementById('chart-category'), {
            type: 'bar',
            data: {
                labels: byCategory.map(c => c.name),
                datasets: [
                    {
                        label: 'Jumlah Ujian',
                        data: sessionsList,
                        backgroundColor: colors[0] + '80',
                        borderColor: colors[0],
                        borderWidth: 1,
                    },
                    {
                        label: 'Rata-rata Skor',
                        data: byCategory.map(c => Number(c.avg_score ?? 0)),
                        backgroundColor: colors[1] + '80',
                        borderColor: colors[1],
                        borderWidth: 1,
                        yAxisID: 'y1',
                    },
                ],
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'top' } },
                scales: {
                    y: { beginAtZero: true, max: maxSessions * 1.2 },
                    y1: { beginAtZero: true, max: 100, position: 'right', grid: { drawOnChartArea: false } },
                },
            },
        });
    }

    if (byClass.length && document.getElementById('chart-class')) {
        new Chart(document.getElementById('chart-class'), {
            type: 'bar',
            data: {
                labels: byClass.map(c => c.name),
                datasets: [
                    {
                        label: 'Jumlah Ujian',
                        data: byClass.map(c => Number(c.sessions ?? 0)),
                        backgroundColor: colors[2] + '99',
                        borderColor: colors[2],
                        borderWidth: 1,
                    },
                    {
                        label: 'Rata-rata Skor',
                        data: byClass.map(c => Number(c.avg_score ?? 0)),
                        backgroundColor: colors[3] + '99',
                        borderColor: colors[3],
                        borderWidth: 1,
                    },
                ],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                plugins: { legend: { position: 'top' } },
                scales: { x: { beginAtZero: true } },
            },
        });
    }
});

