@extends('adminlte::page')

@section('title', trans('labels.ad_spent_social_media'))

@section('content_header')
    <h1>{{ trans('labels.ad_spent_social_media') }}</h1>
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
                                    <select class="form-control" id="filterSocialMedia">
                                        <option value="" selected>{{ trans('placeholder.select_social_media') }}</option>
                                        <option value="">{{ trans('labels.all') }}</option>
                                        @foreach($socialMedia as $media)
                                            <option value={{ $media->id }}>{{ $media->name }}</option>
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
                            <h4 id="totalAdSpentSocialMediaCount">0</h4>
                            <p>{{ trans('labels.total') }} {{ trans('labels.ad_spent_social_media') }}</p>
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
                @foreach($socialMedia as $media)
                    <div class="col-lg-3 col-6">
                        <div class="small-box {{ $bgColors[$colorIndex % count($bgColors)] }}">
                            <div class="inner">
                                <h4 id="{{ str()->replace(' ', '', $media->name) }}Count">0</h4>
                                <p>{{ $media->name }}</p>
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
            @include('admin.adSpentSocialMedia.chart')
            <div class="card-body">
                <div>
                    <table id="adSocialMediaTable" class="table table-bordered table-striped dataTable responsive" aria-describedby="order-info" width="100%">
                        <thead>
                        <tr>
                            <th>{{ trans('labels.date') }}</th>
                            <th>{{ trans('labels.social_media') }}</th>
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
            adSocialMediaTable.draw();
            updateRecapCount();
        });

        const adSocialMediaTableSelector = $('#adSocialMediaTable');
        const filterDate = $('#filterDates');

        $('#resetFilterBtn').click(function () {
            $('#filterDates').val('')
            $('#filterSocialMedia').val('')
            adSocialMediaTable.draw()
            updateRecapCount()
        })

        filterDate.change(function () {
            adSocialMediaTable.draw()
            updateRecapCount()
        });

        $('#filterSocialMedia').change(function () {
            adSocialMediaTable.draw()
        });

        // datatable
        let adSocialMediaTable = adSocialMediaTableSelector.DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            pageLength: 25,
            ajax: {
                url: "{{ route('adSpentSocialMedia.get') }}",
                data: function (d) {
                    d.filterDates = $('#filterDates').val()
                    d.filterSocialMedia = $('#filterSocialMedia').val()
                }
            },
            columns: [
                {data: 'date', name: 'date'},
                {data: 'socialMedia', name: 'socialMedia', sortable: false},
                {data: 'amountFormatted', name: 'amount', sortable: false}
            ],
            columnDefs: [
                { "targets": [2], "className": "text-right" }
            ],
            order: [[0, 'desc']]
        });

        function updateRecapCount() {
            $.ajax({
                url: '{{ route('adSpentSocialMedia.getAdSpentRecap') }}?filterDates=' + filterDate.val(),
                method: 'GET',
                success: function(response) {
                    // Update the count with the retrieved value
                    $('#totalAdSpentSocialMediaCount').text(response.total.toString().replace(/\B(?=(\d{3})+(?!\d))/g, "."));

                    let adSpentGrouped = response.bySocialMedia;

                    Object.entries(adSpentGrouped).forEach(function(entry) {
                        let media = entry[0];
                        let amount = entry[1];

                        let selector = '#' + media.replace(/\s+/g, '') + 'Count';
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

    @include('admin.adSpentSocialMedia.script-chart')
@stop
