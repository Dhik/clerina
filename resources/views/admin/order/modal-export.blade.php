<!-- Modal -->
<div class="modal fade" id="orderExportModal" tabindex="-1" role="dialog" aria-labelledby="orderExportModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orderExportModalLabel">{{ trans('labels.export') }} {{ trans('labels.order') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="orderExportForm" action="{{ route('order.export') }}" method="post">
                    @csrf

                    <div class="form-group">
                        <label for="date">{{ trans('labels.date') }}</label>
                        <input type="text" class="form-control rangeDate" id="exportDate" name="date" autocomplete="off" required>
                    </div>

                    <div class="form-group d-none" id="errorExportOrder"></div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> {{ trans('labels.export') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
