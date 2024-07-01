<!-- Modal -->
<div class="modal fade" id="financeModal" role="dialog" aria-labelledby="financeModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="offerModalLabel">{{ trans('labels.update') }} {{ trans('labels.finance') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="financeForm">
                    @csrf

                    <div class="form-group">
                        <label for="transferStatusField">{{ trans('labels.transfer_status') }}<span class="required">*</span></label>
                        <select id="transferStatusField" name="transfer_status" class="form-control" required>
                            <option value="">{{ trans('placeholder.select', ['field' => trans('labels.status')]) }}</option>
                            @foreach($transferStatuses as $status)
                                <option value="{{ $status }}">
                                    {{ ucfirst($status) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="transferDateField">{{ trans('labels.transfer_date') }}</label>
                        <input type="text" class="form-control singleDate" id="transferDateField" name="transfer_date">
                    </div>

                    <input type="hidden" id="financeOfferId">

                    <div class="form-group d-none" id="errorUpdateFinance"></div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> {{ trans('buttons.update') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
