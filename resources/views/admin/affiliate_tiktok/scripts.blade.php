<script>
$(document).ready(function() {
    // Initialize variables
    let filterDate = initDateRangePicker('filterDates');
    let filterCreator = $('#creatorFilter');
    let modalFilterDate = initDateRangePicker('modalFilterDates');
    let funnelChart = null;
    let gmvChart = null;
    let chartsInitialized = false; // Track chart initialization

    // Initialize Affiliate TikTok DataTable
    let affiliateTiktokTable = $('#affiliateTiktokTable').DataTable({
        responsive: true,
        processing: true,
        serverSide: true,
        pageLength: 25,
        ajax: {
            url: "{{ route('affiliate_tiktok.get_data') }}",
            data: function (d) {
                if (filterDate.val()) {
                    let dates = filterDate.val().split(' - ');
                    d.date_start = moment(dates[0], 'DD/MM/YYYY').format('YYYY-MM-DD');
                    d.date_end = moment(dates[1], 'DD/MM/YYYY').format('YYYY-MM-DD');
                }
                if (filterCreator.val()) {
                    d.creator_username = filterCreator.val();
                }
            }
        },
        columns: [
            {data: 'date', name: 'date'},
            {
                data: 'total_creators',
                name: 'total_creators',
                render: function(data) {
                    return Number(data || 0).toLocaleString('id-ID');
                }
            },
            {data: 'total_gmv', name: 'total_gmv'},
            {data: 'total_live_gmv', name: 'total_live_gmv'},
            {
                data: 'total_products_sold',
                name: 'total_products_sold',
                render: function(data) {
                    return Number(data || 0).toLocaleString('id-ID');
                }
            },
            {
                data: 'total_items_sold',
                name: 'total_items_sold',
                render: function(data) {
                    return Number(data || 0).toLocaleString('id-ID');
                }
            },
            {data: 'total_commission', name: 'total_commission'},
            {data: 'avg_order_value', name: 'avg_order_value'},
            {
                data: 'total_orders',
                name: 'total_orders',
                render: function(data) {
                    return Number(data || 0).toLocaleString('id-ID');
                }
            },
            {
                data: 'total_impressions',
                name: 'total_impressions',
                render: function(data) {
                    return Number(data || 0).toLocaleString('id-ID');
                }
            },
            {
                data: 'total_live_streams',
                name: 'total_live_streams',
                render: function(data) {
                    return Number(data || 0).toLocaleString('id-ID');
                }
            },
            {data: 'conversion_rate', name: 'conversion_rate'},
            {data: 'avg_commission_per_creator', name: 'avg_commission_per_creator'},
            {data: 'performance', name: 'performance', searchable: false}
        ],
        columnDefs: [
            { "targets": [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12], "className": "text-right" },
            { "targets": [13], "className": "text-center" }
        ],
        order: [[0, 'desc']],
        fixedHeader: true,
        scrollCollapse: true,
        deferRender: true,
        scroller: true
    });

    // Affiliate details table
    let affiliateDetailsTable = $('#affiliateDetailsTable').DataTable({
        responsive: false,
        scrollX: true,
        processing: true,
        serverSide: true,
        pageLength: 10,
        ajax: {
            url: "{{ route('affiliate_tiktok.get_details_by_date') }}",
            data: function(d) {
                if (modalFilterDate.val()) {
                    let dates = modalFilterDate.val().split(' - ');
                    d.date_start = moment(dates[0], 'DD/MM/YYYY').format('YYYY-MM-DD');
                    d.date_end = moment(dates[1], 'DD/MM/YYYY').format('YYYY-MM-DD');
                } else {
                    d.date = $('#dailyDetailsModal').data('date');
                }
                if (filterCreator.val()) {
                    d.creator_username = filterCreator.val();
                }
            }
        },
        columns: [
            {data: 'creator_username', name: 'creator_username'},
            {data: 'affiliate_gmv', name: 'affiliate_gmv'},
            {data: 'affiliate_live_gmv', name: 'affiliate_live_gmv'},
            {data: 'affiliate_shoppable_video', name: 'affiliate_shoppable_video'},
            {data: 'affiliate_product_card_gmv', name: 'affiliate_product_card_gmv'},
            {data: 'affiliate_products_sold', name: 'affiliate_products_sold'},
            {data: 'items_sold', name: 'items_sold'},
            {data: 'est_commission', name: 'est_commission'},
            {data: 'avg_order_value', name: 'avg_order_value'},
            {data: 'affiliate_orders', name: 'affiliate_orders'},
            {data: 'ctr', name: 'ctr'},
            {data: 'product_impressions', name: 'product_impressions'},
            {data: 'avg_affiliate_customers', name: 'avg_affiliate_customers'},
            {data: 'affiliate_live_streams', name: 'affiliate_live_streams'},
            {data: 'open_collaboration_gmv', name: 'open_collaboration_gmv'},
            {data: 'open_collaboration_est', name: 'open_collaboration_est'},
            {data: 'affiliate_refunded_gmv', name: 'affiliate_refunded_gmv'},
            {data: 'affiliate_items_refunded', name: 'affiliate_items_refunded'},
            {data: 'affiliate_followers', name: 'affiliate_followers'},
            {data: 'conversion_rate', name: 'conversion_rate'},
            {data: 'commission_rate', name: 'commission_rate'},
            {data: 'refund_rate', name: 'refund_rate'},
            {data: 'action', name: 'action', orderable: false, searchable: false}
        ],
        columnDefs: [
            { "targets": [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21], "className": "text-right" },
            { "targets": [0], "className": "text-center" },
            { "targets": [22], "className": "text-center" }
        ],
        order: [[0, 'asc']],
        fixedHeader: true,
        scrollCollapse: true,
        deferRender: true,
        scroller: true
    });

    // Button click handlers
    $('#resetFilterBtn').click(function() {
        filterDate.val('');
        filterCreator.val('');
        affiliateTiktokTable.draw();
        fetchGmvData();
        initFunnelChart();
    });

    $('#modalResetFilterBtn').click(function() {
        modalFilterDate.val('');
        affiliateDetailsTable.draw();
    });

    // File input handler
    handleFileInputChange('affiliateTiktokExcelFile');

    // Form submit handler
    $('#importAffiliateTiktokForm').on('submit', function(e) {
        e.preventDefault();
        
        showLoadingSwal('Processing Excel file...');
        
        let formData = new FormData(this);
        $.ajax({
            url: "{{ route('affiliate_tiktok.import') }}",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.status === 'success') {
                    $('#importAffiliateTiktokModal').modal('hide');
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 3000
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Warning',
                        text: response.message || 'Unknown error occurred',
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'OK'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error("Ajax error:", xhr, status, error);
                
                let errorMessage = 'An error occurred during import';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: errorMessage,
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK'
                });
            }
        });
    });

    // Filter change handlers
    filterCreator.change(function() {
        affiliateTiktokTable.draw();
        if ($('#dailyDetailsModal').is(':visible')) {
            affiliateDetailsTable.draw();
        }
        updateChartData();
        initFunnelChart();
    });

    filterDate.change(function() {
        affiliateTiktokTable.draw();
        updateChartData();
        initFunnelChart();
    });
    
    // Function to update chart data without recreating
    function updateChartData() {
        if (!window.gmvChartChart) {
            fetchGmvData();
            return;
        }
        
        const filterValue = filterDate.val();
        const creatorValue = filterCreator.val();
        
        const url = new URL("{{ route('affiliate_tiktok.line_data') }}", window.location.origin);
        
        if (filterValue) {
            url.searchParams.append('filterDates', filterValue);
        }
        
        if (creatorValue) {
            url.searchParams.append('creator_username', creatorValue);
        }
        
        fetch(url)
            .then(response => response.json())
            .then(result => {
                if (result.status === 'success') {
                    const gmvData = result.gmv;
                    const gmvDates = gmvData.map(data => data.date);
                    const gmv = gmvData.map(data => data.gmv);
                    
                    // Update existing chart data
                    window.gmvChartChart.data.labels = gmvDates;
                    window.gmvChartChart.data.datasets[0].data = gmv;
                    window.gmvChartChart.update();
                }
            })
            .catch(error => {
                console.error('Error updating chart data:', error);
            });
    }

    modalFilterDate.change(function() {
        affiliateDetailsTable.draw();
    });

    // Modal event handlers
    $('#dailyDetailsModal').on('shown.bs.modal', function() {
        if (!modalFilterDate.val()) {
            const clickedDate = $('#dailyDetailsModal').data('date');
            const formattedDate = moment(clickedDate).format('DD/MM/YYYY');
            modalFilterDate.val(formattedDate + ' - ' + formattedDate);
        }
        $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
    });

    $('#dailyDetailsModal').on('hidden.bs.modal', function() {
        modalFilterDate.val('');
    });

    // Click event handler for date details
    $('#affiliateTiktokTable').on('click', '.date-details', function() {
        let date = $(this).data('date');
        let formattedDate = $(this).text();
        
        $('#dailyDetailsModalLabel').text('TikTok Affiliate Details for ' + formattedDate);
        $('#dailyDetailsModal').data('date', date);

        modalFilterDate.val('');
        
        affiliateDetailsTable.draw();
        $('#dailyDetailsModal').modal('show');
    });

    // Delete affiliate handler
    $('#affiliateDetailsTable').on('click', '.delete-affiliate', function() {
        const affiliateId = $(this).data('id');
        
        Swal.fire({
            title: 'Are you sure?',
            text: 'This will delete the selected TikTok affiliate record!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                showLoadingSwal('Deleting...');
                
                $.ajax({
                    url: "{{ route('affiliate_tiktok.delete') }}",
                    type: 'DELETE',
                    data: {
                        id: affiliateId,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            affiliateDetailsTable.draw();
                            affiliateTiktokTable.draw();
                            fetchGmvData();
                            initFunnelChart();
                        });
                    },
                    error: function(xhr) {
                        let errorMsg = xhr.responseJSON ? xhr.responseJSON.message : 'Failed to delete record';
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: errorMsg,
                            confirmButtonColor: '#3085d6'
                        });
                    }
                });
            }
        });
    });

    function fetchGmvData() {
        // Prevent multiple chart creations
        if (chartsInitialized && window.gmvChartChart) {
            console.log('Chart already exists, updating data instead...');
            return;
        }
        
        const filterValue = filterDate.val();
        const creatorValue = filterCreator.val();
        
        const url = new URL("{{ route('affiliate_tiktok.line_data') }}", window.location.origin);
        
        if (filterValue) {
            url.searchParams.append('filterDates', filterValue);
        }
        
        if (creatorValue) {
            url.searchParams.append('creator_username', creatorValue);
        }
        
        fetch(url)
            .then(response => response.json())
            .then(result => {
                if (result.status === 'success') {
                    const gmvData = result.gmv;
                    const gmvDates = gmvData.map(data => data.date);
                    const gmv = gmvData.map(data => data.gmv);
                    
                    createLineChart('gmvChart', 'Total GMV', gmvDates, gmv, 'rgba(255, 99, 132, 1)');
                    chartsInitialized = true;
                }
            })
            .catch(error => {
                console.error('Error fetching GMV data:', error);
            });
    }

    function initFunnelChart() {
        const filterValue = filterDate.val();
        const creatorValue = filterCreator.val();

        const url = new URL("{{ route('affiliate_tiktok.funnel_data') }}", window.location.origin);
        if (filterValue) {
            url.searchParams.append('filterDates', filterValue);
        }
        if (creatorValue) {
            url.searchParams.append('creator_username', creatorValue);
        }

        fetch(url)
            .then(response => response.json())
            .then(result => {
        fetch(url)
            .then(response => response.json())
            .then(result => {
                if (result.status === 'success') {
                    if (!result.has_data) {
                        // Show empty state for funnel chart
                        showEmptyFunnelChart('funnelChart', 'funnelMetrics');
                    } else {
                        createFunnelChart('funnelChart', result.data, 'funnelMetrics', result);
                    }
                } else {
                    showEmptyFunnelChart('funnelChart', 'funnelMetrics');
                }
            })
            .catch(error => {
                console.error('Error fetching funnel data:', error);
            });
    }

    // Initialize on page load
    $(function () {
        console.log('Initializing Affiliate TikTok page...');
        
        affiliateTiktokTable.draw();
        
        // Initialize charts only once
        setTimeout(function() {
            if (!chartsInitialized) {
                fetchGmvData();
                initFunnelChart();
            }
        }, 100);
        
        $('[data-toggle="tooltip"]').tooltip();
    });
});

