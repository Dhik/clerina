@extends('adminlte::page')

@section('title', trans('labels.offer'))

@section('content_header')
    <h1>{{ trans('labels.offer') }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-auto">
                            <div class="row mb-2">
                                <div class="col-auto mb-2">
                                    <select class="form-control" id="filterStatus">
                                        <option value="" selected>{{ trans('placeholder.select', ['field' => trans('labels.status')]) }}</option>
                                        <option value="">{{ trans('labels.all') }}</option>
                                        @foreach($statuses as $status)
                                            <option value={{ $status }}>{{ ucfirst($status) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-auto mb-2">
                                    <button class="btn btn-default" id="resetFilterBtn">{{ trans('buttons.reset_filter') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <table id="offerTable" class="table table-bordered table-striped dataTable responsive" aria-describedby="offer-info" width="100%">
                        <thead>
                        <tr>
                            <th>{{ trans('labels.date') }}</th>
                            <th>{{ trans('labels.id') }}</th>
                            <th>{{ trans('labels.campaign') }}</th>
                            <th>{{ trans('labels.created_by') }}</th>
                            <th>{{ trans('labels.username') }}</th>
                            <th data-toggle="tooltip" data-placement="top" title="{{ trans('labels.slot_rate') }}">
                                {{ trans('labels.rate') }}
                            </th>
                            <th data-toggle="tooltip" data-placement="top" title="{{ trans('labels.cpm') }}">
                                {{ trans('labels.cpm_short') }}
                            </th>
                            <th>{{ trans('labels.average_view') }}</th>
                            <th>{{ trans('labels.benefit') }}</th>
                            <th>{{ trans('labels.negotiate') }}</th>
                            <th>{{ trans('labels.acc_slot') }}</th>
                            <th>{{ trans('labels.status') }}</th>
                            <th width="10%">{{ trans('labels.action') }}</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @include('admin.campaign.modal-status-offer')
    @include('admin.campaign.modal-review-offer')
@stop

@section('js')
    <script>
        const filterStatus = $('#filterStatus');

        filterStatus.change(function () {
            offerTable.draw();
        });

        $('#resetFilterBtn').click(function () {
           filterStatus.val('').trigger('change');
            offerTable.draw();
        });

        // submit update form
        $('#statusUpdateForm').submit(function(e) {
            e.preventDefault();

            let formData = $(this).serialize();

            $.ajax({
                type: 'PUT',
                url: "{{ route('offer.updateStatus', ['offer' => ':offer']) }}".replace(':offer', $('#statusOfferId').val()),
                data: formData,
                success: function(response) {
                    offerTable.ajax.reload();
                    $('#errorUpdateStatus').addClass('d-none');
                    $('#statusUpdateModal').modal('hide');
                    $('#statusUpdateForm')[0].reset();
                    toastr.success('{{ trans('messages.success_update', ['model' => trans('labels.offer')]) }}');
                },
                error: function(xhr, status, error) {
                    errorAjaxValidation(xhr, status, error, $('#errorUpdateStatus'));
                }
            });
        });

        // submit update form
        $('#reviewOfferForm').submit(function(e) {
            e.preventDefault();

            let formData = $(this).serialize();

            $.ajax({
                type: 'PUT',
                url: "{{ route('offer.reviewOffering', ['offer' => ':offer']) }}".replace(':offer', $('#reviewOfferId').val()),
                data: formData,
                success: function(response) {
                    offerTable.ajax.reload();
                    $('#errorReviewOffer').addClass('d-none');
                    $('#reviewOfferModal').modal('hide');
                    toastr.success('{{ trans('messages.success_update', ['model' => trans('labels.offer')]) }}');
                },
                error: function(xhr, status, error) {
                    errorAjaxValidation(xhr, status, error, $('#errorReviewOffer'));
                }
            });
        });

        // datatable
        let offerTable = $('#offerTable').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            pageLength: 25,
            ajax: {
                url: "{{ route('offer.get') }}",
                data: function (d) {
                    d.status = filterStatus.val();
                }
            },
            columns: [
                {data: 'created_at', name: 'created_at'},
                {data: 'id', name: 'id'},
                {data: 'campaign_title', name: 'campaign.title'},
                {data: 'created_by_name', name: 'createdBy.name'},
                {data: 'key_opinion_leader_username', name: 'keyOpinionLeader.username'},
                {data: 'rate_formatted', name: 'rate_per_slot'},
                {data: 'key_opinion_leader_cpm', name: 'keyOpinionLeader.cpm'},
                {data: 'key_opinion_leader_average_view', name: 'keyOpinionLeader.average_view'},
                {data: 'benefit', name: 'benefit'},
                {data: 'negotiate', name: 'negotiate'},
                {data: 'acc_slot', name: 'acc_slot'},
                {data: 'status_label', name: 'status'},
                {data: 'actions', sortable: false, orderable: false}
            ],
            columnDefs: [
                { "targets": [0, 1], "visible": false },
                { "targets": [5, 6, 7, 10], "className": "text-right" },
                { "targets": [11, 12], "className": "text-center" },
            ],
            order: [[0, 'desc']]
        });

        // Handle row click event to open modal and fill form
        offerTable.on('draw.dt', function() {
            const tableBodySelector = $('#offerTable tbody');

            tableBodySelector.on('click', '.btnUpdateStatus', function () {
                let rowData = offerTable.row($(this).closest('tr')).data();

                $('#statusOfferId').val(rowData.id);
                $('#statusField').val(rowData.status).trigger('change');
                $('#accSlot').val(rowData.acc_slot);
                $('#statusUpdateModal').modal('show');
            });

            tableBodySelector.on('click', '.btnReviewOffer', function () {
                let rowData = offerTable.row($(this).closest('tr')).data();
                $('#reviewOfferId').val(rowData.id);
                $('#rateFinalSlot').val(rowData.rate_final_slot);
                $('#rateTotalSlot').val(rowData.rate_total_slot);
                $('#npwpCheckbox').prop('checked', !!rowData.npwp);
                $('#reviewOfferModal').modal('show');
            });
        });

        $(function () {
            offerTable.draw()
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
@stop
