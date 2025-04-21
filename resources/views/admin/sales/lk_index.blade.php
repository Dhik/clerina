@extends('adminlte::page')

@section('title', "Financial Report")

@section('content_header')
    <h1>Financial Report</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <input type="text" id="filterDates" class="form-control daterange" placeholder="DD/MM/YYYY - DD/MM/YYYY">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 col-lg-3">
                    <div class="small-box bg-gradient-success">
                        <div class="inner">
                            <h4 id="totalGrossRevenue">Rp 0</h4>
                            <p>Total Gross Revenue</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="small-box bg-gradient-primary">
                        <div class="inner">
                            <h4 id="totalHpp">Rp 0</h4>
                            <p>Total HPP</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-box"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="small-box bg-gradient-info">
                        <div class="inner">
                            <h4 id="totalFeeAdmin">Rp 0</h4>
                            <p>Total Fee Admin</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-money-bill"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="small-box bg-gradient-warning">
                        <div class="inner">
                            <h4 id="netProfit">Rp 0</h4>
                            <p>Net Profit</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Channel Summary Cards Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Revenue by Channel</h3>
                        </div>
                        <div class="card-body">
                            <div class="row" id="channelRevenueCards">
                                <!-- Cards will be dynamically added here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">HPP by Channel</h3>
                        </div>
                        <div class="card-body">
                            <div class="row" id="channelHppCards">
                                <!-- Cards will be dynamically added here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Financial Report Details</h3>
                </div>
                <div class="card-body">
                    <!-- Tabs for different report views -->
                    <ul class="nav nav-tabs mb-3" id="reportTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="summary-tab" data-toggle="tab" href="#summary" role="tab" aria-controls="summary" aria-selected="true">Summary</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="gross-revenue-tab" data-toggle="tab" href="#gross-revenue" role="tab" aria-controls="gross-revenue" aria-selected="false">Gross Revenue</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="hpp-tab" data-toggle="tab" href="#hpp" role="tab" aria-controls="hpp" aria-selected="false">HPP</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="fee-admin-tab" data-toggle="tab" href="#fee-admin" role="tab" aria-controls="fee-admin" aria-selected="false">Fee Admin</a>
                        </li>
                    </ul>
                    
                    <div class="tab-content" id="reportTabsContent">
                        <!-- Summary Tab -->
                        <div class="tab-pane fade show active" id="summary" role="tabpanel" aria-labelledby="summary-tab">
                            <div class="table-responsive">
                                <table id="summaryTable" class="table table-bordered table-striped dataTable" width="100%">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Total Gross Revenue</th>
                                            <th>Total HPP</th>
                                            <th>Total Fee Admin</th>
                                            <th>Net Profit</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Gross Revenue Tab -->
                        <div class="tab-pane fade" id="gross-revenue" role="tabpanel" aria-labelledby="gross-revenue-tab">
                            <div class="table-responsive">
                                <table id="grossRevenueTable" class="table table-bordered table-striped dataTable" width="100%">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            @foreach($salesChannels as $channel)
                                            <th>{{ $channel->name }}</th>
                                            @endforeach
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>

                        <!-- HPP Tab -->
                        <div class="tab-pane fade" id="hpp" role="tabpanel" aria-labelledby="hpp-tab">
                            <div class="table-responsive">
                                <table id="hppTable" class="table table-bordered table-striped dataTable" width="100%">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            @foreach($salesChannels as $channel)
                                            <th>{{ $channel->name }}</th>
                                            @endforeach
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>

                        <!-- Fee Admin Tab -->
                        <div class="tab-pane fade" id="fee-admin" role="tabpanel" aria-labelledby="fee-admin-tab">
                            <div class="table-responsive">
                                <table id="feeAdminTable" class="table table-bordered table-striped dataTable" width="100%">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            @foreach($salesChannels as $channel)
                                            <th>{{ $channel->name }}</th>
                                            @endforeach
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Standard Detail Modal -->
    <div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailModalLabel">Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table id="detailTable" class="table table-bordered table-striped">
                            <thead id="detailTableHead">
                                <!-- Dynamic headers will be added here -->
                            </thead>
                            <tbody id="detailTableBody">
                                <!-- Dynamic content will be added here -->
                            </tbody>
                            <tfoot id="detailTableFoot">
                                <!-- Dynamic footer will be added here -->
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Gross Revenue Detail Modal with Tabs -->
<div class="modal fade" id="grossRevenueDetailModal" tabindex="-1" role="dialog" aria-labelledby="grossRevenueDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="grossRevenueDetailModalLabel">Gross Revenue Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body position-relative">
                <!-- Loading overlay positioned inside modal-body with position-relative -->
                <div id="gross-revenue-loading-overlay" class="loading-overlay">
                    <div class="spinner-border loading-spinner text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
                
                <!-- Channel summary section -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card card-primary card-outline card-tabs">
                            <div class="card-header p-0 pt-1 border-bottom-0">
                                <ul class="nav nav-tabs" id="gross-revenue-channel-tabs" role="tablist">
                                    <!-- Channel tabs will be added here -->
                                </ul>
                            </div>
                            <div class="card-body">
                                <div class="tab-content" id="gross-revenue-channel-tab-content">
                                    <!-- Channel tab contents will be added here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
    <!-- HPP Detail Modal with Tabs -->
    <div class="modal fade" id="hppDetailModal" tabindex="-1" role="dialog" aria-labelledby="hppDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="hppDetailModalLabel">HPP Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body position-relative">
                    <!-- Loading overlay positioned inside modal-body with position-relative -->
                    <div id="hpp-loading-overlay" class="loading-overlay">
                        <div class="spinner-border loading-spinner text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                    
                    <!-- Channel summary section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card card-primary card-outline card-tabs">
                                <div class="card-header p-0 pt-1 border-bottom-0">
                                    <ul class="nav nav-tabs" id="channel-tabs" role="tablist">
                                        <!-- Channel tabs will be added here -->
                                    </ul>
                                </div>
                                <div class="card-body">
                                    <div class="tab-content" id="channel-tab-content">
                                        <!-- Channel tab contents will be added here -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
