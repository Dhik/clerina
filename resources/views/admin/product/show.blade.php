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
            <div class="row">
                <div class="col-8">
                    <div class="card">
                        <div class="card-header p-2">
                            <ul class="nav nav-pills">
                                <li class="nav-item">
                                    <a class="nav-link active" href="#dailyTab" data-toggle="tab" onclick="updateChart('daily')">Daily</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#monthlyTab" data-toggle="tab" onclick="updateChart('monthly')">Monthly</a>
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
                                        <td>Unique Customers Count (on Shopee)</td>
                                        <td style="font-size: 18px;"><strong>{{ number_format($uniqueCustomerCount, 0, ',', '.') }}</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Total Order</td>
                                        <td style="font-size: 18px;"><strong>{{ number_format($totalOrdersCount, 0, ',', '.') }}</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Total Revenue</td>
                                        <td style="font-size: 18px;"><strong>Rp {{ number_format($totalAmountSum, 0, ',', '.') }}</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Avg. Daily Order</td>
                                        <td style="font-size: 18px;"><strong>{{ number_format($avgDailyOrdersCount, 0, ',', '.') }}</strong></td>
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
                                    <h1 id="newSalesCount">{{ number_format($ordersPerCustomerRatio, 2, ',', '.') }}</h1>
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
                                    <h1 id="newSalesCount">Rp {{ number_format($averageOrderValue, 0, ',', '.') }}</h1>
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
                            <canvas id="salesChannelOrderCountChart" width="400" height="160"></canvas>
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
                </div>
                <div class="card-body">
                    <table id="talentContentTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Campaign ID</th>
                                <th>Talent ID</th>
                                <th>Transfer Date</th>
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
    function updateChart(type) {
            // Make an AJAX call to fetch data based on the type (daily or monthly)
            $.ajax({
                url: '{{ route('product.getOrderCountPerDay', $product->id) }}',
                method: 'GET',
                data: { type: type }, // Pass type parameter
                success: function(response) {
                    const ctx = document.getElementById('orderCountChart').getContext('2d');

                    // If the chart exists, destroy it before reinitializing
                    if (window.orderCountChart instanceof Chart) {
                        window.orderCountChart.destroy();
                    }

                    // Create a new chart instance
                    orderCountChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: response.labels, // x-axis labels (dates or months)
                            datasets: [{
                                label: 'Order Count',
                                data: response.data, // y-axis data (order count)
                                borderColor: 'rgba(75, 192, 192, 1)', // Line color
                                backgroundColor: 'rgba(75, 192, 192, 0.2)', // Area color
                                fill: true, // Fill the area under the line
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                x: {
                                    title: {
                                        display: true,
                                        text: type === 'daily' ? 'Date' : 'Month'
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
                }
            });
    }

    // Ensure the function is accessible globally
    window.updateChart = updateChart;
    $(document).ready(function() {
        updateChart('daily');

        // Event listeners for daily and monthly tabs
        $('a[href="#dailyTab"]').on('click', function() {
            updateChart('daily');
        });

        $('a[href="#monthlyTab"]').on('click', function() {
            updateChart('monthly');
        });


        $('#salesBtn').on('click', function() {
            $('#salesContent').show();
            $('#marketingContent').hide();
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

        $('#ordersTable').DataTable({
            processing: true,
            serverSide: true, // Enable server-side processing
            ajax: '{{ route('product.orders', $product->id) }}', // AJAX call to fetch orders
            columns: [
                { data: 'id_order', name: 'id_order' },
                { data: 'customer_name', name: 'customer_name' },
                { data: 'qty', name: 'qty' },
                { data: 'total_price', name: 'total_price' },
                { data: 'shipment', name: 'shipment' },
                { data: 'sku', name: 'sku' },
                { data: 'date', name: 'date' }
            ],
            order: [[6, 'desc']] // Order by date
        });
        $('#talentContentTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('product.talent-content', $product->id) }}',
            columns: [
                { data: 'id', name: 'id' },
                { data: 'campaign_id', name: 'campaign_id' },
                { data: 'talent_id', name: 'talent_id' },
                { data: 'transfer_date', name: 'transfer_date' },
                { data: 'posting_date', name: 'posting_date' },
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
            language: {
                processing: '<div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div>'
            }
        });

        $.ajax({
            url: '{{ route('product.getOrderCountBySku', $product->id) }}',
            method: 'GET',
            success: function(response) {
                var ctx = document.getElementById('skuOrderCountChart').getContext('2d');
                var skuOrderCountChart = new Chart(ctx, {
                    type: 'pie',  // Change to 'pie' for a pie chart
                    data: {
                        labels: response.map(item => item.sku), // SKU labels
                        datasets: [{
                            label: 'Order Count',
                            data: response.map(item => item.count), // Order count values
                            backgroundColor: [
                                'rgba(75, 192, 192, 0.6)', 
                                'rgba(255, 99, 132, 0.6)', 
                                'rgba(54, 162, 235, 0.6)',
                                'rgba(153, 102, 255, 0.6)',
                                'rgba(255, 159, 64, 0.6)', 
                            ],
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: false, // Position of the legend
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(tooltipItem) {
                                        // Format the tooltip to display the count with 'SKU: count'
                                        return tooltipItem.label + ': ' + tooltipItem.raw + ' orders';
                                    }
                                }
                            }
                        }
                    }
                });
            }
        });
        $.ajax({
            url: '{{ route('product.getOrderCountBySalesChannel', $product->id) }}',
            method: 'GET',
            success: function(response) {
                var ctx = document.getElementById('salesChannelOrderCountChart').getContext('2d');
                var salesChannelOrderCountChart = new Chart(ctx, {
                    type: 'bar',  // Bar chart
                    data: {
                        labels: response.labels, // Access the 'labels' directly from the response
                        datasets: [{
                            label: 'Order Count by Sales Channel',
                            data: response.data, // Access the 'data' directly from the response
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
                                display: false // Hide legend for the bar chart
                            }
                        }
                    }
                });
            }
        });

    });
</script>
@stop
