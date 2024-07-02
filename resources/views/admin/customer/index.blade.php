@extends('adminlte::page')

@section('title', trans('labels.customer'))

@section('content_header')
    <h1>{{ trans('labels.customer') }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="btn-group">
                                <form id="orderExportForm" action="{{ route('customer.export') }}" method="post">
                                    @csrf
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-file-download"></i> {{ trans('labels.export') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <input type="number" class="form-control" id="filterCountOrders" placeholder="{{ trans('placeholder.count_orders') }}" autocomplete="off">
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" id="filterTenant">
                                <option value="" selected>{{ trans('placeholder.select_tenant') }}</option>
                                @foreach($tenants as $tenant)
                                    <option value="{{ $tenant->id }}">{{ $tenant->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-default" id="resetFilterBtn">{{ trans('buttons.reset_filter') }}</button>
                        </div>
                    </div>
                    <table id="customerTable" class="table table-bordered table-striped dataTable responsive" aria-describedby="customerTable-info" width="100%">
                        <thead>
                            <tr>
                                <th>{{ trans('labels.name') }}</th>
                                <th>{{ trans('labels.phone_number') }}</th>
                                <th>{{ trans('labels.total_order') }}</th>
                                <th>{{ trans('labels.tenant_name') }}</th>
                                <th width="10%">{{ trans('labels.action') }}</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @include('admin.socialMedia.modal')
    @include('admin.socialMedia.modal-update')
@stop

@section('js')
    <script>
        $(function () {
            const customerTableSelector = $('#customerTable');
            const filterCountOrders = $('#filterCountOrders');
            const filterTenant = $('#filterTenant');

            let customerTable = customerTableSelector.DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                pageLength: 25,
                ajax: {
                    url: "{{ route('customer.get') }}",
                    data: function (d) {
                        d.filterCountOrders = filterCountOrders.val();
                        d.filterTenant = filterTenant.val();
                    }
                },
                columns: [
                    {data: 'name', name: 'name'},
                    {data: 'phone_number', name: 'phone_number'},
                    {data: 'count_orders', name: 'count_orders'},
                    {data: 'tenant_name', name: 'tenant_name'},
                    {data: 'actions', sortable: false, orderable: false}
                ]
            });

            filterCountOrders.change(function () {
                customerTable.draw();
            });

            filterTenant.change(function () {
                customerTable.draw();
            });

            $('#resetFilterBtn').click(function () {
                filterCountOrders.val('');
                filterTenant.val('');
                customerTable.draw();
            });
        });
    </script>
@stop
