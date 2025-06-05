<script>
/**
 * Spent vs GMV tab functionality - FIXED with proper headers
 */
$(document).ready(function() {
    // Initialize variables
    let spentVsGmvFilterDate = initDateRangePicker('spentVsGmvFilterDates');
    let spentVsGmvChannelFilter = $('#spentVsGmvChannelFilter');
    let spentVsGmvChart = null;
    let roasTrendChart = null;
    let spentVsGmvTable = null;

    // Function to initialize DataTable
    function initSpentVsGmvTable() {
        // Check if DataTable already exists and destroy it
        if ($.fn.DataTable.isDataTable('#spentVsGmvTable')) {
            $('#spentVsGmvTable').DataTable().destroy();
        }

        // Clear the table but keep the header structure
        $('#spentVsGmvTable tbody').empty();

        // Initialize Spent vs GMV DataTable
        spentVsGmvTable = $('#spentVsGmvTable').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            pageLength: 25,
            destroy: true,
            ajax: {
                url: "{{ route('adSpentSocialMedia.get_spent_vs_gmv') }}",
                data: function (d) {
                    if (spentVsGmvFilterDate && spentVsGmvFilterDate.val()) {
                        let dates = spentVsGmvFilterDate.val().split(' - ');
                        d.date_start = moment(dates[0], 'DD/MM/YYYY').format('YYYY-MM-DD');
                        d.date_end = moment(dates[1], 'DD/MM/YYYY').format('YYYY-MM-DD');
                    }
                    if (spentVsGmvChannelFilter && spentVsGmvChannelFilter.val()) {
                        d.channel = spentVsGmvChannelFilter.val();
                    }
                },
                error: function(xhr, error, thrown) {
                    console.error('DataTable AJAX error:', error, thrown);
                }
            },
            columns: [
                {
                    data: 'date', 
                    name: 'date',
                    title: 'Date'
                },
                {
                    data: 'channel_name', 
                    name: 'channel_name',
                    title: 'Channel'
                },
                {
                    data: 'sales_amount', 
                    name: 'sales_amount',
                    title: 'GMV',
                    render: function(data, type, row) {
                        if (type === 'display' || type === 'type') {
                            if (data === null || data === undefined) return 'Rp 0';
                            
                            // If it's already a formatted string with commas, just add Rp prefix
                            if (typeof data === 'string' && data.includes(',')) {
                                return data.startsWith('Rp') ? data : 'Rp ' + data;
                            }
                            
                            // If it's a number, format it
                            const numValue = parseFloat(data);
                            if (isNaN(numValue)) return 'Rp 0';
                            return 'Rp ' + numValue.toLocaleString('id-ID');
                        }
                        return data;
                    }
                },
                {
                    data: 'spend_amount', 
                    name: 'spend_amount',
                    title: 'Ad Spent',
                    render: function(data, type, row) {
                        if (type === 'display' || type === 'type') {
                            if (data === null || data === undefined) return 'Rp 0';
                            
                            // If it's already a formatted string with commas, just add Rp prefix
                            if (typeof data === 'string' && data.includes(',')) {
                                return data.startsWith('Rp') ? data : 'Rp ' + data;
                            }
                            
                            // If it's a number, format it
                            const numValue = parseFloat(data);
                            if (isNaN(numValue)) return 'Rp 0';
                            return 'Rp ' + numValue.toLocaleString('id-ID');
                        }
                        return data;
                    }
                },
                {
                    data: 'roas', 
                    name: 'roas',
                    title: 'ROAS',
                    render: function(data, type, row) {
                        if (type === 'display' || type === 'type') {
                            const numValue = parseFloat(data);
                            if (isNaN(numValue)) return '0.00';
                            return numValue.toFixed(2);
                        }
                        return data;
                    }
                },
                {
                    data: 'spent_percentage', 
                    name: 'spent_percentage',
                    title: 'Spent/GMV %',
                    render: function(data, type, row) {
                        if (type === 'display' || type === 'type') {
                            const numValue = parseFloat(data);
                            if (isNaN(numValue)) return '0.00%';
                            return numValue.toFixed(2) + '%';
                        }
                        return data;
                    }
                }
            ],
            columnDefs: [
                { 
                    "targets": [2, 3, 4, 5], 
                    "className": "text-right" 
                },
                { 
                    "targets": [1], 
                    "className": "text-center" 
                }
            ],
            order: [[0, 'desc']],
            language: {
                processing: "Loading data...",
                emptyTable: "No data available",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "Showing 0 to 0 of 0 entries",
                infoFiltered: "(filtered from _MAX_ total entries)",
                lengthMenu: "Show _MENU_ entries",
                loadingRecords: "Loading...",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                },
                search: "Search:",
                zeroRecords: "No matching records found"
            },
            dom: 'Bfrtip',
            buttons: [],
            fixedHeader: false, // Disable fixed header temporarily
            scrollCollapse: true,
            deferRender: true
        });

        // Handle DataTable events
        spentVsGmvTable.on('xhr', function() {
            console.log('DataTable data loaded successfully');
        });

        spentVsGmvTable.on('error.dt', function(e, settings, techNote, message) {
            console.error('DataTable error:', message);
        });
    }

    // Initialize DataTable when document is ready
    // Use a small delay to ensure all elements are properly loaded
    setTimeout(function() {
        if (typeof initDateRangePicker === 'function') {
            spentVsGmvFilterDate = initDateRangePicker('spentVsGmvFilterDates');
        }
        initSpentVsGmvTable();
    }, 100);

    // Button click handlers
    $(document).on('click', '#spentVsGmvResetFilterBtn', function() {
        if (spentVsGmvFilterDate && typeof spentVsGmvFilterDate.val === 'function') {
            spentVsGmvFilterDate.val('');
        }
        if (spentVsGmvChannelFilter) {
            spentVsGmvChannelFilter.val('');
        }
        if (spentVsGmvTable) {
            spentVsGmvTable.draw();
        }
        initSpentVsGmvCharts();
    });

    // Export button handler
    $(document).on('click', '#btnExportSpentVsGmvReport', function() {
        let params = {};
        
        if (spentVsGmvFilterDate && spentVsGmvFilterDate.val()) {
            let dates = spentVsGmvFilterDate.val().split(' - ');
            params.date_start = moment(dates[0], 'DD/MM/YYYY').format('YYYY-MM-DD');
            params.date_end = moment(dates[1], 'DD/MM/YYYY').format('YYYY-MM-DD');
        }
        
        if (spentVsGmvChannelFilter && spentVsGmvChannelFilter.val()) {
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
        document.body.removeChild(form);
        
        // Close loading after a short delay
        setTimeout(function() {
            if (typeof Swal !== 'undefined') {
                Swal.close();
            }
        }, 2000);
    });

    // Refresh Data button handler
    $(document).on('click', '#btnRefreshSpentVsGmvData', function() {
        if (typeof Swal !== 'undefined') {
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
                    if (spentVsGmvTable) {
                        spentVsGmvTable.draw();
                    }
                    initSpentVsGmvCharts();
                    Swal.fire('Success!', 'Data has been refreshed.', 'success');
                }
            });
        } else {
            if (confirm('Refresh Spent vs GMV Data. Continue?')) {
                if (spentVsGmvTable) {
                    spentVsGmvTable.draw();
                }
                initSpentVsGmvCharts();
            }
        }
    });

    // Filter change handlers
    $(document).on('change', '#spentVsGmvChannelFilter', function() {
        if (spentVsGmvTable) {
            spentVsGmvTable.draw();
        }
        initSpentVsGmvCharts();
    });

    $(document).on('change', '#spentVsGmvFilterDates', function() {
        if (spentVsGmvTable) {
            spentVsGmvTable.draw();
        }
        initSpentVsGmvCharts();
    });

    // Chart initialization function
    function initSpentVsGmvCharts() {
        const filterValue = spentVsGmvFilterDate ? spentVsGmvFilterDate.val() : '';
        const channelValue = spentVsGmvChannelFilter ? spentVsGmvChannelFilter.val() : '';
        
        const url = new URL("{{ route('adSpentSocialMedia.spent_vs_gmv_chart_data') }}", window.location.origin);
        
        if (filterValue) {
            let dates = filterValue.split(' - ');
            if (dates.length === 2) {
                url.searchParams.append('date_start', moment(dates[0], 'DD/MM/YYYY').format('YYYY-MM-DD'));
                url.searchParams.append('date_end', moment(dates[1], 'DD/MM/YYYY').format('YYYY-MM-DD'));
            }
        }
        
        if (channelValue) {
            url.searchParams.append('channel', channelValue);
        }

        // Destroy existing charts
        destroySpentVsGmvCharts();
        
        fetch(url)
            .then(response => response.json())
            .then(result => {
                console.log('Chart data received:', result);
                if (result.status === 'success') {
                    createSpentVsGmvChart(result.data);
                    createRoasTrendChart(result.data);
                    updateSummaryStats(result.data);
                } else {
                    console.error('Chart data error:', result.message || 'Unknown error');
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
        const ctx = document.getElementById('spentVsGmvChart');
        if (!ctx) {
            console.error('Chart canvas element not found: spentVsGmvChart');
            return;
        }

        const context = ctx.getContext('2d');
        
        if (!data.chart_data || data.chart_data.length === 0) {
            console.log('No chart data available');
            return;
        }

        // Prepare data for dual-axis chart
        const labels = data.chart_data.map(item => item.date);
        const gmvData = data.chart_data.map(item => item.sales_amount);
        const spentData = data.chart_data.map(item => item.spend_amount);

        const datasets = [
            {
                label: 'GMV',
                data: gmvData,
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                fill: false,
                tension: 0.1,
                yAxisID: 'y'
            },
            {
                label: 'Ad Spent',
                data: spentData,
                borderColor: '#dc3545',
                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                borderDash: [5, 5],
                fill: false,
                tension: 0.1,
                yAxisID: 'y1'
            }
        ];

        spentVsGmvChart = new Chart(context, {
            type: 'line',
            data: {
                labels: labels,
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

    // Create ROAS Trend Chart
    function createRoasTrendChart(data) {
        const ctx = document.getElementById('roasTrendChart');
        if (!ctx) {
            console.error('Chart canvas element not found: roasTrendChart');
            return;
        }

        const context = ctx.getContext('2d');
        
        if (!data.chart_data || data.chart_data.length === 0) {
            console.log('No ROAS chart data available');
            return;
        }

        const labels = data.chart_data.map(item => item.date);
        const roasData = data.chart_data.map(item => item.roas);

        const dataset = {
            label: 'ROAS',
            data: roasData,
            borderColor: '#28a745',
            backgroundColor: 'rgba(40, 167, 69, 0.1)',
            fill: true,
            tension: 0.1
        };

        roasTrendChart = new Chart(context, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [dataset]
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

    // Update summary statistics
    function updateSummaryStats(data) {
        if (!data.summary_stats) return;
        
        const stats = data.summary_stats;
        
        const totalGmvElement = document.getElementById('totalGmvStat');
        const totalSpentElement = document.getElementById('totalSpentStat');
        const avgRoasElement = document.getElementById('avgRoasStat');
        const spentPercentageElement = document.getElementById('spentPercentageStat');

        if (totalGmvElement) totalGmvElement.textContent = formatCurrency(stats.total_gmv);
        if (totalSpentElement) totalSpentElement.textContent = formatCurrency(stats.total_spent);
        if (avgRoasElement) avgRoasElement.textContent = stats.avg_roas.toFixed(2);
        if (spentPercentageElement) spentPercentageElement.textContent = stats.spent_percentage.toFixed(2) + '%';
    }

    // Helper function to format currency
    function formatCurrency(value) {
        if (value === null || value === undefined || isNaN(value)) return 'Rp 0';
        
        const numValue = Number(value);
        
        if (numValue >= 1000000) {
            return 'Rp ' + (numValue / 1000000).toFixed(1) + 'M';
        } else if (numValue >= 1000) {
            return 'Rp ' + (numValue / 1000).toFixed(1) + 'K';
        }
        return 'Rp ' + numValue.toLocaleString('id-ID');
    }

    // Helper function for loading state
    function showLoadingSwal(message) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Loading...',
                text: message,
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });
        }
    }

    // Initialize when Spent vs GMV tab is shown
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        if (e.target.id === 'spent-vs-gmv-tab') {
            setTimeout(function() {
                // Only adjust columns if table exists
                if (spentVsGmvTable) {
                    spentVsGmvTable.columns.adjust();
                }
                initSpentVsGmvCharts();
            }, 150);
        }
    });

    // Initialize charts when the tab becomes visible
    setTimeout(function() {
        initSpentVsGmvCharts();
    }, 1000);
});
</script>