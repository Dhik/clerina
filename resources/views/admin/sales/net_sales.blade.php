@extends('adminlte::page')

@section('title', trans('labels.sales'))

@section('content_header')
    <h1>Net Profit</h1>
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
                                    <input type="text" id="filterDates" class="form-control daterange" placeholder="DD/MM/YYYY - DD/MM/YYYY">
                                </div>
                                <!-- <div class="col-auto">
                                    <button class="btn btn-default" id="resetFilterBtn">{{ trans('buttons.reset_filter') }}</button>
                                </div> -->
                                <div class="col-auto">
                                    <button class="btn btn-primary" id="refreshDataBtn">
                                        <i class="fas fa-sync-alt"></i> Refresh Data
                                    </button>
                                </div>
                                <div class="col-auto">
                                    <a href="{{ route('operational-spent.index') }}" class="btn btn-success">
                                        <i class="fas fa-cog"></i> Set Operational Spent
                                    </a>
                                </div>
                                <!-- <div class="col-auto">
                                    <button class="btn bg-info" id="importDataBtn">
                                        <i class="fas fa-sync-alt"></i> Import Data
                                    </button>
                                </div> -->
                                <!-- <div class="col-auto">
                                    <button class="btn bg-info" id="importShopeeBtn">
                                        <i class="fas fa-sync-alt"></i> Import Shopee
                                    </button>
                                </div>
                                <div class="col-auto">
                                    <button class="btn bg-info" id="importTiktokBtn">
                                        <i class="fas fa-sync-alt"></i> Import Tiktok
                                    </button>
                                </div>
                                <div class="col-auto">
                                    <button class="btn bg-info" id="importLazadaBtn">
                                        <i class="fas fa-sync-alt"></i> Import Lazada
                                    </button>
                                </div>
                                <div class="col-auto">
                                    <button class="btn bg-info" id="importTokopediaBtn">
                                        <i class="fas fa-sync-alt"></i> Import Tokopedia
                                    </button>
                                </div> -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-3">
                    <div class="small-box bg-gradient-success">
                        <div class="inner">
                            <h4 id="totalSales">Rp 0</h4>
                            <p>Total Sales</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                    </div>
                </div>
                <div class="col-3">
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
                <div class="col-3">
                    <div class="small-box bg-gradient-info">
                        <div class="inner">
                            <h4 id="totalSpent">Rp 0</h4>
                            <p>Total Spent</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-money-bill"></i>
                        </div>
                    </div>
                </div>
                <div class="col-3">
                    <div class="small-box bg-gradient-teal">
                        <div class="inner">
                            <h4 id="totalNetProfit">Rp 0</h4>
                            <p>Total Net Profit</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                </div>
                <div class="col-3">
                    <div class="small-box bg-gradient-info">
                        <div class="inner">
                            <h4 id="totalMarketingSpent">Rp 0</h4>
                            <p>Total Marketing Spent</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
                </div>
                <div class="col-3">
                    <div class="small-box bg-gradient-teal">
                        <div class="inner">
                            <h4 id="avgROMI">0</h4>
                            <p>Average ROMI</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                </div>
            </div>

            @include('admin.sales.net-recap-card')

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="netProfitsTable" class="table table-bordered table-striped dataTable" width="100%">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Visit</th>
                                    <th>Qty</th>
                                    <th>Order</th>
                                    <th>Closing Rate</th>
                                    <th>ROAS</th>
                                    <th>Total Sales</th>
                                    <th>MP Sales</th>
                                    <th>B2B Sales</th>
                                    <th>CRM Sales</th>
                                    <th>Penjualan Bersih (85%)</th>
                                    <th>Ads Spent</th>
                                    <th>KOL</th>
                                    <th>ROMI</th>
                                    <th>Affiliate</th>
                                    <th>Social Media Ads</th>
                                    <th>Marketplace Ads</th>
                                    <th>Operational</th>
                                    <th>HPP</th>
                                    <th>Fee Packing</th>
                                    <th>Fee Admin (16%)</th>
                                    <th>PPN (3%)</th>
                                    <th>Fee Ads (2%)</th>
                                    <th>Total Marketing</th>
                                    <th>Net Profit</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="adSpentDetailModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="adSpentDetailModalTitle">Ads Spent Detail</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table id="adSpentDetailTable" class="table table-bordered table-striped" width="100%">
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="kolDetailModal">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">KOL Detail</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Talent</th>
                            <th>Username</th>
                            <th>Platform</th>
                            <th>Followers</th>
                            <th>Product</th>
                            <th>Rate</th>
                            <th>Link</th>
                        </tr>
                    </thead>
                    <tbody id="kolDetailContent"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="salesDetailModal">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Sales by Channel</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Sales Channel</th>
                            <th class="text-right">Number of Orders</th>
                            <th class="text-right">Total Sales</th>
                        </tr>
                    </thead>
                    <tbody id="salesDetailContent"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="hppDetailModal">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">HPP Detail</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-8">
                        <h6>Sales Channel Summary</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Sales Channel</th>
                                        <th>Quantity</th>
                                        <th>Total HPP</th>
                                    </tr>
                                </thead>
                                <tbody id="hppChannelContent"></tbody>
                                <tfoot>
                                    <tr class="font-weight-bold">
                                        <td>Total</td>
                                        <td id="totalQuantity" class="text-right"></td>
                                        <td id="totalHpp" class="text-right"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div id="salesChannelPieChart" style="height: 250px;"></div>
                    </div>
                </div>
                <div class="row mt-4">
                    <div class="col-md-12">
                        <h6>Product Details</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>SKU</th>
                                        <th>Product</th>
                                        <th>Quantity</th>
                                        <th>HPP/Unit</th>
                                        <th>Total HPP</th>
                                    </tr>
                                </thead>
                                <tbody id="hppProductContent"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@stop

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
<style>
    #salesPieChart {
        height: 400px !important;
        width: 100% !important;
    }
    .modal-content {
        border-radius: 8px;
    }

    .modal-header {
        border-top-left-radius: 8px;
        border-top-right-radius: 8px;
        border-bottom: 1px solid #dee2e6;
    }

    .table th, .table td {
        padding: 12px;
        vertical-align: middle;
    }

    .table tbody tr:hover {
        background-color: #f8f9fa;
    }

    #salesDetailTable td {
        border-top: 1px solid #dee2e6;
    }

    .chart-container {
        position: relative;
        height: 400px;
        width: 100%;
    }
    .dataTables_wrapper {
        overflow-x: auto;
        width: 100%;
    }

    #netProfitsTable {
        width: 100% !important;
        white-space: nowrap;
    }

    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .dt-button-collection {
        padding: 8px !important;
    }
    
    .dt-button-collection .dt-button {
        margin: 2px !important;
    }
    
    .dt-button.buttons-columnVisibility {
        display: block;
        padding: 8px;
        margin: 2px;
        text-align: left;
    }
    
    .dt-button.buttons-columnVisibility.active {
        background: #e9ecef;
    }
