<!-- Modal -->
<div class="modal fade" id="visitModal" tabindex="-1" role="dialog" aria-labelledby="visitModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="brandingModalLabel">{{ trans('labels.add') }} {{ trans('labels.visit') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="visitForm">
                    @csrf

                    <div class="form-group">
                        <label for="dateVisit">{{ trans('labels.date') }}</label>
                        <input type="text" class="form-control singleDate" id="dateVisit" name="date" autocomplete="off" required>
                    </div>

                    <div class="form-group">
                        <label for="salesChannelVisit">{{ trans('labels.sales_channel') }}</label>
                        <select type="text" class="form-control" id="salesChannelVisit" name="sales_channel_id" required>
                            @foreach($salesChannels as $salesChannel)
                                <option value={{ $salesChannel->id }}>{{ $salesChannel->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="amountVisit">{{ trans('labels.amount') }}</label>
                        <input type="text" class="form-control money" id="amountVisit" name="visit_amount" required>
                    </div>

                    <div class="form-group d-none" id="errorSubmitVisit"></div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> {{ trans('buttons.save') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
