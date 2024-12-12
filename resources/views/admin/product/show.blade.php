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
            <div class="col-md-6">
                <div class="form-group">
                    <label for="product_name">Product Name</label>
                    <input type="text" id="product_name" class="form-control" value="{{ $product->product }}" readonly>
                </div>
                <div class="form-group">
                    <label for="sku">SKU</label>
                    <input type="text" id="sku" class="form-control" value="{{ $product->sku }}" readonly>
                </div>
                <div class="form-group">
                    <label for="stock">Stock</label>
                    <input type="number" id="stock" class="form-control" value="{{ $product->stock }}" readonly>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="harga_jual">Harga Jual</label>
                    <input type="text" id="harga_jual" class="form-control" value="Rp {{ number_format($product->harga_jual, 0, ',', '.') }}" readonly>
                </div>
                <div class="form-group">
                    <label for="harga_markup">Harga Markup</label>
                    <input type="text" id="harga_markup" class="form-control" value="Rp {{ number_format($product->harga_markup, 0, ',', '.') }}" readonly>
                </div>
                <div class="form-group">
                    <label for="harga_cogs">Harga COGS</label>
                    <input type="text" id="harga_cogs" class="form-control" value="Rp {{ number_format($product->harga_cogs, 0, ',', '.') }}" readonly>
                </div>
                <div class="form-group">
                    <label for="harga_batas_bawah">Harga Batas Bawah</label>
                    <input type="text" id="harga_batas_bawah" class="form-control" value="Rp {{ number_format($product->harga_batas_bawah, 0, ',', '.') }}" readonly>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
