@extends('adminlte::page')

@section('title', 'Products')

@section('content_header')
    <h1>Product List</h1>
@stop

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div id="topProductCard" class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">🏆 Top Performing Product of the Month</h3>
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
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="col-12">
                    <div class="row">
                        <div class="col-auto">
                            <input type="month" id="monthFilter" class="form-control" value="{{ date('Y-m') }}">
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-primary" data-toggle="modal" data-target="#addProductModal">
                                <i class="fas fa-plus"></i> Add Product
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="row">
                    <!-- Single Products Table -->
                    <div class="col-md-6">
                        <h4>Single Products</h4>
                        <table id="singleProductsTable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>SKU</th>
                                    <th>Product Name</th>
                                    <th>Jumlah Order</th>
                                    <th>Harga Jual</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                    
                    <!-- Combination Products Table -->
                    <div class="col-md-6">
                        <h4>Combination Products</h4>
                        <table id="combinationProductsTable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>SKU</th>
                                    <th>Product Name</th>
                                    <th>Jumlah Order</th>
                                    <th>Harga Jual</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                        </table>
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
    .medal-icon {
        position: relative;
        top: -2px; /* Slight vertical adjustment */
        margin-left: 5px; /* Space between rank number and medal */
    }

    .medal-gold {
        color: #FFD700; /* Gold color */
        text-shadow: 0 0 5px rgba(255, 215, 0, 0.5);
    }

    .medal-silver {
        color: #C0C0C0; /* Silver color */
        text-shadow: 0 0 5px rgba(192, 192, 192, 0.5);
    }

    .medal-bronze {
        color: #CD7F32; /* Bronze color */
        text-shadow: 0 0 5px rgba(205, 127, 50, 0.5);
    }
</style>
@endsection

@section('js')
    <!-- DataTables JS -->
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.5.1/dist/confetti.browser.min.js"></script>
    <script>
        $(document).ready(function() {
            Swal.fire({
                title: 'Loading Products',
                html: 'Please wait while we prepare your data...',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            let loadingCounter = 0;
            const totalLoads = 3; // Top product + 2 tables

            function checkAllLoaded() {
                loadingCounter++;
                if (loadingCounter === totalLoads) {
                    Swal.close();   
                }
            }

            var initialMonth = $('#monthFilter').val();
            
            // Function to initialize DataTable
            function initProductTable(tableId, type) {
                return $('#' + tableId).DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: { 
                        url: '{{ route('product.data') }}',
                        data: function(d) {
                            d.month = $('#monthFilter').val();
                            d.type = type;
                        },
                        dataSrc: function(response) {
                            checkAllLoaded();
                            return response.data;
                        }
                    },
                    columns: [
                        { 
                            data: null, 
                            name: 'rank', 
                            render: function(data, type, row, meta) {
                                var rank = meta.row + 1;

                                if (rank === 1) {
                                    return rank + ' <i class="fas fa-medal fa-2x medal-icon medal-gold"></i>'; // Gold Medal for rank 1
                                } else if (rank === 2) {
                                    return rank + ' <i class="fas fa-medal fa-2x medal-icon medal-silver"></i>'; // Silver Medal for rank 2
                                } else if (rank === 3) {
                                    return rank + ' <i class="fas fa-medal fa-2x medal-icon medal-bronze"></i>'; // Bronze Medal for rank 3
                                } else {
                                    return rank;
                                }
                            }
                        },
                        { data: 'sku', name: 'sku' },
                        { 
                            data: 'product', 
                            name: 'product', 
                            render: function(data, type, row) {
                                return '<a href="' + '{{ route('product.show', ':id') }}'.replace(':id', row.id) + '">' + data + '</a>';
                            }
                        },
                        { 
                            data: 'order_count', 
                            name: 'order_count', 
                            render: function(data, type, row) {
                                if (data == null) {
                                    return '';
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
                            render: function(data, type, row) {
                                if (data == null) {
                                    return '';
                                }
                                return 'Rp ' + parseFloat(data).toLocaleString('id-ID', {
                                    minimumFractionDigits: 0,
                                    maximumFractionDigits: 0
                                });
                            }
                        },
                        { data: 'action', name: 'action', orderable: false, searchable: false }
                    ],
                    order: [[3, 'desc']], 
                    drawCallback: function(settings) {
                        var api = this.api();
                        api.rows().every(function() {
                            var row = this.node();
                            var rankCell = $(row).find('td').eq(0); // The rank column (0 index)
                            var rank = api.row(row).index() + 1; // Get the rank (1-based index)

                            // Set the rank and add the medal icon
                            if (rank === 1) {
                                rankCell.html(rank + ' <i class="fas fa-medal fa-2x medal-icon medal-gold"></i>'); // Gold Medal
                            } else if (rank === 2) {
                                rankCell.html(rank + ' <i class="fas fa-medal fa-2x medal-icon medal-silver"></i>'); // Silver Medal
                            } else if (rank === 3) {
                                rankCell.html(rank + ' <i class="fas fa-medal fa-2x medal-icon medal-bronze"></i>'); // Bronze Medal
                            } else {
                                rankCell.html(rank); // For all other ranks
                            }
                        });
                    }
                });
            }

            // Initialize both tables
            var singleTable = initProductTable('singleProductsTable', 'Single');
            var combinationTable = initProductTable('combinationProductsTable', 'Combination');

            $('#monthFilter').on('change', function() {
                Swal.fire({
                    title: 'Updating Data',
                    html: 'Please wait...',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                loadingCounter = 0;
                singleTable.ajax.reload();
                combinationTable.ajax.reload();

                var month = $(this).val();
                fetchTopProduct(month);
            });

            function fetchTopProduct(month) {
                $.ajax({
                    url: '{{ route('product.top') }}', // You'll need to create this route
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
                        checkAllLoaded();
                    },
                    error: function() {
                        $('#topProductContent').html('<p class="text-danger">Failed to load top product.</p>');
                        checkAllLoaded();
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

            // Click handler for view buttons on both tables
            $('#singleProductsTable, #combinationProductsTable').on('click', '.viewButton', function() {
                var id = $(this).data('id');
                window.location.href = '{{ route('product.show', ':id') }}'.replace(':id', id);
            });

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
                var table = $(this).closest('table').DataTable();
                var row = table.row($(this).closest('tr'));
                
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