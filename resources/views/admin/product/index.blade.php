@extends('adminlte::page')

@section('title', 'Products')

@section('content_header')
    <h1>Product List</h1>
@stop

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div id="topProductCard" class="card card-primary card-outline shadow-sm">
            <div class="card-header bg-light">
                <h3 class="card-title"><i class="fas fa-trophy text-warning mr-2"></i>Top Performing Product of the Month</h3>
            </div>
            <div class="card-body">
                <div id="topProductContent" class="text-center">
                    <p class="text-muted">Loading top product...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <div class="row align-items-center">
                    <div class="col-md-3">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                            </div>
                            <input type="month" id="monthFilter" class="form-control" value="{{ date('Y-m') }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h4 class="mb-0 text-center">Product Performance Dashboard</h4>
                    </div>
                    <div class="col-md-3 text-right">
                        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addProductModal">
                            <i class="fas fa-plus mr-1"></i> Add Product
                        </button>
                    </div>
                </div>
            </div>

            <div class="card-body pb-0">
                <!-- Product Tables Stacked -->
                <div class="row">
                    <div class="col-12 mb-4">
                        <div class="card">
                            <div class="card-header bg-gradient-light py-2">
                                <h5 class="mb-0"><i class="fas fa-box mr-2"></i>Single Products</h5>
                            </div>
                            <div class="card-body p-0">
                                <table id="singleProductsTable" class="table table-striped table-hover m-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th width="60">Rank</th>
                                            <th>SKU</th>
                                            <th>Product</th>
                                            <th>Direct Orders Qty</th>
                                            <th>Bundle Usage Qty</th>
                                            <th>Total</th>
                                            <th>Price</th>
                                            <th width="100">Actions</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Bundle Products Table - Full Width -->
                    <div class="col-12 mb-4">
                        <div class="card">
                            <div class="card-header bg-gradient-light py-2">
                                <h5 class="mb-0"><i class="fas fa-boxes mr-2"></i>Bundle Products</h5>
                            </div>
                            <div class="card-body p-0">
                                <table id="combinationProductsTable" class="table table-striped table-hover m-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th width="60">Rank</th>
                                            <th>SKU</th>
                                            <th>Product</th>
                                            <th>Combination SKUs</th>
                                            <th>Orders</th>
                                            <th>Price</th>
                                            <th width="100">Actions</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('admin.product.modals.add_product')
@include('admin.product.modals.edit_product')
@stop

@section('css')
<style>
    /* Medal styling */
    .medal-icon {
        position: relative;
        top: -2px;
        margin-left: 5px;
    }

    .medal-gold {
        color: #FFD700;
        text-shadow: 0 0 5px rgba(255, 215, 0, 0.5);
    }

    .medal-silver {
        color: #C0C0C0;
        text-shadow: 0 0 5px rgba(192, 192, 192, 0.5);
    }

    .medal-bronze {
        color: #CD7F32;
        text-shadow: 0 0 5px rgba(205, 127, 50, 0.5);
    }

    /* DataTable improvements */
    .table {
        width: 100% !important;
    }
    
    .dataTables_wrapper .row {
        margin: 0;
    }
    
    .dataTables_filter, .dataTables_length {
        padding: 8px 15px;
    }
    
    .dataTables_info, .dataTables_paginate {
        padding: 10px 15px;
    }
    
    .dataTables_length select, .dataTables_filter input {
        border-radius: 4px;
        border: 1px solid #ced4da;
        padding: 4px 8px;
    }
    
    .card-header.bg-gradient-light {
        background: linear-gradient(to right, #f8f9fa, #e9ecef);
    }
    
    /* Action button spacing */
    .btn-sm {
        margin-right: 3px;
    }
    
    /* Helper class for smaller icons */
    .fas.fa-sm {
        font-size: 0.8em;
    }
    
    /* Compact table */
    .table-compact td, .table-compact th {
        padding: 0.5rem;
    }
    
    /* Improve hover effects */
    .table-hover tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
    }
    
    /* Better scrolling for tables */
    .card-body-scroll {
        max-height: 500px;
        overflow-y: auto;
    }
</style>
@endsection

