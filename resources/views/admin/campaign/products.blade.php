@extends('adminlte::page')

@section('title', 'Distinct Products')

@section('content_header')
    <h1>Distinct Products</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <table id="productsTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Content will be loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Initialize the DataTable
            let productsTable = $('#productsTable').DataTable({
                "processing": true,
                "serverSide": false,
                "ajax": {
                    "url": "{{ route('campaignContent.getProduct') }}", // Using route name instead of URL
                    "method": "GET",
                    "dataSrc": function(json) {
                        return json.data; // Ensure it returns the 'data' array
                    }
                },
                "columns": [
                    { "data": "product" },
                    { "data": "actions", "orderable": false, "searchable": false }
                ],
                "pageLength": 10,
                "responsive": true
            });

            // Tooltips initialization for the dynamic content
            productsTable.on('draw.dt', function() {
                $('[data-toggle="tooltip"]').tooltip();
            });
        });
    </script>
@stop
