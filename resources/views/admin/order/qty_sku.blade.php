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
                <div class="col-md-1">
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
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailModalLabel">Order Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table id="detailTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>SKU</th>
                                <th>Quantity</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $(document).ready(function() {
            let detailTable;
            
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
                    { "data": "quantity" },
                    { 
                        "data": null,
                        "render": function(data, type, row) {
                            return '<button class="btn btn-info btn-sm detail-btn" data-sku="' + row.sku + '">Detail Data</button>';
                        }
                    }
                ],
                "order": [[1, "desc"]], 
                "pageLength": 25,
                "language": {
                    "search": "Search:",
                    "lengthMenu": "Show _MENU_ entries",
                    "processing": "Loading...",
                }
            });

            $('#skuTable').on('click', '.detail-btn', function() {
                let sku = $(this).data('sku');
                let date = $('#dateFilter').val();
                
                if (detailTable) {
                    detailTable.destroy();
                }

                detailTable = $('#detailTable').DataTable({
                    "processing": true,
                    "ajax": {
                        "url": "{{ route('order.sku_detail') }}",
                        "data": {
                            sku: sku,
                            date: date
                        }
                    },
                    "columns": [
                        { "data": "id_order" },
                        { "data": "date" },
                        { "data": "customer_name" },
                        { "data": "sku" },
                        { "data": "qty" },
                        { 
                            "data": "status",
                            "render": function(data, type, row) {
                                let statusClass = '';
                                switch(data?.toLowerCase()) {
                                    case 'completed':
                                        statusClass = 'badge badge-success';
                                        break;
                                    case 'pending':
                                        statusClass = 'badge badge-warning';
                                        break;
                                    case 'cancelled':
                                        statusClass = 'badge badge-danger';
                                        break;
                                    default:
                                        statusClass = 'badge badge-secondary';
                                }
                                return `<span class="${statusClass}">${data || '-'}</span>`;
                            }
                        }
                    ],
                    "pageLength": 10
                });

                $('#detailModalLabel').text('Order Details - ' + sku);
                $('#detailModal').modal('show');
            });

            $('#filterButton').click(function() {
                Swal.fire({
                    title: 'Loading...',
                    html: 'Fetching data for the selected date',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                table.ajax.reload(function() {
                    Swal.close();
                });

                let newDate = $('#dateFilter').val();
                let exportUrl = "{{ route('order.sku_qty_export') }}?date=" + newDate;
                $('#exportButton').attr('href', exportUrl);
            });
        });
    </script>
@stop