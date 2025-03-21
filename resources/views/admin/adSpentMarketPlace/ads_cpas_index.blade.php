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
                <div class="table-responsive">
                    <table id="campaignDetailsTable" class="table table-bordered table-striped dataTable" width="100%">
                        <thead>
                            <tr>
                                <th>Nama Akun</th>
                                <th>Product Category</th>
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
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <script>
        filterDate = $('#filterDates');
        filterChannel = $('#filterChannel');
        filterCategory = $('#kategoriProdukFilter');
        let funnelChart = null;
        let impressionChart = null;

        $('.rangeDate').daterangepicker({
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

        $('.rangeDate').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
            $(this).trigger('change');
        });

        $('.rangeDate').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
            $(this).trigger('change');
        });

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
            filterDate.val('')
            filterCategory.val('')
            adsMetaTable.draw()
        });

        filterCategory.change(function() {
            adsMetaTable.draw();
            campaignDetailsTable.draw();
            initFunnelChart();
            fetchImpressionData();
        });

        $('#metaAdsCsvFile').on('change', function() {
            let fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').html(fileName || 'Choose file');
            
            if (fileName.toLowerCase().endsWith('.zip')) {
                $('<div class="alert alert-info mt-2">ZIP file detected. All CSV files in the archive will be processed.</div>')
                    .insertAfter($(this).closest('.custom-file'));
            }
        });

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

        filterDate.change(function () {
            adsMetaTable.draw()
            initFunnelChart()
            fetchImpressionData()
        });

        filterChannel.change(function () {
            adsMetaTable.draw()
        });

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
                    d.date = $('#dailyDetailsModal').data('date');
                }
            },
            columns: [
                {data: 'account_name', name: 'account_name'},
                {data: 'kategori_produk', name: 'kategori_produk'},
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
                { "targets": [2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14], "className": "text-right" },
                { "targets": [1, 15], "className": "text-center" },
                { "targets": [16], "className": "text-center" }
            ],
            order: [[0, 'asc']],
            // Initialize with fixed column headers
            fixedHeader: true
        });
        $('#dailyDetailsModal').on('shown.bs.modal', function () {
            $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
        });

        $('#adsMetaTable').on('click', '.date-details', function(){
            let date = $(this).data('date');
            let formattedDate = $(this).text();
            
            $('#dailyDetailsModalLabel').text('Campaign Details for ' + formattedDate);
            $('#dailyDetailsModal').data('date', date);
            
            campaignDetailsTable.draw();
            $('#dailyDetailsModal').modal('show');
        });

        // Add this to your JS section
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
            const url = new URL('{{ route("adSpentSocialMedia.funnel-data") }}');
            if (filterValue) {
                url.searchParams.append('filterDates', filterValue);
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
            
            const url = new URL('{{ route("adSpentSocialMedia.line-data") }}', window.location.origin);
            
            if (filterValue) {
                url.searchParams.append('filterDates', filterValue);
            }
            
            if (kategoriProduk) {
                url.searchParams.append('kategori_produk', kategoriProduk);
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

        $(function () {
            adsMetaTable.draw();
            fetchImpressionData();
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

        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            if (e.target.getAttribute('href') === '#funnelChartTab') {
                initFunnelChart();
            }
        });
    </script>
    @include('admin.adSpentMarketPlace.script-line-chart')
@stop
