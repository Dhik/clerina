<!-- Modal -->
<div class="modal fade" id="offerUpdateModal" role="dialog" aria-labelledby="offerModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="offerModalLabel">{{ trans('labels.update') }} {{ trans('labels.offer') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="offerUpdateForm">
                    @csrf
                    <!-- Your form fields here -->
                    <div class="form-group">
                        <label for="usernameOfferUpdate">{{ trans('labels.username_kol') }}<span class="required">*</span></label>
                        <input type="text" class="form-control" id="usernameOfferUpdate" disabled>
                    </div>

                    <div class="form-group">
                        <label for="rateUpdate">{{ trans('labels.slot_rate') }}<span class="required">*</span></label>
                        <input type="text" class="form-control money" id="rateUpdate" name="rate_per_slot" disabled>
                    </div>

                    <div class="form-group">
                        <label for="benefitUpdate">{{ trans('labels.benefit') }}<span class="required">*</span></label>
                        <textarea
                            id="benefitUpdate"
                            name="benefit"
                            class="form-control"
                            placeholder="{{ trans('placeholder.input', ['field' => trans('labels.benefit')]) }}"
                            autofocus
                            required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="negotiateUpdate">{{ trans('labels.negotiate') }}<span class="required">*</span></label>
                        <select id="negotiateUpdate" name="negotiate" class="form-control" required>
                            @foreach($negotiates as $negotiate)
                                <option value="{{ $negotiate }}">
                                    {{ ucfirst($negotiate) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="bankName">{{ trans('labels.bank_name') }}</label>
                        <input type="text" class="form-control" id="updateBankName" name="bank_name">
                    </div>

                    <div class="form-group">
                        <label for="bankAccount">{{ trans('labels.bank_account') }}</label>
                        <input type="text" class="form-control" id="updateBankAccount" name="bank_account">
                    </div>

                    <div class="form-group">
                        <label for="bankAccountName">{{ trans('labels.bank_account_name') }}</label>
                        <input type="text" class="form-control" id="updateBankAccountName" name="bank_account_name">
                    </div>

                    <div class="form-group">
                        <label for="nik">{{ trans('labels.nik') }}</label>
                        <input type="text" class="form-control" id="updateNik" name="nik">
                    </div>

                    <input type="hidden" id="offerId">

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> {{ trans('buttons.update') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
