<!-- Modal -->
<div class="modal fade" id="salesChannelModal" tabindex="-1" role="dialog" aria-labelledby="salesChannelModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="salesChannelModalLabel">{{ trans('labels.add') }} {{ trans('labels.social_media') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="salesChannelForm">
                    @csrf
                    <!-- Your form fields here -->
                    <div class="form-group">
                        <label for="name">{{ trans('labels.name') }}</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> {{ trans('buttons.save') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
