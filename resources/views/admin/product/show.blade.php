@extends('adminlte::page')

@section('title', 'Product Details')

@section('content_header')
    <h1>Product Details </h1>
    <h4>{{ $product->product }} (SKU: {{ $product->sku }})</h4>
    
@stop

@section('content')
<div>
    <div class="card">
        <div class="card-header">
            <a href="{{ route('product.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Product List</a>
            <div class="btn-group ml-4" role="group" aria-label="Switch View">
                <button type="button" class="btn btn-primary" id="salesBtn">Sales</button>
                <button type="button" class="btn btn-secondary" id="marketingBtn">Marketing</button>
            </div>
        </div>
    </div>

    <!-- Sales content (Initially visible) -->
    <div class="card" id="salesContent">
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-4">
                                <select class="form-control" id="filterChannel">
                                    <option value="" selected>{{ trans('placeholder.select_sales_channel') }}</option>
                                    <option value="">{{ trans('labels.all') }}</option>
                                    @foreach($salesChannels as $salesChannel)
                                        <option value="{{ $salesChannel->id }}">{{ $salesChannel->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <input type="month" id="monthFilter" class="form-control" value="{{ date('Y-m') }}">
                            </div>
                        </div>
            <div class="row">
                <div class="col-8">
                    <div class="card">
                        <div class="card-header p-2">
                            <ul class="nav nav-pills">
                                <li class="nav-item">
                                    <a class="nav-link active" href="#dailyTab" data-toggle="tab">Daily</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#monthlyTab" data-toggle="tab">Monthly</a>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <canvas id="orderCountChart" width="400" height="160"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-4 mb-3">
                    <div class="card">
                        <div class="card-header">
                            <h3>Order Count by SKU</h3>
                        </div>
                        <div class="card-body" style="height: 350px;">
                            <canvas id="skuOrderCountChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-6">
                    <div class="card">
                        <div class="card-header">
                            <h3>Product Sales Performance</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <td>Unique Customers Count</td>
                                        <td style="font-size: 18px;"><strong id="uniqueCustomerCount">0</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Total Orders</td>
                                        <td style="font-size: 18px;"><strong id="totalOrdersCount">0</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Total Revenue</td>
                                        <td style="font-size: 18px;"><strong id="totalAmountSum">Rp 0</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Net Profit for Single Product</td>
                                        <td style="font-size: 18px;"><strong id="netProfit">Rp 0</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Avg. Daily Orders</td>
                                        <td style="font-size: 18px;"><strong id="avgDailyOrdersCount">0</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Harga Jual</td>
                                        <td>Rp {{ number_format($product->harga_jual, 0, ',', '.') }}</td>
                                    </tr>
                                    <tr>
                                        <td>Harga MarkUp</td>
                                        <td>Rp {{ number_format($product->harga_markup, 0, ',', '.') }}</td>
                                    </tr>
                                    <tr>
                                        <td>Harga COGS</td>
                                        <td>Rp {{ number_format($product->harga_cogs, 0, ',', '.') }}</td>
                                    </tr>
                                    <tr>
                                        <td>Harga Batas Bawah</td>
                                        <td>Rp {{ number_format($product->harga_batas_bawah, 0, ',', '.') }}</td>
                                    </tr>
                                </tbody>
                            </table>

                        </div>
                    </div>
                </div>

                <div class="col-6">
                    <div class="row">
                        <div class="col-6">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <p>Customer Repeat Rate</p>
                                    <h1 id="ordersPerCustomerRatio">0</h1>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <p>Average Order Value</p>
                                    <h1 id="averageOrderValue">0</h1>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h3>Order Count by Sales Channel (SKU: {{ $product->sku }})</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="salesChannelOrderCountChart" width="400" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Orders Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3>{{ $product->product }} (SKU: {{ $product->sku }})</h3>
                    </div>
                    <div class="card-body">
                        <table id="ordersTable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer Name</th>
                                    <th>Quantity</th>
                                    <th>Total Price</th>
                                    <th>Shipment</th>
                                    <th>SKU</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data will be populated by DataTables -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Marketing content (Initially hidden) -->
    <div class="card" id="marketingContent" style="display: none;">
        <div class="card-body">
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h4 id="talentContentCount">0</h4>
                            <p>Content Count</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-purple">
                        <div class="inner">
                            <h4 id="uniqueTalentIdCount">0</h4>
                            <p>Talent Count</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h4 id="averageEngagementRate">0</h4>
                            <p>Avg. Engagement Rate</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3>Talent Content for {{ $product->product }} (SKU: {{ $product->sku }})</h3>
                    <div class="row">
                            <div class="col-md-4">
                                <input type="month" id="monthFilterMarketing" class="form-control" value="{{ date('Y-m') }}">
                            </div>
                        </div>
                </div>
                <div class="card-body">
                    <table id="talentContentTable" class="table table-bordered table-striped" style="width: 100% !important;">
                        <thead>
                            <tr>
                                <th>Talent ID</th>
                                <th>Posting Date</th>
                                <th>Status</th>
                                <th>Upload Link</th>
                                <th>Rate Card</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data will be populated by DataTables -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>



@stop


@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    filterChannel = $('#filterChannel');
    monthFilter = $('#monthFilter');
    monthFilterMarketing = $('#monthFilterMarketing');

    let skuOrderCountChartInstance = null;
    let salesChannelOrderCountChartInstance = null;

    function createSkuOrderCountChart(chartId, data) {
        const ctx = document.getElementById(chartId).getContext('2d');

        // Destroy the previous chart instance if it exists
        if (skuOrderCountChartInstance) {
            skuOrderCountChartInstance.destroy();
        }

        // Create a new chart instance
        skuOrderCountChartInstance = new Chart(ctx, {
            type: 'pie', // Pie chart
            data: {
                labels: data.map(item => item.sku), // SKU labels
                datasets: [{
                    label: 'Order Count',
                    data: data.map(item => item.count), // Order count values
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.6)',
                        'rgba(255, 99, 132, 0.6)',
                        'rgba(54, 162, 235, 0.6)',
                        'rgba(153, 102, 255, 0.6)',
                        'rgba(255, 159, 64, 0.6)'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false // Hide legend
                    },
                    tooltip: {
                        callbacks: {
                            label: function (tooltipItem) {
                                return tooltipItem.label + ': ' + tooltipItem.raw + ' orders';
                            }
                        }
                    }
                }
            }
        });
    }

    function createSalesChannelOrderCountChart(chartId, response) {
        const ctx = document.getElementById(chartId).getContext('2d');

        // Destroy the previous chart instance if it exists
        if (salesChannelOrderCountChartInstance) {
            salesChannelOrderCountChartInstance.destroy();
        }

        // Create a new chart instance
        salesChannelOrderCountChartInstance = new Chart(ctx, {
            type: 'bar', // Bar chart
            data: {
                labels: response.labels, // Sales channel labels
                datasets: [{
                    label: 'Order Count by Sales Channel',
                    data: response.data, // Order count values
                    backgroundColor: 'rgba(54, 162, 235, 0.6)', // Bar color
                    borderColor: 'rgba(54, 162, 235, 1)', // Border color
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Sales Channel'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Order Count'
                        },
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        display: false // Hide legend
                    }
                }
            }
        });
    }

    $(document).ready(function() {
        $('#salesBtn').on('click', function() {
            $('#salesContent').hide();
            $('#marketingContent').show();
            $('#salesBtn').addClass('btn-primary').removeClass('btn-secondary');
            $('#marketingBtn').addClass('btn-secondary').removeClass('btn-primary');
        });

        function updateMarketingMetrics() {
            const marketingUrl = '{{ route("product.marketing", $product->id) }}'; // Use the route name

            fetch(marketingUrl)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    // Update the marketing metrics values
                    document.getElementById('talentContentCount').textContent =
                        new Intl.NumberFormat('en-US').format(data.talentContentCount);
                    document.getElementById('uniqueTalentIdCount').textContent =
                        new Intl.NumberFormat('en-US').format(data.uniqueTalentIdCount);
                    document.getElementById('averageEngagementRate').textContent =
                        `${new Intl.NumberFormat('en-US').format(data.averageEngagementRate)}%`;
                })
                .catch(error => {
                    console.error('Error fetching marketing data:', error);
                });
        }
        // Switch to Marketing view
        $('#marketingBtn').on('click', function() {
            $('#salesContent').hide();
            $('#marketingContent').show();
            $('#marketingBtn').addClass('btn-primary').removeClass('btn-secondary');
            $('#salesBtn').addClass('btn-secondary').removeClass('btn-primary');
            updateMarketingMetrics();
        });

        // Initial state: Show Sales content
        $('#salesBtn').click();

        var ordersTable = $('#ordersTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('product.orders', $product->id) }}',
                data: function (d) {
                    d.sales_channel = $('#filterChannel').val();
                    d.month = $('#monthFilter').val();
                }
            },
            columns: [
                { data: 'id_order', name: 'id_order' },
                { data: 'customer_name', name: 'customer_name' },
                { data: 'qty', name: 'qty' },
                { data: 'total_price', name: 'total_price' },
                { data: 'shipment', name: 'shipment' },
                { data: 'sku', name: 'sku' },
                { data: 'date', name: 'date' }
            ],
            order: [[6, 'desc']]
        });

        filterChannel.change(function () {
            ordersTable.draw();
            fetchSalesMetrics();
            loadSkuOrderCountChart();
            loadSalesChannelOrderCountChart();
            lineDailyChart();
        });
        monthFilter.change(function () {
            ordersTable.draw();
            fetchSalesMetrics();
            loadSkuOrderCountChart();
            loadSalesChannelOrderCountChart();
            lineDailyChart();
        });

        $('#talentContentTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('product.talent-content', $product->id) }}',
                data: function (d) {
                    d.month = $('#monthFilterMarketing').val();
                }
            },
            columns: [
                { data: 'talent_id', name: 'talent_id' },
                {
                    data: 'posting_date', 
                    name: 'posting_date',
                    render: function(data) {
                        if (data) {
                            let date = new Date(data);
                            return ('0' + date.getDate()).slice(-2) + '/' + 
                                   ('0' + (date.getMonth() + 1)).slice(-2) + '/' + 
                                   date.getFullYear();
                        }
                        return '';
                    }
                }, 
                { 
                    data: 'status', 
                    name: 'status',
                    render: function(data, type, row) {
                        return row.done ? 
                            '<span class="badge badge-success">Completed</span>' : 
                            '<span class="badge badge-warning">Pending</span>';
                    }
                },
                { data: 'upload_link', name: 'upload_link' },
                { 
                    data: 'final_rate_card', 
                    name: 'final_rate_card',
                    render: function(data) {
                        return data ? 'Rp ' + Number(data).toLocaleString() : '-';
                    }
                }
            ],
            order: [[0, 'desc']], // Order by ID descending
            responsive: true,
        });

        function loadSkuOrderCountChart() {
            const salesChannel = $('#filterChannel').val();
            const month = $('#monthFilter').val();

            $.ajax({
                url: '{{ route("product.getOrderCountBySku", $product->id) }}',
                method: 'GET',
                data: {
                    sales_channel: salesChannel,
                    month: month
                },
                success: function (response) {
                    createSkuOrderCountChart('skuOrderCountChart', response);
                },
                error: function (error) {
                    console.error('Error fetching SKU order count data:', error);
                }
            });
        }

        function loadSalesChannelOrderCountChart() {
            const salesChannel = $('#filterChannel').val();
            const month = $('#monthFilter').val();

            $.ajax({
                url: '{{ route("product.getOrderCountBySalesChannel", $product->id) }}',
                method: 'GET',
                data: {
                    sales_channel: salesChannel,
                    month: month
                },
                success: function (response) {
                    createSalesChannelOrderCountChart('salesChannelOrderCountChart', response);
                },
                error: function (error) {
                    console.error('Error fetching sales channel order count data:', error);
                }
            });
        }
        loadSkuOrderCountChart();
        loadSalesChannelOrderCountChart();

        function fetchSalesMetrics() {
            const url = '{{ route("product.sales", $product->id) }}';
            const salesChannel = $('#filterChannel').val();
            const monthFilter = $('#monthFilter').val();

            $.ajax({
                url: url,
                method: 'GET',
                data: {
                    sales_channel: salesChannel,
                    month: monthFilter
                },
                success: function (data) {
                    // Update the HTML with the fetched metrics
                    $('#uniqueCustomerCount').text(new Intl.NumberFormat().format(data.uniqueCustomerCount));
                    $('#totalOrdersCount').text(new Intl.NumberFormat().format(data.totalOrdersCount));
                    $('#totalAmountSum').text('Rp ' + new Intl.NumberFormat().format(data.totalAmountSum));
                    $('#avgDailyOrdersCount').text(new Intl.NumberFormat().format(data.avgDailyOrdersCount));
                    $('#ordersPerCustomerRatio').text(new Intl.NumberFormat().format(data.ordersPerCustomerRatio));
                    $('#averageOrderValue').text('Rp ' + new Intl.NumberFormat().format(data.averageOrderValue));
                    $('#netProfit').text('Rp ' + new Intl.NumberFormat().format(data.netProfitSingleProduct));
                },
                error: function (error) {
                    console.error('Error fetching sales metrics:', error);
                }
            });
        }
        fetchSalesMetrics();

        function lineDailyChart() {
            const salesChannel = $('#filterChannel').val();
            const monthFilter = $('#monthFilter').val();

            $.ajax({
                url: '{{ route('product.getOrderCountPerDay', $product->id) }}',
                method: 'GET',
                data: { 
                    sales_channel: salesChannel,
                    month: monthFilter
                },
                success: function(response) {
                    const ctx = document.getElementById('orderCountChart').getContext('2d');

                    if (window.orderCountChart instanceof Chart) {
                        window.orderCountChart.destroy();
                    }
                    window.orderCountChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: response.labels,
                            datasets: [{
                                label: 'Order Count',
                                data: response.data, // y-axis data (order count)
                                borderColor: 'rgba(75, 192, 192, 1)', // Line color
                                backgroundColor: 'rgba(75, 192, 192, 0.2)', // Area color
                                fill: true // Fill the area under the line
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                x: {
                                    title: {
                                        display: true,
                                        text: 'Date'
                                    }
                                },
                                y: {
                                    title: {
                                        display: true,
                                        text: 'Order Count'
                                    },
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                },
                error: function(error) {
                    console.error('Error fetching chart data:', error);
                }
            });
        }
        lineDailyChart();
    });
</script>
@stop
