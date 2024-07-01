<script>
    let totalChart;

    function generateChart(response) {
        const totalChartData = response.adSpent;

        const totalDates = Object.keys(totalChartData)
        const totalSpent = Object.values(totalChartData)

        // Clear existing chart if it exists
        if (totalChart) {
            totalChart.destroy();
        }

        const ctxTotal = document.getElementById('totalChart').getContext('2d');
        totalChart = createLineChart(ctxTotal, 'Total Spent', totalDates, totalSpent);

        let adSpentGrouped = response.adSpentGrouped;

        // Store references to chart instances
        let chartInstances = {};

        for (let channel in adSpentGrouped) {
            if (adSpentGrouped.hasOwnProperty(channel)) {
                let visitGroupedData = adSpentGrouped[channel];
                const socialMediaDates = Object.keys(visitGroupedData);
                const socialMedialVisit = Object.values(visitGroupedData);
                let chartSelector = channel.replace(/\s+/g, '') + 'Chart';
                let canvas = document.getElementById(chartSelector);

                // Check if chart instance exists
                if (chartInstances[chartSelector]) {
                    chartInstances[chartSelector].destroy(); // Destroy existing chart instance
                }

                // Create new canvas element
                let newCanvas = document.createElement('canvas');
                newCanvas.id = chartSelector;
                newCanvas.width = 800; // Set desired width
                newCanvas.height = 200; // Set desired height
                canvas.parentNode.replaceChild(newCanvas, canvas);

                // Get context and create new chart
                const ctxChannel = newCanvas.getContext('2d');
                chartInstances[chartSelector] = createLineChart(ctxChannel, channel, socialMediaDates, socialMedialVisit);
            }
        }

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
                        enabled: true, // Always display tooltips
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
    }
</script>
