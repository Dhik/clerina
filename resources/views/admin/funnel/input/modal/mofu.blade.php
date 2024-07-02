<!-- Modal -->
<div class="modal fade" id="mofuModal" tabindex="-1" role="dialog" aria-labelledby="mofuModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ trans('labels.add') }} {{ trans('labels.mofu') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="mofuForm">
                    @csrf

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="mofuDate">{{ trans('labels.date') }}</label>
                                <input type="text" class="form-control singleDate" id="mofuDate" name="date" autocomplete="off" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="mofuSocialMedia">{{ trans('labels.social_media') }}</label>
                                <select class="form-control" id="mofuSocialMedia" name="social_media_id" required>
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
                                <label for="mofuSpend">{{ trans('labels.spend') }}</label>
                                <input type="text" class="form-control money" id="mofuSpend" name="spend">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="mofuReach">{{ trans('labels.reach') }}</label>
                                <input type="text" class="form-control money" id="mofuReach" name="reach">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="mofuImpression">{{ trans('labels.impression') }}</label>
                                <input type="text" class="form-control money" id="mofuImpression" name="impression">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="mofuEngagement">{{ trans('labels.engagement') }}</label>
                                <input type="text" class="form-control money" id="mofuEngagement" name="engagement">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="mofuLinkClick">{{ trans('labels.link_click') }}</label>
                                <input type="text" class="form-control money" id="mofuLinkClick" name="link_click">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="mofuCTR">{{ trans('labels.ctr') }}</label>
                                <input type="text" class="form-control moneyDecimal" id="mofuCTR" name="ctr">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="mofuCPLV">{{ trans('labels.cplv') }}</label>
                                <input type="text" class="form-control money" id="mofuCPLV" name="cplv">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="mofuCPA">{{ trans('labels.cpa') }}</label>
                                <input type="text" class="form-control money" id="mofuCPA" name="cpa">
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
