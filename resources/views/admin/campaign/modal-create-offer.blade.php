<!-- Modal -->
<div class="modal fade" id="offerModal" role="dialog" aria-labelledby="offerModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="offerModalLabel">{{ trans('labels.add') }} {{ trans('labels.offer') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="offerForm">
                    @csrf
                    <!-- Your form fields here -->
                    <div class="form-group">
                        <label for="usernameOffer">{{ trans('labels.username_kol') }}<span class="required">*</span></label>

                        <select class="form-control" id="usernameOffer" name="key_opinion_leader_id" required>
                            <option value=""></option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="ratePerSlot">{{ trans('labels.slot_rate') }}<span class="required">*</span></label>
                        <input type="text" class="form-control money" id="ratePerSlot" name="rate_per_slot" required readonly>
                    </div>

                    <div class="form-group">
                        <label for="benefit">{{ trans('labels.benefit') }}<span class="required">*</span></label>
                        <textarea
                            id="benefit"
                            name="benefit"
                            class="form-control"
                            placeholder="{{ trans('placeholder.input', ['field' => trans('labels.benefit')]) }}"
                            autofocus
                            required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="negotiate">{{ trans('labels.negotiate') }}<span class="required">*</span></label>
                        <select id="negotiate" name="negotiate" class="form-control" required>
                            @foreach($negotiates as $negotiate)
                                <option value="{{ $negotiate }}">
                                    {{ ucfirst($negotiate) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="bankName">{{ trans('labels.bank_name') }}</label>
                        <input type="text" class="form-control" id="bankName" name="bank_name">
                    </div>

                    <div class="form-group">
                        <label for="bankAccount">{{ trans('labels.bank_account') }}</label>
                        <input type="text" class="form-control" id="bankAccount" name="bank_account">
                    </div>

                    <div class="form-group">
                        <label for="bankAccountName">{{ trans('labels.bank_account_name') }}</label>
                        <input type="text" class="form-control" id="bankAccountName" name="bank_account_name">
                    </div>

                    <div class="form-group">
                        <label for="nik">{{ trans('labels.nik') }}</label>
                        <input type="text" class="form-control" id="nik" name="nik">
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> {{ trans('buttons.save') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
