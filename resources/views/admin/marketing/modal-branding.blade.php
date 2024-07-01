<!-- Modal -->
<div class="modal fade" id="brandingModal" tabindex="-1" role="dialog" aria-labelledby="brandingModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="brandingModalLabel">{{ trans('labels.add') }} {{ trans('labels.branding') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="brandingForm">
                    @csrf

                    <div class="form-group">
                        <label for="dateBranding">{{ trans('labels.date') }}</label>
                        <input type="text" class="form-control singleDate" id="dateBranding" name="date" autocomplete="off" required>
                    </div>

                    <div class="form-group">
                        <label for="categoryBranding">{{ trans('labels.category') }}</label>
                        <select type="text" class="form-control" id="categoryBranding" name="marketing_category_id" required>
                            @foreach($brandingCategories as $brandingCategory)
                                <option value={{ $brandingCategory->id }}>{{ $brandingCategory->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="amountBranding">{{ trans('labels.amount') }} ({{ trans('labels.rp') }})</label>
                        <input type="text" class="form-control money" id="amountBranding" name="amount" required>
                    </div>

                    <div class="form-group d-none" id="errorSubmitBranding"></div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> {{ trans('buttons.save') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
