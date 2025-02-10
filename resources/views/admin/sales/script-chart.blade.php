<script>

    let visitChart;
    let closingRateChart;
    let orderChart;
    let turnoverChart;
    let adSpentChart;
    let roasChart;
    let qtyChart;
    let impressionChart;

    function generateChart(response) {
        const salesData = response.sales;
        const dates = salesData.map(data => data.date);
        const visits = salesData.map(data => data.visit);
        const closingRates = salesData.map(data => data.closing_rate);
        const orders = salesData.map(data => data.order);
        const turnovers = salesData.map(data => data.turnover);
        const adSpents = salesData.map(data => data.ad_spent_total);
        const roas = salesData.map(data => data.roas);
        const qty = salesData.map(data => data.qty);

        if (visitChart) {
            visitChart.destroy();
        }

        if (closingRateChart) {
            closingRateChart.destroy();
        }

        if (orderChart) {
            orderChart.destroy();
        }

        if (turnoverChart) {
            turnoverChart.destroy();
        }

        if (adSpentChart) {
            adSpentChart.destroy();
        }

        if (roasChart) {
            roasChart.destroy();
        }

        if (qtyChart) {
            qtyChart.destroy();
        }
        if (impressionChart) {
            impressionChart.destroy();
        }

        function createLineChart(ctx, label, data) {
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

        const ctxVisit = document.getElementById('visitChart').getContext('2d');
        visitChart = createLineChart(ctxVisit, 'Visits', visits);

        const ctxClosingRate = document.getElementById('closingRateChart').getContext('2d');
        closingRateChart = createLineChart(ctxClosingRate, 'Closing Rate', closingRates);

        const ctxOrder = document.getElementById('orderChart').getContext('2d');
        orderChart = createLineChart(ctxOrder, 'Orders', orders);

        const ctxTurnover = document.getElementById('turnOverChart').getContext('2d');
        turnoverChart = createLineChart(ctxTurnover, 'Sales', turnovers);

        const ctxAdSpent = document.getElementById('adSpentChart').getContext('2d');
        adSpentChart = createLineChart(ctxAdSpent, 'AdSpent', adSpents);

        const ctxRoas = document.getElementById('roasChart').getContext('2d');
        roasChart = createLineChart(ctxRoas, 'ROAS', roas);

        const ctxQty = document.getElementById('qtyChart').getContext('2d');
        qtyChart = createLineChart(ctxQty, 'QTY', qty);

        fetch('{{ route("adSpentSocialMedia.line-data") }}')
            .then(response => response.json())
            .then(result => {
                if (result.status === 'success') {
                    const impressionData = result.impressions;
                    const impressionDates = impressionData.map(data => data.date);
                    const impressions = impressionData.map(data => data.impressions);

                    const ctxImpression = document.getElementById('impressionChart').getContext('2d');
                    impressionChart = createLineChart(ctxImpression, 'Impressions', impressions);
                }
            })
            .catch(error => {
                console.error('Error fetching impression data:', error);
        });
    }
</script>
