<script>

function generateChart(response) {
    function createLineChart(ctx, label, dates, data) {
        return new Chart(ctx, {
            type: 'line',
            data: {
                labels: dates,
                datasets: [{
                    label: label,
                    data: data,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                tooltips: {
                    enabled: true,
                    callbacks: {
                        label: function(tooltipItem, data) {
                            let label = data.datasets[tooltipItem.datasetIndex].label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += tooltipItem.yLabel.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                            return label;
                        }
                    }
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            callback: function(value, index, values) {
                                if (parseInt(value) >= 1000) {
                                    return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                                } else {
                                    return value;
                                }
                            }
                        }
                    }]
                }
            }
        });
    }
    
    // Make sure to define the dates and data here, and actually call the function
    // This assumes response contains the chart data
    if (response && response.chartData) {
        // Get chart data from response
        const impressionDates = response.chartData.dates;
        const impressions = response.chartData.impressions;
        
        // Get the canvas context
        const ctxImpression = document.getElementById('impressionChart').getContext('2d');
        
        // Destroy existing chart if it exists
        if (window.impressionChart) {
            window.impressionChart.destroy();
        }
        
        // Create new chart and store it globally
        window.impressionChart = createLineChart(ctxImpression, 'Impressions', impressionDates, impressions);
    }
}
</script>
