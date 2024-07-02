<h5 class="mt-4 mb-2">{{ trans('labels.sub_marketing_category') }}</h5>
<div class="row justify-content-center">
    <div class="col-md-12">
        <div class="card card-default">
            <div class="card-body">
                <div class="row justify-content-end">
                    <div class="col-auto">
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#marketingSubCategoryModal">
                            <i class="fas fa-plus"></i> {{ trans('labels.add') }}
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <table id="marketingSubCategoryTable" class="table table-bordered table-striped dataTable responsive" aria-describedby="marketingCategory-info" width="100%">
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

@include('admin.marketingCategory.subCategory.modal')
@include('admin.marketingCategory.subCategory.modal-update')

@section('js')
    <script>
        $(function () {

            const marketingSubCategoryTableSelector = $('#marketingSubCategoryTable');

            // datatable
            let marketingSubCategoryTable = marketingSubCategoryTableSelector.DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                ajax: "{{ route('marketingSubCategories.get', $marketingCategory->id) }}",
                columns: [
                    {data: 'name', name: 'name'},
                    {data: 'actions', sortable: false, orderable: false}
                ]
            });

            // submit form
            $('#marketingSubCategoryForm').submit(function(e) {
                e.preventDefault();

                let formData = $(this).serialize();

                $.ajax({
                    type: 'POST',
                    url: "{{ route('marketingSubCategories.store') }}",
                    data: formData,
                    success: function(response) {
                        marketingSubCategoryTable.ajax.reload();
                        $('#marketingSubCategoryForm')[0].reset();
                        $('#marketingSubCategoryModal').modal('hide');
                        toastr.success('{{ trans('messages.success_save', ['model' => trans('labels.sub_marketing_category')]) }}');
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText);
                    }
                });
            });

            // Handle row click event to open modal and fill form
            marketingSubCategoryTableSelector.on('draw.dt', function() {
                const tableBodySelector =  $('#marketingSubCategoryTable tbody');

                tableBodySelector.on('click', '.updateButton', function() {
                    let rowData = marketingSubCategoryTable.row($(this).closest('tr')).data();

                    $('#marketingSubCategoryId').val(rowData.id);
                    $('#nameUpdate').val(rowData.name);
                    $('#marketingSubCategoryUpdateModal').modal('show');
                });

                tableBodySelector.on('click', '.deleteButton', function() {
                    let rowData = marketingSubCategoryTable.row($(this).closest('tr')).data();

                    let route = '{{ route('marketingSubCategories.destroy', ':id') }}';
                    deleteAjax(route, rowData.id, marketingSubCategoryTable);
                });
            });

            // submit update form
            $('#marketingSubCategoryUpdateForm').submit(function(e) {
                e.preventDefault();

                let formData = $(this).serialize();
                let marketingSubCategoryId = $('#marketingSubCategoryId').val();

                let updateUrl = '{{ route('marketingSubCategories.update', ':marketingSubCategoryId') }}';
                updateUrl = updateUrl.replace(':marketingSubCategoryId', marketingSubCategoryId);

                $.ajax({
                    type: 'PUT',
                    url: updateUrl,
                    data: formData,
                    success: function(response) {
                        marketingSubCategoryTable.ajax.reload();
                        $('#marketingSubCategoryUpdateModal').modal('hide');
                        toastr.success('{{ trans('messages.success_update', ['model' => trans('labels.sub_marketing_category')]) }}');
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText);
                    }
                });
            });
        });
    </script>
@stop