// Utility Functions
function initDateRangePicker(elementId) {
    const element = $('#' + elementId);
    
    element.daterangepicker({
        autoUpdateInput: false,
        autoApply: true,
        alwaysShowCalendars: true,
        opens: 'right',
        locale: {
            cancelLabel: 'Clear',
            format: 'DD/MM/YYYY'
        },
        ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
    });

    element.on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
        $(this).trigger('change');
    });

    element.on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
        $(this).trigger('change');
    });
    
    return element;
}

function numberFormat(value, decimals = 0) {
    if (value === null || value === undefined) return '-';
    return Number(value).toLocaleString('id-ID', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals
    });
}

function showLoadingSwal(message) {
    Swal.fire({
        title: message,
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
}

function handleFileInputChange(inputId) {
    $('#' + inputId).on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').html(fileName || 'Choose file');
    });
}

function createLineChart(ctxId, label, dates, data, color = 'rgba(54, 162, 235, 1)') {
    // Get canvas and set fixed height in CSS
    const canvas = document.getElementById(ctxId);
    if (!canvas) {
        console.error('Canvas not found:', ctxId);
        return;
    }
    
    // Set fixed height via CSS to prevent growth
    canvas.style.height = '570px';
    canvas.style.maxHeight = '570px';
    
    const ctx = canvas.getContext('2d');
    
    // Destroy existing chart if it exists
    if (window[ctxId + 'Chart'] && typeof window[ctxId + 'Chart'].destroy === 'function') {
        window[ctxId + 'Chart'].destroy();
        window[ctxId + 'Chart'] = null;
    }
    
    // Create new chart with size restrictions
    window[ctxId + 'Chart'] = new Chart(ctx, {
        type: 'line',
        data: {
            labels: dates,
            datasets: [{
                label: label,
                data: data,
                backgroundColor: color.replace('1)', '0.5)'),
                borderColor: color,
                borderWidth: 2,
                tension: 0.1,
                fill: false,
                pointBackgroundColor: color,
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    enabled: true,
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += 'Rp ' + context.parsed.y.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value, index, values) {
                            return 'Rp ' + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                        }
                    }
                }
            }
        }
    });
    
    return window[ctxId + 'Chart'];
}

