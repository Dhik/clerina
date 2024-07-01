<!-- Modal -->
<div class="modal fade" id="orderUpdateModal" tabindex="-1" role="dialog" aria-labelledby="orderUpdateModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orderModalLabel">{{ trans('labels.update') }} {{ trans('labels.order') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="orderUpdateForm">
                    @csrf

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="dateUpdate">{{ trans('labels.date') }}</label>
                                <input type="text" class="form-control singleDate" id="dateUpdate" name="date" autocomplete="off" required>
                            </div>

                            <div class="form-group">
                                <label for="idOrderUpdate">{{ trans('labels.id_order') }}</label>
                                <input type="text" class="form-control" id="idOrderUpdate" name="id_order" required>
                            </div>

                            <div class="form-group">
                                <label for="receiptNumberUpdate">{{ trans('labels.receipt_number') }}</label>
                                <input type="text" class="form-control" id="receiptNumberUpdate" name="receipt_number" required>
                            </div>

                            <div class="form-group">
                                <label for="shipmentUpdate">{{ trans('labels.shipment') }}</label>
                                <input type="text" class="form-control" id="shipmentUpdate" name="shipment">
                            </div>

                            <div class="form-group">
                                <label for="paymentMethodUpdate">{{ trans('labels.payment_method') }}</label>
                                <input type="text" class="form-control" id="paymentMethodUpdate" name="payment_method">
                            </div>

                            <div class="form-group">
                                <label for="salesChannelIdUpdate">{{ trans('labels.sales_channel') }}</label>
                                <select type="text" class="form-control" id="salesChannelIdUpdate" name="sales_channel_id" required>
                                    <option value="" selected>{{ trans('placeholder.select_sales_channel') }}</option>
                                    @foreach($salesChannels as $salesChannel)
                                        <option value={{ $salesChannel->id }}>{{ $salesChannel->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="productUpdate">{{ trans('labels.product') }}</label>
                                <input type="text" class="form-control" id="productUpdate" name="product">
                            </div>

                            <div class="form-group">
                                <label for="variantUpdate">{{ trans('labels.variant') }}</label>
                                <input type="text" class="form-control" id="variantUpdate" name="variant">
                            </div>

                            <div class="form-group">
                                <label for="priceUpdate">{{ trans('labels.price') }} ({{ trans('labels.rp') }})</label>
                                <input type="text" class="form-control money" id="priceUpdate" name="price" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="usernameUpdate">{{ trans('labels.username') }}</label>
                                <input type="text" class="form-control" id="usernameUpdate" name="username">
                            </div>

                            <div class="form-group">
                                <label for="customerNameUpdate">{{ trans('labels.customer_name') }}</label>
                                <input type="text" class="form-control" id="customerNameUpdate" name="customer_name" required>
                            </div>

                            <div class="form-group">
                                <label for="customerPhoneNumberUpdate">{{ trans('labels.phone_number') }}</label>
                                <input type="text" class="form-control" id="customerPhoneNumberUpdate" name="customer_phone_number" required>
                            </div>

                            <div class="form-group">
                                <label for="shippingAddressUpdate">{{ trans('labels.shipping_address') }}</label>
                                <input type="text" class="form-control" id="shippingAddressUpdate" name="shipping_address" required>
                            </div>

                            <div class="form-group">
                                <label for="cityUpdate">{{ trans('labels.city') }}</label>
                                <input type="text" class="form-control" id="cityUpdate" name="city">
                            </div>

                            <div class="form-group">
                                <label for="provinceUpdate">{{ trans('labels.province') }}</label>
                                <input type="text" class="form-control" id="provinceUpdate" name="province">
                            </div>

                            <div class="form-group">
                                <label for="skuUpdate">{{ trans('labels.sku') }}</label>
                                <input type="text" class="form-control" id="skuUpdate" name="sku">
                            </div>

                            <div class="form-group">
                                <label for="qtyUpdate">{{ trans('labels.qty') }}</label>
                                <input type="text" class="form-control money" id="qtyUpdate" name="qty" required>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="orderId" id="orderId"/>
                    <div class="form-group d-none" id="errorUpdateSubmitOrder"></div>

                    <button type="submit" class="btn btn-primary" id="btnOrderExport">
                        <i class="fas fa-save"></i> {{ trans('buttons.save') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
