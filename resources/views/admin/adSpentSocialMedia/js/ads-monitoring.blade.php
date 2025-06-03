<script>
/**
 * Ads Monitoring tab functionality
 */
$(document).ready(function() {
    // Initialize variables
    let adsMonitoringFilterDate = initDateRangePicker('adsMonitoringFilterDates');
    let adsMonitoringChannelFilter = $('#adsMonitoringChannelFilter');
    let gmvChart = null;
    let roasChart = null;
    let spentChart = null;
    let cpaChart = null;

    // Initialize Ads Monitoring DataTable
    let adsMonitoringTable = $('#adsMonitoringTable').DataTable({
        responsive: true,
        processing: true,
        serverSide: true,
        pageLength: 25,
        ajax: {
            url: "{{ route('adSpentSocialMedia.get_ads_monitoring') }}",
            data: function (d) {
                if (adsMonitoringFilterDate.val()) {
                    let dates = adsMonitoringFilterDate.val().split(' - ');
                    d.date_start = moment(dates[0], 'DD/MM/YYYY').format('YYYY-MM-DD');
                    d.date_end = moment(dates[1], 'DD/MM/YYYY').format('YYYY-MM-DD');
                }
                if (adsMonitoringChannelFilter.val()) {
                    d.channel = adsMonitoringChannelFilter.val();
                }
            }
        },
        columns: [
            {data: 'date', name: 'date'},
            {data: 'channel', name: 'channel'},
            {data: 'gmv_target', name: 'gmv_target'},
            {data: 'gmv_actual', name: 'gmv_actual'},
            {data: 'gmv_variance', name: 'gmv_variance', orderable: false},
            {data: 'spent_target', name: 'spent_target'},
            {data: 'spent_actual', name: 'spent_actual'},
            {data: 'spent_variance', name: 'spent_variance', orderable: false},
            {data: 'roas_target', name: 'roas_target'},
            {data: 'roas_actual', name: 'roas_actual'},
            {data: 'roas_variance', name: 'roas_variance', orderable: false},
            {data: 'cpa_target', name: 'cpa_target'},
            {data: 'cpa_actual', name: 'cpa_actual'},
            {data: 'cpa_variance', name: 'cpa_variance', orderable: false},
            {data: 'aov_to_cpa_target', name: 'aov_to_cpa_target'},
            {data: 'aov_to_cpa_actual', name: 'aov_to_cpa_actual'},
            {data: 'performance_status', name: 'performance_status', orderable: false}
        ],
        columnDefs: [
            { "targets": [2, 3, 5, 6, 8, 9, 11, 12, 14, 15], "className": "text-right" },
            { "targets": [1, 4, 7, 10, 13, 16], "className": "text-center" }
        ],
        order: [[0, 'desc'], [1, 'asc']],
        fixedHeader: true,
        scrollCollapse: true,
        deferRender: true,
        scroller: true
    });

    // Button click handlers
    $('#adsMonitoringResetFilterBtn').click(function() {
        adsMonitoringFilterDate.val('');
        adsMonitoringChannelFilter.val('');
        adsMonitoringTable.draw();
        initAdsMonitoringCharts();
    });

    // Export button handler
    $('#btnExportAdsMonitoringReport').click(function() {
        let params = {};
        
        if (adsMonitoringFilterDate.val()) {
            let dates = adsMonitoringFilterDate.val().split(' - ');
            params.date_start = moment(dates[0], 'DD/MM/YYYY').format('YYYY-MM-DD');
            params.date_end = moment(dates[1], 'DD/MM/YYYY').format('YYYY-MM-DD');
        }
        
        if (adsMonitoringChannelFilter.val()) {
            params.channel = adsMonitoringChannelFilter.val();
        }
        
        // Show loading state
        showLoadingSwal('Generating report...');
        
        // Create URL with params
        const url = new URL("{{ route('adSpentSocialMedia.export_ads_monitoring') }}", window.location.origin);
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
    $('#btnRefreshAdsMonitoringData').click(function() {
        Swal.fire({
            title: 'Refresh Ads Monitoring Data',
            text: 'This will update actual performance data from TikTok, Shopee, and Meta ads for the current month. Continue?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, refresh data',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                refreshAdsMonitoringData();
            }
        });
    });

    // Function to refresh ads monitoring data
    function refreshAdsMonitoringData() {
        // Show loading with progress
        Swal.fire({
            title: 'Refreshing Data...',
            html: 'Updating actual performance data from all ad platforms...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });

        // Call refresh all endpoint
        fetch("{{ route('adSpentSocialMedia.refresh_all_ads_monitoring') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        })
        .then(response => response.json())
        .then(result => {
            if (result.status === 'success') {
                Swal.fire({
                    title: 'Success!',
                    html: `
                        <div class="text-left">
                            <p><strong>All ads monitoring data refreshed successfully!</strong></p>
                            <ul>
                                <li>TikTok: ${result.results.tiktok.message}</li>
                                <li>Shopee: ${result.results.shopee.message}</li>
                                <li>Meta: ${result.results.meta.message}</li>
                            </ul>
                            <p><small>Period: ${result.period}</small></p>
                        </div>
                    `,
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    // Refresh the table and charts
                    adsMonitoringTable.draw();
                    initAdsMonitoringCharts();
                });
            } else if (result.status === 'partial_success') {
                let errorMessages = [];
                Object.keys(result.results).forEach(platform => {
                    if (result.results[platform].status === 'error') {
                        errorMessages.push(`${platform.charAt(0).toUpperCase() + platform.slice(1)}: ${result.results[platform].message}`);
                    }
                });
                
                Swal.fire({
                    title: 'Partial Success',
                    html: `
                        <div class="text-left">
                            <p>Some platforms were updated successfully, but there were errors:</p>
                            <ul>
                                ${errorMessages.map(msg => `<li class="text-danger">${msg}</li>`).join('')}
                            </ul>
                        </div>
                    `,
                    icon: 'warning',
                    confirmButtonText: 'OK'
                }).then(() => {
                    adsMonitoringTable.draw();
                    initAdsMonitoringCharts();
                });
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: result.message || 'Failed to refresh ads monitoring data',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        })
        .catch(error => {
            console.error('Error refreshing ads monitoring data:', error);
            Swal.fire({
                title: 'Error!',
                text: 'Network error occurred while refreshing data',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        });
    }

    // Filter change handlers
    adsMonitoringChannelFilter.change(function() {
        adsMonitoringTable.draw();
        initAdsMonitoringCharts();
    });

    adsMonitoringFilterDate.change(function() {
        adsMonitoringTable.draw();
        initAdsMonitoringCharts();
    });

    // Chart initialization function
    function initAdsMonitoringCharts() {
        const filterValue = adsMonitoringFilterDate.val();
        const channelValue = adsMonitoringChannelFilter.val();
        
        const url = new URL("{{ route('adSpentSocialMedia.ads_monitoring_chart_data') }}", window.location.origin);
        
        if (filterValue) {
            let dates = filterValue.split(' - ');
            url.searchParams.append('date_start', moment(dates[0], 'DD/MM/YYYY').format('YYYY-MM-DD'));
            url.searchParams.append('date_end', moment(dates[1], 'DD/MM/YYYY').format('YYYY-MM-DD'));
        }
        
        if (channelValue) {
            url.searchParams.append('channel', channelValue);
        }

        // Destroy existing charts
        destroyCharts();
        
        fetch(url)
            .then(response => response.json())
            .then(result => {
                if (result.status === 'success') {
                    createGMVChart(result.data);
                    createROASChart(result.data);
                    createSpentChart(result.data);
                    createCPAChart(result.data);
                }
            })
            .catch(error => {
                console.error('Error fetching chart data:', error);
            });
    }

    // Destroy all charts
    function destroyCharts() {
        if (gmvChart) {
            gmvChart.destroy();
            gmvChart = null;
        }
        if (roasChart) {
            roasChart.destroy();
            roasChart = null;
        }
        if (spentChart) {
            spentChart.destroy();
            spentChart = null;
        }
        if (cpaChart) {
            cpaChart.destroy();
            cpaChart = null;
        }
    }

    // Create GMV Performance Chart
    function createGMVChart(data) {
        const ctx = document.getElementById('gmvPerformanceChart').getContext('2d');
        
        const datasets = [];
        const colors = ['#007bff', '#28a745', '#dc3545', '#ffc107', '#17a2b8'];
        let colorIndex = 0;

        // Create datasets for each channel
        data.channels.forEach(channel => {
            const color = colors[colorIndex % colors.length];
            
            // Target dataset
            datasets.push({
                label: `${channel} - Target`,
                data: data.datasets.gmv_target[channel] || [],
                borderColor: color,
                backgroundColor: color + '20',
                borderDash: [5, 5],
                fill: false,
                tension: 0.1
            });
            
            // Actual dataset
            datasets.push({
                label: `${channel} - Actual`,
                data: data.datasets.gmv_actual[channel] || [],
                borderColor: color,
                backgroundColor: color + '40',
                fill: false,
                tension: 0.1
            });
            
            colorIndex++;
        });

        gmvChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.dates,
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Date'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'GMV (Rp)'
                        },
                        ticks: {
                            callback: function(value) {
                                return formatCurrency(value);
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + formatCurrency(context.raw);
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

    // Create ROAS Performance Chart
    function createROASChart(data) {
        const ctx = document.getElementById('roasPerformanceChart').getContext('2d');
        
        const datasets = [];
        const colors = ['#007bff', '#28a745', '#dc3545', '#ffc107', '#17a2b8'];
        let colorIndex = 0;

        data.channels.forEach(channel => {
            const color = colors[colorIndex % colors.length];
            
            datasets.push({
                label: `${channel} - Target`,
                data: data.datasets.roas_target[channel] || [],
                borderColor: color,
                backgroundColor: color + '20',
                borderDash: [5, 5],
                fill: false,
                tension: 0.1
            });
            
            datasets.push({
                label: `${channel} - Actual`,
                data: data.datasets.roas_actual[channel] || [],
                borderColor: color,
                backgroundColor: color + '40',
                fill: false,
                tension: 0.1
            });
            
            colorIndex++;
        });

        roasChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.dates,
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
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
                                return context.dataset.label + ': ' + context.raw.toFixed(2);
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

    // Create Spent Performance Chart
    function createSpentChart(data) {
        const ctx = document.getElementById('spentPerformanceChart').getContext('2d');
        
        const datasets = [];
        const colors = ['#007bff', '#28a745', '#dc3545', '#ffc107', '#17a2b8'];
        let colorIndex = 0;

        data.channels.forEach(channel => {
            const color = colors[colorIndex % colors.length];
            
            datasets.push({
                label: `${channel} - Target`,
                data: data.datasets.spent_target[channel] || [],
                borderColor: color,
                backgroundColor: color + '20',
                borderDash: [5, 5],
                fill: false,
                tension: 0.1
            });
            
            datasets.push({
                label: `${channel} - Actual`,
                data: data.datasets.spent_actual[channel] || [],
                borderColor: color,
                backgroundColor: color + '40',
                fill: false,
                tension: 0.1
            });
            
            colorIndex++;
        });

        spentChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.dates,
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Date'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Ad Spend (Rp)'
                        },
                        ticks: {
                            callback: function(value) {
                                return formatCurrency(value);
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + formatCurrency(context.raw);
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

    // Create CPA Performance Chart
    function createCPAChart(data) {
        const ctx = document.getElementById('cpaPerformanceChart').getContext('2d');
        
        const datasets = [];
        const colors = ['#007bff', '#28a745', '#dc3545', '#ffc107', '#17a2b8'];
        let colorIndex = 0;

        data.channels.forEach(channel => {
            const color = colors[colorIndex % colors.length];
            
            datasets.push({
                label: `${channel} - Target`,
                data: data.datasets.cpa_target[channel] || [],
                borderColor: color,
                backgroundColor: color + '20',
                borderDash: [5, 5],
                fill: false,
                tension: 0.1
            });
            
            datasets.push({
                label: `${channel} - Actual`,
                data: data.datasets.cpa_actual[channel] || [],
                borderColor: color,
                backgroundColor: color + '40',
                fill: false,
                tension: 0.1
            });
            
            colorIndex++;
        });

        cpaChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.dates,
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Date'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'CPA (Rp)'
                        },
                        ticks: {
                            callback: function(value) {
                                return formatCurrency(value);
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + formatCurrency(context.raw);
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

    // Helper function to format currency
    function formatCurrency(value) {
        if (value === null || value === undefined) return 'N/A';
        
        if (value >= 1000000) {
            return 'Rp ' + (value / 1000000).toFixed(1) + 'M';
        } else if (value >= 1000) {
            return 'Rp ' + (value / 1000).toFixed(1) + 'K';
        }
        return 'Rp ' + Number(value).toLocaleString('id-ID');
    }

    // Initialize when Ads Monitoring tab is shown
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        if (e.target.id === 'ads-monitoring-tab') {
            setTimeout(function() {
                adsMonitoringTable.columns.adjust();
                initAdsMonitoringCharts();
            }, 150);
        }
    });

    // Initialize if Ads Monitoring tab is active on page load
    if ($('#ads-monitoring-tab').hasClass('active')) {
        initAdsMonitoringCharts();
    }
});
</script>