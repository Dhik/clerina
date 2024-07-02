<!-- Modal -->
<div class="modal fade" id="customerNoteModal" tabindex="-1" role="dialog" aria-labelledby="customerNoteModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="socialMediaModalLabel">{{ trans('labels.add') }} {{ trans('labels.customer_note') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="customerNoteForm">
                    @csrf
                    <!-- Your form fields here -->
                    <div class="form-group">
                        <label for="note">{{ trans('labels.note') }}</label>
                        <textarea class="form-control" id="note" name="note" required></textarea>
                    </div>

                    <input type="hidden" name="customer_id" value="{{ $customer->id }}">

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> {{ trans('buttons.save') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
