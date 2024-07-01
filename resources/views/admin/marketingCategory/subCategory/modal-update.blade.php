<!-- Modal -->
<div class="modal fade" id="marketingSubCategoryUpdateModal" tabindex="-1" role="dialog" aria-labelledby="marketingSubCategoryUpdateModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="marketingSubCategoryModalLabel">{{ trans('labels.update') }} {{ trans('labels.sub_marketing_category') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="marketingSubCategoryUpdateForm">
                    @csrf
                    <!-- Your form fields here -->
                    <div class="form-group">
                        <label for="name">{{ trans('labels.name') }}</label>
                        <input type="text" class="form-control" id="nameUpdate" name="name" required>
                    </div>

                    <input type="hidden" name="marketing_category_id" value={{ $marketingCategory->id }} >
                    <input type="hidden" name="marketingSubCategoryId" id="marketingSubCategoryId"/>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> {{ trans('buttons.update') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
