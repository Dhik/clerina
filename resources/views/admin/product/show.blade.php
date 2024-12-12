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
            <div class="col-lg-3 col-6">
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
                <div class="col-lg-3 col-6">
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
                <div class="col-lg-3 col-6">
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
                <div class="col-lg-3 col-6">
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
                        @foreach($orders as $order)
                            <tr>
                                <td>{{ $order->id_order }}</td>
                                <td>{{ $order->customer_name }}</td>
                                <td>{{ $order->qty }}</td>
                                <td>{{ number_format($order->amount, 0, ',', '.') }}</td>
                                <td>{{ $order->shipment }}</td>
                                <td>{{ $order->sku }}</td>
                                <td>{{ $order->date }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<!-- DataTables JS -->
<script>
    $(document).ready(function() {
        $('#ordersTable').DataTable({
            processing: true,
            serverSide: false, // Because we're passing data directly from the controller
            paging: true,
            ordering: true,
            order: [[6, 'desc']] // Order by date
        });
    });
</script>
@stop

