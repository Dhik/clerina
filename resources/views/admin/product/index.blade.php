@extends('adminlte::page')

@section('title', 'Products')

@section('content_header')
    <h1>Product List</h1>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <button class="btn btn-primary" data-toggle="modal" data-target="#addProductModal">
                    <i class="fas fa-plus"></i> Add Product
                </button>
            </div>
            <div class="card-body">
                <table id="productsTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Product Name</th>
                            <th>Harga Jual</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

    

    @include('admin.product.modals.add_product')
    @include('admin.product.modals.edit_product')
    @include('admin.product.modals.view_product')
@stop


@section('js')
    <!-- DataTables JS -->
    <script>
        $(document).ready(function() {
            var table = $('#productsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('product.data') }}',
                columns: [
                    { data: 'sku', name: 'sku' },
                    { data: 'product', name: 'product' },
                    { data: 'harga_jual', name: 'harga_jual' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                order: [[2, 'desc']] 
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
                        $('#productsTable').DataTable().ajax.reload();
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

            $('#addProductModal').on('hidden.bs.modal', function () {
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').hide();
            });

            $('#productsTable').on('click', '.viewButton', function() {
                var id = $(this).data('id');
                $.ajax({
                    url: '{{ route('product.show', ':id') }}'.replace(':id', id),
                    method: 'GET',
                    success: function(response) {
                        // Populate the view modal with product data
                        $('#view_product_name').val(response.product.product);
                        $('#view_stock').val(response.product.stock);
                        $('#view_sku').val(response.product.sku);
                        $('#view_harga_jual').val(response.product.harga_jual);
                        $('#viewProductModal').modal('show');
                    },
                    error: function() {
                        Swal.fire('Error', 'Failed to load product details', 'error');
                    }
                });
            });

            $('#productsTable').on('click', '.editButton', function() {
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
                        $('#edit_harga_cogs').val(response.product.harga_cogs);
                        $('#edit_harga_batas_bawah').val(response.product.harga_batas_bawah);
                        $('#editProductModal').modal('show');
                    },
                    error: function() {
                        Swal.fire('Error', 'Failed to load product data', 'error');
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
                        $('#productsTable').DataTable().ajax.reload();
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


            $('#productsTable').on('click', '.deleteButton', function() {
                let rowData = table.row($(this).closest('tr')).data();
                let route = '{{ route('product.destroy', ':id') }}'.replace(':id', rowData.id);

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
                            url: route,
                            type: 'DELETE',
                            data: {
                                '_token': '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                table.ajax.reload(); // Reload the table after deletion
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

        });
    </script>
@stop