</style>
@stop

@section('js')
    <script src="https://cdn.plot.ly/plotly-latest.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.colVis.min.js"></script>
    <script>
        filterDate = $('#filterDates');
        filterChannel = $('#filterChannel');
        $('#resetFilterBtn').click(function () {
            filterDate.val('')
            netProfitsTable.draw()
        })
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
            netProfitsTable.draw();
            fetchSummary();
            renderWaterfallChart();
            loadNetProfitsChart();
            loadCorrelationChart();
        });
        function showAdSpentDetail(date) {
            // Open modal
            $('#adSpentDetailModal').modal('show');
            $('#adSpentDetailModalTitle').text('Ads Spent Detail - ' + date);
            
            // Clear existing data
            if ($.fn.DataTable.isDataTable('#adSpentDetailTable')) {
                $('#adSpentDetailTable').DataTable().destroy();
            }
            
            // Initialize datatable
            $('#adSpentDetailTable').DataTable({
                processing: true,
                serverSide: false, // We'll load all data at once for simplicity
                ajax: {
                    url: "{{ route('net-profit.get_ad_spent_detail') }}",
                    data: { date: date }
                },
                columns: [
                    { data: 'name', title: 'Channel/Platform' },
                    { 
                        data: 'amount', 
                        title: 'Amount',
                        render: function(data) {
                            return 'Rp ' + Math.round(data).toLocaleString('id-ID');
                        }
                    }
                ],
                columnDefs: [
                    { "targets": [1], "className": "text-right" }
                ]
            });
        }

        function showKolDetail(date) {
            $('#kolDetailModal').modal('show');
            $.get("{{ route('talent-content.getByDate') }}", { date: date }, function(data) {
                let html = '';
                data.forEach(function(item) {
                    html += `<tr>
                        <td>${item.talent_name || '-'}</td>
                        <td>${item.username || '-'}</td>
                        <td>${item.platform || '-'}</td>
                        <td>${item.followers ? item.followers.toLocaleString('id-ID') : '-'}</td>
                        <td>${item.product || '-'}</td>
                        <td>Rp ${Math.round(item.rate).toLocaleString('id-ID')}</td>
                        <td><a href="${item.upload_link}" target="_blank">View</a></td>
                    </tr>`;
                });
                $('#kolDetailContent').html(html);
            });
        }

        // $('#importDataBtn').on('click', function() {
        //     Swal.fire({
        //         title: 'Importing Data',
        //         html: 'Starting import process...',
        //         allowOutsideClick: false,
        //         didOpen: () => {
        //             Swal.showLoading();
        //         }
        //     });

        //     const endpoints = [
        //         { 
        //             name: 'Cleora Import Data', 
        //             url: "{{ route('net-profit.import-data') }}"
        //         },
        //         { 
        //             name: 'Azrina Import Data', 
        //             url: "{{ route('net-profit.import-data-azrina') }}"
        //         }
        //     ];

        //     let completedEndpoints = 0;
        //     let failedEndpoints = [];
        //     let currentIndex = 0;

        //     function processNextEndpoint() {
        //         if (currentIndex >= endpoints.length) {
        //             // All endpoints processed
        //             if (failedEndpoints.length > 0) {
        //                 Swal.fire({
        //                     icon: 'warning',
        //                     title: 'Import Completed with Warnings',
        //                     html: `Completed: ${completedEndpoints}/${endpoints.length}<br>Failed: ${failedEndpoints.join(', ')}`,
        //                     confirmButtonText: 'OK'
        //                 });
        //             } else {
        //                 Swal.fire({
        //                     icon: 'success',
        //                     title: 'Data Imported Successfully!',
        //                     text: 'All data has been imported and updated.',
        //                     timer: 2000,
        //                     showConfirmButton: false
        //                 });
        //             }
                    
        //             // Reload the table to show updated data
        //             netProfitsTable.draw();
                    
        //             return;
        //         }

        //         const endpoint = endpoints[currentIndex];
        //         Swal.update({
        //             html: `${endpoint.name}... (${currentIndex + 1}/${endpoints.length})`
        //         });

        //         $.ajax({
        //             url: endpoint.url,
        //             method: 'GET',
        //             success: function(response) {
        //                 completedEndpoints++;
        //                 currentIndex++;
        //                 processNextEndpoint();
        //             },
        //             error: function(xhr, status, error) {
        //                 failedEndpoints.push(endpoint.name);
        //                 currentIndex++;
        //                 console.error(`Failed at ${endpoint.name}:`, error);
        //                 processNextEndpoint();
        //             }
        //         });
        //     }
            
        //     processNextEndpoint();
        // });

        function refreshData() {
            Swal.fire({
                title: 'Refreshing Data',
                html: 'Starting refresh process...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            const endpoints = [
                { 
                    name: 'KOL Spending', 
                    url: "{{ route('net-profit.update-spent-kol') }}"
                },
                { 
                    name: 'Azrina KOL Spending', 
                    url: "{{ route('net-profit.update-spent-kol-azrina') }}"
                },
                { 
                    name: 'HPP', 
                    url: "{{ route('net-profit.update-hpp') }}"
                },
                { 
                    name: 'HPP Azrina', 
                    url: "{{ route('net-profit.update-hpp-azrina') }}"
                },
                { 
                    name: 'Marketing', 
                    url: "{{ route('net-profit.update-marketing') }}"
                },
                { 
                    name: 'Marketing Azrina', 
                    url: "{{ route('net-profit.update-marketing-azrina') }}"
                },
                { 
                    name: 'Visit', 
                    url: "{{ route('net-profit.update-visit') }}"
                },
                { 
                    name: 'Visit Azrina', 
                    url: "{{ route('net-profit.update-visit-azrina') }}"
                },
                { 
                    name: 'Sales', 
                    url: "{{ route('net-profit.update-sales') }}"
                },
                { 
                    name: 'Sales Azrina', 
                    url: "{{ route('net-profit.update-sales-azrina') }}"
                },
                { 
                    name: 'ROAS', 
                    url: "{{ route('net-profit.update-roas') }}"
                },
                { 
                    name: 'ROAS Azrina', 
                    url: "{{ route('net-profit.update-roas-azrina') }}"
                },
                { 
                    name: 'Quantity', 
                    url: "{{ route('net-profit.update-qty') }}"
                },
                { 
                    name: 'Quantity Azrina', 
                    url: "{{ route('net-profit.update-qty-azrina') }}"
                },
                { 
                    name: 'Order Count', 
                    url: "{{ route('net-profit.update-order-count') }}"
                },
                { 
                    name: 'Order Count Azrina', 
                    url: "{{ route('net-profit.update-order-count-azrina') }}"
                },
                { 
                    name: 'B2B and CRM Sales', 
                    url: "{{ route('net-profit.update-b2b-crm') }}"
                },
                { 
                    name: 'B2B and CRM Sales Azrina', 
                    url: "{{ route('net-profit.update-b2b-crm-azrina') }}"
                },
                { 
                    name: 'Cleora Import Data', 
                    url: "{{ route('net-profit.import-data') }}"
                },
                { 
                    name: 'Azrina Import Data', 
                    url: "{{ route('net-profit.import-data-azrina') }}"
                }
            ];

            let completedEndpoints = 0;
            let failedEndpoints = [];
            let currentIndex = 0;

            function processNextEndpoint() {
                if (currentIndex >= endpoints.length) {
                    // All endpoints processed
                    if (failedEndpoints.length > 0) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Refresh Completed with Warnings',
                            html: `Completed: ${completedEndpoints}/${endpoints.length}<br>Failed: ${failedEndpoints.join(', ')}`,
                            confirmButtonText: 'OK'
                        });
                    } else {
                        Swal.fire({
                            icon: 'success',
                            title: 'Data Refreshed Successfully!',
                            html: `All ${endpoints.length} operations completed successfully!`,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                    
                    // Reload the table to show updated data
                    table.ajax.reload();
                    
                    return;
                }

                const endpoint = endpoints[currentIndex];
                Swal.update({
                    html: `${endpoint.name}... (${currentIndex + 1}/${endpoints.length})`
                });

                $.ajax({
                    url: endpoint.url,
                    method: 'GET',
                    success: function(response) {
                        completedEndpoints++;
                        currentIndex++;
                        processNextEndpoint();
                    },
                    error: function(xhr, status, error) {
                        failedEndpoints.push(endpoint.name);
                        currentIndex++;
                        console.error(`Failed at ${endpoint.name}:`, error);
                        processNextEndpoint();
                    }
                });
            }
            
            processNextEndpoint();
        }

        $('#refreshDataBtn').click(refreshData);


            let netProfitsTable = $('#netProfitsTable').DataTable({
                scrollX: true,
                responsive: false,
                processing: true,
                serverSide: true,
                pageLength: 25,
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'colvis',
                        text: 'Show/Hide Columns',
                        className: 'btn btn-secondary'
                    }
                ],
                ajax: {
                    url: "{{ route('sales.get_net_sales') }}",
                    data: function (d) {
                        d.filterDates = filterDate.val()
                    }
                },
                columns: [
                    {
                        data: 'date', 
                        name: 'date',
                        visible: true
                    },
                    {
                        data: 'visit',
                        render: function(data) {
                            return Math.round(data || 0).toLocaleString('id-ID');
                        },
                        visible: false 
                    },
                    {
                        data: 'qty',
                        render: function(data) {
                            return Math.round(data || 0).toLocaleString('id-ID');
                        },
                        visible: false  // Hidden by default
                    },
                    {
                        data: 'order',
                        render: function(data) {
                            return Math.round(data || 0).toLocaleString('id-ID');
                        },
                        visible: false  // Hidden by default
                    },
                    {
                        data: 'closing_rate',
                        render: function(data) {
                            const value = parseFloat(data) || 0;
                            return value.toFixed(2) + '%';
                        },
                        visible: false  // Hidden by default
                    },
                    {
                        data: 'roas',
                        render: function(data) {
                            const value = parseFloat(data) || 0;
                            return value.toFixed(2);
                        },
                        visible: false  // Hidden by default
                    },
                    {
                        data: 'total_sales',
                        render: function(data, type, row) {
                            return '<a href="#" onclick="showSalesDetail(\'' + row.date + '\')" class="text-primary">' + 
                                'Rp ' + Math.round(data).toLocaleString('id-ID') + '</a>';
                        },
                        visible: true
                    },
                    {
                        data: 'mp_sales',
                        render: function(data) {
                            return '<span class="text-success">Rp ' + Math.round(data).toLocaleString('id-ID') + '</span>';
                        },
                        visible: false
                    },
                    {
                        data: 'b2b_sales',
                        render: function(data) {
                            return '<span class="text-success">Rp ' + Math.round(data).toLocaleString('id-ID') + '</span>';
                        },
                        visible: false
                    },
                    {
                        data: 'crm_sales',
                        render: function(data) {
                            return '<span class="text-success">Rp ' + Math.round(data).toLocaleString('id-ID') + '</span>';
                        },
                        visible: false
                    },
                    {
                        data: 'penjualan_bersih',
                        render: function(data) {
                            return '<span class="text-info">Rp ' + Math.round(data).toLocaleString('id-ID') + '</span>';
                        },
                        visible: false // Set to true to show by default
                    },
                    {
                        data: 'marketing',
                        name: 'marketing',
                        render: function(data, type, row) {
                            return '<a href="#" onclick="showAdSpentDetail(\'' + row.date + '\')" class="text-primary">' + 
                                'Rp ' + Math.round(data).toLocaleString('id-ID') + '</a>';
                        },
                        visible: true
                    },
                    {
                        data: 'spent_kol',
                        render: function(data, type, row) {
                            return '<a href="#" onclick="showKolDetail(\'' + row.date + '\')" class="text-primary">' + 
                                'Rp ' + Math.round(data).toLocaleString('id-ID') + '</a>';
                        },
                        visible: true  // Visible by default
                    },
                    {
                        data: 'romi',
                        render: function(data) {
                            const value = parseFloat(data) || 0;
                            return value.toFixed(2);
                        },
                        visible: false  // Hidden by default, or set to true if you want it visible
                    },
                    {
                        data: 'affiliate',
                        render: function(data) {
                            return 'Rp ' + Math.round(data || 0).toLocaleString('id-ID');
                        },
                        visible: true  // Visible by default
                    },
                    {
                        data: 'ad_spent_social_media',
                        render: function(data) {
                            return 'Rp ' + Math.round(data || 0).toLocaleString('id-ID');
                        },
                        visible: false  // Hidden by default
                    },
                    {
                        data: 'ad_spent_market_place',
                        render: function(data) {
                            return 'Rp ' + Math.round(data || 0).toLocaleString('id-ID');
                        },
                        visible: false  // Hidden by default
                    },
                    {
                        data: 'operasional',
                        render: function(data) {
                            return 'Rp ' + Math.round(data).toLocaleString('id-ID');
                        },
                        visible: true  // Visible by default
                    },
                    {
                        data: 'hpp',
                        render: function(data, type, row) {
                            return '<a href="#" onclick="showHppDetail(\'' + row.date + '\')" class="text-primary">' + 
                                'Rp ' + Math.round(data).toLocaleString('id-ID') + '</a>';
                        },
                        visible: true  // Visible by default
                    },
                    {
                        data: 'fee_packing',
                        render: function(data) {
                            return 'Rp ' + Math.round(data || 0).toLocaleString('id-ID');
                        },
                        visible: false  // Hidden by default
                    },
                    {
                        data: 'estimasi_fee_admin',
                        render: function(data) {
                            return 'Rp ' + Math.round(data || 0).toLocaleString('id-ID');
                        },
                        visible: false  // Hidden by default
                    },
                    {
                        data: 'ppn',
                        render: function(data) {
                            return 'Rp ' + Math.round(data || 0).toLocaleString('id-ID');
                        },
                        visible: false  // Hidden by default
                    },
                    {
                        data: 'fee_ads',
                        render: function(data) {
                            return 'Rp ' + Math.round(data || 0).toLocaleString('id-ID');
                        },
                        visible: false  // Hidden by default
                    },
                    {
                        data: 'total_marketing_spend',
                        render: function(data) {
                            return 'Rp ' + Math.round(data || 0).toLocaleString('id-ID');
                        },
                        visible: true  // Hidden by default
                    },
                    {
                        data: 'net_profit',
                        render: function(data) {
                            const isPositive = data >= 0;
                            const arrowIcon = isPositive ? '↑' : '↓';
                            const colorClass = isPositive ? 'text-success' : 'text-danger';
                            return `<div class="${colorClass}">
                                ${arrowIcon} Rp ${Math.round(data).toLocaleString('id-ID')}
                            </div>`;
                        },
                        visible: true  // Visible by default
                    }
                ],
                columnDefs: [
                    { "targets": [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19], "className": "text-right" }
                ],
                order: [[0, 'asc']]
            });

            function fetchSummary() {
                const filterDates = document.getElementById('filterDates').value;
                const url = new URL("{{ route('sales.get_net_sales_summary') }}");
                if (filterDates) {
                    url.searchParams.append('filterDates', filterDates);
                }

                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('totalSales').textContent = 'Rp ' + Math.round(data.total_sales).toLocaleString('id-ID');
                        document.getElementById('totalHpp').textContent = 'Rp ' + Math.round(data.total_hpp).toLocaleString('id-ID');
                        document.getElementById('totalSpent').textContent = 'Rp ' + Math.round(data.total_spent).toLocaleString('id-ID');
                        document.getElementById('totalNetProfit').textContent = 'Rp ' + Math.round(data.total_net_profit).toLocaleString('id-ID');
                        document.getElementById('totalMarketingSpent').textContent = 'Rp ' + Math.round(data.total_marketing_spent).toLocaleString('id-ID');
                        document.getElementById('avgROMI').textContent = Number(data.avg_romi).toLocaleString('id-ID', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                    })
                    .catch(error => console.error('Error:', error));
            }
        fetchSummary();

        $('#totalSpentCard').click(function() {
            const campaignExpense = $('#newCampaignExpense').text().trim();
            const adsSpentTotal = $('#newAdsSpentTotal').text().trim();
            const totalSpent = $('#newAdSpentCount').text().trim();
            console.log(campaignExpense);
            console.log(adsSpentTotal);
            console.log(totalSpent);

            // Update modal content
            $('#modalCampaignExpense').text('Campaign Expense: ' + campaignExpense);
            $('#modalAdsSpentTotal').text('Total Ads Spent: ' + adsSpentTotal);
            $('#modalTotalSpent').text('Total Spent: ' + totalSpent);

            // Show the modal
            $('#detailSpentModal').modal('show');
        });

        let salesPieChart = null;

        $('#totalSalesCard').click(function() {
            $('#detailSalesModal').modal('show');
        });

        function showHppDetail(date) {
            $('#hppDetailModal').modal('show');
            
            $.get("{{ route('net-profit.getHppByDate') }}", { date: date }, function(data) {
                // Product details table
                let productHtml = '';
                let totalProductQuantity = 0;
                
                data.productDetails.forEach(function(item) {
                    let total = item.quantity * item.harga_satuan;
                    productHtml += `<tr>
                        <td>${item.sku}</td>
                        <td>${item.product}</td>
                        <td class="text-right">${item.quantity.toLocaleString('id-ID')}</td>
                        <td class="text-right">Rp ${Math.round(item.harga_satuan).toLocaleString('id-ID')}</td>
                        <td class="text-right">Rp ${Math.round(total).toLocaleString('id-ID')}</td>
                    </tr>`;
                    totalProductQuantity += parseInt(item.quantity);
                });
                
                $('#hppProductContent').html(productHtml);
                
                // Sales channel table
                let channelHtml = '';
                let totalChannelQuantity = 0;
                
                data.channelDetails.forEach(function(item) {
                    channelHtml += `<tr>
                        <td>${item.channel_name || 'Unknown'}</td>
                        <td class="text-right">${item.quantity.toLocaleString('id-ID')}</td>
                        <td class="text-right">Rp ${Math.round(item.total_hpp).toLocaleString('id-ID')}</td>
                    </tr>`;
                    totalChannelQuantity += parseInt(item.quantity);
                });
                
                $('#hppChannelContent').html(channelHtml);
                
                // Set totals
                $('#totalQuantity').text(totalChannelQuantity.toLocaleString('id-ID'));
                $('#totalHpp').text('Rp ' + Math.round(data.totalHpp).toLocaleString('id-ID'));
                
                // Generate pie chart
                generateSalesChannelPieChart(data.channelDetails);
            });
        }

        function generateSalesChannelPieChart(channelData) {
            // Prepare data for the pie chart
            const labels = channelData.map(item => item.channel_name || 'Unknown');
            const values = channelData.map(item => parseFloat(item.total_hpp));
            
            // Generate random colors
            const backgroundColors = generateRandomColors(channelData.length);
            
            // Get canvas context
            const ctx = document.getElementById('salesChannelPieChart').getContext('2d');
            
            // Destroy existing chart if it exists
            if (salesChannelPieChart) {
                salesChannelPieChart.destroy();
            }
            
            // Create new chart
            salesChannelPieChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: labels,
                    datasets: [{
                        data: values,
                        backgroundColor: backgroundColors,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                font: {
                                    size: 10
                                }
                            }
                        },
                        title: {
                            display: true,
                            text: 'HPP by Sales Channel',
                            font: {
                                size: 14
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw;
                                    const formattedValue = 'Rp ' + Math.round(value).toLocaleString('id-ID');
                                    return label + ': ' + formattedValue;
                                }
                            }
                        }
                    }
                }
            });
        }

        function generateRandomColors(count) {
            const colors = [];
            const predefinedColors = [
                '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b',
                '#5a5c69', '#6610f2', '#6f42c1', '#fd7e14', '#20c9a6'
            ];
            
            for (let i = 0; i < count; i++) {
                if (i < predefinedColors.length) {
                    colors.push(predefinedColors[i]);
                } else {
                    // Generate random colors if we need more
                    const r = Math.floor(Math.random() * 255);
                    const g = Math.floor(Math.random() * 255);
                    const b = Math.floor(Math.random() * 255);
                    colors.push(`rgba(${r}, ${g}, ${b}, 0.8)`);
                }
            }
            
            return colors;
        }

        function showSalesDetail(date) {
            // Show modal
            $('#salesDetailModal').modal('show');
            
            // Clear previous content and show loading
            $('#salesDetailContent').html(`
                <tr>
                    <td colspan="3" class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </td>
                </tr>
            `);

            // Fetch data
            $.get("{{ route('net-profit.getSalesByChannel') }}", { date: date }, function(data) {
                let html = '';
                let totalSales = 0;
                let totalOrders = 0;
                
                // Process salesByChannel data
                data.salesByChannel.forEach(function(item) {
                    totalSales += parseFloat(item.total_sales);
                    totalOrders += parseInt(item.order_count);
                    html += `<tr>
                        <td>${item.sales_channel}</td>
                        <td class="text-right">${parseInt(item.order_count).toLocaleString('id-ID')}</td>
                        <td class="text-right">Rp ${Math.round(parseFloat(item.total_sales)).toLocaleString('id-ID')}</td>
                    </tr>`;
                });
                
                // Add normal total row
                html += `<tr class="font-weight-bold">
                    <td>Total</td>
                    <td class="text-right">${totalOrders.toLocaleString('id-ID')}</td>
                    <td class="text-right">Rp ${Math.round(totalSales).toLocaleString('id-ID')}</td>
                </tr>`;
                
                // Add B2B and CRM sales rows
                html += `<tr class="bg-light">
                    <td colspan="3" class="font-weight-bold">Additional Sales</td>
                </tr>`;
                
                html += `<tr>
                    <td>B2B Sales</td>
                    <td></td>
                    <td class="text-right">Rp ${Math.round(parseFloat(data.b2b_sales || 0)).toLocaleString('id-ID')}</td>
                </tr>`;
                
                html += `<tr>
                    <td>CRM Sales</td>
                    <td></td>
                    <td class="text-right">Rp ${Math.round(parseFloat(data.crm_sales || 0)).toLocaleString('id-ID')}</td>
                </tr>`;
                
                // Calculate grand total including all sales types
                const grandTotal = totalSales + parseFloat(data.b2b_sales || 0) + parseFloat(data.crm_sales || 0);
                
                html += `<tr class="font-weight-bold bg-success text-white">
                    <td>Grand Total</td>
                    <td></td>
                    <td class="text-right">Rp ${Math.round(grandTotal).toLocaleString('id-ID')}</td>
                </tr>`;
                
                $('#salesDetailContent').html(html);
            }).fail(function() {
                // Handle error case
                $('#salesDetailContent').html(`
                    <tr>
                        <td colspan="3" class="text-center text-danger">
                            Failed to load data. Please try again.
                        </td>
                    </tr>
                `);
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

        let netProfitChart = null;

        function loadNetProfitsChart() {
            const existingChart = Chart.getChart('netProfitsChart');
            if (existingChart) {
                existingChart.destroy();
            }
            
            if (netProfitChart) {
                netProfitChart.destroy();
            }
            const filterDates = document.getElementById('filterDates').value;
            fetch(`{{ route('sales.net_sales_line') }}${filterDates ? `?filterDates=${filterDates}` : ''}`)
                .then(response => response.json())
                .then(data => {
                    const ctx = document.getElementById('netProfitsChart').getContext('2d');
                    
                    netProfitChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: data.map(item => item.date),
                            datasets: [{
                                label: 'Sales',
                                data: data.map(item => item.sales),
                                borderColor: '#4CAF50',
                                tension: 0.1,
                                fill: false
                            }, {
                                label: 'Marketing',
                                data: data.map(item => item.marketing),
                                borderColor: '#2196F3',
                                tension: 0.1,
                                fill: false
                            }, {
                                label: 'HPP',
                                data: data.map(item => item.hpp),
                                borderColor: '#FFC107',
                                tension: 0.1,
                                fill: false
                            }, {
                                label: 'Net Profit',
                                data: data.map(item => item.netProfit),
                                borderColor: '#F44336',
                                tension: 0.1,
                                fill: false
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return context.dataset.label + ': Rp ' + Math.round(context.raw).toLocaleString('id-ID');
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    grid: {
                                        zeroLineColor: '#888',
                                        zeroLineWidth: 1
                                    },
                                    ticks: {
                                        callback: function(value) {
                                            return 'Rp ' + Math.round(value).toLocaleString('id-ID');
                                        }
                                    }
                                }
                            }
                        }
                    });
                })
                .catch(error => console.error('Error:', error));
        }
        function loadCorrelationChart() {
            const filterDates = document.getElementById('filterDates').value;
            const selectedVariable = document.getElementById('correlationVariable').value;
            
            fetch(`{{ route('net-profit.sales-vs-marketing') }}?variable=${selectedVariable}${filterDates ? `&filterDates=${filterDates}` : ''}`)
                .then(response => response.json())
                .then(result => {
                    if (result.data && result.layout) {
                        Plotly.newPlot('correlationChart', result.data, result.layout, {
                            responsive: true,
                            displayModeBar: true
                        });
                    }

                    if (result.statistics) {
                        document.getElementById('correlationCoefficient').textContent = 
                            (result.statistics.correlation || 0).toFixed(4);
                        document.getElementById('rSquared').textContent = 
                            (result.statistics.r_squared || 0).toFixed(4);
                        document.getElementById('dataPoints').textContent = 
                            result.statistics.data_points || 0;
                    } else {
                        document.getElementById('correlationCoefficient').textContent = '0.0000';
                        document.getElementById('rSquared').textContent = '0.0000';
                        document.getElementById('dataPoints').textContent = '0';
                    }
                })
                .catch(error => {
                    console.error('Error fetching correlation data:', error);

                    document.getElementById('correlationCoefficient').textContent = '0.0000';
                    document.getElementById('rSquared').textContent = '0.0000';
                    document.getElementById('dataPoints').textContent = '0';
                    
                    if (document.getElementById('correlationChart')) {
                        Plotly.purge('correlationChart');
                    }
                });
        }
        loadNetProfitsChart();
        loadCorrelationChart();

        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            if (e.target.getAttribute('href') === '#recapChartTab') {
                renderWaterfallChart();
            } else if (e.target.getAttribute('href') === '#netProfitsTab') {
                loadNetProfitsChart();
            } else if (e.target.getAttribute('href') === '#correlationTab') {
                loadCorrelationChart();
            }
        });

        document.getElementById('correlationVariable').addEventListener('change', loadCorrelationChart);
    </script>
@stop
