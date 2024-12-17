@extends('adminlte::page')

@section('title', 'Product Details')

@section('content_header')
    <h1>Product Details</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <a href="{{ route('product.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Product List</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-8">
                <div class="card">
                    <div class="card-header">
                        <h3>Order Count Per Day (SKU: {{ $product->sku }})</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="orderCountChart" width="400" height="160"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-4 mb-3">
                <div class="card">
                    <div class="card-header">
                        <h3>Order Count by SKU (SKU: {{ $product->sku }})</h3>
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
                                    <td>3000</td>
                                </tr>
                                <tr>
                                    <td>Total Order</td>
                                    <td>7000</td>
                                </tr>
                                <tr>
                                    <td>Total Revenue</td>
                                    <td>Rp. 500.000.000</td>
                                </tr>
                                <tr>
                                    <td>Avg. Daily Order</td>
                                    <td>50</td>
                                </tr>
                                <tr>
                                    <td>Harga Jual</td>
                                    <td>Rp. 50.000</td>
                                </tr>
                                <tr>
                                    <td>Harga MarkUp</td>
                                    <td>Rp. 50.000</td>
                                </tr>
                                <tr>
                                    <td>Harga COGS</td>
                                    <td>Rp. 50.000</td>
                                </tr>
                                <tr>
                                    <td>Harga Batas Bawah</td>
                                    <td>Rp. 50.000</td>
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
                                <h1 id="newSalesCount">45 %</h1>
                            </div>
                            <div class="icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <p>Customer Repeat Rate</p>
                                <h1 id="newSalesCount">45 %</h1>
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
</div>

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
@stop


@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(document).ready(function() {
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
        $.ajax({
            url: '{{ route('product.getOrderCountPerDay', $product->id) }}',
            method: 'GET',
            success: function(response) {
                // Initialize the chart
                var ctx = document.getElementById('orderCountChart').getContext('2d');
                var orderCountChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: response.labels, // x-axis labels (dates)
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
