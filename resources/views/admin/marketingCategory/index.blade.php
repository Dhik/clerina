@extends('adminlte::page')

@section('title', trans('labels.marketing_category'))

@section('content_header')
    <h1>{{ trans('labels.marketing_category') }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row justify-content-end">
                        <div class="col-auto">
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#marketingCategoryModal">
                                <i class="fas fa-plus"></i> {{ trans('labels.add') }}
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <table id="marketingCategoryTable" class="table table-bordered table-striped dataTable responsive" aria-describedby="marketingCategory-info" width="100%">
                        <thead>
                            <tr>
                                <th>{{ trans('labels.type') }}</th>
                                <th>{{ trans('labels.name') }}</th>
                                <th>{{ trans('labels.sub_marketing_category') }}</th>
                                <th width="15%">{{ trans('labels.action') }}</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @include('admin.marketingCategory.modal')
    @include('admin.marketingCategory.modal-update')
@stop

@section('js')
    <script>
        $(function () {

            $('#type').select2({
                theme: 'bootstrap4',
                minimumResultsForSearch: Infinity
            });

            const marketingCategoryTableSelector = $('#marketingCategoryTable');

            // datatable
            let marketingCategoryTable = marketingCategoryTableSelector.DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                ajax: "{{ route('marketingCategories.get') }}",
                columns: [
                    {data: 'type_transform', name: 'type'},
                    {data: 'name', name: 'name'},
                    {data: 'marketingSubCategories', name:'marketingSubCategories', orderable: false},
                    {data: 'actions', sortable: false, orderable: false}
                ],
            });

            // submit form
            $('#marketingCategoryForm').submit(function(e) {
                e.preventDefault();

                let formData = $(this).serialize();

                $.ajax({
                    type: 'POST',
                    url: "{{ route('marketingCategories.store') }}",
                    data: formData,
                    success: function(response) {
                        marketingCategoryTable.ajax.reload();
                        $('#marketingCategoryForm')[0].reset();
                        $('#type').val('').trigger('change');
                        $('#marketingCategoryModal').modal('hide');
                        toastr.success('{{ trans('messages.success_save', ['model' => trans('labels.marketing_category')]) }}');
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText);
                    }
                });
            });

            // Handle row click event to open modal and fill form
            marketingCategoryTableSelector.on('draw.dt', function() {
                const tableBodySelector =  $('#marketingCategoryTable tbody');

                tableBodySelector.on('click', '.updateButton', function() {
                    let rowData = marketingCategoryTable.row($(this).closest('tr')).data();

                    $('#marketingCategoryId').val(rowData.id);
                    $('#nameUpdate').val(rowData.name);
                    $('#typeUpdate').val(rowData.type);
                    $('#marketingCategoryUpdateModal').modal('show');
                });

                tableBodySelector.on('click', '.deleteButton', function() {
                    let rowData = marketingCategoryTable.row($(this).closest('tr')).data();
                    let route = '{{ route('marketingCategories.destroy', ':id') }}';
                    deleteAjax(route, rowData.id, marketingCategoryTable);
                });
            });

            // submit update form
            $('#marketingCategoryUpdateForm').submit(function(e) {
                e.preventDefault();

                let formData = $(this).serialize();
                let marketingCategoryId = $('#marketingCategoryId').val();

                let updateUrl = '{{ route('marketingCategories.update', ':marketingCategoryId') }}';
                updateUrl = updateUrl.replace(':marketingCategoryId', marketingCategoryId);

                $.ajax({
                    type: 'PUT',
                    url: updateUrl,
                    data: formData,
                    success: function(response) {
                        marketingCategoryTable.ajax.reload();
                        $('#marketingCategoryUpdateModal').modal('hide');
                        toastr.success('{{ trans('messages.success_update', ['model' => trans('labels.marketing_category')]) }}');
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText);
                    }
                });
            });
        });
    </script>
@stop
