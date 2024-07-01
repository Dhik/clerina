<!-- Modal -->
<div class="modal fade" id="brandingUpdateModal" tabindex="-1" role="dialog" aria-labelledby="brandingUpdateModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="brandingUpdateModalLabel">{{ trans('labels.edit') }} {{ trans('labels.branding') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="brandingUpdateForm">
                    @csrf

                    <div class="form-group">
                        <label for="dateBranding">{{ trans('labels.date') }}</label>
                        <input type="text" class="form-control singleDate" id="dateUpdateBranding" name="date" autocomplete="off" required>
                    </div>

                    <div class="form-group">
                        <label for="categoryUpdateBranding">{{ trans('labels.category') }}</label>
                        <select type="text" class="form-control" id="categoryUpdateBranding" name="marketing_category_id" required>
                            @foreach($brandingCategories as $brandingCategory)
                                <option value={{ $brandingCategory->id }}>{{ $brandingCategory->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="amountUpdateBranding">{{ trans('labels.amount') }} ({{ trans('labels.rp') }})</label>
                        <input type="text" class="form-control money" id="amountUpdateBranding" name="amount" required>
                    </div>

                    <input type="hidden" name="marketingId" id="brandingId"/>
                    <div class="form-group d-none" id="errorSubmitUpdateBranding"></div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> {{ trans('buttons.save') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
