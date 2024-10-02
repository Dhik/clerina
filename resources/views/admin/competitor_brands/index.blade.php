@extends('adminlte::page')

@section('title', 'Competitor Brands')

@section('content_header')
    <h1>Competitor Brands</h1>
@stop

@section('content')

<div class="row">
    <div class="col-12">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Monthly Sales Cleora</h3>
                    </div>
                    <div class="card-body">
                        <img src="{{ asset('img/cleora-logo.png') }}" alt="Cleora Logo" style="width: 400px; height: auto;">
                    </div>
                    <div class="card-body">
                        <canvas id="cleoraSalesChart" class="w-100" style="height: 400px;"></canvas>
                    </div>
                </div>
            </div>
            <!-- <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Monthly Sales Competitor</h3>
                    </div>
                    <div class="card-body">
                        <img src="{{ asset('img/wardah.png') }}" alt="Cleora Logo" style="width: 200px; height: auto;">
                    </div>
                    <div class="card-body">
                        <canvas id="competitorSalesChart" class="w-100"></canvas>
                    </div>
                </div>
            </div> -->
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addCompetitorBrandModal">
                <i class="fas fa-plus"></i> Add Competitor Brand
                </button>
            </div>
            <div class="card-body">
                <table id="competitor-brands-table" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Logo</th>
                            <th>Brand</th>
                            <th>Keterangan</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@include('admin.competitor_brands.modals.add_competitor_brand_modal')
@include('admin.competitor_brands.modals.edit_competitor_brand_modal')

@stop

@section('js')
<script>
    $(function() {
        var baseUrl = "{{ asset('storage/') }}";
        var defaultImageUrl = "{{ asset('img/user.png') }}";
        var table = $('#competitor-brands-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('competitor_brands.data') }}',
            columns: [
                { data: 'id', name: 'id' },
                { 
                    data: 'logo', 
                    name: 'logo', 
                    render: function(data, type, row) {
                        var logoUrl = row.logo ? row.logo : defaultImageUrl;
                        return logoUrl;
                    }
                },
                { data: 'brand', name: 'brand' },
                { data: 'keterangan', name: 'keterangan' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });

        // Handle Edit button click
        $('#competitor-brands-table').on('click', '.editButton', function() {
            var id = $(this).data('id');
            $.ajax({
                url: '{{ route('competitor_brands.edit', ':id') }}'.replace(':id', id),
                method: 'GET',
                success: function(response) {
                    $('#editCompetitorBrandForm').attr('action', '{{ route('competitor_brands.update', ':id') }}'.replace(':id', id));
                    $('#edit_brand').val(response.competitorBrand.brand);
                    $('#edit_keterangan').val(response.competitorBrand.keterangan);
                    $('#editCompetitorBrandModal').modal('show');
                },
                error: function(response) {
                    console.error('Error fetching competitor brand data:', response);
                }
            });
        });

        // SweetAlert Confirmation for Delete
        $('#competitor-brands-table').on('click', '.deleteButton', function(e) {
            e.preventDefault(); // Prevent the default button behavior
            var id = $(this).data('id');
            var url = '{{ route('competitor_brands.destroy', ':id') }}'.replace(':id', id);

            Swal.fire({
                title: 'Are you sure?',
                text: 'This action cannot be undone!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            _method: 'DELETE'
                        },
                        success: function(response) {
                            Swal.fire(
                                'Deleted!',
                                'Competitor brand has been deleted.',
                                'success'
                            );
                            table.ajax.reload(); // Reload DataTable
                        },
                        error: function(xhr) {
                            Swal.fire(
                                'Error!',
                                'There was an issue deleting the competitor brand.',
                                'error'
                            );
                        }
                    });
                }
            });
        });

        // Clear form on modal close
        $('#addCompetitorBrandModal, #editCompetitorBrandModal').on('hidden.bs.modal', function () {
            $(this).find('form')[0].reset();
        });
    });
</script>
@include('admin.competitor_brands.script.script_cleora_sales')
@include('admin.competitor_brands.script.script_competitor_sales')
@stop