<style>
    .small-box {
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transition: transform 0.2s;
    }
    
    .small-box:hover {
        transform: translateY(-5px);
    }
    
    .small-box .inner {
        padding: 20px;
    }
    
    .small-box .icon {
        right: 15px;
        top: 15px;
        font-size: 60px;
        opacity: 0.3;
    }
    
    .card {
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        margin-bottom: 20px;
    }
    
    .card-header {
        background-color: rgba(0,0,0,0.03);
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }
    
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    .table th, .table td {
        padding: 12px;
        vertical-align: middle;
    }
    
    .table tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    .modal-content {
        border-radius: 10px;
    }
    
    .modal-header {
        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
    }
    
    a.show-details, a.show-gross-revenue-details {
        color: inherit;
        text-decoration: none;
        cursor: pointer;
    }
    
    a.show-details:hover, a.show-gross-revenue-details:hover {
        text-decoration: underline;
    }
    
    .text-success {
        color: #28a745 !important;
    }
    
    .text-primary {
        color: #007bff !important;
    }
    
    .daterange {
        border-radius: 5px;
        padding: 10px;
    }
    
    /* Marketplace specific styles */
    .marketplace-card {
        border-radius: 10px;
        overflow: hidden;
        transition: transform 0.2s;
        margin-bottom: 20px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        position: relative;
    }
    
    .marketplace-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.15);
    }
    
    .marketplace-card .inner {
        padding: 15px;
        position: relative;
        z-index: 10;
    }
    
    .marketplace-card h5 {
        font-weight: 700;
        font-size: 1.25rem;
        margin-bottom: 8px;
        color: #fff;
    }
    
    .marketplace-card p {
        font-size: 1rem;
        margin-bottom: 0;
        color: rgba(255, 255, 255, 0.9);
    }
    
    .marketplace-card .logo {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 2.5rem;
        opacity: 0.8;
        color: rgba(255, 255, 255, 0.85);
    }
    
    /* Shopee specific styles */
    .shopee-card {
        background: linear-gradient(135deg, #ee4d2d, #ff7337);
    }
    
    .shopee-2-card {
        background: linear-gradient(135deg, #d93b1c, #ee4d2d);
    }
    
    .shopee-3-card {
        background: linear-gradient(135deg, #c52d0e, #d93b1c);
    }
    
    /* Lazada specific styles */
    .lazada-card {
        background: linear-gradient(135deg, #0f146d, #2026b2);
    }
    
    /* Tokopedia specific styles */
    .tokopedia-card {
        background: linear-gradient(135deg, #03ac0e, #42d149);
    }
    
    /* TikTok specific styles */
    .tiktok-card {
        background: linear-gradient(135deg, #010101, #333333);
    }
    
    /* B2B specific styles */
    .b2b-card {
        background: linear-gradient(135deg, #6a7d90, #8ca3ba);
    }
    
    /* CRM specific styles */
    .crm-card {
        background: linear-gradient(135deg, #7b68ee, #9370db);
    }
    
    /* Generic style for other channels */
    .other-card {
        background: linear-gradient(135deg, #607d8b, #90a4ae);
    }
    
    /* Tab styles */
    .nav-tabs .nav-link {
        border-radius: 0.25rem 0.25rem 0 0;
    }
    
    .nav-tabs .nav-link.active {
        font-weight: bold;
    }
    
    .tab-content {
        padding: 15px;
        background-color: #fff;
        border: 1px solid #dee2e6;
        border-top: 0;
        border-radius: 0 0 0.25rem 0.25rem;
    }
    
    .channel-summary {
        padding: 12px;
        margin-bottom: 15px;
        border-radius: 8px;
        background-color: #f8f9fa;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .channel-summary h5 {
        margin-bottom: 0;
    }
    
    .loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 255, 255, 0.8);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1000;
        border-radius: 10px;
    }
    
    .loading-spinner {
        width: 4rem;
        height: 4rem;
        border-width: 0.25em;
    }
    /* HPP Card styles - using different color schemes */
.shopee-hpp-card {
    background: linear-gradient(135deg, #6f42c1, #9370db);
}

.shopee-2-hpp-card {
    background: linear-gradient(135deg, #5e37a6, #6f42c1);
}

.shopee-3-hpp-card {
    background: linear-gradient(135deg, #4b2d89, #5e37a6);
}

.lazada-hpp-card {
    background: linear-gradient(135deg, #fd7e14, #f8a064);
}

.tokopedia-hpp-card {
    background: linear-gradient(135deg, #007bff, #59a6ff);
}

.tiktok-hpp-card {
    background: linear-gradient(135deg, #6c757d, #8f9193);
}

.b2b-hpp-card {
    background: linear-gradient(135deg, #20c997, #68e3c5);
}

.crm-hpp-card {
    background: linear-gradient(135deg, #e83e8c, #f493c3);
}

.other-hpp-card {
    background: linear-gradient(135deg, #17a2b8, #6ad7e5);
}
</style>
@stop

@section('js')
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.colVis.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script>
   // Date range picker
// Date range picker
let filterDate = $('#filterDates');
let currentTab = 'summary';
let dataTables = {};

$('.daterange').daterangepicker({
    autoUpdateInput: false,
    autoApply: true,
    alwaysShowCalendars: true,
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

$('.daterange').on('apply.daterangepicker', function(ev, picker) {
    $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
    $(this).trigger('change'); 
});

$('.daterange').on('cancel.daterangepicker', function(ev, picker) {
    $(this).val('');
    $(this).trigger('change'); 
});

filterDate.change(function () {
    reloadAllTables();
    fetchSummary();
});

function formatNumber(num) {
    return Math.round(num).toLocaleString('id-ID');
}

// Initialize DataTables for all tabs
function initializeTables() {
    // Summary table (keep as is)
    dataTables.summary = $('#summaryTable').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 25,
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel"></i> Export to Excel',
                className: 'btn btn-success',
                title: 'Financial Report Summary'
            }
        ],
        ajax: {
            url: "{{ route('lk.get') }}",
            data: function (d) {
                d.filterDates = filterDate.val();
                d.type = 'summary';
            }
        },
        columns: [
            { data: 'date', name: 'date' },
            { data: 'total_gross_revenue', name: 'total_gross_revenue' },
            { data: 'total_hpp', name: 'total_hpp' },
            { data: 'total_fee_admin', name: 'total_fee_admin' },
            { data: 'net_profit', name: 'net_profit' }
        ],
        columnDefs: [
            { 
                "targets": [1, 2, 3, 4], 
                "className": "text-right" 
            }
        ],
        order: [[0, 'desc']]
    });
    
    // Create the channel columns dynamically for the other tabs
    // Use an array where we know the exact structure
    let grossRevenueColumns = [
        { data: 'date', name: 'date' }
    ];
    
    // Loop through sales channels to build column definitions
    @foreach($salesChannels as $channel)
    grossRevenueColumns.push({ 
        data: 'channel_{{ $channel->id }}', 
        name: 'channel_{{ $channel->id }}',
        className: 'text-right',
        defaultContent: 'Rp 0'  // Provide default content if the value is missing
    });
    @endforeach
    
    grossRevenueColumns.push({ 
        data: 'total', 
        name: 'total',
        className: 'text-right font-weight-bold'
    });
    
    // Gross Revenue table with dynamic columns
    dataTables.grossRevenue = $('#grossRevenueTable').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 25,
        dom: 'Bfrtip',
        scrollX: true,
        buttons: [
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel"></i> Export to Excel',
                className: 'btn btn-success',
                title: 'Gross Revenue by Channel'
            }
        ],
        ajax: {
            url: "{{ route('lk.get') }}",
            data: function (d) {
                d.filterDates = filterDate.val();
                d.type = 'gross_revenue';
            }
        },
        columns: grossRevenueColumns,
        order: [[0, 'desc']],
        createdRow: function(row, data, dataIndex) {
            // For each cell except the first (date) and last (total)
            $(row).find('td').not(':first').not(':last').each(function(cellIndex) {
                const cellData = $(this).html();
                if (cellData !== 'Rp 0' && cellData !== '') {
                    // Add data attributes and click handler class to the cell
                    const date = data.date;
                    $(this).html(`<a href="#" class="show-gross-revenue-details" data-date="${date}" data-type="gross_revenue">${cellData}</a>`);
                }
            });
        }
    });
    
    // HPP table (similar structure)
    let hppColumns = [
        { data: 'date', name: 'date' }
    ];
    
    @foreach($salesChannels as $channel)
    hppColumns.push({ 
        data: 'channel_{{ $channel->id }}', 
        name: 'channel_{{ $channel->id }}',
        className: 'text-right',
        defaultContent: 'Rp 0'  // Provide default content
    });
    @endforeach
    
    hppColumns.push({ 
        data: 'total', 
        name: 'total',
        className: 'text-right font-weight-bold'
    });
    
    dataTables.hpp = $('#hppTable').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 25,
        dom: 'Bfrtip',
        scrollX: true,
        buttons: [
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel"></i> Export to Excel',
                className: 'btn btn-success',
                title: 'HPP by Channel'
            }
        ],
        ajax: {
            url: "{{ route('lk.get') }}",
            data: function (d) {
                d.filterDates = filterDate.val();
                d.type = 'hpp';
            }
        },
        columns: hppColumns,
        order: [[0, 'desc']],
        createdRow: function(row, data, dataIndex) {
            // For each cell except the first (date) and last (total)
            $(row).find('td').not(':first').not(':last').each(function(cellIndex) {
                const cellData = $(this).html();
                if (cellData !== 'Rp 0' && cellData !== '') {
                    // Add data attributes and click handler class to the cell
                    const date = data.date;
                    $(this).html(`<a href="#" class="show-details" data-date="${date}" data-type="hpp">${cellData}</a>`);
                }
            });
        }
    });

    // Fee Admin table (similar structure)
    let feeAdminColumns = [
        { data: 'date', name: 'date' }
    ];
    
    @foreach($salesChannels as $channel)
    feeAdminColumns.push({ 
        data: 'channel_{{ $channel->id }}', 
        name: 'channel_{{ $channel->id }}',
        className: 'text-right',
        defaultContent: 'Rp 0'  // Provide default content
    });
    @endforeach
    
    feeAdminColumns.push({ 
        data: 'total', 
        name: 'total',
        className: 'text-right font-weight-bold'
    });
    
    dataTables.feeAdmin = $('#feeAdminTable').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 25,
        dom: 'Bfrtip',
        scrollX: true,
        buttons: [
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel"></i> Export to Excel',
                className: 'btn btn-success',
                title: 'Fee Admin by Channel'
            }
        ],
        ajax: {
            url: "{{ route('lk.get') }}",
            data: function (d) {
                d.filterDates = filterDate.val();
                d.type = 'fee_admin';
            }
        },
        columns: feeAdminColumns,
        order: [[0, 'desc']]
    });
}

// Reload all tables when date filter changes
function reloadAllTables() {
    for (const key in dataTables) {
        if (dataTables.hasOwnProperty(key)) {
            dataTables[key].ajax.reload();
        }
    }
}

// Handle tab change
$('#reportTabs a').on('shown.bs.tab', function (e) {
    const tabId = $(e.target).attr('id');
    currentTab = tabId.replace('-tab', '');
    
    // Adjust datatable to correct size when tab is shown
    $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
});

// Initialize channel dataTables object to store DataTable instances
let channelDataTables = {};

// Handle detail modal
$(document).on('click', '.show-details', function(e) {
    e.preventDefault();
    
    const date = $(this).data('date');
    const type = $(this).data('type');
    
    // Special handling for HPP details with tabs
    if (type === 'hpp') {
        // Show the HPP modal with loading overlay
        $('#hppDetailModal').modal('show');
        // Loading overlay is already visible by default
        
        $.ajax({
            url: "{{ route('lk.details') }}",
            method: 'GET',
            data: {
                date: date,
                type: type
            },
            success: function(response) {
                // Hide loading overlay when data is ready
                $('#hpp-loading-overlay').hide();
                
                // Set modal title
                $('#hppDetailModalLabel').text('HPP Details - ' + date);
                
                // Clear previous tabs and content
                $('#channel-tabs').empty();
                $('#channel-tab-content').empty();
                
                // Add tabs for each channel
                let isFirst = true;
                response.channels.forEach(function(channel, index) {
                    // Create tab
                    const tabId = 'channel-tab-' + channel.id;
                    const tabClass = isFirst ? 'nav-link active' : 'nav-link';
                    const tab = `
                        <li class="nav-item">
                            <a class="${tabClass}" id="${tabId}-tab" data-toggle="pill" href="#${tabId}" 
                               role="tab" aria-controls="${tabId}" aria-selected="${isFirst ? 'true' : 'false'}">
                                ${channel.name}
                            </a>
                        </li>
                    `;
                    $('#channel-tabs').append(tab);
                    
                    // Create tab content
                    const channelData = response.data[channel.id] || [];
                    const channelSummary = response.summaries[channel.id] || { total: 0 };
                    const tabContentClass = isFirst ? 'tab-pane fade show active' : 'tab-pane fade';
                    
                    let tabContent = `
                        <div class="${tabContentClass}" id="${tabId}" role="tabpanel" aria-labelledby="${tabId}-tab">
                            <div class="channel-summary">
                                <h5>${channel.name}</h5>
                                <h5>Total: Rp ${formatNumber(channelSummary.total)}</h5>
                            </div>
                            <div class="table-responsive">
                                <table id="hpp-table-${channel.id}" class="table table-bordered table-striped table-sm" width="100%">
                                    <thead>
                                        <tr>
                                            <th>SKU</th>
                                            <th>Product</th>
                                            <th class="text-right">Quantity</th>
                                            <th class="text-right">HPP</th>
                                            <th class="text-right">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>`;
                    
                    // Add rows to the tab content
                    if (channelData.length > 0) {
                        channelData.forEach(function(item) {
                            tabContent += `
                                <tr>
                                    <td>${item.sku}</td>
                                    <td>${item.product}</td>
                                    <td class="text-right">${item.qty}</td>
                                    <td class="text-right">Rp ${formatNumber(item.hpp)}</td>
                                    <td class="text-right">Rp ${formatNumber(item.total)}</td>
                                </tr>
                            `;
                        });
                    } else {
                        tabContent += `
                            <tr>
                                <td colspan="5" class="text-center">No data available</td>
                            </tr>
                        `;
                    }
                    
                    tabContent += `
                                    </tbody>
                                    <tfoot>
                                        <tr class="font-weight-bold">
                                            <td colspan="4" class="text-right">Total</td>
                                            <td class="text-right">Rp ${formatNumber(channelSummary.total)}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    `;
                    
                    $('#channel-tab-content').append(tabContent);
                    
                    isFirst = false;
                });
                
                // Initialize DataTables for each channel
                response.channels.forEach(function(channel) {
                    if (channelDataTables[channel.id]) {
                        channelDataTables[channel.id].destroy();
                    }
                    
                    channelDataTables[channel.id] = $(`#hpp-table-${channel.id}`).DataTable({
                        paging: true,
                        lengthChange: false,
                        searching: true,
                        ordering: true,
                        info: true,
                        autoWidth: false,
                        pageLength: 10,
                        language: {
                            search: "Search SKU/Product:"
                        }
                    });
                });
            },
            error: function(xhr, status, error) {
                // Hide loading overlay on error
                $('#hpp-loading-overlay').hide();
                console.error('Error fetching HPP details:', error);
                alert('Error fetching HPP details. Please try again.');
            }
        });
    } else {
        // Standard detail modal for other types (fee_admin and net_profit)
        $.ajax({
            url: "{{ route('lk.details') }}",
            method: 'GET',
            data: {
                date: date,
                type: type
            },
            success: function(response) {
                // Set modal title based on type and date
                let modalTitle;
                switch(type) {
                    case 'fee_admin':
                        modalTitle = 'Fee Admin Details - ' + date;
                        break;
                    case 'net_profit':
                        modalTitle = 'Net Profit & HPP Percentage Details - ' + date;
                        break;
                    default:
                        modalTitle = 'Details - ' + date;
                }
                
                $('#detailModalLabel').text(modalTitle);
                
                // Clear previous table content
                $('#detailTableHead').empty();
                $('#detailTableBody').empty();
                $('#detailTableFoot').empty();
                
                // Create table header
                let headerRow = '<tr>';
                headerRow += '<th>Sales Channel</th>';
                
                if (type === 'fee_admin') {
                    headerRow += '<th class="text-right">Fee Admin</th>';
                } else if (type === 'net_profit') {
                    headerRow += '<th class="text-right">Gross Revenue</th>';
                    headerRow += '<th class="text-right">Fee Admin</th>';
                    headerRow += '<th class="text-right">Net Profit</th>';
                    headerRow += '<th class="text-right">HPP</th>';
                    headerRow += '<th class="text-right">HPP %</th>';
                }
                
                headerRow += '</tr>';
                $('#detailTableHead').append(headerRow);
                
                // Add data rows
                $.each(response.details, function(index, item) {
                    let row = '<tr>';
                    row += '<td>' + item.channel_name + '</td>';
                    
                    if (type === 'fee_admin') {
                        row += '<td class="text-right">Rp ' + formatNumber(item.fee_admin) + '</td>';
                    } else if (type === 'net_profit') {
                        row += '<td class="text-right">Rp ' + formatNumber(item.gross_revenue) + '</td>';
                        row += '<td class="text-right">Rp ' + formatNumber(item.fee_admin) + '</td>';
                        row += '<td class="text-right">Rp ' + formatNumber(item.net_profit) + '</td>';
                        row += '<td class="text-right">Rp ' + formatNumber(item.hpp) + '</td>';
                        row += '<td class="text-right">' + item.hpp_percentage.toFixed(2) + '%</td>';
                    }
                    
                    row += '</tr>';
                    $('#detailTableBody').append(row);
                });
                
                // Add footer row with totals
                let footerRow = '<tr class="font-weight-bold">';
                footerRow += '<td>Total</td>';
                
                if (type === 'fee_admin') {
                    footerRow += '<td class="text-right">Rp ' + formatNumber(response.summary.total_fee_admin) + '</td>';
                } else if (type === 'net_profit') {
                    footerRow += '<td class="text-right">Rp ' + formatNumber(response.summary.total_gross_revenue) + '</td>';
                    footerRow += '<td class="text-right">Rp ' + formatNumber(response.summary.total_fee_admin) + '</td>';
                    footerRow += '<td class="text-right">Rp ' + formatNumber(response.summary.total_net_profit) + '</td>';
                    footerRow += '<td class="text-right">Rp ' + formatNumber(response.summary.total_hpp) + '</td>';
                    footerRow += '<td class="text-right">' + response.summary.total_hpp_percentage.toFixed(2) + '%</td>';
                }
                
                footerRow += '</tr>';
                $('#detailTableFoot').append(footerRow);
                
                // Show the modal
                $('#detailModal').modal('show');
            },
            error: function(xhr, status, error) {
                console.error('Error fetching details:', error);
                alert('Error fetching details. Please try again.');
            }
        });
    }
});

// Handle gross revenue detail modal
$(document).on('click', '.show-gross-revenue-details', function(e) {
    e.preventDefault();
    
    const date = $(this).data('date');
    const type = 'gross_revenue'; // Always set to gross_revenue for this function
    
    // Show the modal with loading overlay
    $('#grossRevenueDetailModal').modal('show');
    $('#gross-revenue-loading-overlay').show();
    
    $.ajax({
        url: "{{ route('lk.gross_revenue_details') }}",
        method: 'GET',
        data: {
            date: date,
            type: type
        },
        success: function(response) {
            // Hide loading overlay when data is ready
            $('#gross-revenue-loading-overlay').hide();
            
            // Set modal title
            $('#grossRevenueDetailModalLabel').text('Gross Revenue Details - ' + date);
            
            // Clear previous tabs and content
            $('#gross-revenue-channel-tabs').empty();
            $('#gross-revenue-channel-tab-content').empty();
            
            // Add tabs for each channel
            let isFirst = true;
            response.channels.forEach(function(channel, index) {
                // Create tab
                const tabId = 'gr-channel-tab-' + channel.id;
                const tabClass = isFirst ? 'nav-link active' : 'nav-link';
                const tab = `
                    <li class="nav-item">
                        <a class="${tabClass}" id="${tabId}-tab" data-toggle="pill" href="#${tabId}" 
                           role="tab" aria-controls="${tabId}" aria-selected="${isFirst ? 'true' : 'false'}">
                            ${channel.name}
                        </a>
                    </li>
                `;
                $('#gross-revenue-channel-tabs').append(tab);
                
                // Create tab content
                const channelData = response.data[channel.id] || [];
                const channelSummary = response.summaries[channel.id] || { total: 0 };
                const tabContentClass = isFirst ? 'tab-pane fade show active' : 'tab-pane fade';
                
                let tabContent = `
                    <div class="${tabContentClass}" id="${tabId}" role="tabpanel" aria-labelledby="${tabId}-tab">
                        <div class="channel-summary">
                            <h5>${channel.name}</h5>
                            <h5>Total: Rp ${formatNumber(channelSummary.total)}</h5>
                        </div>
                        <div class="table-responsive">
                            <table id="gross-revenue-table-${channel.id}" class="table table-bordered table-striped table-sm" width="100%">
                                <thead>
                                    <tr>
                                        <th>SKU</th>
                                        <th>Product</th>
                                        <th class="text-right">Quantity</th>
                                        <th class="text-right">Gross Revenue</th>
                                        <th class="text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody>`;
                
                // Add rows to the tab content
                if (channelData.length > 0) {
                    channelData.forEach(function(item) {
                        tabContent += `
                            <tr>
                                <td>${item.sku}</td>
                                <td>${item.product}</td>
                                <td class="text-right">${item.qty}</td>
                                <td class="text-right">Rp ${formatNumber(item.gross_revenue)}</td>
                                <td class="text-right">Rp ${formatNumber(item.total)}</td>
                            </tr>
                        `;
                    });
                } else {
                    tabContent += `
                        <tr>
                            <td colspan="5" class="text-center">No data available</td>
                        </tr>
                    `;
                }
                
                tabContent += `
                                </tbody>
                                <tfoot>
                                    <tr class="font-weight-bold">
                                        <td colspan="4" class="text-right">Total</td>
                                        <td class="text-right">Rp ${formatNumber(channelSummary.total)}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                `;
                
                $('#gross-revenue-channel-tab-content').append(tabContent);
                
                isFirst = false;
            });
            
            // Initialize DataTables for each channel
            response.channels.forEach(function(channel) {
                const tableId = `#gross-revenue-table-${channel.id}`;
                if ($(tableId).length) {
                    if (channelDataTables[`gr_${channel.id}`]) {
                        channelDataTables[`gr_${channel.id}`].destroy();
                    }
                    
                    channelDataTables[`gr_${channel.id}`] = $(tableId).DataTable({
                        paging: true,
                        lengthChange: false,
                        searching: true,
                        ordering: true,
                        info: true,
                        autoWidth: false,
                        pageLength: 10,
                        language: {
                            search: "Search SKU/Product:"
                        }
                    });
                }
            });
        },
        error: function(xhr, status, error) {
            // Hide loading overlay on error
            $('#gross-revenue-loading-overlay').hide();
            console.error('Error fetching Gross Revenue details:', error);
            alert('Error fetching Gross Revenue details. Please try again.');
        }
    });
});

function fetchSummary() {
    const filterDates = document.getElementById('filterDates').value;
    const url = new URL("{{ route('lk.summary') }}");
    if (filterDates) {
        url.searchParams.append('filterDates', filterDates);
    }

    fetch(url)
        .then(response => response.json())
        .then(data => {
            // Update KPI cards
            document.getElementById('totalGrossRevenue').textContent = 'Rp ' + formatNumber(data.total_gross_revenue || 0);
            document.getElementById('totalHpp').textContent = 'Rp ' + formatNumber(data.total_hpp || 0);
            document.getElementById('totalFeeAdmin').textContent = 'Rp ' + formatNumber(data.total_fee_admin || 0);
            document.getElementById('netProfit').textContent = 'Rp ' + formatNumber(data.net_profit || 0);
            
            // Update channel summary cards
            updateChannelRevenueCards(data.channel_summary);
            updateChannelHppCards(data.channel_summary);
        })
        .catch(error => console.error('Error:', error));
}

function updateChannelRevenueCards(channelSummary) {
    const container = document.getElementById('channelRevenueCards');
    container.innerHTML = ''; // Clear previous cards
    
    // Create a card for each channel
    channelSummary.forEach(channel => {
        const channelName = channel.channel_name.toLowerCase();
        let cardClass = 'other-card';
        let logoClass = 'fa-shopping-bag';
        
        // Determine the card class and logo based on channel name
        if (channelName.includes('shopee') && !channelName.includes('2') && !channelName.includes('3')) {
            cardClass = 'shopee-card';
            logoClass = 'fa-shopping-bag';
        } else if (channelName.includes('shopee 2') || channelName.includes('shopee2')) {
            cardClass = 'shopee-2-card';
            logoClass = 'fa-shopping-bag';
        } else if (channelName.includes('shopee 3') || channelName.includes('shopee3')) {
            cardClass = 'shopee-3-card';
            logoClass = 'fa-shopping-bag';
        } else if (channelName.includes('lazada')) {
            cardClass = 'lazada-card';
            logoClass = 'fa-box';
        } else if (channelName.includes('tokopedia')) {
            cardClass = 'tokopedia-card';
            logoClass = 'fa-store';
        } else if (channelName.includes('tiktok')) {
            cardClass = 'tiktok-card';
            logoClass = 'fa-music';
        } else if (channelName === 'b2b') {
            cardClass = 'b2b-card';
            logoClass = 'fa-handshake';
        } else if (channelName === 'crm') {
            cardClass = 'crm-card';
            logoClass = 'fa-users';
        }
        
        const card = `
        <div class="col-md-3 col-sm-6">
            <div class="marketplace-card ${cardClass}">
                <div class="inner">
                    <h5>Rp ${formatNumber(channel.channel_gross_revenue)}</h5>
                    <p>${channel.channel_name}</p>
                    <div class="logo">
                        <i class="fas ${logoClass}"></i>
                    </div>
                </div>
            </div>
        </div>
        `;
        
        container.innerHTML += card;
    });
}

function updateChannelHppCards(channelSummary) {
    const container = document.getElementById('channelHppCards');
    container.innerHTML = ''; // Clear previous cards
    
    // Create a card for each channel
    channelSummary.forEach(channel => {
        const channelName = channel.channel_name.toLowerCase();
        let cardClass = 'other-card';
        let logoClass = 'fa-shopping-bag';
        
        // Determine the card class and logo based on channel name
        // Using different color scheme for HPP cards
        if (channelName.includes('shopee') && !channelName.includes('2') && !channelName.includes('3')) {
            cardClass = 'shopee-hpp-card';
            logoClass = 'fa-shopping-bag';
        } else if (channelName.includes('shopee 2') || channelName.includes('shopee2')) {
            cardClass = 'shopee-2-hpp-card';
            logoClass = 'fa-shopping-bag';
        } else if (channelName.includes('shopee 3') || channelName.includes('shopee3')) {
            cardClass = 'shopee-3-hpp-card';
            logoClass = 'fa-shopping-bag';
        } else if (channelName.includes('lazada')) {
            cardClass = 'lazada-hpp-card';
            logoClass = 'fa-box';
        } else if (channelName.includes('tokopedia')) {
            cardClass = 'tokopedia-hpp-card';
            logoClass = 'fa-store';
        } else if (channelName.includes('tiktok')) {
            cardClass = 'tiktok-hpp-card';
            logoClass = 'fa-music';
        } else if (channelName === 'b2b') {
            cardClass = 'b2b-hpp-card';
            logoClass = 'fa-handshake';
        } else if (channelName === 'crm') {
            cardClass = 'crm-hpp-card';
            logoClass = 'fa-users';
        }
        
        const card = `
        <div class="col-md-3 col-sm-6">
            <div class="marketplace-card ${cardClass}">
                <div class="inner">
                    <h5>Rp ${formatNumber(channel.channel_hpp)}</h5>
                    <p>${channel.channel_name} (${channel.channel_hpp_percentage.toFixed(1)}%)</p>
                    <div class="logo">
                        <i class="fas ${logoClass}"></i>
                    </div>
                </div>
            </div>
        </div>
        `;
        
        container.innerHTML += card;
    });
}

// Initialize all tables and load data
$(document).ready(function() {
    initializeTables();
    fetchSummary();
});
</script>
@stop