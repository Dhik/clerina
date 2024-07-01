<!-- Modal -->
<div class="modal fade" id="adSpentSocialMediaModal" tabindex="-1" role="dialog" aria-labelledby="adSpentSocialMediaModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="brandingModalLabel">{{ trans('labels.add') }} {{ trans('labels.ad_spent_social_media') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="adSpentSocialMediaForm">
                    @csrf

                    <div class="form-group">
                        <label for="dateAdSpentSocialMedia">{{ trans('labels.date') }}</label>
                        <input type="text" class="form-control singleDate" id="dateAdSpentSocialMedia" name="date" autocomplete="off" required>
                    </div>

                    <div class="form-group">
                        <label for="salesSocialMedia">{{ trans('labels.social_media') }}</label>
                        <select type="text" class="form-control" id="salesSocialMedia" name="social_media_id" required>
                            @foreach($socialMedia as $media)
                                <option value={{ $media->id }}>{{ $media->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="amountAdSpentSocialMedia">{{ trans('labels.amount') }}</label>
                        <input type="text" class="form-control money" id="amountAdSpentSocialMedia" name="amount" required>
                    </div>

                    <div class="form-group d-none" id="errorSubmitAdSpentSocialMedia"></div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> {{ trans('buttons.save') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
