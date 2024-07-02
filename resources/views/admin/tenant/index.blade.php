@extends('adminlte::page')

@section('title', trans('labels.tenant'))

@section('content_header')
    <h1>{{ trans('labels.tenant') }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row justify-content-end">
                        <div class="col-auto">
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#tenantModal">
                                <i class="fas fa-plus"></i> {{ trans('labels.add') }}
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <table id="tenantTable" class="table table-bordered table-striped dataTable responsive" aria-describedby="tenant-info" width="100%">
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

    @include('admin.tenant.modal')
    @include('admin.tenant.modal-update')
@stop

@section('js')
    <script>
        $(function () {
            const tenantTableSelector = $('#tenantTable');

            // datatable
            let tenantTable = tenantTableSelector.DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                ajax: "{{ route('tenant.get') }}",
                columns: [
                    {data: 'name', name: 'name'},
                    {data: 'actions', sortable: false, orderable: false}
                ]
            });

            // submit form
            $('#tenantForm').submit(function(e) {
                e.preventDefault();

                let formData = $(this).serialize();

                $.ajax({
                    type: 'POST',
                    url: "{{ route('tenant.store') }}",
                    data: formData,
                    success: function(response) {
                        tenantTable.ajax.reload();
                        $('#tenantForm').trigger("reset");
                        $('#tenantModal').modal('hide');
                        toastr.success('{{ trans('messages.success_save', ['model' => trans('labels.tenant')]) }}');
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText);
                    }
                });
            });

            // Handle row click event to open modal and fill form
            tenantTableSelector.on('draw.dt', function() {
                const tableBodySelector =  $('#tenantTable tbody');

                tableBodySelector.on('click', '.updateButton', function() {
                    let rowData = tenantTable.row($(this).closest('tr')).data();

                    $('#tenantId').val(rowData.id);
                    $('#nameUpdate').val(rowData.name);
                    $('#tenantUpdateModal').modal('show');
                });

                tableBodySelector.on('click', '.deleteButton', function() {
                    let rowData = tenantTable.row($(this).closest('tr')).data();
                    let route = '{{ route('tenant.destroy', ':id') }}';
                    deleteAjax(route, rowData.id, tenantTable);
                });
            });

            // submit update form
            $('#tenantUpdateForm').submit(function(e) {
                e.preventDefault();

                let formData = $(this).serialize();
                let tenantId = $('#tenantId').val();

                let updateUrl = '{{ route('tenant.update', ':tenantId') }}';
                updateUrl = updateUrl.replace(':tenantId', tenantId);

                $.ajax({
                    type: 'PUT',
                    url: updateUrl,
                    data: formData,
                    success: function(response) {
                        tenantTable.ajax.reload();
                        $('#tenantUpdateModal').modal('hide');
                        toastr.success('{{ trans('messages.success_update', ['model' => trans('labels.tenant')]) }}');
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText);
                    }
                });
            });
        });
    </script>
@stop
