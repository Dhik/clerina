@extends('adminlte::page')

@section('title', 'Live Shopee Product')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Live Shopee Product</h1>
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
                            <div class="col-md-3 mb-2">
                                <input type="text" id="filterDates" class="form-control rangeDate" placeholder="DD/MM/YYYY - DD/MM/YYYY">
                            </div>
                            <div class="col-auto">
                                <select class="form-control" id="userFilter">
                                    <option value="">All Users</option>
                                    @foreach($userList as $user)
                                        <option value="{{ $user }}">{{ $user }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-auto">
                                <button class="btn btn-default" id="resetFilterBtn">Reset Filter</button>
                            </div>
                            <div class="col-auto">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#importLiveShopeeProductModal" id="btnImportLiveShopeeProduct">
                                        <i class="fas fa-file-upload"></i> Import Live Shopee Product (CSV)
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Charts Row -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Product Clicks Over Time</h5>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="clicksChart" width="400" height="300"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Product Conversion Funnel</h5>
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
                <table id="liveShopeeProductTable" class="table table-bordered table-striped dataTable responsive" width="100%">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Total Products</th>
                            <th>Total Clicks</th>
                            <th>Add to Cart</th>
                            <th>Orders Created</th>
                            <th>Orders Ready</th>
                            <th>Products Sold</th>
                            <th>Sales Created</th>
                            <th>Sales Ready</th>
                            <th>Avg Ranking</th>
                            <th>Click to Cart Rate</th>
                            <th>Conversion Rate</th>
                            <th>AOV</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importLiveShopeeProductModal" tabindex="-1" role="dialog" aria-labelledby="importLiveShopeeProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importLiveShopeeProductModalLabel">Import Live Shopee Product Data</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="importLiveShopeeProductForm" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="liveShopeeProductCSVFile">Import CSV File</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="liveShopeeProductCSVFile" name="live_shopee_product_csv_file" accept=".csv" required>
                            <label class="custom-file-label" for="liveShopeeProductCSVFile">Choose file</label>
                        </div>
                        <small class="form-text text-muted">Please upload a CSV file with Live Shopee Product data</small>
                        <small class="form-text text-muted">Expected columns in this order:</small>
                        <small class="form-text text-muted"><strong>Periode Data (A), User Id (B), Ranking (C), Produk (D), Klik Produk (E), Tambah ke Keranjang (F), Pesanan(Pesanan Dibuat) (G), Pesanan(Pesanan Siap Dikirim) (H), Produk Terjual(Pesanan Siap Dikirim) (I), Penjualan(Pesanan Dibuat) (J), Penjualan(Pesanan Siap Dikirim) (K)</strong></small>
                        <small class="form-text text-muted">Date format: DD-MM-YYYY, Sales format: Rp1.234.567</small>
                    </div>
                    <div class="form-group d-none" id="errorImportLiveShopeeProduct"></div>
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
                <h5 class="modal-title" id="dailyDetailsModalLabel">Product Details</h5>
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
                
                <table id="productDetailsTable" class="table table-bordered table-striped dataTable responsive" width="100%">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Ranking</th>
                            <th>Product</th>
                            <th>Clicks</th>
                            <th>Add to Cart</th>
                            <th>Orders Created</th>
                            <th>Orders Ready</th>
                            <th>Products Sold</th>
                            <th>Sales Created</th>
                            <th>Sales Ready</th>
                            <th>Click to Cart Rate</th>
                            <th>Conversion Rate</th>
                            <th>AOV</th>
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
    let filterUser = $('#userFilter');
    let modalFilterDate = initDateRangePicker('modalFilterDates');
    let funnelChart = null;
    let clicksChart = null;
    let chartsInitialized = false;

    // Initialize Live Shopee Product DataTable
    let liveShopeeProductTable = $('#liveShopeeProductTable').DataTable({
        responsive: true,
        processing: true,
        serverSide: true,
        pageLength: 25,
        ajax: {
            url: "{{ route('live_shopee_product.get_data') }}",
            data: function (d) {
                if (filterDate.val()) {
                    let dates = filterDate.val().split(' - ');
                    d.date_start = moment(dates[0], 'DD/MM/YYYY').format('YYYY-MM-DD');
                    d.date_end = moment(dates[1], 'DD/MM/YYYY').format('YYYY-MM-DD');
                }
                if (filterUser.val()) {
                    d.user_id = filterUser.val();
                }
            }
        },
        columns: [
            {data: 'periode_data', name: 'periode_data'},
            {
                data: 'total_products',
                name: 'total_products',
                render: function(data) {
                    return Number(data || 0).toLocaleString('id-ID');
                }
            },
            {data: 'total_clicks', name: 'total_clicks'},
            {data: 'total_add_to_cart', name: 'total_add_to_cart'},
            {data: 'total_orders_created', name: 'total_orders_created'},
            {data: 'total_orders_ready', name: 'total_orders_ready'},
            {data: 'total_products_sold', name: 'total_products_sold'},
            {data: 'total_sales_created', name: 'total_sales_created'},
            {data: 'total_sales_ready', name: 'total_sales_ready'},
            {data: 'avg_ranking', name: 'avg_ranking'},
            {data: 'click_to_cart_rate', name: 'click_to_cart_rate'},
            {data: 'conversion_rate', name: 'conversion_rate'},
            {data: 'avg_order_value', name: 'avg_order_value'}
        ],
        columnDefs: [
            { "targets": [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12], "className": "text-right" }
        ],
        order: [[0, 'desc']],
        fixedHeader: true,
        scrollCollapse: true,
        deferRender: true,
        scroller: true
    });

    // Product details table
    let productDetailsTable = $('#productDetailsTable').DataTable({
        responsive: false,
        scrollX: true,
        processing: true,
        serverSide: true,
        pageLength: 10,
        ajax: {
            url: "{{ route('live_shopee_product.get_details_by_date') }}",
            data: function(d) {
                if (modalFilterDate.val()) {
                    let dates = modalFilterDate.val().split(' - ');
                    d.date_start = moment(dates[0], 'DD/MM/YYYY').format('YYYY-MM-DD');
                    d.date_end = moment(dates[1], 'DD/MM/YYYY').format('YYYY-MM-DD');
                } else {
                    d.date = $('#dailyDetailsModal').data('date');
                }
                if (filterUser.val()) {
                    d.user_id = filterUser.val();
                }
            }
        },
        columns: [
            {data: 'user_id', name: 'user_id'},
            {data: 'ranking', name: 'ranking'},
            {data: 'produk', name: 'produk'},
            {data: 'klik_produk', name: 'klik_produk'},
            {data: 'tambah_ke_keranjang', name: 'tambah_ke_keranjang'},
            {data: 'pesanan_dibuat', name: 'pesanan_dibuat'},
            {data: 'pesanan_siap_dikirim', name: 'pesanan_siap_dikirim'},
            {data: 'produk_terjual_siap_dikirim', name: 'produk_terjual_siap_dikirim'},
            {data: 'penjualan_dibuat', name: 'penjualan_dibuat'},
            {data: 'penjualan_siap_dikirim', name: 'penjualan_siap_dikirim'},
            {data: 'click_to_cart_rate', name: 'click_to_cart_rate'},
            {data: 'conversion_rate', name: 'conversion_rate'},
            {data: 'avg_order_value', name: 'avg_order_value'},
            {data: 'action', name: 'action', orderable: false, searchable: false}
        ],
        columnDefs: [
            { "targets": [3, 4, 5, 6, 7, 8, 9, 10, 11, 12], "className": "text-right" },
            { "targets": [0, 1, 13], "className": "text-center" },
            { "targets": [2], "className": "text-left" }
        ],
        order: [[1, 'asc']],
        fixedHeader: true,
        scrollCollapse: true,
        deferRender: true,
        scroller: true
    });

    // Button click handlers
    $('#resetFilterBtn').click(function() {
        filterDate.val('');
        filterUser.val('');
        liveShopeeProductTable.draw();
        fetchClicksData();
        initFunnelChart();
    });

    $('#modalResetFilterBtn').click(function() {
        modalFilterDate.val('');
        productDetailsTable.draw();
    });

    // File input handler
    handleFileInputChange('liveShopeeProductCSVFile');

    // Form submit handler
    $('#importLiveShopeeProductForm').on('submit', function(e) {
        e.preventDefault();
        
        showLoadingSwal('Processing...');
        
        let formData = new FormData(this);
        $.ajax({
            url: "{{ route('live_shopee_product.import') }}",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.status === 'success') {
                    $('#importLiveShopeeProductModal').modal('hide');
                    
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
    filterUser.change(function() {
        liveShopeeProductTable.draw();
        if ($('#dailyDetailsModal').is(':visible')) {
            productDetailsTable.draw();
        }
        updateChartData();
        initFunnelChart();
    });

    filterDate.change(function() {
        liveShopeeProductTable.draw();
        updateChartData();
        initFunnelChart();
    });
    
    // Function to update chart data without recreating
    function updateChartData() {
        if (!window.clicksChartChart) {
            fetchClicksData();
            return;
        }
        
        const filterValue = filterDate.val();
        const userValue = filterUser.val();
        
        const url = new URL("{{ route('live_shopee_product.line_data') }}", window.location.origin);
        
        if (filterValue) {
            url.searchParams.append('filterDates', filterValue);
        }
        
        if (userValue) {
            url.searchParams.append('user_id', userValue);
        }
        
        fetch(url)
            .then(response => response.json())
            .then(result => {
                if (result.status === 'success') {
                    const clicksData = result.clicks;
                    const clicksDates = clicksData.map(data => data.date);
                    const clicks = clicksData.map(data => data.clicks);
                    
                    // Update existing chart data
                    window.clicksChartChart.data.labels = clicksDates;
                    window.clicksChartChart.data.datasets[0].data = clicks;
                    window.clicksChartChart.update();
                }
            })
            .catch(error => {
                console.error('Error updating chart data:', error);
            });
    }

    modalFilterDate.change(function() {
        productDetailsTable.draw();
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
    $('#liveShopeeProductTable').on('click', '.date-details', function() {
        let date = $(this).data('date');
        let formattedDate = $(this).text();
        
        $('#dailyDetailsModalLabel').text('Product Details for ' + formattedDate);
        $('#dailyDetailsModal').data('date', date);

        modalFilterDate.val('');
        
        productDetailsTable.draw();
        $('#dailyDetailsModal').modal('show');
    });

    // Delete product handler
    $('#productDetailsTable').on('click', '.delete-product', function() {
        const productId = $(this).data('id');
        
        Swal.fire({
            title: 'Are you sure?',
            text: 'This will delete the selected product record!',
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
                    url: "{{ route('live_shopee_product.delete') }}",
                    type: 'DELETE',
                    data: {
                        id: productId,
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
                            productDetailsTable.draw();
                            liveShopeeProductTable.draw();
                            fetchClicksData();
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

    function fetchClicksData() {
        if (chartsInitialized && window.clicksChartChart) {
            console.log('Chart already exists, updating data instead...');
            return;
        }
        
        const filterValue = filterDate.val();
        const userValue = filterUser.val();
        
        const url = new URL("{{ route('live_shopee_product.line_data') }}", window.location.origin);
        
        if (filterValue) {
            url.searchParams.append('filterDates', filterValue);
        }
        
        if (userValue) {
            url.searchParams.append('user_id', userValue);
        }
        
        fetch(url)
            .then(response => response.json())
            .then(result => {
                if (result.status === 'success') {
                    const clicksData = result.clicks;
                    const clicksDates = clicksData.map(data => data.date);
                    const clicks = clicksData.map(data => data.clicks);
                    
                    createLineChart('clicksChart', 'Total Clicks', clicksDates, clicks, 'rgba(34, 197, 94, 1)');
                    chartsInitialized = true;
                }
            })
            .catch(error => {
                console.error('Error fetching clicks data:', error);
            });
    }

    function initFunnelChart() {
        const filterValue = filterDate.val();
        const userValue = filterUser.val();

        const url = new URL("{{ route('live_shopee_product.funnel_data') }}", window.location.origin);
        if (filterValue) {
            url.searchParams.append('filterDates', filterValue);
        }
        if (userValue) {
            url.searchParams.append('user_id', userValue);
        }

        fetch(url)
            .then(response => response.json())
            .then(result => {
                if (result.status === 'success') {
                    if (!result.has_data) {
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
        console.log('Initializing Live Shopee Product page...');
        
        liveShopeeProductTable.draw();
        
        setTimeout(function() {
            if (!chartsInitialized) {
                fetchClicksData();
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

function handleFileInputChange(inputId) {
    $('#' + inputId).on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').html(fileName || 'Choose file');
    });
}

function createLineChart(ctxId, label, dates, data, color = 'rgba(54, 162, 235, 1)') {
    const canvas = document.getElementById(ctxId);
    if (!canvas) {
        console.error('Canvas not found:', ctxId);
        return;
    }
    
    canvas.style.height = '570px';
    canvas.style.maxHeight = '570px';
    
    const ctx = canvas.getContext('2d');
    
    if (window[ctxId + 'Chart'] && typeof window[ctxId + 'Chart'].destroy === 'function') {
        window[ctxId + 'Chart'].destroy();
        window[ctxId + 'Chart'] = null;
    }
    
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
                            label += context.parsed.y.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
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
                            if (parseInt(value) >= 1000) {
                                return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                            } else {
                                return value;
                            }
                        }
                    }
                }
            }
        }
    });
    
    return window[ctxId + 'Chart'];
}

function createFunnelChart(elementId, data, metricsElementId, result) {
    if (window[elementId + 'Chart']) {
        window[elementId + 'Chart'].destroy();
    }
    
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
        colors: ['#22C55E', '#16A34A', '#15803D', '#166534', '#14532D'],
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
                <span class="text-success font-weight-bold">
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
            const totalOrders = data.find(item => item.name === 'Orders Created')?.value || 0;
            const conversionRate = totalClicks > 0 ? ((totalOrders / totalClicks) * 100).toFixed(2) : 0;
            
            const addToCart = data.find(item => item.name === 'Add to Cart')?.value || 0;
            const clickToCartRate = totalClicks > 0 ? ((addToCart / totalClicks) * 100).toFixed(2) : 0;
            
            additionalInsightsHtml = `
                <div class="mt-3 pt-3 border-top">
                    <h6 class="font-weight-bold text-center">Key Metrics</h6>
                    <div class="row">
                        <div class="col-6">
                            <div class="card bg-success text-white text-center">
                                <div class="card-body p-2">
                                    <div class="text-sm font-weight-bold">Click to Cart Rate</div>
                                    <div class="h6 mb-0 font-weight-bold">${clickToCartRate}%</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card bg-primary text-white text-center">
                                <div class="card-body p-2">
                                    <div class="text-sm font-weight-bold">Conversion Rate</div>
                                    <div class="h6 mb-0 font-weight-bold">${conversionRate}%</div>
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
    
    document.querySelector('#' + elementId).innerHTML = `
        <div class="d-flex flex-column align-items-center justify-content-center" style="height: 350px;">
            <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No data available</h5>
            <p class="text-muted">Import some CSV data to see the product funnel analysis</p>
        </div>
    `;
    
    if (metricsElementId) {
        document.querySelector('#' + metricsElementId).innerHTML = `
            <div class="text-center text-muted">
                <i class="fas fa-info-circle mb-2"></i>
                <p class="mb-0">Product metrics will appear here once you have data</p>
            </div>
        `;
    }
}
</script>