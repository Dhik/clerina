<!-- Modal -->
<div class="modal fade" id="contentUpdateModal" role="dialog" aria-labelledby="contentUpdateModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="contentUpdateModalLabel">{{ trans('labels.update') }} {{ trans('labels.content') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="contentUpdateForm">
                    @csrf
                    <!-- Your form fields here -->
                    <div class="form-group">
                        <label for="usernameUpdate">{{ trans('labels.influencer') }}<span class="required">*</span></label>
                        <input type="text" class="form-control" id="usernameUpdate" readonly>
                    </div>

                    <div class="form-group">
                        <label for="taskNameUpdate">{{ trans('labels.task') }}<span class="required">*</span></label>
                        <input type="text" class="form-control" id="taskNameUpdate" name="task_name" placeholder="{{ trans('placeholder.input', ['field' => trans('labels.task')]) }}" required>
                    </div>

                    <div class="form-group">
                        <label for="rateCardUpdate">{{ trans('labels.rate_card') }}<span class="required">*</span></label>
                        <input type="text" class="form-control money" id="rateCardUpdate" name="rate_card" placeholder="{{ trans('placeholder.input', ['field' => trans('labels.rate_card')]) }}" required>
                    </div>

                    <div class="form-group">
                        <label for="platformUpdate">{{ trans('labels.platform') }}<span class="required">*</span></label>
                        <select class="form-control" id="platformUpdate" name="channel" required>
                            @foreach($platforms as $platform)
                                <option value={{ $platform['value'] }}>
                                    {{ $platform['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="linkUpdate">{{ trans('labels.link') }}</label>
                        <input type="text" class="form-control" id="linkUpdate" name="link" placeholder="{{ trans('placeholder.input', ['field' => trans('labels.link')]) }}">
                    </div>

                    <div class="form-group">
                        <label for="productUpdate">{{ trans('labels.product') }}<span class="required">*</span></label>
                        <input type="text" class="form-control" id="productUpdate" name="product" placeholder="{{ trans('placeholder.input', ['field' => trans('labels.product')]) }}" required>
                    </div>

                    <div class="form-group">
                        <label for="boostCodeUpdate">{{ trans('labels.boost_code') }}</label>
                        <input type="text" class="form-control" id="boostCodeUpdate" name="boost_code" placeholder="{{ trans('placeholder.input', ['field' => trans('labels.boost_code')]) }}">
                    </div>

                    <div class="form-group">
                        <label for="adsCodeUpdate">{{ trans('labels.kode_ads') }}</label>
                        <input type="text" class="form-control" id="adsCodeUpdate" name="kode_ads" placeholder="{{ trans('placeholder.input', ['field' => trans('labels.kode_ads')]) }}">
                    </div>

                    <!-- New fields for views, likes, and comments -->
                    <div class="form-group">
                        <label for="viewsUpdate">{{ trans('labels.views') }}</label>
                        <input type="number" class="form-control" id="viewsUpdate" name="views" placeholder="{{ trans('placeholder.input', ['field' => trans('labels.views')]) }}">
                    </div>

                    <div class="form-group">
                        <label for="likesUpdate">{{ trans('labels.like') }}</label>
                        <input type="number" class="form-control" id="likesUpdate" name="likes" placeholder="{{ trans('placeholder.input', ['field' => trans('labels.like')]) }}">
                    </div>

                    <div class="form-group">
                        <label for="commentsUpdate">{{ trans('labels.comment') }}</label>
                        <input type="number" class="form-control" id="commentsUpdate" name="comments" placeholder="{{ trans('placeholder.input', ['field' => trans('labels.comment')]) }}">
                    </div>

                    <input type="hidden" id="contentId">

                    <div class="form-group d-none" id="errorContentUpdate"></div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> {{ trans('buttons.update') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
