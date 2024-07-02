<!-- Modal -->
<div class="modal fade" id="marketingCategoryUpdateModal" tabindex="-1" role="dialog" aria-labelledby="marketingCategoryUpdateModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="marketingCategoryModalLabel">{{ trans('labels.update') }} {{ trans('labels.marketing_category') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="marketingCategoryUpdateForm">
                    @csrf
                    <!-- Your form fields here -->
                    <div class="form-group">
                        <label for="name">{{ trans('labels.name') }}</label>
                        <input type="text" class="form-control" id="nameUpdate" name="name" required>
                    </div>

                    <!-- Your form fields here -->
                    <div class="form-group">
                        <label for="name">{{ trans('labels.type') }}</label>

                        <select class="form-control" id="typeUpdate" name="type" required>
                            @foreach($marketingCategoryTypes as $category)
                                <option value="{{$category}}">
                                    {{ ucfirst($category) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <input type="hidden" name="marketingCategoryId" id="marketingCategoryId"/>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> {{ trans('buttons.update') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
