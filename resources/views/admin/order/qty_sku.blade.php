@extends('adminlte::page')

@section('title', trans('labels.order'))

@section('content_header')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Quantity per SKU</h1>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="mb-3 row">
                <div class="col-md-3">
                    <input type="date" id="dateFilter" class="form-control" value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-2">
                    <button id="filterButton" class="btn btn-primary">Filter</button>
                </div>
                <div class="col-md-2">
                    <a id="exportButton" href="{{ route('order.sku_qty_export') }}?date={{ date('Y-m-d') }}" class="btn btn-success">
                        <i class="fas fa-file-excel"></i> Export to Excel
                    </a>
                </div>
            </div>
            <table id="skuTable" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>SKU</th>
                        <th>Quantity</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
@stop

@section('js')
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $(document).ready(function() {
            let table = $('#skuTable').DataTable({
                "processing": true,
                "ajax": {
                    "url": "{{ route('order.sku_qty') }}",
                    "data": function(d) {
                        d.date = $('#dateFilter').val();
                    }
                },
                "columns": [
                    { "data": "sku" },
                    { "data": "quantity" }
                ],
                "order": [[1, "desc"]], 
                "pageLength": 25,
                "language": {
                    "search": "Search:",
                    "lengthMenu": "Show _MENU_ entries",
                    "processing": "Loading...",
                    "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                    "infoEmpty": "Showing 0 to 0 of 0 entries",
                    "infoFiltered": "(filtered from _MAX_ total entries)"
                }
            });

            $('#filterButton').click(function() {
                table.ajax.reload();
                // Update export button URL
                let newDate = $('#dateFilter').val();
                let exportUrl = "{{ route('order.sku_qty.export') }}?date=" + newDate;
                $('#exportButton').attr('href', exportUrl);
            });
        });
    </script>
@stop