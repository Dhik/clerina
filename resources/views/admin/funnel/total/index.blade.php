@extends('adminlte::page')

@section('title', trans('labels.funnel'))

@section('content_header')
    <h1>{{ trans('labels.total') }} {{ trans('labels.tofu') }} - {{ trans('labels.mofu') }} - {{ trans('labels.bofu') }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-auto">
                            <div class="row">
                                <div class="col-auto">
                                    <input type="text" class="form-control monthYear" id="filterDates"
                                           placeholder="{{ trans('placeholder.select_date') }}" autocomplete="off">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <table id="funnelTable" class="table table-bordered table-striped dataTable responsive" aria-describedby="funnel-info" width="100%">
                        <thead>
                        <tr>
                            <th>{{ trans('labels.date') }}</th>
                            <th>{{ trans('labels.total_reach') }}</th>
                            <th>{{ trans('labels.total_impression') }}</th>
                            <th>{{ trans('labels.total_engagement') }}</th>
                            <th data-toggle="tooltip" data-placement="top" title="{{ trans('labels.cpm') }}">{{ trans('labels.cpm_short') }}</th>
                            <th>{{ trans('labels.total_roas') }}</th>
                            <th>{{ trans('labels.total_spend') }}</th>
                            <th>{{ trans('labels.screenshot') }}</th>
                            <th width="10%">{{ trans('labels.action') }}</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @include('admin.funnel.total.modal-screenshot')
@endsection

@section('js')
    <script>
        filterDate = $('#filterDates');

        const funnelTableSelector = $('#funnelTable');

        // datatable
        let funnelTable = funnelTableSelector.DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            pageLength: 50,
            searching: false,
            lengthChange: false,
            ajax: {
                url: "{{ route('funnel.total.get') }}",
                data: function (d) {
                    d.filterDates = filterDate.val();
                }
            },
            columns: [
                {data: 'date', name: 'date', sortable: false, orderable: false},
                {data: 'reachFormatted', name: 'total_reach', sortable: false, orderable: false},
                {data: 'impressionFormatted', name: 'total_impression', sortable: false, orderable: false},
                {data: 'engagementFormatted', name: 'total_engagement', sortable: false, orderable: false},
                {data: 'cpmFormatted', name: 'total_cpm', sortable: false, orderable: false},
                {data: 'roasFormatted', name: 'total_roas', sortable: false, orderable: false},
                {data: 'spendFormatted', name: 'total_spend', sortable: false, orderable: false},
                {data: 'screenshot_url', name: 'total_spend', sortable: false, orderable: false},
                {data: 'actions', sortable: false, orderable: false}
            ],
            columnDefs: [
                { "targets": [1], "className": "text-right" },
                { "targets": [2], "className": "text-right" },
                { "targets": [3], "className": "text-right" },
                { "targets": [4], "className": "text-right" },
                { "targets": [5], "className": "text-right" },
                { "targets": [6], "className": "text-right" },
                { "targets": [7], "className": "text-center" },
                { "targets": [8], "className": "text-center" }
            ]
        });

        // Handle row click event to open modal and fill form
        funnelTableSelector.on('draw.dt', function() {
            const tableBodySelector =  $('#funnelTable tbody');

            tableBodySelector.on('click', '.uploadScreenshot', function() {
                let rowData = funnelTable.row($(this).closest('tr')).data();

                $('#funnelTotalId').val(rowData.id);
                $('#screenshotModal').modal('show');
            });
        });

        // submit form
        $('#screenshotUploadForm').submit(function(e) {
            e.preventDefault();

            let form = $(this);
            let submitBtn = form.find('button[type="submit"]');
            let spinner = $('<div class="spinner-border spinner-border-sm" role="status"><span class="sr-only">Loading...</span></div>'); // Create spinner element

            // Disable the submit button to prevent multiple submissions
            submitBtn.prop('disabled', true).append(spinner);

            const fieldUpload = $('#fileScreenshot');

            let fileInput = fieldUpload.prop('files')[0];

            let funnelTotalId = $('#funnelTotalId').val();

            let updateUrl = '{{ route('funnel.store-screenshot', ':funnelTotalId') }}';
            updateUrl = updateUrl.replace(':funnelTotalId', funnelTotalId);

            let formData = new FormData();
            formData.append('image', fileInput);

            let csrfToken = $('meta[name="csrf-token"]').attr('content');

            $.ajax({
                type: 'POST',
                url: updateUrl,
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    fieldUpload.val(null);
                    const newPlaceholder = $('<label class="custom-file-label" for="customFile" id="labelFileScreenshot"">{{ trans('placeholder.select_image') }}</label>');
                    $('#labelFileScreenshot').replaceWith(newPlaceholder);

                    toastr.success('{{ trans('messages.success_upload', ['model' => trans('labels.screenshot')]) }}');

                    submitBtn.prop('disabled', false);
                    spinner.remove();

                    funnelTable.ajax.reload();
                    $('#errorUploadScreenshot').addClass('d-none');
                    $('#screenshotModal').modal('hide');
                },
                error: function (xhr, status, error) {
                    errorImportAjaxValidation(xhr, status, error, $('#errorImportOrder'));

                    submitBtn.prop('disabled', false);
                    spinner.remove();
                }
            });
        })

        filterDate.change(function () {
            funnelTable.draw();
        });

        $(function () {
            funnelTable.draw();
            bsCustomFileInput.init();
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
@endsection
