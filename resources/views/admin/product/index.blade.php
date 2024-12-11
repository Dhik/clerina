@extends('adminlte::page')

@section('title', 'Products')

@section('content_header')
    <h1>Product List</h1>
@stop

@section('content')
    <a href="{{ route('products.create') }}" class="btn btn-primary mb-3">Create New Product</a>
    
    <table class="table">
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Stock</th>
                <th>SKU</th>
                <th>Price</th>
                <th>Actions</th>
            </tr>
        </thead>
    </table>
@stop
