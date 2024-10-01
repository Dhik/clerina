<script>
    let competitorSalesChart;

    function initSalesChart() {
        // Make AJAX request to get sales chart data
        $.ajax({
            url: "{{ route('competitor_brands.monthly_sales') }}",
            type: 'GET',
            success: function (response) {
                renderSalesChart(response);
            },
            error: function (xhr, status, error) {
                console.error(xhr.responseText);
            }
        });
    }

    function renderSalesChart(chartData) {
        // Clear existing chart if it exists
        if (competitorSalesChart) {
            competitorSalesChart.destroy();
        }

        // Set up the Chart.js configuration
        let ctx = document.getElementById('competitorSalesChart').getContext('2d');
        competitorSalesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.map(data => data.date),
                datasets: [{
                    label: 'Sales',
                    data: chartData.map(data => data.sales),
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    fill: false
                }]
            },
            options: {
                responsive: true,
                title: {
                    display: true,
                    text: 'Competitor Sales Chart'
                },
                scales: {
                    xAxes: [{
                        scaleLabel: {
                            display: true,
                            labelString: 'Date'
                        }
                    }],
                    yAxes: [{
                        scaleLabel: {
                            display: true,
                            labelString: 'Sales (IDR)'
                        },
                        ticks: {
                            beginAtZero: true
                        }
                    }]
                }
            }
        });
    }

    // Initialize the sales chart when the page loads or data changes
    $(document).ready(function () {
        initSalesChart();
    });
</script>
