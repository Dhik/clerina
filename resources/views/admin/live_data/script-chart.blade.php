<script>
    let liveDataChart;

    function initLiveDataChart() {
        $.ajax({
            url: "{{ route('live_data.chart') }}",
            type: 'GET',
            success: function (response) {
                console.log(response);
                renderLiveDataChart(response);
            },
            error: function (xhr, status, error) {
                console.error(xhr.responseText);
            }
        });
    }

    function renderLiveDataChart(chartData) {
        // Clear existing chart if it exists
        if (liveDataChart) {
            liveDataChart.destroy();
        }

        // Set up the Chart.js configuration
        let ctx = document.getElementById('statisticChartLiveData').getContext('2d');
        liveDataChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.map(data => data.date),
                datasets: [
                    {
                        label: 'Dilihat',
                        data: chartData.map(data => data.total_view),
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        fill: false
                    },
                    {
                        label: 'Peak Viewers',
                        data: chartData.map(data => data.peak_viewers),
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        fill: false
                    },
                    {
                        label: 'Comments',
                        data: chartData.map(data => data.total_comment),
                        backgroundColor: 'rgba(255, 206, 86, 0.2)',
                        borderColor: 'rgba(255, 206, 86, 1)',
                        fill: false
                    },
                    {
                        label: 'Orders',
                        data: chartData.map(data => data.orders),
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        fill: false
                    }
                ]
            },
            options: {
                responsive: true,
                title: {
                    display: true,
                    text: 'Live Data Statistics Chart'
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
                            labelString: 'Value'
                        }
                    }]
                }
            }
        });
    }

    // Initialize chart when page loads
    $(document).ready(function() {
        if ($('#statisticChartLiveData').length) {
            initLiveDataChart();
        }
    });
</script>