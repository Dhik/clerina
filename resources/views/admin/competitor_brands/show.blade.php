@extends('adminlte::page')

@section('title', 'Competitor Brand Details')

@section('content_header')
    <h1>Competitor Brand: {{ $competitorBrand->brand }}</h1>
@stop

@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ $competitorBrand->brand }}</h3>
                <a href="{{ route('competitor_brands.index') }}" class="btn btn-secondary float-right">Back to List</a>
            </div>
            <div class="card-body">
                
                <div class="row">
                    <div class="col-3">
                        <div class="form-group">
                        @if($competitorBrand->logo)
                            <img src="{{ asset('storage/' . $competitorBrand->logo) }}" alt="Brand Logo" class="img-fluid" width="350">
                        @else
                            <p>No Logo</p>
                        @endif
                        </div>

                        <h4>Competitor Sales</h4>
                        <div class="row">
                            <div class="col-12">
                                <div class="small-box bg-info">
                                    <div class="inner">
                                        <h4 id="newSalesCount">0</h4>
                                        <p>Total Omset</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-chart-line"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-primary mb-4" data-toggle="modal" data-target="#addCompetitorSaleModal">
                            <i class="fas fa-plus"></i> Add Competitor Sale
                        </button>
                        <div class="row">
                            <label for="channel">Channel</label>
                            <select name="channel" id="filterChannel" class="form-control" required>
                                <option value="" disabled selected>Select a channel</option>
                                <option value="Instagram">Instagram</option>
                                <option value="Tiktok">Tiktok</option>
                                <option value="Twitter">Twitter</option>
                                <option value="Shopee">Shopee</option>
                            </select>
                        </div>
                        <div class="row">
                            <label for="type">Type</label>
                            <select name="type" id="filterType" class="form-control" required>
                                <option value="" disabled selected>Select a type</option>
                                <option value="Direct">Direct</option>
                                <option value="Indirect">Indirect</option>
                            </select>
                        </div>
                        <div class="row">
                            <button id="resetFilters" class="btn btn-secondary mt-4">Reset Filters</button>
                        </div>
                    </div>
                    <div class="col-9">
                        <div class="card">
                            <div class="card-body">
                                <canvas id="competitorSalesChart" class="w-100"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <table id="competitor-sales-table" class="table table-bordered mt-3">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Channel</th>
                            <th>Omset</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Include Add Competitor Sale Modal -->
@include('admin.competitor_brands.modals.add_competitor_sale_modal', ['competitorBrand' => $competitorBrand])

<!-- View Competitor Sale Modal -->
<div class="modal fade" id="viewCompetitorSaleModal" tabindex="-1" aria-labelledby="viewCompetitorSaleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewCompetitorSaleModalLabel">Competitor Sale Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="sale_channel">Channel</label>
                    <input type="text" id="sale_channel" class="form-control" readonly>
                </div>
                <div class="form-group">
                    <label for="sale_omset">Omset</label>
                    <input type="text" id="sale_omset" class="form-control" readonly>
                </div>
                <div class="form-group">
                    <label for="sale_date">Date</label>
                    <input type="text" id="sale_date" class="form-control" readonly>
                </div>
                <div class="form-group">
                    <label for="sale_type">Type</label>
                    <input type="text" id="sale_type" class="form-control" readonly>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@include('admin.competitor_brands.modals.edit_competitor_sale_modal')
@stop

