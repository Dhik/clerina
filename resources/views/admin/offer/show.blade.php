@extends('adminlte::page')

@section('title', trans('labels.offer'))

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0">{{ trans('labels.offer') }} - {{ $offer->keyOpinionLeader->username }}</h1>
        <div>
            @can('updateOffer', $offer)
                <button class="btn btn-outline-success mr-1" id="btnUpdateOffer">
                    {{ trans('labels.update') }}
                </button>
            @endcan
            @can('approveRejectOffer', $offer)
                <button class="btn btn-outline-primary mr-1" id="btnUpdateStatus">
                    {{ trans("labels.approve_reject") }}
                </button>
            @endcan
            @can('reviewOffer', $offer)
                <button class="btn btn-outline-warning" id="btnReviewOffer">
                    {{ trans("labels.review") }} {{ trans("labels.offering") }}
                </button>
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
                            <li class="nav-item">
                                <a class="nav-link {{ request()->query('tab', 'offer') == 'offer' ? 'active' : '' }}" href="#offer" data-toggle="tab">
                                    {{ trans('labels.offer') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->query('tab') == 'chat-proof' ? 'active' : '' }}" href="#chatProof" data-toggle="tab">
                                    {{ trans('labels.chat_proof') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->query('tab') == 'sign' ? 'active' : '' }}" href="#signKOLTab" data-toggle="tab">
                                    {{ trans('labels.kol_sign') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->query('tab') == 'finance' ? 'active' : '' }}" href="#financeTab" data-toggle="tab">
                                    {{ trans('labels.payment_proof') }}
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <div
                                class="tab-pane {{ request()->query('tab', 'offer') == 'offer' || null ? 'active' : '' }}" id="offer">
                                @include('admin.offer.partial.general-info')
                            </div>

                            <div class="tab-pane {{ request()->query('tab') == 'chat-proof' ? 'active' : '' }}" id="chatProof">
                                @include('admin.offer.partial.chat-proof')
                            </div>

                            <div class="tab-pane {{ request()->query('tab') == 'sign' ? 'active' : '' }}" id="signKOLTab">
                                @include('admin.offer.partial.sign-view')
                            </div>

                            <div class="tab-pane {{ request()->query('tab') == 'finance' ? 'active' : '' }}" id="financeTab">
                                @include('admin.offer.partial.finance')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('admin.campaign.modal-update-offer')
    @include('admin.campaign.modal-status-offer')
    @include('admin.campaign.modal-review-offer')
    @include('admin.offer.partial.modal-status-finance')
@endsection

