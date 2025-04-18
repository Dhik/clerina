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
            
            <!-- Channel Summary Cards -->
            <div class="row" id="channelSummaryCards">
                <!-- Cards will be dynamically added here -->
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Financial Report Details</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="laporanKeuanganTable" class="table table-bordered table-striped dataTable" width="100%">
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
                <div class="modal-body">
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
    
    a.show-details {
        color: inherit;
        text-decoration: none;
        cursor: pointer;
    }
    
    a.show-details:hover {
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
</style>
@stop

@section('js')
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.colVis.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script>
    // Date range picker
    let filterDate = $('#filterDates');
    
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

    function formatNumber(num) {
        return Math.round(num).toLocaleString('id-ID');
    }
    
    // Create DataTable
    let laporanKeuanganTable = $('#laporanKeuanganTable').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 25,
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel"></i> Export to Excel',
                className: 'btn btn-success',
                title: 'Financial Report'
            }
        ],
        ajax: {
            url: "{{ route('lk.get') }}",
            data: function (d) {
                d.filterDates = filterDate.val()
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
    
    // Initialize channel dataTables object to store DataTable instances
    let channelDataTables = {};

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
    
    // Handle detail modal
    $(document).on('click', '.show-details', function(e) {
        e.preventDefault();
        
        const date = $(this).data('date');
        const type = $(this).data('type');
        
        // Special handling for HPP details with tabs
        if (type === 'hpp') {
            $.ajax({
                url: "{{ route('lk.details') }}",
                method: 'GET',
                data: {
                    date: date,
                    type: type
                },
                success: function(response) {
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
                    
                    // Show the modal
                    $('#hppDetailModal').modal('show');
                    
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
                    console.error('Error fetching HPP details:', error);
                    alert('Error fetching HPP details. Please try again.');
                }
            });
        } else {
            // Standard detail modal for other types
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
                        case 'gross_revenue':
                            modalTitle = 'Gross Revenue Details - ' + date;
                            break;
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
                    
                    if (type === 'gross_revenue') {
                        headerRow += '<th class="text-right">Gross Revenue</th>';
                    } else if (type === 'fee_admin') {
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
                        
                        if (type === 'gross_revenue') {
                            row += '<td class="text-right">Rp ' + formatNumber(item.gross_revenue) + '</td>';
                        } else if (type === 'fee_admin') {
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
                    
                    if (type === 'gross_revenue') {
                        footerRow += '<td class="text-right">Rp ' + formatNumber(response.summary.total_gross_revenue) + '</td>';
                    } else if (type === 'fee_admin') {
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
    
    // Initial load
    fetchSummary();
</script>
@stop