@section('js')
    <!-- DataTables JS -->
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.5.1/dist/confetti.browser.min.js"></script>
    <script>
        $(document).ready(function() {
            var initialMonth = $('#monthFilter').val();
            
            function initProductTable(tableId, type) {
                // Define columns based on table type
                let columns;
                
                if (type === 'Single') {
                    columns = [
                        { 
                            data: null, 
                            name: 'rank',
                            className: 'text-center',
                            width: '60px',
                            render: function(data, type, row, meta) {
                                var rank = meta.row + 1;

                                if (rank === 1) {
                                    return rank + ' <i class="fas fa-medal medal-icon medal-gold"></i>';
                                } else if (rank === 2) {
                                    return rank + ' <i class="fas fa-medal medal-icon medal-silver"></i>';
                                } else if (rank === 3) {
                                    return rank + ' <i class="fas fa-medal medal-icon medal-bronze"></i>';
                                } else {
                                    return rank;
                                }
                            }
                        },
                        { 
                            data: 'sku', 
                            name: 'sku',
                            className: 'text-nowrap'
                        },
                        { 
                            data: 'product', 
                            name: 'product',
                            className: 'font-weight-medium',
                            render: function(data, type, row) {
                                return '<a href="' + '{{ route('product.show', ':id') }}'.replace(':id', row.id) + '">' + data + '</a>';
                            }
                        },
                        { 
                            data: 'direct_orders', 
                            name: 'direct_orders',
                            className: 'text-right',
                            render: function(data, type, row) {
                                if (data == null) {
                                    return '0';
                                }
                                return parseFloat(data).toLocaleString('id-ID', {
                                    minimumFractionDigits: 0,
                                    maximumFractionDigits: 0
                                });
                            }
                        },
                        { 
                            data: 'bundle_usage', 
                            name: 'bundle_usage',
                            className: 'text-right',
                        },
                        { 
                            data: 'order_count', 
                            name: 'order_count',
                            className: 'text-right font-weight-bold',
                            render: function(data, type, row) {
                                if (data == null) {
                                    return '0';
                                }
                                return parseFloat(data).toLocaleString('id-ID', {
                                    minimumFractionDigits: 0,
                                    maximumFractionDigits: 0
                                });
                            }
                        },
                        { 
                            data: 'harga_jual', 
                            name: 'harga_jual',
                            className: 'text-right',
                            render: function(data, type, row) {
                                if (data == null) {
                                    return '-';
                                }
                                return 'Rp ' + parseFloat(data).toLocaleString('id-ID', {
                                    minimumFractionDigits: 0,
                                    maximumFractionDigits: 0
                                });
                            }
                        },
                        { 
                            data: 'action', 
                            name: 'action', 
                            orderable: false, 
                            searchable: false,
                            className: 'text-center',
                            width: '100px',
                            render: function(data, type, row) {
                                return `
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('product.show', ':id') }}".replace(':id', row.id) class="btn btn-xs btn-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button class="btn btn-xs btn-success editButton" data-id="${row.id}" title="Edit">
                                            <i class="fas fa-pencil-alt"></i>
                                        </button>
                                        <button class="btn btn-xs btn-danger deleteButton" data-id="${row.id}" title="Delete">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                `;
                            }
                        }
                    ];
                } else {
                    // Bundle product columns
                    columns = [
                        { 
                            data: null, 
                            name: 'rank',
                            className: 'text-center',
                            width: '60px',
                            render: function(data, type, row, meta) {
                                var rank = meta.row + 1;

                                if (rank === 1) {
                                    return rank + ' <i class="fas fa-medal medal-icon medal-gold"></i>';
                                } else if (rank === 2) {
                                    return rank + ' <i class="fas fa-medal medal-icon medal-silver"></i>';
                                } else if (rank === 3) {
                                    return rank + ' <i class="fas fa-medal medal-icon medal-bronze"></i>';
                                } else {
                                    return rank;
                                }
                            }
                        },
                        { 
                            data: 'sku', 
                            name: 'sku',
                            className: 'text-nowrap'
                        },
                        { 
                            data: 'product', 
                            name: 'product',
                            className: 'font-weight-medium',
                            render: function(data, type, row) {
                                return '<a href="' + '{{ route('product.show', ':id') }}'.replace(':id', row.id) + '">' + data + '</a>';
                            }
                        },
                        { 
                            data: 'combination_skus', 
                            name: 'combination_skus',
                            className: 'text-center',
                            orderable: false
                        },
                        { 
                            data: 'order_count', 
                            name: 'order_count',
                            className: 'text-right font-weight-bold',
                            render: function(data, type, row) {
                                if (data == null) {
                                    return '0';
                                }
                                return parseFloat(data).toLocaleString('id-ID', {
                                    minimumFractionDigits: 0,
                                    maximumFractionDigits: 0
                                });
                            }
                        },
                        { 
                            data: 'harga_jual', 
                            name: 'harga_jual',
                            className: 'text-right',
                            render: function(data, type, row) {
                                if (data == null) {
                                    return '-';
                                }
                                return 'Rp ' + parseFloat(data).toLocaleString('id-ID', {
                                    minimumFractionDigits: 0,
                                    maximumFractionDigits: 0
                                });
                            }
                        },
                        { 
                            data: 'action', 
                            name: 'action', 
                            orderable: false, 
                            searchable: false,
                            className: 'text-center',
                            width: '100px',
                            render: function(data, type, row) {
                                return `
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('product.show', ':id') }}".replace(':id', row.id) class="btn btn-xs btn-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button class="btn btn-xs btn-success editButton" data-id="${row.id}" title="Edit">
                                            <i class="fas fa-pencil-alt"></i>
                                        </button>
                                        <button class="btn btn-xs btn-danger deleteButton" data-id="${row.id}" title="Delete">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                `;
                            }
                        }
                    ];
                }

                return $('#' + tableId).DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    ajax: { 
                        url: '{{ route('product.data') }}',
                        data: function(d) {
                            d.month = $('#monthFilter').val();
                            d.type = type;
                        }
                    },
                    columns: columns,
                    order: [[type === 'Single' ? 5 : 4, 'desc']], // Order by total count column
                    language: {
                        processing: '<div class="text-center my-2"><i class="fas fa-spinner fa-spin fa-2x fa-fw"></i><div class="mt-2">Loading...</div></div>',
                        search: '<i class="fas fa-search"></i> _INPUT_',
                        searchPlaceholder: 'Search...',
                        lengthMenu: '<i class="fas fa-list-ol"></i> _MENU_',
                        info: 'Showing _START_ to _END_ of _TOTAL_ products',
                        infoEmpty: 'No products found',
                        zeroRecords: 'No matching products found',
                        paginate: {
                            first: '<i class="fas fa-angle-double-left"></i>',
                            previous: '<i class="fas fa-angle-left"></i>',
                            next: '<i class="fas fa-angle-right"></i>',
                            last: '<i class="fas fa-angle-double-right"></i>'
                        }
                    },
                    dom: '<"row"<"col-sm-6"l><"col-sm-6"f>>rt<"row"<"col-sm-6"i><"col-sm-6"p>>',
                    pagingType: 'simple_numbers',
                    pageLength: 10,
                    lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, 'All']],
                    drawCallback: function(settings) {
                        var api = this.api();
                        api.rows().every(function() {
                            var row = this.node();
                            var rankCell = $(row).find('td').eq(0);
                            var rank = api.row(row).index() + 1;

                            if (rank === 1) {
                                rankCell.html(rank + ' <i class="fas fa-medal medal-icon medal-gold"></i>');
                            } else if (rank === 2) {
                                rankCell.html(rank + ' <i class="fas fa-medal medal-icon medal-silver"></i>');
                            } else if (rank === 3) {
                                rankCell.html(rank + ' <i class="fas fa-medal medal-icon medal-bronze"></i>');
                            } else {
                                rankCell.html(rank);
                            }
                        });
                    }
                });
            }

            // Initialize both tables
            var singleTable = initProductTable('singleProductsTable', 'Single');
            var combinationTable = initProductTable('combinationProductsTable', 'Bundle');

            $('#monthFilter').on('change', function() {
                // Instead of showing a global loading overlay, let the individual tables show their loading state
                singleTable.ajax.reload();
                combinationTable.ajax.reload();

                var month = $(this).val();
                fetchTopProduct(month);
            });

            function fetchTopProduct(month) {
                // Add loading indicator to top product card
                $('#topProductContent').html('<div class="text-center my-3"><i class="fas fa-spinner fa-spin fa-2x"></i><div class="mt-2">Loading top product...</div></div>');
                
                $.ajax({
                    url: '{{ route('product.top') }}',
                    method: 'GET',
                    data: { month: month },
                    success: function(response) {
                        if (response.product) {
                            // Create top product content
                            var content = `
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h2 class="font-weight-bold">${response.product}</h2>
                                        <p class="lead">
                                            <strong>SKU:</strong> ${response.sku}<br>
                                            <strong>Total Orders:</strong> ${response.order_count.toLocaleString('id-ID')}<br>
                                        </p>
                                    </div>
                                    <div class="col-md-4 text-center">
                                        <i class="fas fa-trophy fa-4x text-warning"></i>
                                    </div>
                                </div>
                            `;
                            $('#topProductContent').html(content);
                            
                            // Trigger confetti
                            fireConfetti();
                        } else {
                            $('#topProductContent').html('<p class="text-muted">No top product found this month.</p>');
                        }
                    },
                    error: function() {
                        $('#topProductContent').html('<p class="text-danger">Failed to load top product.</p>');
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Failed to load top product data.',
                        });
                    }
                });
            }
            fetchTopProduct(initialMonth);

            function fireConfetti() {
                // Use canvas-confetti library
                if (window.confetti) {
                    confetti({
                        particleCount: 100,
                        spread: 70,
                        origin: { y: 0.6 },
                        colors: ['#FFD700', '#FFA500', '#FFFF00']
                    });
                }
            }

            // Click handler for edit buttons on both tables
            $('#singleProductsTable, #combinationProductsTable').on('click', '.editButton', function() {
                var id = $(this).data('id');
                $.ajax({
                    url: '{{ route('product.edit', ':id') }}'.replace(':id', id),
                    method: 'GET',
                    success: function(response) {
                        $('#editProductForm').attr('action', '{{ route('product.update', ':id') }}'.replace(':id', id));
                        $('#edit_product_name').val(response.product.product);
                        $('#edit_stock').val(response.product.stock);
                        $('#edit_sku').val(response.product.sku);
                        $('#edit_harga_jual').val(response.product.harga_jual);
                        $('#edit_harga_markup').val(response.product.harga_markup);
                        $('#edit_harga_satuan').val(response.product.harga_satuan);
                        $('#edit_harga_cogs').val(response.product.harga_cogs);
                        $('#edit_harga_batas_bawah').val(response.product.harga_batas_bawah);
                        $('#editProductModal').modal('show');
                    },
                    error: function() {
                        Swal.fire('Error', 'Failed to load product data', 'error');
                    }
                });
            });

            // Click handler for delete buttons on both tables
            $('#singleProductsTable, #combinationProductsTable').on('click', '.deleteButton', function() {
                var id = $(this).data('id');
                
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ route('product.destroy', ':id') }}'.replace(':id', id),
                            type: 'DELETE',
                            data: {
                                '_token': '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                singleTable.ajax.reload();
                                combinationTable.ajax.reload();
                                Swal.fire(
                                    'Deleted!',
                                    'Product has been deleted.',
                                    'success'
                                );
                            },
                            error: function(response) {
                                Swal.fire(
                                    'Error!',
                                    'There was an error deleting the product.',
                                    'error'
                                );
                                console.error('Error deleting product:', response);
                            }
                        });
                    }
                });
            });

            $('#addProductForm').on('submit', function(e) {
                e.preventDefault();
                var form = $(this);
                var url = form.attr('action');

                $.ajax({
                    type: "POST",
                    url: url,
                    data: form.serialize(),
                    success: function(response) {
                        $('#addProductModal').modal('hide');
                        singleTable.ajax.reload();
                        combinationTable.ajax.reload();
                        Swal.fire('Success', 'Product added successfully!', 'success');
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            var errors = xhr.responseJSON.errors;
                            // Display error messages
                            if (errors.product) {
                                $('#product').addClass('is-invalid');
                                $('#product-error').text(errors.product[0]).show();
                            }
                            if (errors.stock) {
                                $('#stock').addClass('is-invalid');
                                $('#stock-error').text(errors.stock[0]).show();
                            }
                        } else {
                            Swal.fire('Error', 'Failed to add product', 'error');
                        }
                    }
                });
            });

            $('#editProductForm').on('submit', function(e) {
                e.preventDefault();
                var form = $(this);
                var url = form.attr('action');
                
                $.ajax({
                    type: "PUT",
                    url: url,
                    data: form.serialize(),
                    success: function(response) {
                        $('#editProductModal').modal('hide');
                        singleTable.ajax.reload();
                        combinationTable.ajax.reload();
                        Swal.fire('Success', 'Product updated successfully!', 'success');
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            var errors = xhr.responseJSON.errors;
                            // Display error messages
                            if (errors.product) {
                                $('#edit_product_name').addClass('is-invalid');
                                $('#edit_product_name-error').text(errors.product[0]).show();
                            }
                            if (errors.stock) {
                                $('#edit_stock').addClass('is-invalid');
                                $('#edit_stock-error').text(errors.stock[0]).show();
                            }
                        } else {
                            Swal.fire('Error', 'Failed to update product', 'error');
                        }
                    }
                });
            });

            $('#addProductModal').on('hidden.bs.modal', function () {
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').hide();
            });
        });
    </script>
@stop