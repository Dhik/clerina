<!-- Modal -->
<div class="modal fade" id="marketingModal" tabindex="-1" role="dialog" aria-labelledby="marketingModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="marketingModalLabel">{{ trans('labels.add') }} {{ trans('labels.marketing') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="marketingForm">
                    @csrf

                    <div class="form-group">
                        <label for="dateMarketing">{{ trans('labels.date') }}</label>
                        <input type="text" class="form-control singleDate" id="dateMarketing" name="date" autocomplete="off" required>
                    </div>

                    <div class="form-group">
                        <label for="categoryMarketing">{{ trans('labels.category') }}</label>
                        <select type="text" class="form-control" id="categoryMarketing" name="marketing_category_id" required>
                            <option value="" selected>{{ trans('placeholder.select_category') }}</option>
                            @foreach($marketingCategories as $marketingCategory)
                                <option value={{ $marketingCategory->id }}>{{ $marketingCategory->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="subCategoryMarketing">{{ trans('labels.sub_category') }}</label>
                        <select type="text" class="form-control" id="subCategoryMarketing" name="marketing_sub_category_id" required>
                            <option value="" selected>{{ trans('placeholder.select_category_first') }}</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="amountMarketing">{{ trans('labels.amount') }} ({{ trans('labels.rp') }})</label>
                        <input type="text" class="form-control money" id="amountMarketing" name="amount" required>
                    </div>

                    <div class="form-group d-none" id="errorSubmitMarketing"></div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> {{ trans('buttons.save') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
