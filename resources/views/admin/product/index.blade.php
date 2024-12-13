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
                            <th>Rank</th>
                            <th>SKU</th>
                            <th width="50%">Product Name</th>
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

    

    @include('admin.product.modals.add_product')
    @include('admin.product.modals.edit_product')
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
                { 
                    data: null, 
                    name: 'rank', 
                    render: function(data, type, row, meta) {
                        var rank = meta.row + 1;

                        if (rank === 1) {
                            return rank + ' <i class="fas fa-medal text-warning"></i>'; // Gold Medal for rank 1
                        } else if (rank === 2) {
                            return rank + ' <i class="fas fa-medal text-secondary"></i>'; // Silver Medal for rank 2
                        } else if (rank === 3) {
                            return rank + ' <i class="fas fa-medal text-bronze"></i>'; // Bronze Medal for rank 3
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
            order: [[3, 'desc']], // Sort by order_count (column index 3) in descending order
            drawCallback: function(settings) {
                // After the table is drawn and sorted, update the rank column
                var api = this.api();
                api.rows().every(function() {
                    var row = this.node();
                    var rankCell = $(row).find('td').eq(0); // The rank column (0 index)
                    var rank = api.row(row).index() + 1; // Get the rank (1-based index)

                    // Set the rank and add the medal icon
                    if (rank === 1) {
                        rankCell.html(rank + ' <i class="fas fa-medal text-warning"></i>'); // Gold Medal
                    } else if (rank === 2) {
                        rankCell.html(rank + ' <i class="fas fa-medal text-secondary"></i>'); // Silver Medal
                    } else if (rank === 3) {
                        rankCell.html(rank + ' <i class="fas fa-medal text-bronze"></i>'); // Bronze Medal
                    } else {
                        rankCell.html(rank); // For all other ranks
                    }
                });
            }
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
                window.location.href = '{{ route('product.show', ':id') }}'.replace(':id', id);
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
