@extends('adminlte::page')

@section('title', trans('labels.campaign'))

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0">{{ $campaign->title }} : {{ $campaign->start_date }} - {{ $campaign->end_date }}</h1>
        <div>
            @can('updateCampaign', $campaign)
                <a href="{{ route('campaign.edit', $campaign->id) }}" class="btn btn-outline-success mr-1">
                    {{ trans('buttons.edit') }}
                </a>
            @endcan
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header p-2">
                        <ul class="nav nav-pills">
                            <li class="nav-item"><a class="nav-link active" href="#statistic" data-toggle="tab">{{ trans('labels.statistic') }}</a></li>
                            <!-- <li class="nav-item"><a class="nav-link" href="#offer" data-toggle="tab">{{ trans('labels.offer') }}</a></li> -->
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <div class="tab-pane active" id="statistic">
                                @include('admin.campaign.statistic')
                            </div>
                            <div class="tab-pane" id="offer">

                                <div class="row mb-lg-2 justify-content-between">
                                    <div class="col-auto">
                                        <select class="form-control" id="filterStatus">
                                            <option value="" selected>{{ trans('placeholder.select', ['field' => trans('labels.status')]) }}</option>
                                            <option value="">{{ trans('labels.all') }}</option>
                                            @foreach($statuses as $status)
                                                <option value={{ $status }}>{{ ucfirst($status) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-auto">
                                        @can(\App\Domain\User\Enums\PermissionEnum::CreateOffer)
                                            <button class="btn btn-primary" data-toggle="modal" data-target="#offerModal">
                                                <i class="fas fa-plus"></i> {{ trans('labels.add') }}
                                            </button>
                                        @endcan
                                        <a class="btn btn-success" href={{ route('offer.export', $campaign->id) }}>
                                            <i class="fas fa-file-download"></i> {{ trans('labels.export') }}
                                        </a>
                                    </div>
                                </div>

                                <table id="offerTable" class="table table-bordered table-striped dataTable responsive" aria-describedby="offer-info" width="100%">
                                    <thead>
                                        <tr>
                                            <th>{{ trans('labels.date') }}</th>
                                            <th>{{ trans('labels.id') }}</th>
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
                    <div class="card-footer">
                        {{ trans('labels.created_by') }} {{ $campaign->createdBy->name ?? '' }}

                        @can('deleteCampaign', $campaign)
                            <a href="#" class="delete-campaign">{{ trans('buttons.delete') }}</a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('admin.campaign.modal-create-offer')
    @include('admin.campaign.modal-update-offer')
    @include('admin.campaign.modal-status-offer')
    @include('admin.campaign.modal-review-offer')
@endsection

@section('js')
    <script>
        let campaignId = '{{ $campaign->id }}'
        const filterStatus = $('#filterStatus');

        filterStatus.change(function () {
            offerTable.draw();
        });

        // Handle change event of Select2
        $('#usernameOffer').on('change', function() {
            let selectedOption = $(this).select2('data')[0];

            if (selectedOption && selectedOption.id !== '') {
                // Fetch additional data based on the selected option
                $.ajax({
                    url: "{{ route('kol.show.json', ['keyOpinionLeader' => ':keyOpinionLeaderId']) }}".replace(':keyOpinionLeaderId', selectedOption.id),
                    type: 'GET',
                    success: function(response) {
                        $('#ratePerSlot').val(response.rate);
                        $('#bankName').val(response.bank_name);
                        $('#bankAccount').val(response.bank_account);
                        $('#bankAccountName').val(response.bank_account_name);
                        $('#nik').val(response.nik);
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching additional data:', error);
                    }
                });
            }
        });

        // submit update form
        $('#offerForm').submit(function(e) {
            e.preventDefault();

            let formData = $(this).serialize();

            $.ajax({
                type: 'POST',
                url: "{{ route('offer.store', ['campaignId' => ':campaignId']) }}".replace(':campaignId', campaignId),
                data: formData,
                success: function(response) {
                    offerTable.ajax.reload();
                    $('#offerModal').modal('hide');
                    $('#username').val(null).trigger('change');
                    $('#offerForm')[0].reset();
                    toastr.success('{{ trans('messages.success_save', ['model' => trans('labels.offer')]) }}');
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        });

        // submit update form
        $('#offerUpdateForm').submit(function(e) {
            e.preventDefault();

            let formData = $(this).serialize();

            $.ajax({
                type: 'PUT',
                url: "{{ route('offer.update', ['offer' => ':offer']) }}".replace(':offer', $('#offerId').val()),
                data: formData,
                success: function(response) {
                    offerTable.ajax.reload();
                    $('#offerUpdateModal').modal('hide');
                    $('#offerForm')[0].reset();
                    toastr.success('{{ trans('messages.success_update', ['model' => trans('labels.offer')]) }}');
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
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
                url: "{{ route('offer.getByCampaignId', ['campaignId' => ':campaignId']) }}".replace(':campaignId', campaignId),
                data: function (d) {
                    d.status = filterStatus.val();
                }
            },
            columns: [
                {data: 'created_at', name: 'created_at'},
                {data: 'id', name: 'id'},
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
                { "targets": [4, 5, 6, 9], "className": "text-right" },
                { "targets": [10, 11], "className": "text-center" },
            ],
            order: [[0, 'desc']]
        });

        // Handle row click event to open modal and fill form
        offerTable.on('draw.dt', function() {
            const tableBodySelector =  $('#offerTable tbody');

            tableBodySelector.on('click', '.btnUpdateOffer', function() {
                let rowData = offerTable.row($(this).closest('tr')).data();

                $('#offerId').val(rowData.id);
                $('#usernameOfferUpdate').val(rowData.key_opinion_leader.username);
                $('#rateUpdate').val(rowData.rate_per_slot);
                $('#benefitUpdate').val(rowData.benefit);
                $('#negotiateUpdate').val(rowData.negotiate).trigger('change');
                $('#updateBankName').val(rowData.bank_name);
                $('#updateBankAccount').val(rowData.bank_account);
                $('#updateBankAccountName').val(rowData.bank_account_name);
                $('#updateNik').val(rowData.nik);

                $('#offerUpdateModal').modal('show');
            });

            tableBodySelector.on('click', '.btnUpdateStatus', function() {
                let rowData = offerTable.row($(this).closest('tr')).data();

                $('#statusOfferId').val(rowData.id);
                $('#statusField').val(rowData.status).trigger('change');
                $('#accSlot').val(rowData.acc_slot);
                $('#statusUpdateModal').modal('show');
            });

            tableBodySelector.on('click', '.btnReviewOffer', function() {
                let rowData = offerTable.row($(this).closest('tr')).data();
                $('#reviewOfferId').val(rowData.id);
                $('#rateFinalSlot').val(rowData.rate_final_slot);
                $('#rateTotalSlot').val(rowData.rate_total_slot);
                $('#npwpCheckbox').prop('checked', !!rowData.npwp);
                $('#reviewOfferModal').modal('show');
            });
        });

        const filterDates = $('#filterDates')
        const filterInfluencer = $('#filterInfluencer')
        const filterProduct = $('#filterProduct')
        const filterPlatform = $('#filterPlatform')
        const filterFyp = $('#filterFyp')
        const filterPayment = $('#filterPayment')
        const filterDelivery = $('#filterDelivery')

        let contentTable = $('#contentTable').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            pageLength: 25,
            ajax: {
                url: "{{ route('campaignContent.getDataTable', ['campaignId' => ':campaignId']) }}".replace(':campaignId', campaignId),
                data: function (d) {
                    // Log the data being sent to the server
                    d.filterDates = filterDates.val();
                    d.filterInfluencer = filterInfluencer.val();
                    d.filterProduct = filterProduct.val();
                    d.filterPlatform = filterPlatform.val();
                    d.filterFyp = filterFyp.prop('checked');
                    d.filterPayment = filterPayment.prop('checked');
                    d.filterDelivery = filterDelivery.prop('checked');

                    console.log('Data sent to server:', d);
                },
                success: function(data) {
                    // Log the data received from the server
                    console.log('Data received from server:', data);
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', error);
                    console.log('Response:', xhr.responseText);
                }
            },
            columns: [
                {data: 'id', name: 'id'},
                {data: 'key_opinion_leader_username', name: 'keyOpinionLeader.username', sortable: false, orderable: false},
                {data: 'channel', name: 'channel'},
                {data: 'product', name: 'product'},
                {data: 'task_name', name: 'task_name'},
                {data: 'like', name: 'latestStatistic.like', sortable: false, orderable: false},
                {data: 'comment', name: 'latestStatistic.comment', sortable: false, orderable: false},
                {data: 'view', name: 'latestStatistic.view', sortable: false, orderable: false},
                {data: 'cpm', name: 'latestStatistic.view', sortable: false, orderable: false},
                {data: 'additional_info', sortable: false, orderable: false},
                {data: 'actions', sortable: false, orderable: false}
            ],
            columnDefs: [
                { "targets": [0], "visible": false },
                { "targets": [5], "className": "text-right" },
                { "targets": [6], "className": "text-right" },
                { "targets": [7], "className": "text-right" },
                { "targets": [8], "className": "text-right" },
                { "targets": [9], "className": "text-center" },
                { "targets": [10], "className": "text-center" },
            ],
            order: [[0, 'desc']]
        });


        // Handle row click event to open modal and fill form
        contentTable.on('draw.dt', function() {
            $('[data-toggle="tooltip"]').tooltip();
        });

        filterDates.change(function (){
            contentTable.ajax.reload()
            updateCard();
            initChart();
        })

        filterPlatform.change(function() {
            contentTable.ajax.reload()
        });

        filterFyp.change(function() {
            contentTable.ajax.reload()
        });

        filterPayment.change(function() {
            contentTable.ajax.reload()
        });

        filterDelivery.change(function() {
            contentTable.ajax.reload()
        });

        $(function () {
            offerTable.draw();
            $('[data-toggle="tooltip"]').tooltip();

            $('#usernameOffer').select2({
                theme: 'bootstrap4',
                dropdownParent: $("#offerModal"),
                placeholder: '{{ trans('placeholder.select', ['field' => trans('labels.username_kol')]) }}',
                ajax: {
                    url: "{{ route('kol.select') }}",
                    data: function (params) {
                        return {
                            search: params.term,
                        };
                    },
                    dataType: 'json',
                    delay: 250,
                    processResults: function(data) {
                        let formattedResults = [];
                        data.forEach(function(result) {
                            formattedResults.push({
                                id: result.id,
                                text: result.username + ' - ' +result.channel
                            });
                        });
                        return {
                            results: formattedResults
                        };
                    },
                    cache: true
                }
            });

            const startFilter = moment('{{ $campaign->start_date }}', "DD MMMM YYYY");
            const endFilter = moment('{{ $campaign->end_date }}', "DD MMMM YYYY");

            $('.filterDate').daterangepicker({
                startDate: startFilter,
                endDate: endFilter,
                autoApply: true,
                showDropdowns: true,
                locale: {
                    format: 'DD/MM/YYYY'
                }
            });

            bsCustomFileInput.init()

            $('#username').select2({
                theme: 'bootstrap4',
                placeholder: '{{ trans('placeholder.select', ['field' => trans('labels.influencer')]) }}',
                dropdownParent: $("#contentModal"),
                ajax: {
                    url: "{{ route('campaignContent.select', ['campaignId' => ':campaignId']) }}".replace(':campaignId', campaignId),
                    data: function (params) {
                        return {
                            search: params.term,
                        };
                    },
                    dataType: 'json',
                    delay: 250,
                    processResults: function(data) {
                        let formattedResults = [];
                        data.forEach(function(result) {
                            formattedResults.push({
                                id: result.key_opinion_leader_id,
                                text: result.key_opinion_leader.username + ' - ' +result.key_opinion_leader.channel+ ' - {{ trans('labels.remaining_slot') }}:'+ result.remaining_slot
                            });
                        });

                        return {
                            results: formattedResults
                        };
                    },
                    cache: true
                }
            });
        });
    </script>

    @include('admin.campaign.content.script.script-button-info')
    @include('admin.campaign.content.script.script-refresh')
    @include('admin.campaign.content.script.script-manual-statistic')
    @include('admin.campaign.content.script.script-add-content')
    @include('admin.campaign.content.script.script-update-content')
    @include('admin.campaign.content.script.script-import')
    @include('admin.campaign.content.script.script-detail-content')
    @include('admin.campaign.content.script.script-card')
    @include('admin.campaign.content.script.script-chart')
    @include('admin.campaign.content.script.script-delete-content')
@endsection
