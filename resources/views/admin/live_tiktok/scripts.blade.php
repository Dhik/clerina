<script>
$(document).ready(function() {
    // Initialize variables
    let filterDate = initDateRangePicker('filterDates');
    let funnelChart = null;
    let gmvChart = null;
    let chartsInitialized = false;
    let isEditMode = false;

    // Initialize Live TikTok DataTable
    let liveTiktokTable = $('#liveTiktokTable').DataTable({
        responsive: true,
        processing: true,
        serverSide: true,
        pageLength: 25,
        ajax: {
            url: "{{ route('live_tiktok.get_data') }}",
            data: function (d) {
                if (filterDate.val()) {
                    let dates = filterDate.val().split(' - ');
                    d.date_start = moment(dates[0], 'DD/MM/YYYY').format('YYYY-MM-DD');
                    d.date_end = moment(dates[1], 'DD/MM/YYYY').format('YYYY-MM-DD');
                }
            }
        },
        columns: [
            {data: 'date', name: 'date'},
            {data: 'gmv_live', name: 'gmv_live'},
            {
                data: 'pesanan',
                name: 'pesanan',
                render: function(data) {
                    return Number(data || 0).toLocaleString('id-ID');
                }
            },
            {
                data: 'tayangan',
                name: 'tayangan',
                render: function(data) {
                    return Number(data || 0).toLocaleString('id-ID');
                }
            },
            {data: 'gpm', name: 'gpm'},
            {data: 'conversion_rate', name: 'conversion_rate'},
            {data: 'avg_order_value', name: 'avg_order_value'},
            {data: 'performance', name: 'performance', searchable: false},
            {data: 'action', name: 'action', orderable: false, searchable: false}
        ],
        columnDefs: [
            { "targets": [1, 2, 3, 4, 5, 6], "className": "text-right" },
            { "targets": [7, 8], "className": "text-center" }
        ],
        order: [[0, 'desc']],
        fixedHeader: true,
        scrollCollapse: true,
        deferRender: true,
        scroller: true
    });

    // Button click handlers
    $('#resetFilterBtn').click(function() {
        filterDate.val('');
        liveTiktokTable.draw();
        fetchGmvData();
        initFunnelChart();
    });

    $('#btnAddRecord').click(function() {
        resetForm();
        isEditMode = false;
        $('#addRecordModalLabel').text('Add New Live TikTok Record');
        $('#submitBtn').html('<i class="fas fa-save"></i> Save Record');
        $('#formMethod').val('POST');
    });

    // Filter change handlers
    filterDate.change(function() {
        liveTiktokTable.draw();
        updateChartData();
        initFunnelChart();
    });

    // Form submit handler
    $('#recordForm').on('submit', function(e) {
        e.preventDefault();
        
        showLoadingSwal('Saving record...');
        
        let formData = new FormData(this);
        let method = $('#formMethod').val();
        let url = "{{ route('live_tiktok.store') }}";
        let ajaxMethod = 'POST';
        
        if (isEditMode) {
            let recordId = $('#recordId').val();
            url = "{{ route('live_tiktok.update', '') }}/" + recordId;
            ajaxMethod = 'PUT';
            formData.append('_method', 'PUT');
        }

        $.ajax({
            url: url,
            type: ajaxMethod,
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.status === 'success') {
                    $('#addRecordModal').modal('hide');
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        liveTiktokTable.draw();
                        fetchGmvData();
                        initFunnelChart();
                        resetForm();
                    });
                } else {
                    showError(response.message || 'Unknown error occurred');
                }
            },
            error: function(xhr, status, error) {
                console.error("Ajax error:", xhr, status, error);
                
                let errorMessage = 'An error occurred while saving';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    let errors = xhr.responseJSON.errors;
                    errorMessage = Object.values(errors).flat().join('<br>');
                }
                
                showError(errorMessage);
            }
        });
    });

    // Edit record handler
    $('#liveTiktokTable').on('click', '.edit-record', function() {
        let recordId = $(this).data('id');
        
        showLoadingSwal('Loading record...');
        
        $.ajax({
            url: "{{ route('live_tiktok.edit', '') }}/" + recordId,
            type: 'GET',
            success: function(response) {
                if (response.status === 'success') {
                    Swal.close();
                    
                    isEditMode = true;
                    $('#recordId').val(response.data.id);
                    $('#date').val(response.data.date);
                    $('#gmv_live').val(response.data.gmv_live);
                    $('#pesanan').val(response.data.pesanan);
                    $('#tayangan').val(response.data.tayangan);
                    $('#gpm').val(response.data.gpm);
                    
                    $('#addRecordModalLabel').text('Edit Live TikTok Record');
                    $('#submitBtn').html('<i class="fas fa-save"></i> Update Record');
                    $('#formMethod').val('PUT');
                    
                    $('#addRecordModal').modal('show');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message || 'Failed to load record',
                        confirmButtonColor: '#3085d6'
                    });
                }
            },
            error: function(xhr) {
                let errorMsg = xhr.responseJSON ? xhr.responseJSON.message : 'Failed to load record';
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: errorMsg,
                    confirmButtonColor: '#3085d6'
                });
            }
        });
    });

    // Delete record handler
    $('#liveTiktokTable').on('click', '.delete-record', function() {
        const recordId = $(this).data('id');
        
        Swal.fire({
            title: 'Are you sure?',
            text: 'This will delete the selected Live TikTok record!',
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
                    url: "{{ route('live_tiktok.destroy', '') }}/" + recordId,
                    type: 'DELETE',
                    data: {
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
                            liveTiktokTable.draw();
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

    // Modal event handlers
    $('#addRecordModal').on('hidden.bs.modal', function() {
        resetForm();
    });

    // Function to update chart data without recreating
    function updateChartData() {
        if (!window.gmvChartChart) {
            fetchGmvData();
            return;
        }
        
        const filterValue = filterDate.val();
        const url = new URL("{{ route('live_tiktok.line_data') }}", window.location.origin);
        
        if (filterValue) {
            url.searchParams.append('filterDates', filterValue);
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

    function fetchGmvData() {
        // Prevent multiple chart creations
        if (chartsInitialized && window.gmvChartChart) {
            console.log('Chart already exists, updating data instead...');
            return;
        }
        
        const filterValue = filterDate.val();
        const url = new URL("{{ route('live_tiktok.line_data') }}", window.location.origin);
        
        if (filterValue) {
            url.searchParams.append('filterDates', filterValue);
        }
        
        fetch(url)
            .then(response => response.json())
            .then(result => {
                if (result.status === 'success') {
                    const gmvData = result.gmv;
                    const gmvDates = gmvData.map(data => data.date);
                    const gmv = gmvData.map(data => data.gmv);
                    
                    createLineChart('gmvChart', 'GMV Live', gmvDates, gmv, 'rgba(75, 192, 192, 1)');
                    chartsInitialized = true;
                }
            })
            .catch(error => {
                console.error('Error fetching GMV data:', error);
            });
    }

    function initFunnelChart() {
        const filterValue = filterDate.val();
        const url = new URL("{{ route('live_tiktok.funnel_data') }}", window.location.origin);
        
        if (filterValue) {
            url.searchParams.append('filterDates', filterValue);
        }

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

    function resetForm() {
        $('#recordForm')[0].reset();
        $('#recordId').val('');
        $('#errorMessage').addClass('d-none');
        $('#errorText').text('');
        isEditMode = false;
    }

    function showError(message) {
        Swal.close();
        $('#errorText').html(message);
        $('#errorMessage').removeClass('d-none');
    }

    // Initialize on page load
    $(function () {
        console.log('Initializing Live TikTok page...');
        
        liveTiktokTable.draw();
        
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

function showLoadingSwal(message) {
    Swal.fire({
        title: message,
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
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
    ctx.fillText('Add some records to see the chart', canvas.width / 2, canvas.height / 2 + 15);
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
        colors: ['#4BC0C0', '#36A2EB', '#FFCE56', '#FF6384'],
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
            const totalTayangan = data[0].value;
            const totalPesanan = data[1].value;
            const conversionRate = totalTayangan > 0 ? ((totalPesanan / totalTayangan) * 100).toFixed(4) : 0;
            
            additionalInsightsHtml = `
                <div class="mt-3 pt-3 border-top">
                    <h6 class="font-weight-bold text-center">Key Metrics</h6>
                    <div class="row">
                        <div class="col-12">
                            <div class="card bg-info text-white text-center">
                                <div class="card-body p-2">
                                    <div class="text-sm font-weight-bold">Tayangan-to-Order Rate</div>
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
            <p class="text-muted">Add some records to see the funnel analysis</p>
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