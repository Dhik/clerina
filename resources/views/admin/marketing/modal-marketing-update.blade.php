<!-- Modal -->
<div class="modal fade" id="marketingUpdateModal" tabindex="-1" role="dialog" aria-labelledby="marketingUpdateModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="marketingModalLabel">{{ trans('labels.edit') }} {{ trans('labels.marketing') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="marketingUpdateForm">
                    @csrf

                    <div class="form-group">
                        <label for="dateUpdateMarketing">{{ trans('labels.date') }}</label>
                        <input type="text" class="form-control singleDate" id="dateUpdateMarketing" name="date" autocomplete="off" required>
                    </div>

                    <div class="form-group">
                        <label for="categoryUpdateMarketing">{{ trans('labels.category') }}</label>
                        <select type="text" class="form-control" id="categoryUpdateMarketing" name="marketing_category_id" required>
                            <option value="" selected>{{ trans('placeholder.select_category') }}</option>
                            @foreach($marketingCategories as $marketingCategory)
                                <option value={{ $marketingCategory->id }}>{{ $marketingCategory->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="subCategoryUpdateMarketing">{{ trans('labels.sub_category') }}</label>
                        <select type="text" class="form-control" id="subCategoryUpdateMarketing" name="marketing_sub_category_id" required>
                            <option value="" selected>{{ trans('placeholder.select_category_first') }}</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="amountUpdateMarketing">{{ trans('labels.amount') }} ({{ trans('labels.rp') }})</label>
                        <input type="text" class="form-control money" id="amountUpdateMarketing" name="amount" required>
                    </div>

                    <input type="hidden" name="marketingId" id="marketingId"/>
                    <div class="form-group d-none" id="errorSubmitUpdateMarketing"></div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> {{ trans('buttons.save') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
