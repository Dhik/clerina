<!-- Modal -->
<div class="modal fade" id="bofuModal" tabindex="-1" role="dialog" aria-labelledby="bofuModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ trans('labels.add') }} {{ trans('labels.bofu') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="bofuForm">
                    @csrf

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="bofuDate">{{ trans('labels.date') }}</label>
                                <input type="text" class="form-control singleDate" id="bofuDate" name="date" autocomplete="off" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="bofuSocialMedia">{{ trans('labels.social_media') }}</label>
                                <select class="form-control" id="bofuSocialMedia" name="social_media_id" required>
                                    @foreach($socialMedia as $media)
                                        <option value="{{ $media->id }}">{{ $media->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="bofuSpend">{{ trans('labels.spend') }}</label>
                                <input type="text" class="form-control money" id="bofuSpend" name="spend">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="bofuATC">{{ trans('labels.atc') }}</label>
                                <input type="text" class="form-control money" id="bofuATC" name="atc">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="bofuIC">{{ trans('labels.initiated_checkout_number') }}</label>
                                <input type="text" class="form-control money" id="bofuIC" name="initiated_checkout_number">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="bofuPurchaseNumber">{{ trans('labels.purchase_number') }}</label>
                                <input type="text" class="form-control money" id="bofuPurchaseNumber" name="purchase_number">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="bofuRoas">{{ trans('labels.roas') }}</label>
                                <input type="text" class="form-control moneyDecimal" id="bofuRoas" name="roas">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="bofuFrequency">{{ trans('labels.frequency') }}</label>
                                <input type="text" class="form-control moneyDecimal" id="bofuFrequency" name="frequency">
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> {{ trans('buttons.save') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
