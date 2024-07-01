<!-- Modal -->
<div class="modal fade" id="orderModal" tabindex="-1" role="dialog" aria-labelledby="orderModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orderModalLabel">{{ trans('labels.add') }} {{ trans('labels.order') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="orderForm">
                    @csrf

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="date">{{ trans('labels.date') }}</label>
                                <input type="text" class="form-control singleDate" id="date" name="date" autocomplete="off" required>
                            </div>

                            <div class="form-group">
                                <label for="idOrder">{{ trans('labels.id_order') }}</label>
                                <input type="text" class="form-control" id="idOrder" name="id_order" required>
                            </div>

                            <div class="form-group">
                                <label for="receiptNumber">{{ trans('labels.receipt_number') }}</label>
                                <input type="text" class="form-control" id="receiptNumber" name="receipt_number" required>
                            </div>

                            <div class="form-group">
                                <label for="shipment">{{ trans('labels.shipment') }}</label>
                                <input type="text" class="form-control" id="shipment" name="shipment">
                            </div>

                            <div class="form-group">
                                <label for="paymentMethod">{{ trans('labels.payment_method') }}</label>
                                <input type="text" class="form-control" id="paymentMethod" name="payment_method">
                            </div>

                            <div class="form-group">
                                <label for="salesChannelId">{{ trans('labels.sales_channel') }}</label>
                                <select type="text" class="form-control" id="salesChannelId" name="sales_channel_id" required>
                                    <option value="" selected>{{ trans('placeholder.select_sales_channel') }}</option>
                                    @foreach($salesChannels as $salesChannel)
                                        <option value={{ $salesChannel->id }}>{{ $salesChannel->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="product">{{ trans('labels.product') }}</label>
                                <input type="text" class="form-control" id="product" name="product">
                            </div>

                            <div class="form-group">
                                <label for="variant">{{ trans('labels.variant') }}</label>
                                <input type="text" class="form-control" id="variant" name="variant">
                            </div>

                            <div class="form-group">
                                <label for="price">{{ trans('labels.price') }} ({{ trans('labels.rp') }})</label>
                                <input type="text" class="form-control money" id="price" name="price" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="username">{{ trans('labels.username') }}</label>
                                <input type="text" class="form-control" id="username" name="username">
                            </div>

                            <div class="form-group">
                                <label for="customerName">{{ trans('labels.customer_name') }}</label>
                                <input type="text" class="form-control" id="customerName" name="customer_name" required>
                            </div>

                            <div class="form-group">
                                <label for="customerPhoneNumber">{{ trans('labels.phone_number') }}</label>
                                <input type="text" class="form-control" id="customerPhoneNumber" name="customer_phone_number" required>
                            </div>

                            <div class="form-group">
                                <label for="shippingAddress">{{ trans('labels.shipping_address') }}</label>
                                <input type="text" class="form-control" id="shippingAddress" name="shipping_address" required>
                            </div>

                            <div class="form-group">
                                <label for="city">{{ trans('labels.city') }}</label>
                                <input type="text" class="form-control" id="city" name="city">
                            </div>

                            <div class="form-group">
                                <label for="province">{{ trans('labels.province') }}</label>
                                <input type="text" class="form-control" id="province" name="province">
                            </div>

                            <div class="form-group">
                                <label for="sku">{{ trans('labels.sku') }}</label>
                                <input type="text" class="form-control" id="sku" name="sku">
                            </div>

                            <div class="form-group">
                                <label for="qty">{{ trans('labels.qty') }}</label>
                                <input type="text" class="form-control money" id="qty" name="qty" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group d-none" id="errorSubmitOrder"></div>

                    <button type="submit" class="btn btn-primary" id="btnOrderExport">
                        <i class="fas fa-save"></i> {{ trans('buttons.save') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
