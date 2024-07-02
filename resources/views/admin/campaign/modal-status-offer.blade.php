<!-- Modal -->
<div class="modal fade" id="statusUpdateModal" role="dialog" aria-labelledby="offerModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="offerModalLabel">{{ trans('labels.update') }} {{ trans('labels.status') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="statusUpdateForm">
                    @csrf

                    <div class="form-group">
                        <label for="statusField">{{ trans('labels.status') }}<span class="required">*</span></label>
                        <select id="statusField" name="status" class="form-control" required>
                            <option value="">{{ trans('placeholder.select', ['field' => trans('labels.status')]) }}</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status }}">
                                    {{ ucfirst($status) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="accSlot">{{ trans('labels.acc_slot') }}</label>
                        <input type="text" class="form-control money" id="accSlot" name="acc_slot">
                        <small id="accSlotHelp" class="form-text text-muted">{{ trans('labels.required_if_status_approved') }}</small>
                    </div>

                    <input type="hidden" id="statusOfferId">

                    <div class="form-group d-none" id="errorUpdateStatus"></div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> {{ trans('buttons.update') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
