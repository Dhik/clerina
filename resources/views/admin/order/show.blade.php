@extends('adminlte::page')

@section('title', trans('labels.order'))

@section('content_header')
    <h1>{{ $order->id_order }}</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <table class="table table-striped">
                            <tbody>
                            <tr>
                                <th>{{ trans('labels.id_order') }}</th>
                                <td>{{ $order->id_order }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.receipt_number') }}</th>
                                <td>{{ $order->receipt_number }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.shipment') }}</th>
                                <td>{{ $order->shipment }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.date') }}</th>
                                <td>{{ $order->date }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.payment_method') }}</th>
                                <td>{{ $order->payment_method }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.product') }}</th>
                                <td>{{ $order->product }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.sku') }}</th>
                                <td>{{ $order->sku }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.variant') }}</th>
                                <td>{{ $order->variant }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.price') }}</th>
                                <td>{{ number_format($order->price, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.qty') }}</th>
                                <td>{{ number_format($order->qty, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.username') }}</th>
                                <td>{{ $order->username }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.customer_name') }}</th>
                                <td>{{ $order->customer_name }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.phone_number') }}</th>
                                <td>{{ $order->customer_phone_number }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.shipping_address') }}</th>
                                <td>{{ $order->shipping_address }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.city') }}</th>
                                <td>{{ $order->city }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.province') }}</th>
                                <td>{{ $order->province }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.amount') }}</th>
                                <td>{{ number_format($order->amount, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.channel') }}</th>
                                <td>{{ $order->salesChannel->name ?? '' }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="card-footer">
{{--                        <a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary">{{ trans('buttons.edit') }}</a>--}}
                        <button class="btn btn-danger delete-user">{{ trans('buttons.delete') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function () {
            $('.delete-user').click(function (e) {
                e.preventDefault();

                Swal.fire({
                    title: '{{ trans('labels.are_you_sure') }}',
                    text: '{{ trans('labels.not_be_able_to_recover') }}',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: '{{ trans('buttons.confirm_swal') }}',
                    cancelButtonText: '{{ trans('buttons.cancel_swal') }}',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ route('order.destroy', $order->id) }}',
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function (response) {
                                Swal.fire(
                                    '{{ trans('labels.success') }}',
                                    '{{ trans('messages.success_delete') }}',
                                    'success'
                                ).then(() => {
                                    window.location.href = "{{ route('order.index') }}";
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
@stop
