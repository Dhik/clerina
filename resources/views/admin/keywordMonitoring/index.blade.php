@extends('adminlte::page')

@section('title', trans('labels.keyword_monitorings'))

@section('content_header')
    <h1>{{ trans('labels.keyword_monitorings') }}</h1>
@stop

@section('content')
<div class="row">
    <!-- KPI Cards -->
    <div class="col-lg-2 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h4 id="total_play_count">0</h4>
                <p>Total Views</p>
            </div>
            <div class="icon">
                <i class="fas fa-chart-line"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h4 id="total_comment_count">0</h4>
                <p>Total Comment Count</p>
            </div>
            <div class="icon">
                <i class="fas fa-chart-area"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h4 id="total_digg_count">0</h4>
                <p>Total Like Count</p>
            </div>
            <div class="icon">
                <i class="fas fa-chart-bar"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h4 id="total_share_count">0</h4>
                <p>Total Share Count</p>
            </div>
            <div class="icon">
                <i class="fas fa-chart-pie"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h4 id="total_collect_count">0</h4>
                <p>Total Saved Count</p>
            </div>
            <div class="icon">
                <i class="fas fa-chart-area"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-6">
        <div class="small-box bg-secondary">
            <div class="inner">
                <h4 id="total_download_count">0</h4>
                <p>Total Download Count</p>
            </div>
            <div class="icon">
                <i class="fas fa-chart-line"></i>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Average Play Count per Keyword and Composition of Play Count per Keyword -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Average View Count per Post</h3>
            </div>
            <div class="card-body">
                <canvas id="playCountChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Comparison Insights -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Comparison Insights</h3>
            </div>
            <div class="card-body">
                <canvas id="comparisonChart"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <a href="{{ route('keywordMonitoring.create') }}" class="btn btn-primary">Create Keyword</a>
            </div>
            <div class="card-body">
                <table class="table table-bordered" id="keywordMonitorings-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Keyword</th>
                            <th>Created At</th>
                            <th>Updated At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(function() {
    $('#keywordMonitorings-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{!! route('keywordMonitoring.data') !!}',
        columns: [
            { data: 'id', name: 'id' },
            { data: 'keyword', name: 'keyword' },
            { data: 'created_at', name: 'created_at' },
            { data: 'updated_at', name: 'updated_at' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ]
    });

    // Fetch data for charts and KPI cards
    $.ajax({
        url: '{{ route('keywordMonitoring.getAllPostings') }}',
        method: 'GET',
        success: function(response) {
            let totalPlayCount = 0;
            let totalCommentCount = 0;
            let totalDiggCount = 0;
            let totalShareCount = 0;
            let totalCollectCount = 0;
            let totalDownloadCount = 0;

            let labels = [];
            let avgPlayCounts = [];
            let commentCounts = [];
            let diggCounts = [];
            let shareCounts = [];
            let collectCounts = [];
            let downloadCounts = [];

            response.forEach(item => {
                labels.push(item.keyword);
                avgPlayCounts.push(parseInt(item.avg_play_count.replace(/,/g, '')));
                commentCounts.push(parseInt(item.total_comment_count.replace(/,/g, '')));
                diggCounts.push(parseInt(item.total_digg_count.replace(/,/g, '')));
                shareCounts.push(parseInt(item.total_share_count.replace(/,/g, '')));
                collectCounts.push(parseInt(item.total_collect_count.replace(/,/g, '')));
                downloadCounts.push(parseInt(item.total_download_count.replace(/,/g, '')));

                totalPlayCount += parseInt(item.total_play_count.replace(/,/g, ''));
                totalCommentCount += parseInt(item.total_comment_count.replace(/,/g, ''));
                totalDiggCount += parseInt(item.total_digg_count.replace(/,/g, ''));
                totalShareCount += parseInt(item.total_share_count.replace(/,/g, ''));
                totalCollectCount += parseInt(item.total_collect_count.replace(/,/g, ''));
                totalDownloadCount += parseInt(item.total_download_count.replace(/,/g, ''));
            });

            // Update KPI cards
            $('#total_play_count').text(totalPlayCount.toLocaleString());
            $('#total_comment_count').text(totalCommentCount.toLocaleString());
            $('#total_digg_count').text(totalDiggCount.toLocaleString());
            $('#total_share_count').text(totalShareCount.toLocaleString());
            $('#total_collect_count').text(totalCollectCount.toLocaleString());
            $('#total_download_count').text(totalDownloadCount.toLocaleString());

            // Create bar chart for average play count per keyword
            new Chart(document.getElementById('playCountChart'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Average View Count per Post',
                            data: avgPlayCounts,
                            backgroundColor: 'rgba(75, 192, 192, 0.6)',
                            borderColor: 'rgba(75, 192, 192, 1)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Create bar chart for comparison insights
            new Chart(document.getElementById('comparisonChart'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Comment Count',
                            data: commentCounts,
                            backgroundColor: 'rgba(54, 162, 235, 0.6)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Like Count',
                            data: diggCounts,
                            backgroundColor: 'rgba(255, 206, 86, 0.6)',
                            borderColor: 'rgba(255, 206, 86, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Share Count',
                            data: shareCounts,
                            backgroundColor: 'rgba(255, 99, 132, 0.6)',
                            borderColor: 'rgba(255, 99, 132, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Saved Count',
                            data: collectCounts,
                            backgroundColor: 'rgba(153, 102, 255, 0.6)',
                            borderColor: 'rgba(153, 102, 255, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Download Count',
                            data: downloadCounts,
                            backgroundColor: 'rgba(255, 159, 64, 0.6)',
                            borderColor: 'rgba(255, 159, 64, 1)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
    });
});
</script>
@stop
