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
            
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Revenue Distribution by Channel</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="salesPieChart" style="height: 300px;"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">HPP/Revenue Percentage by Channel</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="hppPercentageChart" style="height: 300px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Daily Trend</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="dailyTrendChart" style="height: 300px;"></canvas>
                        </div>
                    </div>
                </div>
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
    
    <!-- Detail Modal -->
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
    
    .chart-container {
        position: relative;
        height: 100%;
        width: 100%;
    }
    
    #salesPieChart, #hppPercentageChart, #dailyTrendChart {
        width: 100% !important;
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.colVis.min.js"></script>
<script>
    // Chart objects
    let salesPieChart = null;
    let hppPercentageChart = null;
    let dailyTrendChart = null;
    
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

    // Handle detail modal
    $(document).on('click', '.show-details', function(e) {
        e.preventDefault();
        
        const date = $(this).data('date');
        const type = $(this).data('type');
        
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
                    case 'hpp':
                        modalTitle = 'HPP Details - ' + date;
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
                } else if (type === 'hpp') {
                    headerRow += '<th class="text-right">HPP</th>';
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
                    } else if (type === 'hpp') {
                        row += '<td class="text-right">Rp ' + formatNumber(item.hpp) + '</td>';
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
                } else if (type === 'hpp') {
                    footerRow += '<td class="text-right">Rp ' + formatNumber(response.summary.total_hpp) + '</td>';
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
                
                // Update charts
                updateSalesPieChart(data.channel_summary);
                updateHppPercentageChart(data.channel_summary);
                updateDailyTrendChart(data.daily_trend);
            })
            .catch(error => console.error('Error:', error));
    }
    
    function updateSalesPieChart(channelSummary) {
        // Prepare data for pie chart
        const labels = channelSummary.map(channel => channel.channel_name);
        const data = channelSummary.map(channel => channel.channel_gross_revenue);
        
        // Generate colors
        const colors = generateColors(channelSummary.length);
        
        // Create or update chart
        const ctx = document.getElementById('salesPieChart').getContext('2d');
        
        if (salesPieChart) {
            salesPieChart.destroy();
        }
        
        salesPieChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: colors,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = context.raw;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = (value * 100 / total).toFixed(2) + '%';
                                return context.label + ': Rp ' + formatNumber(value) + ' (' + percentage + ')';
                            }
                        }
                    }
                }
            }
        });
    }
    
    function updateHppPercentageChart(channelSummary) {
        // Prepare data for bar chart
        const labels = channelSummary.map(channel => channel.channel_name);
        const data = channelSummary.map(channel => channel.channel_hpp_percentage);
        
        // Create or update chart
        const ctx = document.getElementById('hppPercentageChart').getContext('2d');
        
        if (hppPercentageChart) {
            hppPercentageChart.destroy();
        }
        
        hppPercentageChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'HPP/Revenue Percentage',
                    data: data,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.raw.toFixed(2) + '%';
                            }
                        }
                    }
                }
            }
        });
    }
    
    function updateDailyTrendChart(dailyTrend) {
        // Prepare data for line chart
        const labels = dailyTrend.map(day => day.date_formatted);
        const grossRevenueData = dailyTrend.map(day => day.daily_gross_revenue);
        const netProfitData = dailyTrend.map(day => day.daily_net_profit);
        const hppData = dailyTrend.map(day => day.daily_hpp);
        
        // Create or update chart
        const ctx = document.getElementById('dailyTrendChart').getContext('2d');
        
        if (dailyTrendChart) {
            dailyTrendChart.destroy();
        }
        
        dailyTrendChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Gross Revenue',
                        data: grossRevenueData,
                        backgroundColor: 'rgba(40, 167, 69, 0.2)',
                        borderColor: 'rgba(40, 167, 69, 1)',
                        borderWidth: 2,
                        tension: 0.1
                    },
                    {
                        label: 'Net Profit',
                        data: netProfitData,
                        backgroundColor: 'rgba(0, 123, 255, 0.2)',
                        borderColor: 'rgba(0, 123, 255, 1)',
                        borderWidth: 2,
                        tension: 0.1
                    },
                    {
                        label: 'HPP',
                        data: hppData,
                        backgroundColor: 'rgba(220, 53, 69, 0.2)',
                        borderColor: 'rgba(220, 53, 69, 1)',
                        borderWidth: 2,
                        tension: 0.1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + formatNumber(value);
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': Rp ' + formatNumber(context.raw);
                            }
                        }
                    }
                }
            }
        });
    }
    
    function generateColors(count) {
        const baseColors = [
            'rgba(255, 99, 132, 0.8)',
            'rgba(54, 162, 235, 0.8)',
            'rgba(255, 206, 86, 0.8)',
            'rgba(75, 192, 192, 0.8)',
            'rgba(153, 102, 255, 0.8)',
            'rgba(255, 159, 64, 0.8)',
            'rgba(199, 199, 199, 0.8)',
            'rgba(83, 102, 255, 0.8)',
            'rgba(40, 167, 69, 0.8)',
            'rgba(220, 53, 69, 0.8)'
        ];
        
        let colors = [];
        
        // Use base colors first
        for (let i = 0; i < count; i++) {
            if (i < baseColors.length) {
                colors.push(baseColors[i]);
            } else {
                // Generate random colors if we need more than the base colors
                const r = Math.floor(Math.random() * 255);
                const g = Math.floor(Math.random() * 255);
                const b = Math.floor(Math.random() * 255);
                colors.push(`rgba(${r}, ${g}, ${b}, 0.8)`);
            }
        }
        
        return colors;
    }
    
    // Initial load
    fetchSummary();
</script>
@stop