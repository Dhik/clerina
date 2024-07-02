<!-- Modal -->
<div class="modal fade" id="adSpentMarketPlaceModal" tabindex="-1" role="dialog" aria-labelledby="adSpentMarketPlaceModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="adSpentMarketPlaceLabel">{{ trans('labels.add') }} {{ trans('labels.ad_spent_market_place') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="adSpentMarketPlaceForm">
                    @csrf

                    <div class="form-group">
                        <label for="dateAdSpentMarketPlace">{{ trans('labels.date') }}</label>
                        <input type="text" class="form-control singleDate" id="dateAdSpentMarketPlace" name="date" autocomplete="off" required>
                    </div>

                    <div class="form-group">
                        <label for="salesMarketPlace">{{ trans('labels.sales_channel') }}</label>
                        <select type="text" class="form-control" id="salesMarketPlace" name="sales_channel_id" required>
                            @foreach($salesChannels as $salesChannel)
                                <option value={{ $salesChannel->id }}>{{ $salesChannel->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="amountAdSpentMarketPlace">{{ trans('labels.amount') }}</label>
                        <input type="text" class="form-control money" id="amountAdSpentMarketPlace" name="amount" required>
                    </div>

                    <div class="form-group d-none" id="errorSubmitAdSpentMarketPlace"></div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> {{ trans('buttons.save') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
