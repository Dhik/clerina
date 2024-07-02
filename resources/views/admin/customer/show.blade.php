@extends('adminlte::page')

@section('title', trans('labels.customer'))

@section('content_header')
    <h1>{{ $customer->name }}</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <table class="table table-striped">
                            <tbody>
                                <tr>
                                    <th>{{ trans('labels.name') }}</th>
                                    <td>{{ $customer->name }}</td>
                                </tr>
                                <tr>
                                    <th>{{ trans('labels.phone_number') }}</th>
                                    <td>
                                        <a href="{{ $customer->wa_link }}" target="_blank">
                                            {{ $customer->phone_number }}
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ trans('labels.total_oder') }}</th>
                                    <td>{{ $customer->count_orders_formatted }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h4>{{ trans('labels.history_order') }}</h4>

                    </div>
                    <div class="card-body">
                        <table id="orderTable" class="table table-bordered table-striped dataTable responsive" aria-describedby="orderTable-info" width="100%">
                            <thead>
                            <tr>
                                <th>{{ trans('labels.date') }}</th>
                                <th>{{ trans('labels.id_order') }}</th>
                                <th>{{ trans('labels.channel') }}</th>
                                <th>{{ trans('labels.province') }}</th>
                                <th>{{ trans('labels.city') }}</th>
                                <th>{{ trans('labels.sku') }}</th>
                                <th>{{ trans('labels.qty') }}</th>
                                <th>{{ trans('labels.price') }}</th>
                                <th width="10%">{{ trans('labels.action') }}</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col">
                                <h4>{{ trans('labels.customer_note') }}</h4>
                            </div>
                            <div class="col-auto justify-content-end">
                                <div class="col-auto">
                                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#customerNoteModal">
                                        <i class="fas fa-plus"></i> {{ trans('labels.add') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <table id="customerNoteTable" class="table table-bordered table-striped dataTable responsive" aria-describedby="customerNoteTable-info" width="100%">
                            <thead>
                            <tr>
                                <th>{{ trans('labels.date') }}</th>
                                <th>{{ trans('labels.note') }}</th>
                                <th>{{ trans('labels.created_by') }}</th>
                                <th width="10%">{{ trans('labels.action') }}</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

    @include('admin.customer.customer-note-modal')
    @include('admin.customer.customer-note-update-modal')
@endsection

@section('js')
    <script>
        $(function () {
            orderTable.draw();
            customerNoteTable.draw();
        });

        const orderTableSelector = $('#orderTable');
        const customerNoteTableSelector = $('#customerNoteTable');

        // datatable
        let orderTable = orderTableSelector.DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            pageLength: 25,
            ajax: {
                url: "{{ route('order.get') }}",
                data: function (d) {
                    d.phone_number = '{{ $customer->phone_number }}'
                }
            },
            columns: [
                {data: 'date', name: 'date'},
                {data: 'id_order', name: 'id_order', sortable: false},
                {data: 'salesChannel', name: 'salesChannel', sortable: false},
                {data: 'province', name: 'province', sortable: false},
                {data: 'city', name: 'city', sortable: false},
                {data: 'sku', name: 'sku', sortable: false},
                {data: 'qtyFormatted', name: 'qty', sortable: false},
                {data: 'priceFormatted', name:'price'},
                {data: 'view_only', sortable: false}
            ],
            columnDefs: [
                { "targets": [6], "className": "text-right" },
                { "targets": [7], "className": "text-right" },
                { "targets": [8], "className": "text-center" }
            ],
            order: [[0, 'desc']]
        });

        // datatable
        let customerNoteTable = customerNoteTableSelector.DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            pageLength: 25,
            ajax: {
                url: "{{ route('customerNote.get') }}",
                data: function (d) {
                    d.customer_id = '{{ $customer->id }}'
                }
            },
            columns: [
                {data: 'created_at', name: 'created_at'},
                {data: 'note', name: 'note', sortable: false},
                {data: 'user.name', name: 'user.name', sortable: false},
                {data: 'actions', sortable: false}
            ],
            order: [[0, 'asc']]
        });

        // submit form
        $('#customerNoteForm').submit(function(e) {
            e.preventDefault();

            let formData = $(this).serialize();

            $.ajax({
                type: 'POST',
                url: "{{ route('customerNote.store') }}",
                data: formData,
                success: function(response) {
                    customerNoteTable.ajax.reload();
                    $('#customerNoteForm').trigger("reset");
                    $('#customerNoteModal').modal('hide');
                    toastr.success('{{ trans('messages.success_save', ['model' => trans('labels.customer_note')]) }}');
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        });

        // Handle row click event to open modal and fill form
        customerNoteTable.on('draw.dt', function() {
            const tableBodySelector =  $('#customerNoteTable tbody');

            // console.log('trigger');

            tableBodySelector.on('click', '.updateButton', function() {
                let rowData = customerNoteTable.row($(this).closest('tr')).data();

                $('#customerNoteId').val(rowData.id);
                $('#noteUpdate').val(rowData.note);
                $('#customerNoteUpdateModal').modal('show');
            });

            tableBodySelector.on('click', '.deleteButton', function() {
                let rowData = customerNoteTable.row($(this).closest('tr')).data();
                let route = '{{ route('customerNote.destroy', ':id') }}';
                deleteAjax(route, rowData.id, customerNoteTable);
            });
        });

        // submit update form
        $('#customerNoteUpdateForm').submit(function(e) {
            e.preventDefault();

            let formData = $(this).serialize();
            let customerNoteId = $('#customerNoteId').val();

            let updateUrl = '{{ route('customerNote.update', ':customerNoteId') }}';
            updateUrl = updateUrl.replace(':customerNoteId', customerNoteId);

            $.ajax({
                type: 'PUT',
                url: updateUrl,
                data: formData,
                success: function(response) {
                    customerNoteTable.ajax.reload();
                    $('#customerNoteUpdateModal').modal('hide');
                    toastr.success('{{ trans('messages.success_update', ['model' => trans('labels.customer_note')]) }}');
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        });
    </script>
@stop
