<!-- Modal -->
<div class="modal fade" id="reviewOfferModal" role="dialog" aria-labelledby="reviewOfferModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="offerModalLabel">{{ trans('labels.review') }} {{ trans('labels.offering') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="reviewOfferForm">
                    @csrf
                    <!-- Your form fields here -->
                    <div class="form-group">
                        <label for="rateFinalSlot">{{ trans('labels.rate_final_slot') }}<span class="required">*</span></label>
                        <input type="text" class="form-control money" id="rateFinalSlot" name="rate_final_slot">
                        <small id="rateFinalSlotHelp" class="form-text text-muted">{{ trans('labels.price_after_negotiation') }}</small>
                    </div>

                    <div class="form-group">
                        <label for="rateFinalSlot">{{ trans('labels.npwp') }}<span class="required">*</span></label>
                        <div class="col-md-6 icheck-primary d-inline">
                            <input
                                type="checkbox"
                                name="npwp"
                                id="npwpCheckbox"
                                value=1
                            >
                            <label for="npwpCheckbox">
                            </label>
                        </div>
                    </div>

                    <input type="hidden" id="reviewOfferId">
                    <input type="hidden" id="rateTotalSlot" name="rate_total_slot">

                    <div class="form-group d-none" id="errorReviewOffer"></div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> {{ trans('buttons.update') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
