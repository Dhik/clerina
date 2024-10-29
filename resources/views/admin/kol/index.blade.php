@extends('adminlte::page')

@section('title', trans('labels.key_opinion_leader'))

@section('content_header')
    <h1>Account</h1>
@stop

@section('content')
<div class="row">
        <div class="col-4">
            <div class="card">
                <div class="card-body">
                    <h5>Channel Distribution</h5>
                    <div style="height: 350px;">
                        <canvas id="channelPieChart"></canvas> <!-- Removed fixed width and height -->
                    </div>
                </div>
            </div>
        </div>
        <div class="col-8">
            <div class="card">
                <div class="card-body">
                    <h5>Average Rate Card per Channel</h5>
                    <div style="height: 350px;">
                        <canvas id="channelBarChart"></canvas> <!-- Removed fixed width and height -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <table id="kolTable" class="table table-bordered table-striped dataTable responsive" aria-describedby="kol-info" width="100%">
                        <thead>
                        <tr>
                            <th>{{ trans('labels.channel') }}</th>
                            <th>{{ trans('labels.username') }}</th>
                            <th width="10%">Followers</th>
                            <th width="10%">Following</th>
                            <th width="10%">Rate Card</th>
                            <th width="5%">Refresh Followers</th>
                            <th width="10%">{{ trans('labels.action') }}</th>
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
        const kolTableSelector = $('#kolTable');
        const channelSelector = $('#filterChannel');
        const nicheSelector = $('#filterNiche');
        const skinTypeSelector = $('#filterSkinType');
        const skinConcernSelector = $('#filterSkinConcern');
        const contentTypeSelector = $('#filterContentType');
        const picSelector = $('#filterPIC');
        const btnExportKol = $('#btnExportKol');

        let kolTable = kolTableSelector.DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('kol.get') }}",
                data: function (d) {
                    d.channel = channelSelector.val();
                    d.niche = nicheSelector.val();
                    d.skinType = skinTypeSelector.val();
                    d.skinConcern = skinConcernSelector.val();
                    d.contentType = contentTypeSelector.val();
                    d.pic = picSelector.val();
                }
            },
            columns: [
                {data: 'channel', name: 'channel'},
                {data: 'username', name: 'username'},
                {data: 'followers', name: 'followers'},
                {data: 'following', name: 'following'},
                {
                    data: 'rate',
                    name: 'rate',
                    render: $.fn.dataTable.render.number(',', '.', 0, '')
                },
                {data: 'refresh_follower', sortable: false, orderable: false},
                {data: 'actions', sortable: false, orderable: false}
            ],
            order: [[0, 'desc']]
        });

        btnExportKol.click(function () {
            let data = {
                channel: channelSelector.val(),
                niche: nicheSelector.val(),
                skinType: skinTypeSelector.val(),
                skinConcern: skinConcernSelector.val(),
                contentType: contentTypeSelector.val(),
                pic: picSelector.val()
            };

            let spinner = $('<div class="spinner-border spinner-border-sm" role="status"><span class="sr-only">Loading...</span></div>');
            btnExportKol.prop('disabled', true).append(spinner);

            let now = moment();
            let formattedTime = now.format('YYYYMMDD-HHmmss');

            $.ajax({
                url: "{{ route('kol.export') }}",
                type: "GET",
                data: data,
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(response) {
                    let link = document.createElement('a');
                    link.href = window.URL.createObjectURL(response);
                    link.download = 'KOL-' + formattedTime + '.xlsx';
                    link.click();

                    btnExportKol.prop('disabled', false);
                    spinner.remove();
                },
                error: function(xhr, status, error) {
                    console.error(xhr, status, error);

                    btnExportKol.prop('disabled', false);
                    spinner.remove();
                }
            });
        });

        $(function () {
            kolTable.draw()
        });

        $(document).on('click', '.refresh-follower', function() {
            const username = $(this).data('id'); // Get the username from the data-id attribute
            const url = `{{ route('kol.refresh_follow', ['username' => ':username']) }}`.replace(':username', username);

            Swal.fire({
                title: 'Refreshing...',
                text: 'Updating followers and following counts',
                didOpen: () => {
                    Swal.showLoading();
                },
            });

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    Swal.close();

                    if (data.error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: data.error,
                        });
                    } else {
                        kolTable.ajax.reload(null, false); // Reload table to reflect updated follower data
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: data.message,
                            showConfirmButton: false,
                            timer: 1500
                        });
                    }
                })
                .catch(error => {
                    Swal.close();
                    console.error('Error:', error);

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred while refreshing data. Please try again later.',
                    });
                });
        });

        // Global variables to hold the Chart instances
        let orderPieChart;
        let rateBarChart;

        // Function to fetch data and render pie chart
        function fetchChannelData() {
            $.ajax({
                url: "{{ route('kol.chart') }}",
                type: "GET",
                success: function(response) {
                    renderChannelPieChart(response.labels, response.values);
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching channel distribution data:', error);
                }
            });
        }

        // Function to fetch data and render bar chart for average rate
        function fetchAverageRateData() {
            $.ajax({
                url: "{{ route('kol.averageRate') }}",
                type: "GET",
                success: function(response) {
                    renderChannelBarChart(response.labels, response.values);
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching average rate data:', error);
                }
            });
        }

        // Function to map predefined colors based on label names
        function getColorsForLabels(labels) {
            const colors = {
                "tiktok_video": "#000000",
                "instagram_feed": "#8939C4",
                "twitter_post": "#179CF4",
                "youtube_video": "#F10000",
                "shopee_video": "#EC4D28"
            };

            return labels.map(label => colors[label] || "#CCCCCC");
        }

        // Render the pie chart
        function renderChannelPieChart(labels, values) {
            const ctxPie = document.getElementById('channelPieChart').getContext('2d');

            if (orderPieChart) {
                orderPieChart.destroy();
            }

            orderPieChart = new Chart(ctxPie, {
                type: 'pie',
                data: {
                    labels: labels,
                    datasets: [{
                        data: values,
                        backgroundColor: getColorsForLabels(labels),
                        borderColor: getColorsForLabels(labels),
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false, // Allow it to stretch to container
                    legend: {
                        position: 'right'
                    },
                    tooltips: {
                        callbacks: {
                            label: function(tooltipItem, data) {
                                let dataset = data.datasets[tooltipItem.datasetIndex];
                                let value = dataset.data[tooltipItem.index];
                                return data.labels[tooltipItem.index] + ': ' + value + '%';
                            }
                        }
                    }
                }
            });
        }

        // Render the bar chart for average rate per channel
        function renderChannelBarChart(labels, values) {
            const ctxBar = document.getElementById('channelBarChart').getContext('2d');

            if (rateBarChart) {
                rateBarChart.destroy();
            }

            rateBarChart = new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Average Rate Card',
                        data: values,
                        backgroundColor: getColorsForLabels(labels),
                        borderColor: getColorsForLabels(labels),
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false, // Allow it to stretch to container
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true
                            },
                            scaleLabel: {
                                display: true,
                                labelString: 'Average Rate (IDR)'
                            }
                        }],
                        xAxes: [{
                            scaleLabel: {
                                display: true,
                                labelString: 'Channel'
                            }
                        }]
                    },
                    legend: {
                        display: true,
                        position: 'top'
                    }
                }
            });
        }

        // Fetch and render both charts on page load
        $(document).ready(function () {
            fetchChannelData();
            fetchAverageRateData();
        });
    </script>
@stop
