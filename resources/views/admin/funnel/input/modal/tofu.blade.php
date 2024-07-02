<!-- Modal -->
<div class="modal fade" id="tofuModal" tabindex="-1" role="dialog" aria-labelledby="tofuModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ trans('labels.add') }} {{ trans('labels.tofu') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="tofuForm">
                    @csrf

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="tofuDate">{{ trans('labels.date') }}</label>
                                <input type="text" class="form-control singleDate" id="tofuDate" name="date" autocomplete="off" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="tofuSocialMedia">{{ trans('labels.social_media') }}</label>
                                <select class="form-control" id="tofuSocialMedia" name="social_media_id" required>
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
                                <label for="tofuSpend">{{ trans('labels.spend') }}</label>
                                <input type="text" class="form-control money" id="tofuSpend" name="spend">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tofuReach">{{ trans('labels.reach') }}</label>
                                <input type="text" class="form-control money" id="tofuReach" name="reach">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tofuImpression">{{ trans('labels.impression') }}</label>
                                <input type="text" class="form-control money" id="tofuImpression" name="impression">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tofuCPV">{{ trans('labels.cpv') }}</label>
                                <input type="text" class="form-control money" id="tofuCPV" name="cpv">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tofuPlayVideo">{{ trans('labels.play_video') }}</label>
                                <input type="text" class="form-control money" id="tofuPlayVideo" name="play_video">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tofuLinkClick">{{ trans('labels.link_click') }}</label>
                                <input type="text" class="form-control money" id="tofuLinkClick" name="link_click">
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
