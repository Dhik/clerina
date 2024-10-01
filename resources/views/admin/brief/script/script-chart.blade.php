<script>

    let campaignContentChart;

    function initChart() {
        $.ajax({
            url: "{{ route('brief_contents.chart', ['id_brief' => ':id_brief']) }}".replace(':id_brief', {{ $brief->id }}),
            type: 'GET',
            success: function (response) {
                console.log(response);
                renderChart(response);
            },
            error: function (xhr, status, error) {
                console.error(xhr.responseText);
            }
        });
    }

    function renderChart(chartData) {
        // Clear existing chart if it exists
        if (campaignContentChart) {
            campaignContentChart.destroy();
        }

        // Set up the Chart.js configuration
        let ctx = document.getElementById('statisticChartBrief').getContext('2d');
        campaignContentChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.map(data => data.date),
                datasets: [
                    {
                        label: 'Views',
                        data: chartData.map(data => data.total_view),
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        fill: false
                    },
                    {
                        label: 'Likes',
                        data: chartData.map(data => data.total_like),
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        fill: false
                    },
                    {
                        label: 'Positive Likes',
                        data: chartData.map(data => data.positive_like),
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        fill: false
                    },
                    {
                        label: 'Comments',
                        data: chartData.map(data => data.total_comment),
                        backgroundColor: 'rgba(255, 206, 86, 0.2)',
                        borderColor: 'rgba(255, 206, 86, 1)',
                        fill: false
                    }
                ]
            },
            options: {
                responsive: true,
                title: {
                    display: true,
                    text: 'Brief Content Statistics Chart'
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
</script>
