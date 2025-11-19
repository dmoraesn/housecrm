<div class="card">
    <div class="card-body">
        <h4 class="card-title">{{ $lineChart['title'] ?? 'Gráfico de Linhas' }}</h4>
        <p class="card-subtitle mb-3">{{ $lineChart['subtitle'] ?? '' }}</p>

        <canvas id="lineChartCanvas"></canvas>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const ctx = document.getElementById('lineChartCanvas').getContext('2d');

    const labels = @json($lineChart['labels'] ?? []);
    const datasets = [];

    const data = @json($lineChart['data'] ?? []);
    Object.keys(data).forEach((label, index) => {
        datasets.push({
            label: label,
            data: data[label],
            borderColor: `hsl(${index * 80}, 70%, 50%)`,
            fill: false,
            tension: 0.4
        });
    });

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: datasets
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
@endpush
