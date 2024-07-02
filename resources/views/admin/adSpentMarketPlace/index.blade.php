@extends('adminlte::page')

@section('title', trans('labels.ad_spent_market_place'))

@section('content_header')
    <h1>{{ trans('labels.ad_spent_market_place') }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-10 col-sm-12">
                            <div class="row">
                                <div class="col-md-4">
                                    <input type="text" class="form-control rangeDate" id="filterDates" placeholder="{{ trans('placeholder.select_date') }}" autocomplete="off">
                                </div>
                                <div class="col-md-4">
                                    <select class="form-control" id="filterChannel">
                                        <option value="" selected>{{ trans('placeholder.select_sales_channel') }}</option>
                                        <option value="">{{ trans('labels.all') }}</option>
                                        @foreach($salesChannels as $salesChannel)
                                            <option value={{ $salesChannel->id }}>{{ $salesChannel->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button class="btn btn-default" id="resetFilterBtn">{{ trans('buttons.reset_filter') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h4 id="totalAdSpentMarketPlaceCount">0</h4>
                            <p>{{ trans('labels.total') }} {{ trans('labels.ad_spent_market_place') }}</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                </div>
                @php
                    $bgColors = ['bg-success', 'bg-purple', 'bg-teal', 'bg-danger']; // Define array of background colors
                    $colorIndex = 0; // Initialize color index
                @endphp
                @foreach($salesChannels as $salesChannel)
                    <div class="col-lg-3 col-6">
                        <div class="small-box {{ $bgColors[$colorIndex % count($bgColors)] }}">
                            <div class="inner">
                                <h4 id="{{ str()->replace(' ', '', $salesChannel->name) }}Count">0</h4>
                                <p>{{ $salesChannel->name }}</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                        </div>
                    </div>

                    @php
                        $colorIndex++; // Increment color index
                    @endphp
                @endforeach
            </div>
            @include('admin.visit.chart')
            <div>
                <div class="card-body">
                    <table id="adMarketPlaceTable" class="table table-bordered table-striped dataTable responsive" aria-describedby="order-info" width="100%">
                        <thead>
                        <tr>
                            <th>{{ trans('labels.date') }}</th>
                            <th>{{ trans('labels.sales_channel') }}</th>
                            <th>{{ trans('labels.amount') }}</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(function () {
            adMarketPlaceTable.draw();
            updateRecapCount();
        });

        const adMarketPlaceTableSelector = $('#adMarketPlaceTable');
        const filterDate = $('#filterDates');

        $('#resetFilterBtn').click(function () {
            $('#filterDates').val('')
            $('#filterChannel').val('')
            adMarketPlaceTable.draw()
            updateRecapCount()
        })

        filterDate.change(function () {
            adMarketPlaceTable.draw()
            updateRecapCount()
        });

        $('#filterChannel').change(function () {
            adMarketPlaceTable.draw()
        });

        // datatable
        let adMarketPlaceTable = adMarketPlaceTableSelector.DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            pageLength: 25,
            ajax: {
                url: "{{ route('adSpentMarketPlace.get') }}",
                data: function (d) {
                    d.filterDates = $('#filterDates').val()
                    d.filterChannel = $('#filterChannel').val()
                }
            },
            columns: [
                {data: 'date', name: 'date'},
                {data: 'salesChannel', name: 'salesChannel', sortable: false},
                {data: 'amountFormatted', name: 'amount', sortable: false}
            ],
            columnDefs: [
                { "targets": [2], "className": "text-right" }
            ],
            order: [[0, 'desc']]
        });

        function updateRecapCount() {
            $.ajax({
                url: '{{ route('adSpentMarketPlace.getAdSpentRecap') }}?filterDates=' + filterDate.val(),
                method: 'GET',
                success: function(response) {
                    // Update the count with the retrieved value
                    $('#totalAdSpentMarketPlaceCount').text(response.total.toString().replace(/\B(?=(\d{3})+(?!\d))/g, "."));

                    let adSpentGrouped = response.bySalesChannel;

                    Object.entries(adSpentGrouped).forEach(function(entry) {
                        let channel = entry[0];
                        let amount = entry[1];

                        let selector = '#' + channel.replace(/\s+/g, '') + 'Count';
                        $(selector).text(amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, "."));
                    });

                    generateChart(response);
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching new orders count:', error);
                }
            });
        }
    </script>

    @include('admin.adSpentMarketPlace.script-chart')
@stop
