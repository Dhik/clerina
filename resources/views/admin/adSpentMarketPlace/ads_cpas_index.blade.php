@extends('adminlte::page')

@section('title', 'Ads CPAS Monitor')

@section('content_header')
    <h1>Ads CPAS Monitor</h1>
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
                                    <select class="form-control" id="kategoriProdukFilter">
                                        <option value="">All Categories</option>
                                        @foreach($kategoriProdukList as $kategori)
                                            <option value="{{ $kategori }}">{{ $kategori }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-auto">
                                    <select class="form-control" id="picFilter">
                                        <option value="">All PIC</option>
                                        @foreach($picList as $pic)
                                            <option value="{{ $pic }}">{{ $pic }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-auto">
                                    <button class="btn btn-default" id="resetFilterBtn">{{ trans('buttons.reset_filter') }}</button>
                                </div>
                                <div class="col-auto">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#importMetaAdsSpentModal" id="btnImportMetaAdsSpent">
                                            <i class="fas fa-file-upload"></i> Import Meta Ads Spent (csv or zip)
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                        <div class="row">
                                        <div class="col-md-6">
                                            <div class="card h-100">
                                                <div class="card-header">
                                                    <h5 class="card-title mb-0">Impressions Over Time</h5>
                                                </div>
                                                <div class="card-body">
                                                    <canvas id="impressionChart" width="400" height="300"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card h-100">
                                                <div class="card-header">
                                                    <h5 class="card-title mb-0">Funnel Analysis</h5>
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
            <div class="card">
                <div class="card-body">
                <table id="adsMetaTable" class="table table-bordered table-striped dataTable responsive" width="100%">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Total Spent</th>
                            <th>View Content</th>
                            <th>ATC</th>
                            <th>Purchase</th>
                            <th>CPP</th>
                            <th>Conversion Value</th>
                            <th>ROAS</th>
                            <th>Impression</th>
                            <th>CPM</th>
                            <th>Link Clicks</th>
                            <th>CTR</th>
                            <th>Performance</th>
                        </tr>
                    </thead>
                </table>
                </div>
            </div>
        </div>
    </div>
    @include('admin.adSpentMarketPlace.adds_meta')
    <div class="modal fade" id="detailSalesModal" tabindex="-1" role="dialog" aria-labelledby="detailSalesModalLabel" aria-hidden="true">
</div>

<div class="modal fade" id="dailyDetailsModal" tabindex="-1" role="dialog" aria-labelledby="dailyDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog" style="max-width: 95%; width: 95%;" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dailyDetailsModalLabel">Campaign Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Filter Section -->
                <div class="row mb-3">
                    <div class="col-md-4 offset-md-8">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                            </div>
                            <input type="text" id="modalFilterDates" class="form-control rangeDate" placeholder="DD/MM/YYYY - DD/MM/YYYY">
                        </div>
                    </div>
                </div>

                <!-- Summary Section -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- New Funnel Stage KPI Cards -->
                            <div class="col-4">
                                <div class="small-box bg-gradient-warning">
                                    <div class="inner">
                                        <h4 id="summaryTofuSpent">-</h4>
                                        <p>TOFU Spent <span id="summaryTofuPercentage" class="badge badge-light">-%</span></p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-funnel-dollar"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="small-box bg-gradient-warning">
                                    <div class="inner">
                                        <h4 id="summaryMofuSpent">-</h4>
                                        <p>MOFU Spent <span id="summaryMofuPercentage" class="badge badge-light">-%</span></p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-funnel-dollar"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="small-box bg-gradient-warning">
                                    <div class="inner">
                                        <h4 id="summaryBofuSpent">-</h4>
                                        <p>BOFU Spent <span id="summaryBofuPercentage" class="badge badge-light">-%</span></p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-funnel-dollar"></i>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Existing KPI Cards -->
                            <div class="col-3">
                                <div class="small-box bg-gradient-success">
                                    <div class="inner">
                                        <h4 id="summaryAccountsCount">-</h4>
                                        <p>Total Accounts</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-users"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="small-box bg-gradient-primary">
                                    <div class="inner">
                                        <h4 id="summaryTotalSpent">-</h4>
                                        <p>Total Spent</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-money-bill"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="small-box bg-gradient-info">
                                    <div class="inner">
                                        <h4 id="summaryTotalPurchases">-</h4>
                                        <p>Total Purchases</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-shopping-cart"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="small-box bg-gradient-teal">
                                    <div class="inner">
                                        <h4 id="summaryConversionValue">-</h4>
                                        <p>Conversion Value</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-chart-line"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="small-box bg-gradient-info">
                                    <div class="inner">
                                        <h4 id="summaryRoas">-</h4>
                                        <p>Overall ROAS</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-dollar-sign"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="small-box bg-gradient-teal">
                                    <div class="inner">
                                        <h4 id="summaryCostPerPurchase">-</h4>
                                        <p>Avg. CPP</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-tags"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="small-box bg-gradient-primary">
                                    <div class="inner">
                                        <h4 id="summaryImpressions">-</h4>
                                        <p>Total Impressions</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-eye"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="small-box bg-gradient-success">
                                    <div class="inner">
                                        <h4 id="summaryCtr">-</h4>
                                        <p>Overall CTR</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-mouse-pointer"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Details Table -->
                <div class="table-responsive">
                    <table id="campaignDetailsTable" class="table table-bordered table-striped dataTable" width="100%">
                        <thead>
                            <tr>
                                <th>Nama Akun</th>
                                <th>Product Category</th>
                                <th>TOFU Spent</th>
                                <th>TOFU %</th>
                                <th>MOFU Spent</th>
                                <th>MOFU %</th>
                                <th>BOFU Spent</th>
                                <th>BOFU %</th>
                                <th>Total Spent</th>
                                <th>Impressions</th>
                                <th>Link Clicks</th>
                                <th>Content Views</th>
                                <th>Adds to Cart</th>
                                <th>Purchases</th>
                                <th>Conversion Value</th>
                                <th>Cost per View</th>
                                <th>Cost per ATC</th>
                                <th>Cost per Purchase</th>
                                <th>ROAS</th>
                                <th>CPM</th>
                                <th>CTR</th>
                                <th>Performance</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                    </table>
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
    
    #funnelMetrics {
        padding: 15px;
        background-color: #f8f9fa;
        border-radius: 4px;
    }
    
    .text-muted {
        color: #6c757d;
    }
    
    .font-weight-bold {
        font-weight: 600;
    }
    
    .ml-2 {
        margin-left: 0.5rem;
    }
    
    .mb-2 {
        margin-bottom: 0.5rem;
    }
    
    /* DataTable horizontal scrolling styles */
    .dataTables_wrapper {
        width: 100%;
        overflow-x: auto;
        white-space: nowrap;
    }
    
    #campaignDetailsTable {
        width: 100% !important;
    }
    
    .dataTables_wrapper::-webkit-scrollbar {
        height: 8px;
    }
    
    .dataTables_wrapper::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
    
    .dataTables_wrapper::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }
    
    .dataTables_wrapper::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
    
    /* Ensure proper column sizing */
    #campaignDetailsTable th, 
    #campaignDetailsTable td {
        white-space: nowrap;
        padding: 8px 12px;
    }
    
    /* Make percentage columns narrower */
    #campaignDetailsTable th:nth-child(4),
    #campaignDetailsTable th:nth-child(6),
    #campaignDetailsTable th:nth-child(8),
    #campaignDetailsTable td:nth-child(4),
    #campaignDetailsTable td:nth-child(6),
    #campaignDetailsTable td:nth-child(8) {
        width: 70px;
        max-width: 70px;
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
    <script>
        $(document).ready(function() {
            // Initialize variables
            filterDate = $('#filterDates');
            filterChannel = $('#filterChannel');
            filterCategory = $('#kategoriProdukFilter');
            let funnelChart = null;
            let impressionChart = null;
            let modalFilterDate = $('#modalFilterDates');

            modalFilterDate.daterangepicker({
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

            // Apply and Cancel event handlers for modal daterangepicker
            modalFilterDate.on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
                $(this).trigger('change');
            });

            modalFilterDate.on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                $(this).trigger('change');
            });

            // Filter change handler for modal date range
            modalFilterDate.change(function() {
                campaignDetailsTable.draw();
                updateCampaignSummary();
    
                // Update the modal title to reflect the date range
                let dateRange = $(this).val();
                if (dateRange) {
                    $('#dailyDetailsModalLabel').text('Campaign Details for ' + dateRange);
                } else {
                    const clickedDate = moment($('#dailyDetailsModal').data('date')).format('D MMM YYYY');
                    $('#dailyDetailsModalLabel').text('Campaign Details for ' + clickedDate);
                }
            });

            // Initialize daterangepicker
            filterDate.daterangepicker({
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

            // Apply and Cancel event handlers for daterangepicker
            filterDate.on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
                $(this).trigger('change');
            });

            filterDate.on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                $(this).trigger('change');
            });
            function updateCampaignSummary() {
                // Build parameters object
                let params = {};
                
                // Check if we have a date range filter in the modal
                if (modalFilterDate.val()) {
                    let dates = modalFilterDate.val().split(' - ');
                    params.date_start = moment(dates[0], 'DD/MM/YYYY').format('YYYY-MM-DD');
                    params.date_end = moment(dates[1], 'DD/MM/YYYY').format('YYYY-MM-DD');
                } else if ($('#dailyDetailsModal').data('date')) {
                    // If no date range is selected, use the single date
                    params.date = $('#dailyDetailsModal').data('date');
                }
                
                // Add other filters
                if ($('#picFilter').val()) {
                    params.pic = $('#picFilter').val();
                }
                
                if (filterCategory.val()) {
                    params.kategori_produk = filterCategory.val();
                }
                
                // Show loading state in summary cards
                $('#campaignSummary .card h4').text('Loading...');
                
                // Fetch summary data
                $.ajax({
                    url: "{{ route('adSpentSocialMedia.get_campaign_summary') }}",
                    type: 'GET',
                    data: params,
                    success: function(response) {
                        if (response.success) {
                            const data = response.data;
                            
                            // Update summary cards with formatted values
                            $('#summaryAccountsCount').text(data.accounts_count);
                            $('#summaryTotalSpent').text('Rp ' + numberFormat(data.total_amount_spent));
                            $('#summaryTotalPurchases').text(numberFormat(data.total_purchases, 2));
                            $('#summaryConversionValue').text('Rp ' + numberFormat(data.total_conversion_value));
                            $('#summaryRoas').text(numberFormat(data.roas, 2));
                            $('#summaryCostPerPurchase').text('Rp ' + numberFormat(data.cost_per_purchase));
                            $('#summaryImpressions').text(numberFormat(data.total_impressions));
                            $('#summaryCtr').text(numberFormat(data.ctr, 2) + '%');
                            
                            // Update new funnel stage metrics
                            $('#summaryTofuSpent').text('Rp ' + numberFormat(data.tofu_spent));
                            $('#summaryMofuSpent').text('Rp ' + numberFormat(data.mofu_spent));
                            $('#summaryBofuSpent').text('Rp ' + numberFormat(data.bofu_spent));
                            
                            // Update percentage badges
                            $('#summaryTofuPercentage').text(numberFormat(data.tofu_percentage, 2) + '%');
                            $('#summaryMofuPercentage').text(numberFormat(data.mofu_percentage, 2) + '%');
                            $('#summaryBofuPercentage').text(numberFormat(data.bofu_percentage, 2) + '%');
                            
                            // Add color coding for ROAS based on performance thresholds
                            const roasElement = $('#summaryRoas');
                            roasElement.removeClass('text-success text-primary text-info text-danger');
                            if (data.roas >= 2.5) {
                                roasElement.addClass('text-success');
                            } else if (data.roas >= 2.01) {
                                roasElement.addClass('text-primary');
                            } else if (data.roas >= 1.75) {
                                roasElement.addClass('text-info');
                            } else if (data.roas > 0) {
                                roasElement.addClass('text-danger');
                            }
                        } else {
                            // Handle error response
                            console.error('Error fetching summary data');
                            $('#campaignSummary .card h4').text('-');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX error:', error);
                        $('#campaignSummary .card h4').text('-');
                    }
                });
            }

            // Helper function for number formatting
            function numberFormat(value, decimals = 0) {
                if (value === null || value === undefined) return '-';
                return Number(value).toLocaleString('id-ID', {
                    minimumFractionDigits: decimals,
                    maximumFractionDigits: decimals
                });
            }

            // Button click handlers
            $('#btnAddVisit').click(function() {
                $('#dateVisit').val(moment().format("DD/MM/YYYY"));
            });

            $('#btnAddAdSpentSM').click(function() {
                $('#dateAdSpentSocialMedia').val(moment().format("DD/MM/YYYY"));
            });

            $('#btnAddAdSpentMP').click(function() {
                $('#dateAdSpentMarketPlace').val(moment().format("DD/MM/YYYY"));
            });

            $('#resetFilterBtn').click(function () {
                filterDate.val('');
                filterCategory.val('');
                adsMetaTable.draw();
            });

            // Filter change handlers
            filterCategory.change(function() {
                adsMetaTable.draw();
                campaignDetailsTable.draw();

                if ($('#dailyDetailsModal').is(':visible')) {
                    updateCampaignSummary();
                }

                initFunnelChart();
                fetchImpressionData();
            });

            filterDate.change(function () {
                adsMetaTable.draw();
                initFunnelChart();
                fetchImpressionData();
            });

            filterChannel.change(function () {
                adsMetaTable.draw();
            });

            $('#picFilter').change(function() {
                adsMetaTable.draw();
                if ($('#dailyDetailsModal').is(':visible')) {
                    campaignDetailsTable.draw();
                    updateCampaignSummary();
                }
                fetchImpressionData();
                initFunnelChart();
            });

            // File input handler
            $('#metaAdsCsvFile').on('change', function() {
                let fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').html(fileName || 'Choose file');
                
                if (fileName.toLowerCase().endsWith('.zip')) {
                    $('<div class="alert alert-info mt-2">ZIP file detected. All CSV files in the archive will be processed.</div>')
                        .insertAfter($(this).closest('.custom-file'));
                }
            });

            // Form submit handler
            $('#importMetaAdsSpentForm').on('submit', function(e) {
                e.preventDefault();
                
                // Show loading state
                Swal.fire({
                    title: 'Processing...',
                    text: 'Please wait while we import your data.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                let formData = new FormData(this);
                $.ajax({
                    url: "{{ route('adSpentSocialMedia.import') }}",
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#importMetaAdsSpentModal').modal('hide');
                            
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
                            // Handle unexpected success response without success status
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

            // Initialize DataTables
            let adsMetaTable = $('#adsMetaTable').DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                pageLength: 25,
                ajax: {
                    url: "{{ route('adSpentSocialMedia.get_ads_cpas') }}",
                    data: function (d) {
                        if (filterDate.val()) {
                            let dates = filterDate.val().split(' - ');
                            d.date_start = moment(dates[0], 'DD/MM/YYYY').format('YYYY-MM-DD');
                            d.date_end = moment(dates[1], 'DD/MM/YYYY').format('YYYY-MM-DD');
                        }
                        if (filterCategory && filterCategory.length > 0) {
                            d.kategori_produk = filterCategory.val();
                        }
                        if ($('#picFilter').val()) {
                            d.pic = $('#picFilter').val();
                        }
                    }
                },
                columns: [
                    {data: 'date', name: 'date'},
                    {
                        data: 'total_amount_spent', 
                        name: 'total_amount_spent',
                        render: function(data) {
                            return 'Rp ' + Number(data).toLocaleString('id-ID', {
                                minimumFractionDigits: 0,
                                maximumFractionDigits: 0
                            });
                        }
                    },
                    {
                        data: 'total_content_views', 
                        name: 'total_content_views',
                        render: function(data) {
                            return Number(data).toLocaleString('id-ID', {
                                minimumFractionDigits: 0,
                                maximumFractionDigits: 2
                            });
                        }
                    },
                    {
                        data: 'total_adds_to_cart', 
                        name: 'total_adds_to_cart',
                        render: function(data) {
                            return Number(data).toLocaleString('id-ID', {
                                minimumFractionDigits: 0,
                                maximumFractionDigits: 2
                            });
                        }
                    },
                    {
                        data: 'total_purchases', 
                        name: 'total_purchases',
                        render: function(data) {
                            return Number(data).toLocaleString('id-ID', {
                                minimumFractionDigits: 0,
                                maximumFractionDigits: 2
                            });
                        }
                    },
                    {
                        data: 'cost_per_purchase', 
                        name: 'cost_per_purchase',
                        searchable: false,
                        render: function(data) {
                            return 'Rp ' + Number(data).toLocaleString('id-ID', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                    },
                    {
                        data: 'total_conversion_value', 
                        name: 'total_conversion_value',
                        render: function(data) {
                            return 'Rp ' + Number(data).toLocaleString('id-ID', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                    },
                    {
                        data: 'roas', 
                        name: 'roas', 
                        searchable: false,
                        render: function(data) {
                            return Number(data).toLocaleString('id-ID', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                    },
                    {
                        data: 'total_impressions', 
                        name: 'total_impressions',
                        render: function(data) {
                            return Number(data).toLocaleString('id-ID', {
                                minimumFractionDigits: 0,
                                maximumFractionDigits: 0
                            });
                        }
                    },
                    {
                        data: 'cpm', 
                        name: 'cpm', 
                        searchable: false,
                        render: function(data) {
                            return 'Rp ' + Number(data).toLocaleString('id-ID', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                    },
                    {
                        data: 'total_link_clicks', 
                        name: 'total_link_clicks',
                        render: function(data) {
                            return Number(data).toLocaleString('id-ID', {
                                minimumFractionDigits: 0,
                                maximumFractionDigits: 2
                            });
                        }
                    },
                    {
                        data: 'ctr', 
                        name: 'ctr', 
                        searchable: false,
                        render: function(data) {
                            return Number(data).toLocaleString('id-ID', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            }) + '%';
                        }
                    },
                    {data: 'performance', name: 'performance', searchable: false}
                ],
                columnDefs: [
                    { "targets": [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11], "className": "text-right" },
                    { "targets": [12], "className": "text-center" }
                ],
                order: [[0, 'desc']]
            });

            let campaignDetailsTable = $('#campaignDetailsTable').DataTable({
                responsive: false, // Set to false for horizontal scrolling
                scrollX: true,     // Enable horizontal scrolling
                processing: true,
                serverSide: true,
                pageLength: 10,
                ajax: {
                    url: "{{ route('adSpentSocialMedia.get_details_by_date') }}",
                    data: function(d) {
                        // Add the date from the modal to the request
                        if (modalFilterDate.val()) {
                            let dates = modalFilterDate.val().split(' - ');
                            d.date_start = moment(dates[0], 'DD/MM/YYYY').format('YYYY-MM-DD');
                            d.date_end = moment(dates[1], 'DD/MM/YYYY').format('YYYY-MM-DD');
                        } else {
                            // If no date range is selected, use the single date
                            d.date = $('#dailyDetailsModal').data('date');
                        }
                        if ($('#picFilter').val()) {
                            d.pic = $('#picFilter').val();
                        }
                    }
                },
                columns: [
                    {data: 'account_name', name: 'account_name'},
                    {data: 'kategori_produk', name: 'kategori_produk'},
                    // New TOFU/MOFU/BOFU columns
                    {data: 'tofu_spent', name: 'tofu_spent'},
                    {data: 'tofu_percentage', name: 'tofu_percentage'},
                    {data: 'mofu_spent', name: 'mofu_spent'},
                    {data: 'mofu_percentage', name: 'mofu_percentage'},
                    {data: 'bofu_spent', name: 'bofu_spent'},
                    {data: 'bofu_percentage', name: 'bofu_percentage'},
                    // Original columns
                    {data: 'amount_spent', name: 'amount_spent'},
                    {data: 'impressions', name: 'impressions'},
                    {
                        data: 'link_clicks', 
                        name: 'link_clicks',
                        render: function(data) {
                            // Handle string formatted numbers with comma as decimal separator
                            if (typeof data === 'string') {
                                // Extract the whole number part before the comma
                                return data.split(',')[0];
                            }
                            return Math.floor(data);
                        }
                    },
                    {
                        data: 'content_views_shared_items', 
                        name: 'content_views_shared_items',
                        render: function(data) {
                            // Handle string formatted numbers with comma as decimal separator
                            if (typeof data === 'string') {
                                // Extract the whole number part before the comma
                                return data.split(',')[0];
                            }
                            return Math.floor(data);
                        }
                    },
                    {
                        data: 'adds_to_cart_shared_items', 
                        name: 'adds_to_cart_shared_items',
                        render: function(data) {
                            // Handle string formatted numbers with comma as decimal separator
                            if (typeof data === 'string') {
                                // Extract the whole number part before the comma
                                return data.split(',')[0];
                            }
                            return Math.floor(data);
                        }
                    },
                    {
                        data: 'purchases_shared_items', 
                        name: 'purchases_shared_items',
                        render: function(data) {
                            // Handle string formatted numbers with comma as decimal separator
                            if (typeof data === 'string') {
                                // Extract the whole number part before the comma
                                return data.split(',')[0];
                            }
                            return Math.floor(data);
                        }
                    },
                    {data: 'purchases_conversion_value_shared_items', name: 'purchases_conversion_value_shared_items'},
                    {data: 'cost_per_view', name: 'cost_per_view'},
                    {data: 'cost_per_atc', name: 'cost_per_atc'},
                    {data: 'cost_per_purchase', name: 'cost_per_purchase'},
                    {data: 'roas', name: 'roas'},
                    {data: 'cpm', name: 'cpm'},
                    {data: 'ctr', name: 'ctr'},
                    {data: 'performance', name: 'performance', searchable: false},
                    {data: 'action', name: 'action', orderable: false, searchable: false}
                ],
                columnDefs: [
                    // Update the targets for right alignment to include the new columns
                    { "targets": [2, 4, 6, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20], "className": "text-right" },
                    // Update the targets for center alignment to include the new columns
                    { "targets": [1, 3, 5, 7, 21], "className": "text-center" },
                    { "targets": [22], "className": "text-center" }
                ],
                order: [[0, 'asc']],
                // Initialize with fixed column headers
                fixedHeader: true,
                // Ensure horizontal scrolling is correctly implemented
                scrollCollapse: true,
                // Additional settings for better performance with many columns
                deferRender: true,
                scroller: true
            });

            // Modal shown event handler
            $('#dailyDetailsModal').on('shown.bs.modal', function () {
                // If no date range is set, initialize with the clicked date
                if (!modalFilterDate.val()) {
                    const clickedDate = $('#dailyDetailsModal').data('date');
                    const formattedDate = moment(clickedDate).format('DD/MM/YYYY');
                    modalFilterDate.val(formattedDate + ' - ' + formattedDate);
                }
                updateCampaignSummary();
                $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
            });
            // Click event handler for date details
            $('#adsMetaTable').on('click', '.date-details', function(){
                let date = $(this).data('date');
                let formattedDate = $(this).text();
                
                $('#dailyDetailsModalLabel').text('Campaign Details for ' + formattedDate);
                $('#dailyDetailsModal').data('date', date);

                modalFilterDate.val('');
                
                campaignDetailsTable.draw();
                $('#dailyDetailsModal').modal('show');
            });

            $('#dailyDetailsModal .modal-header').append(
                $('<button>')
                    .addClass('btn btn-default ml-2')
                    .text('Reset Filter')
                    .on('click', function() {
                        // Reset the modal date filter to the original clicked date
                        const clickedDate = $('#dailyDetailsModal').data('date');
                        const formattedDate = moment(clickedDate).format('DD/MM/YYYY');
                        modalFilterDate.val(formattedDate + ' - ' + formattedDate);
                        modalFilterDate.trigger('change');
                    })
            );
            $('#dailyDetailsModal .modal-header button.btn-default').css({
                'position': 'absolute',
                'right': '80px',
                'top': '10px'
            });
            $('#dailyDetailsModal .modal-header button.btn-default').off('click').on('click', function() {
                // Reset the modal date filter to the original clicked date
                const clickedDate = $('#dailyDetailsModal').data('date');
                const formattedDate = moment(clickedDate).format('DD/MM/YYYY');
                modalFilterDate.val(formattedDate + ' - ' + formattedDate);
                
                // Update the modal title
                $('#dailyDetailsModalLabel').text('Campaign Details for ' + moment(clickedDate).format('D MMM YYYY'));
                
                // Trigger the change event to update the table and summary
                modalFilterDate.trigger('change');
            });

            // Click event handler for delete account
            $('#campaignDetailsTable').on('click', '.delete-account', function() {
                const accountName = $(this).data('account');
                const date = $(this).data('date');
                
                // Format date to ensure it's in Y-m-d format
                const formattedDate = moment(date).format('YYYY-MM-DD');
                
                Swal.fire({
                    title: 'Are you sure?',
                    text: `This will delete all data for "${accountName}" on ${moment(date).format('D MMM YYYY')}!`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading state
                        Swal.fire({
                            title: 'Deleting...',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        
                        // Send delete request with properly formatted date
                        $.ajax({
                            url: "{{ route('adSpentSocialMedia.delete_by_account') }}",
                            type: 'DELETE',
                            data: {
                                account_name: accountName,
                                date: formattedDate, // Use the formatted date
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: response.message,
                                    showConfirmButton: false,
                                    timer: 1500
                                }).then(() => {
                                    // Refresh the tables and charts
                                    campaignDetailsTable.draw();
                                    adsMetaTable.draw();
                                    updateRecapCount();
                                    initFunnelChart();
                                    fetchImpressionData();
                                });
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: xhr.responseJSON ? xhr.responseJSON.message : 'Failed to delete data',
                                    confirmButtonColor: '#3085d6'
                                });
                            }
                        });
                    }
                });
            });

            // Chart functions
            function createLineChart(ctx, label, dates, data) {
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
                            enabled: true,
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

            function initFunnelChart() {
                const filterValue = filterDate.val();
                const picValue = $('#picFilter').val();
                const kategoriProduk = $('#kategoriProdukFilter').val();

                const url = new URL('{{ route("adSpentSocialMedia.funnel-data") }}');
                if (filterValue) {
                    url.searchParams.append('filterDates', filterValue);
                }
                if (kategoriProduk) {
                    url.searchParams.append('kategori_produk', kategoriProduk);
                }
                
                if (picValue) {
                    url.searchParams.append('pic', picValue);
                }

                if (funnelChart) {
                    funnelChart.destroy();
                    funnelChart = null;
                }

                fetch(url)
                    .then(response => response.json())
                    .then(result => {
                        if (result.status === 'success') {
                            const data = result.data;
                            
                            const options = {
                                chart: {
                                    type: 'bar',
                                    height: 350,
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
                                colors: ['#60A5FA', '#3B82F6', '#2563EB', '#1D4ED8'],
                                dataLabels: {
                                    enabled: true,
                                    formatter: function(val) {
                                        return val.toLocaleString();
                                    },
                                    style: {
                                        fontSize: '12px',
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
                                }
                            };

                            const series = [{
                                name: 'Total',
                                data: data.map(item => item.value)
                            }];

                            // Create new ApexCharts instance
                            funnelChart = new ApexCharts(document.querySelector("#funnelChart"), {
                                ...options,
                                series: series
                            });
                            funnelChart.render();

                            const metricsHtml = data.map((item, index) => `
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>${item.name}</span>
                                    <span class="font-weight-bold">
                                        ${item.value.toLocaleString()}
                                        ${index > 0 ? `
                                            <span class="text-muted ml-2">
                                                (${((item.value / data[0].value) * 100).toFixed(2)}%)
                                            </span>
                                        ` : ''}
                                    </span>
                                </div>
                            `).join('');

                            document.querySelector('#funnelMetrics').innerHTML = metricsHtml;
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching data:', error);
                    });
            }

            function fetchImpressionData() {
                const filterValue = filterDate.val();
                const kategoriProduk = $('#kategoriProdukFilter').val();
                const picValue = $('#picFilter').val();
                
                const url = new URL('{{ route("adSpentSocialMedia.line-data") }}', window.location.origin);
                
                if (filterValue) {
                    url.searchParams.append('filterDates', filterValue);
                }
                
                if (kategoriProduk) {
                    url.searchParams.append('kategori_produk', kategoriProduk);
                }

                if (picValue) {
                    url.searchParams.append('pic', picValue);
                }
                
                try {
                    if (window.impressionChart && typeof window.impressionChart.destroy === 'function') {
                        window.impressionChart.destroy();
                    }
                } catch (e) {
                    console.error('Error destroying previous chart:', e);
                }
                window.impressionChart = null;
                
                fetch(url)
                    .then(response => response.json())
                    .then(result => {
                        if (result.status === 'success') {
                            const impressionData = result.impressions;
                            const impressionDates = impressionData.map(data => data.date);
                            const impressions = impressionData.map(data => data.impressions);
                            
                            const ctxImpression = document.getElementById('impressionChart').getContext('2d');
                            
                            // Create the chart directly here
                            window.impressionChart = new Chart(ctxImpression, {
                                type: 'line',
                                data: {
                                    labels: impressionDates,
                                    datasets: [{
                                        label: 'Impressions',
                                        data: impressions,
                                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                                        borderColor: 'rgba(54, 162, 235, 1)',
                                        borderWidth: 1
                                    }]
                                },
                                options: {
                                    tooltips: {
                                        enabled: true,
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
                    })
                    .catch(error => {
                        console.error('Error fetching impression data:', error);
                    });
            }

            $('#dailyDetailsModal').on('hidden.bs.modal', function () {
                modalFilterDate.val('');
            });

            // Initialize the page
            $(function () {
                adsMetaTable.draw();
                fetchImpressionData();
                initFunnelChart();
                $('[data-toggle="tooltip"]').tooltip();
            });

            function showLoadingSwal(message) {
                Swal.fire({
                    title: message,
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            }

            // Tab shown event handler
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                if (e.target.getAttribute('href') === '#funnelChartTab') {
                    initFunnelChart();
                }
            });
        });
    </script>
    @include('admin.adSpentMarketPlace.script-line-chart')
@stop
