<!-- Modal -->
<div class="modal fade" id="marketingCategoryModal" tabindex="-1" role="dialog" aria-labelledby="marketingCategoryModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="marketingCategoryModalModalLabel">{{ trans('labels.add') }} {{ trans('labels.marketing_category') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="marketingCategoryForm">
                    @csrf
                    <!-- Your form fields here -->
                    <div class="form-group">
                        <label for="name">{{ trans('labels.name') }}</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>

                    <!-- Your form fields here -->
                    <div class="form-group">
                        <label for="type">{{ trans('labels.type') }}</label>
                        <select class="form-control" id="type" name="type" required>
                            @foreach($marketingCategoryTypes as $category)
                                <option value="{{$category}}">{{ ucfirst($category) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> {{ trans('buttons.save') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
