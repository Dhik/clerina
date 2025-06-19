@extends('adminlte::page')

@section('title', 'Affiliate Shopee')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Affiliate Shopee</h1>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Affiliate Performance Funnel</h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="funnelChart"></div>
                                        <div id="funnelMetrics" class="mt-4"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- DataTable -->
        <div class="card">
            <div class="card-body">
                <table id="affiliateShopeeTable" class="table table-bordered table-striped dataTable responsive" width="100%">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Total Affiliates</th>
                            <th>Total Omzet</th>
                            <th>Products Sold</th>
                            <th>Total Orders</th>
                            <th>Total Clicks</th>
                            <th>Est. Commission</th>
                            <th>Avg ROI</th>
                            <th>Total Buyers</th>
                            <th>New Buyers</th>
                            <th>CTR</th>
                            <th>Avg Commission/Affiliate</th>
                            <th>Performance</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importAffiliateShopeeModal" tabindex="-1" role="dialog" aria-labelledby="importAffiliateShopeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importAffiliateShopeeModalLabel">Import Affiliate Shopee Data</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="importAffiliateShopeeForm" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="importDate">Import Date</label>
                        <input type="date" class="form-control" id="importDate" name="import_date" required>
                        <small class="form-text text-muted">Select the date for this affiliate data import</small>
                    </div>
                    <div class="form-group">
                        <label for="affiliateShopeeCSVFile">Import CSV File</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="affiliateShopeeCSVFile" name="affiliate_shopee_csv_file" accept=".csv" required>
                            <label class="custom-file-label" for="affiliateShopeeCSVFile">Choose file</label>
                        </div>
                        <small class="form-text text-muted">Please upload a CSV file with Affiliate Shopee data</small>
                        <small class="form-text text-muted">Expected columns in this order:</small>
                        <small class="form-text text-muted"><strong>Column A: Affiliate ID, Column B: Nama Affiliate, Column C: Username Affiliate, Column D: Omzet Penjualan, Column E: Produk Terjual, Column F: Pesanan, Column G: Clicks, Column H: Estimasi Komisi, Column I: ROI, Column J: Total Pembeli, Column K: Pembeli Baru</strong></small>
                        <small class="form-text text-muted">Numbers can contain dots as thousand separators (e.g., 1.234.567)</small>
                    </div>
                    <div class="form-group d-none" id="errorImportAffiliateShopee"></div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Import
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Details Modal -->
<div class="modal fade" id="dailyDetailsModal" tabindex="-1" role="dialog" aria-labelledby="dailyDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dailyDetailsModalLabel">Affiliate Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <input type="text" id="modalFilterDates" class="form-control rangeDate" placeholder="DD/MM/YYYY - DD/MM/YYYY">
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-default" id="modalResetFilterBtn">Reset</button>
                    </div>
                </div>
                
                <table id="affiliateDetailsTable" class="table table-bordered table-striped dataTable responsive" width="100%">
                    <thead>
                        <tr>
                            <th>Affiliate ID</th>
                            <th>Nama Affiliate</th>
                            <th>Username</th>
                            <th>Omzet Penjualan</th>
                            <th>Produk Terjual</th>
                            <th>Pesanan</th>
                            <th>Clicks</th>
                            <th>Est. Komisi</th>
                            <th>ROI</th>
                            <th>Total Pembeli</th>
                            <th>Pembeli Baru</th>
                            <th>CTR</th>
                            <th>Commission Rate</th>
                            <th>New Buyer Rate</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<style>
.card {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15) !important;
}

.card .card-header {
    background-color: #f8f9fc;
    border-bottom: 1px solid #e3e6f0;
}

.badge {
    font-size: 0.75em;
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #5a5c69;
    background-color: #f8f9fc;
}

.btn-group .btn {
    border-radius: 0.35rem;
    margin-right: 5px;
}

.custom-file-label::after {
    content: "Browse";
}

.daterangepicker {
    z-index: 9999 !important;
}

.table-responsive {
    overflow-x: auto;
}

.dataTables_wrapper .dataTables_processing {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 200px;
    margin-left: -100px;
    margin-top: -26px;
    text-align: center;
    padding: 1em 0;
    background: rgba(255, 255, 255, 0.9);
    border: 1px solid #ddd;
    border-radius: 4px;
}

.apexcharts-canvas {
    margin: 0 auto;
}

#funnelMetrics {
    max-height: 370px;
    overflow-y: auto;
}

.modal-xl {
    max-width: 1200px;
}

/* Custom scrollbar for tables */
.dataTables_scrollBody::-webkit-scrollbar {
    height: 8px;
}

.dataTables_scrollBody::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.dataTables_scrollBody::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

.dataTables_scrollBody::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .card-body .row .col-auto {
        margin-bottom: 10px;
    }
    
    .btn-group {
        width: 100%;
    }
    
    .btn-group .btn {
        width: 100%;
        margin-bottom: 5px;
    }
}
</style>
@stop