function showEmptyLineChart(ctxId, label) {
    const canvas = document.getElementById(ctxId);
    if (!canvas) {
        console.error('Canvas element not found:', ctxId);
        return;
    }
    
    const ctx = canvas.getContext('2d');
    if (!ctx) {
        console.error('Unable to get 2D context for canvas:', ctxId);
        return;
    }
    
    if (window[ctxId + 'Chart'] && typeof window[ctxId + 'Chart'].destroy === 'function') {
        window[ctxId + 'Chart'].destroy();
    }
    
    // Clear the canvas and show empty message
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    
    // Set canvas size properly
    const rect = canvas.getBoundingClientRect();
    canvas.width = rect.width;
    canvas.height = rect.height;
    
    ctx.fillStyle = '#6c757d';
    ctx.font = '16px Arial';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText('No data available', canvas.width / 2, canvas.height / 2 - 10);
    ctx.font = '12px Arial';
    ctx.fillStyle = '#adb5bd';
    ctx.fillText('Import some Excel data to see the chart', canvas.width / 2, canvas.height / 2 + 15);
}

function createFunnelChart(elementId, data, metricsElementId, result) {
    if (window[elementId + 'Chart']) {
        window[elementId + 'Chart'].destroy();
    }
    
    // Handle empty data
    if (!data || data.length === 0 || data.every(item => item.value === 0)) {
        showEmptyFunnelChart(elementId, metricsElementId);
        return;
    }
    
    const options = {
        chart: {
            type: 'bar',
            height: 250,
            toolbar: {
                show: false
            }
        },
        plotOptions: {
            bar: {
                borderRadius: 4,
                horizontal: true,
                distributed: true,
                dataLabels: {
                    position: 'bottom'
                },
            }
        },
        colors: ['#FF6B9D', '#FF8A8A', '#FFB6C1', '#FFC0CB', '#FF69B4'],
        dataLabels: {
            enabled: true,
            formatter: function(val) {
                return val.toLocaleString();
            },
            style: {
                fontSize: '12px',
                colors: ['#fff']
            }
        },
        xaxis: {
            categories: data.map(item => item.name),
            labels: {
                show: true,
                style: {
                    fontSize: '12px'
                }
            }
        },
        yaxis: {
            labels: {
                show: true,
                style: {
                    fontSize: '12px'
                }
            }
        },
        grid: {
            yaxis: {
                lines: {
                    show: false
                }
            }
        },
        tooltip: {
            y: {
                formatter: function(val) {
                    return val.toLocaleString();
                }
            }
        },
        legend: {
            show: false
        }
    };

    const series = [{
        name: 'Total',
        data: data.map(item => item.value)
    }];

    window[elementId + 'Chart'] = new ApexCharts(document.querySelector("#" + elementId), {
        ...options,
        series: series
    });
    window[elementId + 'Chart'].render();

    if (metricsElementId) {
        const metricsHtml = data.map((item, index) => `
            <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded">
                <span class="font-weight-bold">${item.name}</span>
                <span class="text-primary font-weight-bold">
                    ${item.value.toLocaleString()}
                    ${index > 0 && data[0].value > 0 ? `
                        <span class="text-muted ml-2 small">
                            (${((item.value / data[0].value) * 100).toFixed(2)}%)
                        </span>
                    ` : ''}
                </span>
            </div>
        `).join('');
        
        let additionalInsightsHtml = '';
        if (data.length > 0) {
            const totalImpressions = data[0].value;
            const totalOrders = data[2].value; // Orders are at index 2
            const conversionRate = totalImpressions > 0 ? ((totalOrders / totalImpressions) * 100).toFixed(4) : 0;
            
            additionalInsightsHtml = `
                <div class="mt-3 pt-3 border-top">
                    <h6 class="font-weight-bold text-center">Key Metrics</h6>
                    <div class="row">
                        <div class="col-12">
                            <div class="card bg-danger text-white text-center">
                                <div class="card-body p-2">
                                    <div class="text-sm font-weight-bold">Impression-to-Order Rate</div>
                                    <div class="h5 mb-0 font-weight-bold">${conversionRate}%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        document.querySelector('#' + metricsElementId).innerHTML = metricsHtml + additionalInsightsHtml;
    }
    
    return window[elementId + 'Chart'];
}

function showEmptyFunnelChart(elementId, metricsElementId) {
    if (window[elementId + 'Chart']) {
        window[elementId + 'Chart'].destroy();
    }
    
    // Show empty state for funnel chart
    document.querySelector('#' + elementId).innerHTML = `
        <div class="d-flex flex-column align-items-center justify-content-center" style="height: 350px;">
            <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No data available</h5>
            <p class="text-muted">Import some Excel data to see the funnel analysis</p>
        </div>
    `;
    
    if (metricsElementId) {
        document.querySelector('#' + metricsElementId).innerHTML = `
            <div class="text-center text-muted">
                <i class="fas fa-info-circle mb-2"></i>
                <p class="mb-0">Metrics will appear here once you have data</p>
            </div>
        `;
    }
}
</script>