@section('js')
<script>
    $(function() {
        var table = $('#competitor-sales-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('competitor_brand.sales_data', $competitorBrand->id) }}',
                data: function(d) {
                    d.filterChannel = $('#filterChannel').val();
                    d.filterType = $('#filterType').val(); 
                }
            },
            columns: [
                { data: 'id', name: 'id' },
                { data: 'channel', name: 'channel' },
                { data: 'omset', name: 'omset' },
                { data: 'date', name: 'date' },
                { data: 'type', name: 'type' },
                {
                    data: 'id',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        return `
                            <button class="btn btn-sm btn-success editButton" data-id="${data}">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                            <button class="btn btn-sm btn-danger deleteButton" data-id="${data}">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        `;
                    }
                }
            ]
        });

        function updateTotalOmsetAndChart() {
        // Fetch and update total omset
        $.ajax({
            url: '{{ route('competitor_brand.sales_data', $competitorBrand->id) }}',
            method: 'GET',
            data: {
                filterChannel: $('#filterChannel').val(),
                filterType: $('#filterType').val(),
            },
            success: function(response) {
                var totalOmset = response.data.reduce(function (accumulator, sale) {
                    return accumulator + parseFloat(sale.omset);
                }, 0);

                $('#newSalesCount').text(totalOmset.toLocaleString());
                initSalesChart($('#filterChannel').val(), $('#filterType').val());
            }
        });
    }
        

        $('#filterChannel').change(function() {
            table.draw(); 
            updateTotalOmsetAndChart();
        });

        $('#filterType').change(function() {
            table.draw(); 
            updateTotalOmsetAndChart();
        });

        $('#resetFilters').click(function() {
            $('#filterChannel').val('').change();
            $('#filterType').val('').change();

            table.draw();
            updateTotalOmsetAndChart();
        });

        updateTotalOmsetAndChart();

        // Handle the edit form submission
        $('#competitor-sales-table').on('click', '.editButton', function(e) {
            e.preventDefault();
            var saleId = $(this).data('id');
            console.log(saleId);
            var url = '{{ route("competitor_sales.edit", ":id") }}'.replace(':id', saleId);

            $.ajax({
                url: url,
                method: 'GET',
                success: function(response) {
                    if (response.competitorSales) {
                        // Populate the form with the retrieved data
                        $('#edit_channel').val(response.competitorSales.channel);
                        $('#edit_omset').val(response.competitorSales.omset);
                        $('#edit_date').val(response.competitorSales.date);
                        $('#edit_type').val(response.competitorSales.type);

                        // Update the form action to the correct route
                        $('#editCompetitorSaleForm').attr('action', '{{ route("competitor_sales.update", ":id") }}'.replace(':id', saleId));

                        // Open the modal
                        $('#editCompetitorSaleModal').modal('show');
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error!', 'Failed to fetch sale details.', 'error');
                }
            });
        });

        $('#editCompetitorSaleForm').on('submit', function(e) {
            e.preventDefault(); 
            var form = $(this);
            var url = form.attr('action');

            $.ajax({
                url: url,
                method: 'POST',
                data: form.serialize(),
                success: function(response) {
                    $('#editCompetitorSaleModal').modal('hide'); 
                    Swal.fire('Success!', 'Competitor sale updated successfully.', 'success');
                    table.ajax.reload(); 
                    updateTotalOmsetAndChart();
                },
                error: function(xhr) {
                    Swal.fire('Error!', 'Failed to update competitor sale.', 'error');
                }
            });
        });

        $('#competitor-sales-table').on('click', '.deleteButton', function(e) {
            e.preventDefault(); 
            var saleId = $(this).data('id');
            console.log(saleId);
            var url = '{{ route('competitor_sales.destroy', ':id') }}'.replace(':id', saleId);

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
                                'Competitor sale has been deleted.',
                                'success'
                            );
                            table.ajax.reload(); 
                        },
                        error: function(xhr) {
                            Swal.fire(
                                'Error!',
                                'There was an issue deleting the competitor sale.',
                                'error'
                            );
                        }
                    });
                }
            });
        });

        $('#competitor-sales-table').on('click', '.viewSaleButton', function() {
            var saleId = $(this).data('id');

            $.ajax({
                url: '{{ route("competitor_brand.show_sales", ":id") }}'.replace(':id', saleId),
                method: 'GET',
                success: function(response) {
                    if (response.competitorSale) {
                        $('#sale_channel').val(response.competitorSale.channel);
                        $('#sale_omset').val(response.competitorSale.omset);
                        $('#sale_date').val(response.competitorSale.date);
                        $('#sale_type').val(response.competitorSale.type);

                        $('#viewCompetitorSaleModal').modal('show');
                    }
                },
                error: function() {
                    alert('Error fetching sale details');
                }
            });
        });
    });
</script>

@include('admin.competitor_brands.script.script_chart')
@stop