@section('js')
<script src="https://cdn.plot.ly/plotly-latest.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.1/dist/sweetalert2.all.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<script>
    $(document).ready(function() {
    // Initialize variables
    let filterDate = initDateRangePicker('filterDates');
    let filterAffiliate = $('#affiliateFilter');
    let modalFilterDate = initDateRangePicker('modalFilterDates');
    let funnelChart = null;
    let commissionChart = null;
    let chartsInitialized = false; // Track chart initialization

    // Initialize Affiliate Shopee DataTable
    let affiliateShopeeTable = $('#affiliateShopeeTable').DataTable({
        responsive: true,
        processing: true,
        serverSide: true,
        pageLength: 25,
        ajax: {
            url: "{{ route('affiliate_shopee.get_data') }}",
            data: function (d) {
                if (filterDate.val()) {
                    let dates = filterDate.val().split(' - ');
                    d.date_start = moment(dates[0], 'DD/MM/YYYY').format('YYYY-MM-DD');
                    d.date_end = moment(dates[1], 'DD/MM/YYYY').format('YYYY-MM-DD');
                }
                if (filterAffiliate.val()) {
                    d.affiliate_id = filterAffiliate.val();
                }
            }
        },
        columns: [
            {data: 'date', name: 'date'},
            {
                data: 'total_affiliates',
                name: 'total_affiliates',
                render: function(data) {
                    return Number(data || 0).toLocaleString('id-ID');
                }
            },
            {data: 'total_omzet', name: 'total_omzet'},
            {
                data: 'total_products_sold',
                name: 'total_products_sold',
                render: function(data) {
                    return Number(data || 0).toLocaleString('id-ID');
                }
            },
            {
                data: 'total_orders',
                name: 'total_orders',
                render: function(data) {
                    return Number(data || 0).toLocaleString('id-ID');
                }
            },
            {
                data: 'total_clicks',
                name: 'total_clicks',
                render: function(data) {
                    return Number(data || 0).toLocaleString('id-ID');
                }
            },
            {data: 'total_estimated_commission', name: 'total_estimated_commission'},
            {data: 'avg_roi', name: 'avg_roi'},
            {
                data: 'total_buyers',
                name: 'total_buyers',
                render: function(data) {
                    return Number(data || 0).toLocaleString('id-ID');
                }
            },
            {
                data: 'total_new_buyers',
                name: 'total_new_buyers',
                render: function(data) {
                    return Number(data || 0).toLocaleString('id-ID');
                }
            },
            {data: 'click_through_rate', name: 'click_through_rate'},
            {data: 'avg_commission_per_affiliate', name: 'avg_commission_per_affiliate'},
            {data: 'performance', name: 'performance', searchable: false}
        ],
        columnDefs: [
            { "targets": [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11], "className": "text-right" },
            { "targets": [12], "className": "text-center" }
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
            url: "{{ route('affiliate_shopee.get_details_by_date') }}",
            data: function(d) {
                if (modalFilterDate.val()) {
                    let dates = modalFilterDate.val().split(' - ');
                    d.date_start = moment(dates[0], 'DD/MM/YYYY').format('YYYY-MM-DD');
                    d.date_end = moment(dates[1], 'DD/MM/YYYY').format('YYYY-MM-DD');
                } else {
                    d.date = $('#dailyDetailsModal').data('date');
                }
                if (filterAffiliate.val()) {
                    d.affiliate_id = filterAffiliate.val();
                }
            }
        },
        columns: [
            {data: 'affiliate_id', name: 'affiliate_id'},
            {data: 'nama_affiliate', name: 'nama_affiliate'},
            {data: 'username_affiliate', name: 'username_affiliate'},
            {data: 'omzet_penjualan', name: 'omzet_penjualan'},
            {data: 'produk_terjual', name: 'produk_terjual'},
            {data: 'pesanan', name: 'pesanan'},
            {data: 'clicks', name: 'clicks'},
            {data: 'estimasi_komisi', name: 'estimasi_komisi'},
            {data: 'roi', name: 'roi'},
            {data: 'total_pembeli', name: 'total_pembeli'},
            {data: 'pembeli_baru', name: 'pembeli_baru'},
            {data: 'click_through_rate', name: 'click_through_rate'},
            {data: 'commission_rate', name: 'commission_rate'},
            {data: 'new_buyer_rate', name: 'new_buyer_rate'},
            {data: 'action', name: 'action', orderable: false, searchable: false}
        ],
        columnDefs: [
            { "targets": [3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13], "className": "text-right" },
            { "targets": [0, 1, 2], "className": "text-center" },
            { "targets": [14], "className": "text-center" }
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
        filterAffiliate.val('');
        affiliateShopeeTable.draw();
        fetchCommissionData();
        initFunnelChart();
    });

    $('#modalResetFilterBtn').click(function() {
        modalFilterDate.val('');
        affiliateDetailsTable.draw();
    });

    // File input handler
    handleFileInputChange('affiliateShopeeCSVFile');

    // Set default import date to today
    $('#importDate').val(moment().format('YYYY-MM-DD'));

    // Form submit handler
    $('#importAffiliateShopeeForm').on('submit', function(e) {
        e.preventDefault();
        
        showLoadingSwal('Processing...');
        
        let formData = new FormData(this);
        $.ajax({
            url: "{{ route('affiliate_shopee.import') }}",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.status === 'success') {
                    $('#importAffiliateShopeeModal').modal('hide');
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 1500
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
    filterAffiliate.change(function() {
        affiliateShopeeTable.draw();
        if ($('#dailyDetailsModal').is(':visible')) {
            affiliateDetailsTable.draw();
        }
        // Only update data, don't recreate chart
        updateChartData();
        initFunnelChart();
    });

    filterDate.change(function() {
        affiliateShopeeTable.draw();
        // Only update data, don't recreate chart
        updateChartData();
        initFunnelChart();
    });
    
    // Function to update chart data without recreating
    function updateChartData() {
        if (!window.commissionChartChart) {
            fetchCommissionData();
            return;
        }
        
        const filterValue = filterDate.val();
        const affiliateValue = filterAffiliate.val();
        
        const url = new URL("{{ route('affiliate_shopee.line_data') }}", window.location.origin);
        
        if (filterValue) {
            url.searchParams.append('filterDates', filterValue);
        }
        
        if (affiliateValue) {
            url.searchParams.append('affiliate_id', affiliateValue);
        }
        
        fetch(url)
            .then(response => response.json())
            .then(result => {
                if (result.status === 'success') {
                    const commissionData = result.commission;
                    const commissionDates = commissionData.map(data => data.date);
                    const commission = commissionData.map(data => data.commission);
                    
                    // Update existing chart data
                    window.commissionChartChart.data.labels = commissionDates;
                    window.commissionChartChart.data.datasets[0].data = commission;
                    window.commissionChartChart.update();
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
    $('#affiliateShopeeTable').on('click', '.date-details', function() {
        let date = $(this).data('date');
        let formattedDate = $(this).text();
        
        $('#dailyDetailsModalLabel').text('Affiliate Details for ' + formattedDate);
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
            text: 'This will delete the selected affiliate record!',
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
                    url: "{{ route('affiliate_shopee.delete') }}",
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
                            affiliateShopeeTable.draw();
                            fetchCommissionData();
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

    function fetchCommissionData() {
        // Prevent multiple chart creations
        if (chartsInitialized && window.commissionChartChart) {
            console.log('Chart already exists, updating data instead...');
            return;
        }
        
        const filterValue = filterDate.val();
        const affiliateValue = filterAffiliate.val();
        
        const url = new URL("{{ route('affiliate_shopee.line_data') }}", window.location.origin);
        
        if (filterValue) {
            url.searchParams.append('filterDates', filterValue);
        }
        
        if (affiliateValue) {
            url.searchParams.append('affiliate_id', affiliateValue);
        }
        
        fetch(url)
            .then(response => response.json())
            .then(result => {
                if (result.status === 'success') {
                    const commissionData = result.commission;
                    const commissionDates = commissionData.map(data => data.date);
                    const commission = commissionData.map(data => data.commission);
                    
                    createLineChart('commissionChart', 'Total Commission', commissionDates, commission, 'rgba(34, 197, 94, 1)');
                    chartsInitialized = true;
                }
            })
            .catch(error => {
                console.error('Error fetching commission data:', error);
            });
    }

    function initFunnelChart() {
        const filterValue = filterDate.val();
        const affiliateValue = filterAffiliate.val();

        const url = new URL("{{ route('affiliate_shopee.funnel_data') }}", window.location.origin);
        if (filterValue) {
            url.searchParams.append('filterDates', filterValue);
        }
        if (affiliateValue) {
            url.searchParams.append('affiliate_id', affiliateValue);
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

    // Initialize on page load
    $(function () {
        console.log('Initializing Affiliate Shopee page...');
        
        affiliateShopeeTable.draw();
        
        // Initialize charts only once
        setTimeout(function() {
            if (!chartsInitialized) {
                fetchCommissionData();
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
    ctx.fillText('Import some CSV data to see the chart', canvas.width / 2, canvas.height / 2 + 15);
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
        colors: ['#60A5FA', '#3B82F6', '#34D399', '#2563EB', '#F59E0B'],
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
            const totalClicks = data[0].value;
            const totalOrders = data[2].value; // Orders are at index 2
            const conversionRate = totalClicks > 0 ? ((totalOrders / totalClicks) * 100).toFixed(2) : 0;
            
            additionalInsightsHtml = `
                <div class="mt-3 pt-3 border-top">
                    <h6 class="font-weight-bold text-center">Key Metrics</h6>
                    <div class="row">
                        <div class="col-12">
                            <div class="card bg-success text-white text-center">
                                <div class="card-body p-2">
                                    <div class="text-sm font-weight-bold">Click-to-Order Rate</div>
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
            <p class="text-muted">Import some CSV data to see the funnel analysis</p>
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
@stop