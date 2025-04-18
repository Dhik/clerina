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
                        <div class="col-12">
                            <div class="row">
                                <div class="col-md-3 mb-2">
                                    <input type="text" id="filterDates" class="form-control daterange" placeholder="DD/MM/YYYY - DD/MM/YYYY">
                                </div>
                                <div class="col-auto">
                                    <button class="btn btn-primary" id="refreshDataBtn">
                                        <i class="fas fa-sync-alt"></i> Refresh Data
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-4">
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
                <div class="col-4">
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
                <div class="col-4">
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
            </div>
            
            <!-- Channel Summary Cards -->
            <div class="row" id="channelSummaryCards">
                <!-- Cards will be dynamically added here -->
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="laporanKeuanganTable" class="table table-bordered table-striped dataTable" width="100%">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    @foreach($salesChannels as $channel)
                                    <th>{{ $channel->name }}</th>
                                    @endforeach
                                    <th>Total Gross Revenue</th>
                                    <th>Total HPP</th>
                                    <th>Total Fee Admin</th>
                                </tr>
                            </thead>
                        </table>
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
</style>
@stop

@section('js')
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.colVis.min.js"></script>
    <script>
        let filterDate = $('#filterDates');
        let salesChannels = @json($salesChannels);
        
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
            laporanKeuanganTable.ajax.reload();
            fetchSummary();
        });

        function refreshData() {
            Swal.fire({
                title: 'Refreshing Data',
                html: 'Starting refresh process...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: "{{ route('lk.refresh') }}", // You need to create this route
                method: 'GET',
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Data Refreshed Successfully!',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    
                    // Reload the table
                    laporanKeuanganTable.ajax.reload();
                    fetchSummary();
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error Refreshing Data',
                        text: 'Please try again later.'
                    });
                    console.error('Error refreshing data:', error);
                }
            });
        }

        $('#refreshDataBtn').click(refreshData);

        // Define columns dynamically based on sales channels
        let columns = [
            {
                data: 'date',
                name: 'date'
            }
        ];

        // Add a column for each sales channel
        salesChannels.forEach(function(channel) {
            columns.push({
                data: 'channel_' + channel.id,
                name: 'channel_' + channel.id,
                render: function(data) {
                    // Check if data contains NaN (it will be a string with HTML)
                    if (data && data.includes('NaN')) {
                        return 'Rp 0';
                    }
                    return data || 'Rp 0';
                }
            });
        });

        // Add total columns
        columns.push(
            {
                data: 'total_gross_revenue',
                name: 'total_gross_revenue',
                render: function(data) {
                    if (data && data.includes('NaN')) {
                        return '<span class="text-success">Rp 0</span>';
                    }
                    return data || '<span class="text-success">Rp 0</span>';
                }
            },
            {
                data: 'total_hpp',
                name: 'total_hpp',
                render: function(data) {
                    if (data && data.includes('NaN')) {
                        return 'Rp 0';
                    }
                    return data || 'Rp 0';
                }
            },
            {
                data: 'total_fee_admin',
                name: 'total_fee_admin',
                render: function(data) {
                    if (data && data.includes('NaN')) {
                        return 'Rp 0';
                    }
                    return data || 'Rp 0';
                }
            }
        );

        function formatNumber(num) {
            return Math.round(num).toLocaleString('id-ID');
        }

        let laporanKeuanganTable = $('#laporanKeuanganTable').DataTable({
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
                url: "{{ route('lk.get') }}",
                data: function (d) {
                    d.filterDates = filterDate.val()
                }
            },
            columns: columns,
            columnDefs: [
                { 
                    "targets": Array.from({length: columns.length - 1}, (_, i) => i + 1), 
                    "className": "text-right" 
                }
            ],
            order: [[0, 'asc']]
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
                    document.getElementById('totalGrossRevenue').textContent = 'Rp ' + formatNumber(data.total_gross_revenue || 0);
                    document.getElementById('totalHpp').textContent = 'Rp ' + formatNumber(data.total_hpp || 0);
                    document.getElementById('totalFeeAdmin').textContent = 'Rp ' + formatNumber(data.total_fee_admin || 0);
                    
                    // Update channel summary cards
                    updateChannelSummaryCards(data.channel_summary);
                })
                .catch(error => console.error('Error:', error));
        }
        
        function updateChannelSummaryCards(channelSummary) {
            const container = document.getElementById('channelSummaryCards');
            container.innerHTML = ''; // Clear previous cards
            
            // Create a card for each channel
            channelSummary.forEach(channel => {
                const channelName = channel.channel_name.toLowerCase();
                let cardClass = 'other-card';
                let logoClass = 'fa-shopping-bag';
                
                // Determine the card class and logo based on channel name
                if (channelName === 'shopee') {
                    cardClass = 'shopee-card';
                    logoClass = 'fa-shopping-bag';
                } else if (channelName.includes('shopee 2')) {
                    cardClass = 'shopee-2-card';
                    logoClass = 'fa-shopping-bag';
                } else if (channelName.includes('shopee 3')) {
                    cardClass = 'shopee-3-card';
                    logoClass = 'fa-shopping-bag';
                } else if (channelName === 'lazada') {
                    cardClass = 'lazada-card';
                    logoClass = 'fa-box';
                } else if (channelName === 'tokopedia') {
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
        
        // Initial load
        fetchSummary();
    </script>
@stop