@section('js')
    <script>

        $('#btnUpdateOffer').click(function () {

            $('#offerId').val({{ $offer->id }});
            $('#usernameUpdate').val("{{ $offer->keyOpinionLeader->username }}");
            $('#rateUpdate').val({{ $offer->rate_per_slot }});
            $('#benefitUpdate').val("{{ $offer->benefit }}");
            $('#negotiateUpdate').val("{{ $offer->negotiate }}").trigger('change');
            $('#updateBankName').val("{{ $offer->bank_name }}");
            $('#updateBankAccount').val("{{ $offer->bank_account }}");
            $('#updateBankAccountName').val("{{ $offer->bank_account_name }}");
            $('#updateNik').val("{{ $offer->nik }}");

            $('#offerUpdateModal').modal('show');
        });

        $('#btnUpdateStatus').click(function () {
            $('#statusOfferId').val({{ $offer->id }});
            $('#statusField').val("{{ $offer->status }}").trigger('change');
            $('#accSlot').val({{ $offer->acc_slot }});
            $('#statusUpdateModal').modal('show');
        });

        $('#btnReviewOffer').click(function () {
            $('#reviewOfferId').val({{ $offer->id }});
            $('#rateFinalSlot').val({{ $offer->rate_final_slot }});
            $('#rateTotalSlot').val({{ $offer->rate_total_slot }});
            $('#npwpCheckbox').prop('checked', !!"{{ $offer->npwp }}");
            $('#reviewOfferModal').modal('show');
        });

        $('#btnFinanceOffer').click(function () {

            let dateObject = moment("{{ $offer->transfer_date }}", "YYYY-MM-DD");
            let formattedDate = dateObject.format("DD/MM/YYYY");

            $('#financeOfferId').val({{ $offer->id }});
            $('#transferStatusField').val('{{ $offer->transfer_status }}').trigger('change');

            if ('{{ $offer->transfer_date }}' !== '') {
                $('#transferDateField').val(formattedDate);
            } else {
                $('#transferDateField').val();
            }

            $('#financeModal').modal('show');
        });

        // submit update form
        $('#offerUpdateForm').submit(function (e) {
            e.preventDefault();

            let formData = $(this).serialize();

            $.ajax({
                type: 'PUT',
                url: "{{ route('offer.update', ['offer' => ':offer']) }}".replace(':offer', $('#offerId').val()),
                data: formData,
                success: function (response) {
                    $('#offerUpdateModal').modal('hide');
                    toastr.success('{{ trans('messages.success_update', ['model' => trans('labels.offer')]) }}');
                    location.reload();
                },
                error: function (xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        });

        // submit update form
        $('#statusUpdateForm').submit(function (e) {
            e.preventDefault();

            let formData = $(this).serialize();

            $.ajax({
                type: 'PUT',
                url: "{{ route('offer.updateStatus', ['offer' => ':offer']) }}".replace(':offer', $('#statusOfferId').val()),
                data: formData,
                success: function (response) {
                    $('#errorUpdateStatus').addClass('d-none');
                    $('#statusUpdateModal').modal('hide');
                    toastr.success('{{ trans('messages.success_update', ['model' => trans('labels.offer')]) }}');
                    location.reload();
                },
                error: function (xhr, status, error) {
                    errorAjaxValidation(xhr, status, error, $('#errorUpdateStatus'));
                }
            });
        });

        // submit update form
        $('#reviewOfferForm').submit(function (e) {
            e.preventDefault();

            let formData = $(this).serialize();

            $.ajax({
                type: 'PUT',
                url: "{{ route('offer.reviewOffering', ['offer' => ':offer']) }}".replace(':offer', $('#reviewOfferId').val()),
                data: formData,
                success: function (response) {
                    $('#errorReviewOffer').addClass('d-none');
                    $('#reviewOfferModal').modal('hide');
                    toastr.success('{{ trans('messages.success_update', ['model' => trans('labels.offer')]) }}');
                    location.reload();
                },
                error: function (xhr, status, error) {
                    errorAjaxValidation(xhr, status, error, $('#errorReviewOffer'));
                }
            });
        });

        // submit update form
        $('#financeForm').submit(function (e) {
            e.preventDefault();

            let formData = $(this).serialize();

            $.ajax({
                type: 'PUT',
                url: "{{ route('offer.financeOffering', ['offer' => ':offer']) }}".replace(':offer', $('#financeOfferId').val()),
                data: formData,
                success: function (response) {
                    $('#errorUpdateFinance').addClass('d-none');
                    $('#financeModal').modal('hide');
                    toastr.success('{{ trans('messages.success_update', ['model' => trans('labels.finance')]) }}');
                    location.reload();
                },
                error: function (xhr, status, error) {
                    errorAjaxValidation(xhr, status, error, $('#errorReviewOffer'));
                }
            });
        });

        $("#copyButton").click(function (e) {
            // Prevent the default action of the link
            e.preventDefault();

            // Get the URL from the link's href attribute
            let url = this.getAttribute("href");

            // Create a temporary input element
            let input = document.createElement('input');
            input.style.position = 'fixed';
            input.style.opacity = 0;

            // Set the input value to the URL
            input.value = url;

            // Append the input element to the body
            document.body.appendChild(input);

            // Select the input content
            input.select();
            input.setSelectionRange(0, 99999); /*For mobile devices*/

            // Copy the URL to the clipboard
            document.execCommand('copy');

            // Remove the temporary input element
            document.body.removeChild(input);

            // Alert the user that the URL has been copied
            alert("URL berhasil dicopy: " + url);
        });
    </script>
@endsection
