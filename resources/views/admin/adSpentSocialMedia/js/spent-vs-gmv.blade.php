<script>
/**
 * Spent vs GMV tab functionality
 */
$(document).ready(function() {
    // Initialize variables
    let spentVsGmvFilterDate = initDateRangePicker('spentVsGmvFilterDates');
    let spentVsGmvChannelFilter = $('#spentVsGmvChannelFilter');
    let spentVsGmvChart = null;
    let roasTrendChart = null;

    // Initialize Spent vs GMV DataTable
    let spentVsGmvTable = $('#spentVsGmvTable').DataTable({
        responsive: true,
        processing: true,
        serverSide: true,
        pageLength: 25,
        ajax: {
            url: "{{ route('adSpentSocialMedia.get_spent_vs_gmv') }}",
            data: function (d) {
                if (spentVsGmvFilterDate.val()) {
                    let dates = spentVsGmvFilterDate.val().split(' - ');
                    d.date_start = moment(dates[0], 'DD/MM/YYYY').format('YYYY-MM-DD');
                    d.date_end = moment(dates[1], 'DD/MM/YYYY').format('YYYY-MM-DD');
                }
                if (spentVsGmvChannelFilter.val()) {
                    d.channel = spentVsGmvChannelFilter.val();
                }
            }
        },
        columns: [
            {data: 'date', name: 'date'},
            {data: 'channel_name', name: 'channel_name'},
            {
                data: 'sales_amount', 
                name: 'sales_amount',
                render: function(data) {
                    return formatCurrency(data);
                }
            },
            {
                data: 'spend_amount', 
                name: 'spend_amount',
                render: function(data) {
                    return formatCurrency(data);
                }
            },
            {
                data: 'roas', 
                name: 'roas',
                render: function(data) {
                    return data ? parseFloat(data).toFixed(2) : '0.00';
                }
            },
            {
                data: 'spent_percentage', 
                name: 'spent_percentage',
                render: function(data) {
                    return data ? parseFloat(data).toFixed(2) + '%' : '0.00%';
                }
            }
        ],
        columnDefs: [
            { "targets": [2, 3, 4, 5], "className": "text-right" },
            { "targets": [1], "className": "text-center" }
        ],
        order: [[0, 'desc']],
        fixedHeader: true,
        scrollCollapse: true,
        deferRender: true,
        scroller: true
    });

    // Button click handlers
    $('#spentVsGmvResetFilterBtn').click(function() {
        spentVsGmvFilterDate.val('');
        spentVsGmvChannelFilter.val('');
        spentVsGmvTable.draw();
        initSpentVsGmvCharts();
    });

    // Export button handler
    $('#btnExportSpentVsGmvReport').click(function() {
        let params = {};
        
        if (spentVsGmvFilterDate.val()) {
            let dates = spentVsGmvFilterDate.val().split(' - ');
            params.date_start = moment(dates[0], 'DD/MM/YYYY').format('YYYY-MM-DD');
            params.date_end = moment(dates[1], 'DD/MM/YYYY').format('YYYY-MM-DD');
        }
        
        if (spentVsGmvChannelFilter.val()) {
            params.channel = spentVsGmvChannelFilter.val();
        }
        
        // Show loading state
        showLoadingSwal('Generating report...');
        
        // Create URL with params
        const url = new URL("{{ route('adSpentSocialMedia.export_spent_vs_gmv') }}", window.location.origin);
        Object.keys(params).forEach(key => url.searchParams.append(key, params[key]));
        
        // Create temporary form for POST request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = url.toString();
        form.style.display = 'none';
        
        // Add CSRF token
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = $('meta[name="csrf-token"]').attr('content');
        form.appendChild(csrfInput);
        
        // Add to document, submit, and remove
        document.body.appendChild(form);
        form.submit();
        
        // Close loading after a short delay
        setTimeout(function() {
            Swal.close();
        }, 2000);
    });

    // Refresh Data button handler
    $('#btnRefreshSpentVsGmvData').click(function() {
        Swal.fire({
            title: 'Refresh Spent vs GMV Data',
            text: 'This will refresh the data from the database. Continue?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, refresh data',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                spentVsGmvTable.draw();
                initSpentVsGmvCharts();
                Swal.fire('Success!', 'Data has been refreshed.', 'success');
            }
        });
    });

    // Filter change handlers
    spentVsGmvChannelFilter.change(function() {
        spentVsGmvTable.draw();
        initSpentVsGmvCharts();
    });

    spentVsGmvFilterDate.change(function() {
        spentVsGmvTable.draw();
        initSpentVsGmvCharts();
    });

    // Chart initialization function
    function initSpentVsGmvCharts() {
        const filterValue = spentVsGmvFilterDate.val();
        const channelValue = spentVsGmvChannelFilter.val();
        
        const url = new URL("{{ route('adSpentSocialMedia.spent_vs_gmv_chart_data') }}", window.location.origin);
        
        if (filterValue) {
            let dates = filterValue.split(' - ');
            url.searchParams.append('date_start', moment(dates[0], 'DD/MM/YYYY').format('YYYY-MM-DD'));
            url.searchParams.append('date_end', moment(dates[1], 'DD/MM/YYYY').format('YYYY-MM-DD'));
        }
        
        if (channelValue) {
            url.searchParams.append('channel', channelValue);
        }

        // Destroy existing charts
        destroySpentVsGmvCharts();
        
        fetch(url)
            .then(response => response.json())
            .then(result => {
                if (result.status === 'success') {
                    createSpentVsGmvChart(result.data);
                    createRoasTrendChart(result.data);
                    updateSummaryStats(result.data);
                }
            })
            .catch(error => {
                console.error('Error fetching chart data:', error);
            });
    }

    // Destroy charts
    function destroySpentVsGmvCharts() {
        if (spentVsGmvChart) {
            spentVsGmvChart.destroy();
            spentVsGmvChart = null;
        }
        if (roasTrendChart) {
            roasTrendChart.destroy();
            roasTrendChart = null;
        }
    }

    // Create Spent vs GMV Chart
    function createSpentVsGmvChart(data) {
        const ctx = document.getElementById('spentVsGmvChart').getContext('2d');
        
        const datasets = [];
        const colors = {
            gmv: '#007bff',
            spent: '#dc3545'
        };

        // Group data by channel
        const channelData = {};
        data.chart_data.forEach(item => {
            if (!channelData[item.channel_name]) {
                channelData[item.channel_name] = {
                    dates: [],
                    gmv: [],
                    spent: []
                };
            }
            channelData[item.channel_name].dates.push(item.date);
            channelData[item.channel_name].gmv.push(item.sales_amount);
            channelData[item.channel_name].spent.push(item.spend_amount);
        });

        // Create datasets for each channel
        Object.keys(channelData).forEach((channel, index) => {
            const colorIndex = index % 5;
            const baseColors = ['#007bff', '#28a745', '#dc3545', '#ffc107', '#17a2b8'];
            const channelColor = baseColors[colorIndex];
            
            // GMV dataset
            datasets.push({
                label: `${channel} - GMV`,
                data: data.chart_data.filter(item => item.channel_name === channel).map(item => ({
                    x: item.date,
                    y: item.sales_amount
                })),
                borderColor: channelColor,
                backgroundColor: channelColor + '20',
                fill: false,
                tension: 0.1,
                yAxisID: 'y'
            });
            
            // Spent dataset
            datasets.push({
                label: `${channel} - Spent`,
                data: data.chart_data.filter(item => item.channel_name === channel).map(item => ({
                    x: item.date,
                    y: item.spend_amount
                })),
                borderColor: channelColor,
                backgroundColor: channelColor + '40',
                borderDash: [5, 5],
                fill: false,
                tension: 0.1,
                yAxisID: 'y1'
            });
        });

        spentVsGmvChart = new Chart(ctx, {
            type: 'line',
            data: {
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    x: {
                        type: 'time',
                        time: {
                            parser: 'YYYY-MM-DD',
                            displayFormats: {
                                day: 'MMM DD'
                            }
                        },
                        title: {
                            display: true,
                            text: 'Date'
                        }
                    },
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'GMV (Rp)'
                        },
                        ticks: {
                            callback: function(value) {
                                return formatCurrency(value);
                            }
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Ad Spent (Rp)'
                        },
                        ticks: {
                            callback: function(value) {
                                return formatCurrency(value);
                            }
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + formatCurrency(context.raw.y);
                            }
                        }
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    // Create ROAS Trend Chart
    function createRoasTrendChart(data) {
        const ctx = document.getElementById('roasTrendChart').getContext('2d');
        
        const datasets = [];
        const colors = ['#28a745', '#007bff', '#dc3545', '#ffc107', '#17a2b8'];

        // Group data by channel
        const channelData = {};
        data.chart_data.forEach(item => {
            if (!channelData[item.channel_name]) {
                channelData[item.channel_name] = [];
            }
            channelData[item.channel_name].push({
                x: item.date,
                y: item.roas || 0
            });
        });

        // Create datasets for each channel
        Object.keys(channelData).forEach((channel, index) => {
            const color = colors[index % colors.length];
            
            datasets.push({
                label: `${channel} - ROAS`,
                data: channelData[channel],
                borderColor: color,
                backgroundColor: color + '20',
                fill: false,
                tension: 0.1
            });
        });

        roasTrendChart = new Chart(ctx, {
            type: 'line',
            data: {
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        type: 'time',
                        time: {
                            parser: 'YYYY-MM-DD',
                            displayFormats: {
                                day: 'MMM DD'
                            }
                        },
                        title: {
                            display: true,
                            text: 'Date'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'ROAS'
                        },
                        ticks: {
                            callback: function(value) {
                                return value.toFixed(2);
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.raw.y.toFixed(2);
                            }
                        }
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    // Update summary statistics
    function updateSummaryStats(data) {
        const stats = data.summary_stats;
        
        $('#totalGmvStat').text(formatCurrency(stats.total_gmv));
        $('#totalSpentStat').text(formatCurrency(stats.total_spent));
        $('#avgRoasStat').text(stats.avg_roas.toFixed(2));
        $('#spentPercentageStat').text(stats.spent_percentage.toFixed(2) + '%');
    }

    // Helper function to format currency
    function formatCurrency(value) {
        if (value === null || value === undefined) return 'Rp 0';
        
        if (value >= 1000000) {
            return 'Rp ' + (value / 1000000).toFixed(1) + 'M';
        } else if (value >= 1000) {
            return 'Rp ' + (value / 1000).toFixed(1) + 'K';
        }
        return 'Rp ' + Number(value).toLocaleString('id-ID');
    }

    // Initialize when Spent vs GMV tab is shown
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        if (e.target.id === 'spent-vs-gmv-tab') {
            setTimeout(function() {
                spentVsGmvTable.columns.adjust();
                initSpentVsGmvCharts();
            }, 150);
        }
    });

    // Initialize if Spent vs GMV tab is active on page load
    if ($('#spent-vs-gmv-tab').hasClass('active')) {
        initSpentVsGmvCharts();
    }
});
</script>