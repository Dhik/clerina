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
    <div class="card-body">
        <div class="row">
                <div class="col-6">
                    <div class="row">
                        <div class="col-6">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h4 id="newSalesCount">Rp {{ number_format($product->harga_jual, 0, ',', '.') }}</h4>
                                    <p>Harga Jual</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="small-box bg-purple">
                                <div class="inner">
                                    <h4 id="newVisitCount">Rp {{ number_format($product->harga_markup, 0, ',', '.') }}</h4>
                                    <p>Harga Markup</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-chart-pie"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h4 id="newOrderCount">Rp {{ number_format($product->harga_cogs, 0, ',', '.') }}</h4>
                                    <p>Harga COGS</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-chart-bar"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="small-box bg-teal">
                                <div class="inner">
                                    <h4 id="newRoasCount">Rp {{ number_format($product->harga_batas_bawah, 0, ',', '.') }}</h4>
                                    <p>Harga Batas Bawah</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-chart-area"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-6">
                    <h3>Order Count Per Day (SKU: {{ $product->sku }})</h3>
                    <canvas id="orderCountChart" width="400" height="200"></canvas>
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
<!-- DataTables JS -->
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
    });
</script>
@stop
