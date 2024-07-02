@extends('adminlte::page')

@section('title', trans('labels.sales_channel'))

@section('content_header')
    <h1>{{ trans('labels.sales_channel') }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row justify-content-end">
                        <div class="col-auto">
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#salesChannelModal">
                                <i class="fas fa-plus"></i> {{ trans('labels.add') }}
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <table id="salesChannelTable" class="table table-bordered table-striped dataTable responsive" aria-describedby="salesChannel-info" width="100%">
                        <thead>
                            <tr>
                                <th>{{ trans('labels.name') }}</th>
                                <th width="10%">{{ trans('labels.action') }}</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @include('admin.salesChannel.modal')
    @include('admin.salesChannel.modal-update')
@stop

@section('js')
    <script>
        $(function () {
            const salesChannelTableSelector = $('#salesChannelTable');

            // datatable
            let salesChannelTable = salesChannelTableSelector.DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                ajax: "{{ route('salesChannel.get') }}",
                columns: [
                    {data: 'name', name: 'name'},
                    {data: 'actions', sortable: false, orderable: false}
                ]
            });

            // submit form
            $('#salesChannelForm').submit(function(e) {
                e.preventDefault();

                let formData = $(this).serialize();

                $.ajax({
                    type: 'POST',
                    url: "{{ route('salesChannel.store') }}",
                    data: formData,
                    success: function(response) {
                        salesChannelTable.ajax.reload();
                        $('#salesChannelForm').trigger("reset");
                        $('#salesChannelModal').modal('hide');
                        toastr.success('{{ trans('messages.success_save', ['model' => trans('labels.sales_channel')]) }}');
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText);
                    }
                });
            });

            // Handle row click event to open modal and fill form
            salesChannelTableSelector.on('draw.dt', function() {
                const tableBodySelector =  $('#salesChannelTable tbody');

                tableBodySelector.on('click', '.updateButton', function() {
                    let rowData = salesChannelTable.row($(this).closest('tr')).data();

                    $('#salesChannelId').val(rowData.id);
                    $('#nameUpdate').val(rowData.name);
                    $('#salesChannelUpdateModal').modal('show');
                });

                tableBodySelector.on('click', '.deleteButton', function() {
                    let rowData = salesChannelTable.row($(this).closest('tr')).data();
                    let route = '{{ route('salesChannel.destroy', ':id') }}';
                    deleteAjax(route, rowData.id, salesChannelTable);
                });
            });

            // submit update form
            $('#salesChannelUpdateForm').submit(function(e) {
                e.preventDefault();

                let formData = $(this).serialize();
                let salesChannelId = $('#salesChannelId').val();

                let updateUrl = '{{ route('salesChannel.update', ':salesChannelId') }}';
                updateUrl = updateUrl.replace(':salesChannelId', salesChannelId);

                $.ajax({
                    type: 'PUT',
                    url: updateUrl,
                    data: formData,
                    success: function(response) {
                        salesChannelTable.ajax.reload();
                        $('#salesChannelUpdateModal').modal('hide');
                        toastr.success('{{ trans('messages.success_update', ['model' => trans('labels.sales_channel')]) }}');
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText);
                    }
                });
            });
        });
    </script>
@stop
