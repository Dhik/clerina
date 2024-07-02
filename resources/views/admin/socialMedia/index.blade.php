@extends('adminlte::page')

@section('title', trans('labels.social_media'))

@section('content_header')
    <h1>{{ trans('labels.social_media') }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row justify-content-end">
                        <div class="col-auto">
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#socialMediaModal">
                                <i class="fas fa-plus"></i> {{ trans('labels.add') }}
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <table id="socialMediaTable" class="table table-bordered table-striped dataTable responsive" aria-describedby="socialMedia-info" width="100%">
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

    @include('admin.socialMedia.modal')
    @include('admin.socialMedia.modal-update')
@stop

@section('js')
    <script>
        $(function () {
            const socialMediaTableSelector = $('#socialMediaTable');

            // datatable
            let socialMediaTable = socialMediaTableSelector.DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                ajax: "{{ route('socialMedia.get') }}",
                columns: [
                    {data: 'name', name: 'name'},
                    {data: 'actions', sortable: false, orderable: false}
                ]
            });

            // submit form
            $('#socialMediaForm').submit(function(e) {
                e.preventDefault();

                let formData = $(this).serialize();

                $.ajax({
                    type: 'POST',
                    url: "{{ route('socialMedia.store') }}",
                    data: formData,
                    success: function(response) {
                        socialMediaTable.ajax.reload();
                        $('#socialMediaForm').trigger("reset");
                        $('#socialMediaModal').modal('hide');
                        toastr.success('{{ trans('messages.success_save', ['model' => trans('labels.social_media')]) }}');
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText);
                    }
                });
            });

            // Handle row click event to open modal and fill form
            socialMediaTableSelector.on('draw.dt', function() {
                const tableBodySelector =  $('#socialMediaTable tbody');

                tableBodySelector.on('click', '.updateButton', function() {
                    let rowData = socialMediaTable.row($(this).closest('tr')).data();

                    $('#socialMediaId').val(rowData.id);
                    $('#nameUpdate').val(rowData.name);
                    $('#socialMediaUpdateModal').modal('show');
                });

                tableBodySelector.on('click', '.deleteButton', function() {
                    let rowData = socialMediaTable.row($(this).closest('tr')).data();
                    let route = '{{ route('socialMedia.destroy', ':id') }}';
                    deleteAjax(route, rowData.id, socialMediaTable);
                });
            });

            // submit update form
            $('#socialMediaUpdateForm').submit(function(e) {
                e.preventDefault();

                let formData = $(this).serialize();
                let socialMediaId = $('#socialMediaId').val();

                let updateUrl = '{{ route('socialMedia.update', ':socialMediaId') }}';
                updateUrl = updateUrl.replace(':socialMediaId', socialMediaId);

                $.ajax({
                    type: 'PUT',
                    url: updateUrl,
                    data: formData,
                    success: function(response) {
                        socialMediaTable.ajax.reload();
                        $('#socialMediaUpdateModal').modal('hide');
                        toastr.success('{{ trans('messages.success_update', ['model' => trans('labels.social_media')]) }}');
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText);
                    }
                });
            });
        });
    </script>
@stop
