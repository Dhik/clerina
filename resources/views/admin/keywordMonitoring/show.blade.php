@extends('adminlte::page')

@section('title', trans('labels.keyword_monitorings'))

@section('content_header')
    <h1>View Keyword</h1>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <h2>{{ $keywordMonitoring->keyword }}</h2>
                    <div>
                        <a href="#" id="refreshButton" class="btn btn-success">
                            <span id="refreshText">Refresh</span>
                            <i id="refreshSpinner" class="fas fa-spinner fa-spin" style="display:none;"></i>
                        </a>
                        <a href="#" id="comparisonButton" class="btn btn-primary">Show Comparison</a>
                    </div>
                </div>

                <div class="row">
                    <!-- KPI Cards -->
                    <div class="col-md-3">
                        <div class="kpi-card">
                            <div class="kpi-value" id="totalViews">0</div>
                            <div class="kpi-label">Total Views</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="kpi-card">
                            <div class="kpi-value" id="totalPosts">0</div>
                            <div class="kpi-label">Total Posts</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="kpi-card">
                            <div class="kpi-value" id="totalComments">0</div>
                            <div class="kpi-label">Total Comments</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="kpi-card">
                            <div class="kpi-value" id="totalShares">0</div>
                            <div class="kpi-label">Total Shares</div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <canvas id="timeSeriesChart" width="400" height="200"></canvas>
                    </div>
                    <div class="col-md-6">
                        <canvas id="categoricalChart" width="400" height="200"></canvas>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <table class="table table-bordered" id="postings-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Posting Date</th>
                                    <th>Username</th>
                                    <th>Play Count</th>
                                    <th>Comment Count</th>
                                    <th>Digg Count</th>
                                    <th>Share Count</th>
                                    <th>Collect Count</th>
                                    <th>Download Count</th>
                                    <th>Post Link</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

@include('admin.keywordMonitoring.modal-compare')
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css">
<style>
.kpi-card {
    text-align: center;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 10px;
    background-color: #f8f9fa;
    margin-bottom: 20px;
}
.kpi-value {
    font-size: 2.5em;
    font-weight: bold;
    color: #007bff;
}
.kpi-label {
    font-size: 1.2em;
    color: #6c757d;
}
</style>
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
@stop

@section('js')
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>

<script>
document.getElementById('refreshButton').addEventListener('click', function() {
    document.getElementById('refreshText').style.display = 'none';
    document.getElementById('refreshSpinner').style.display = 'inline-block';
    
    fetch('{{ route('keywordMonitoring.fetchTiktokData', $keywordMonitoring->id) }}')
        .then(response => response.json())
        .then(data => {
            document.getElementById('refreshText').style.display = 'inline';
            document.getElementById('refreshSpinner').style.display = 'none';
            
            if (!data.error) {
                window.location.reload();
            } else {
                console.error('Error fetching data:', data.error);
            }
        })
        .catch(error => {
            document.getElementById('refreshText').style.display = 'inline';
            document.getElementById('refreshSpinner').style.display = 'none';
            console.error('Error fetching data:', error);
        });
});

document.getElementById('comparisonButton').addEventListener('click', function() {
    fetch('{{ route('keywordMonitoring.getAllPostings') }}')
        .then(response => response.json())
        .then(data => {
            const comparisonContent = document.getElementById('comparisonContent');
            comparisonContent.innerHTML = generateComparisonContent(data);
            $('#comparisonModal').modal('show');
        })
        .catch(error => console.error('Error fetching comparison data:', error));
});

function generateComparisonContent(data) {
    // Sort data by total play count in descending order
    data.sort((a, b) => parseInt(b.total_play_count.replace(/,/g, '')) - parseInt(a.total_play_count.replace(/,/g, '')));
    
    let content = '<div class="table-responsive"><table class="table table-bordered">';
    content += '<thead><tr><th>Rank</th><th>Keyword</th><th>Total Play Count</th><th>Total Comment Count</th><th>Total Digg Count</th><th>Total Share Count</th><th>Total Collect Count</th><th>Total Download Count</th></tr></thead><tbody>';
    
    data.forEach((item, index) => {
        content += `<tr>
            <td>${index + 1}</td>
            <td>${item.keyword}</td>
            <td>${item.total_play_count}</td>
            <td>${item.total_comment_count}</td>
            <td>${item.total_digg_count}</td>
            <td>${item.total_share_count}</td>
            <td>${item.total_collect_count}</td>
            <td>${item.total_download_count}</td>
        </tr>`;
    });
    
    content += '</tbody></table></div>';
    return content;
}

$(document).ready(function() {
    $('#postings-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('keywordMonitoring.getPostingsData', $keywordMonitoring->id) }}',
        columns: [
            { data: 'id', name: 'id' },
            { data: 'upload_date', name: 'upload_date' },
            { data: 'username', name: 'username' },
            { data: 'play_count', name: 'play_count' },
            { data: 'comment_count', name: 'comment_count' },
            { data: 'digg_count', name: 'digg_count' },
            { data: 'share_count', name: 'share_count' },
            { data: 'collect_count', name: 'collect_count' },
            { data: 'download_count', name: 'download_count' },
            { data: 'post_link', name: 'post_link', orderable: false, searchable: false }
        ]
    });
});

function fetchPostingsData() {
    fetch('{{ route('keywordMonitoring.getPostingsData', $keywordMonitoring->id) }}')
        .then(response => response.json())
        .then(response => {
            const data = response.data;
            updateKpiCards(data);
            updateCharts(data);
        })
        .catch(error => console.error('Error fetching data:', error));
}

function updateKpiCards(data) {
    const playCounts = data.map(item => item.play_count);
    const commentCounts = data.map(item => item.comment_count);
    const shareCounts = data.map(item => item.share_count);

    document.getElementById('totalViews').innerText = playCounts.reduce((a, b) => a + b, 0).toLocaleString();
    document.getElementById('totalPosts').innerText = data.length.toLocaleString();
    document.getElementById('totalComments').innerText = commentCounts.reduce((a, b) => a + b, 0).toLocaleString();
    document.getElementById('totalShares').innerText = shareCounts.reduce((a, b) => a + b, 0).toLocaleString();
}

function updateCharts(data) {
    const labels = data.map(item => new Date(item.created_at).toLocaleDateString());
    const playCounts = data.map(item => item.play_count);
    const commentCounts = data.map(item => item.comment_count);
    const shareCounts = data.map(item => item.share_count);
    const diggCounts = data.map(item => item.digg_count);
    const collectCounts = data.map(item => item.collect_count);

    const ctx = document.getElementById('timeSeriesChart').getContext('2d');
    const timeSeriesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Play Count',
                data: playCounts,
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1,
                fill: false
            }, {
                label: 'Comment Count',
                data: commentCounts,
                borderColor: 'rgba(153, 102, 255, 1)',
                borderWidth: 1,
                fill: false
            }, {
                label: 'Share Count',
                data: shareCounts,
                borderColor: 'rgba(255, 159, 64, 1)',
                borderWidth: 1,
                fill: false
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    const ctxBar = document.getElementById('categoricalChart').getContext('2d');
    const categoricalChart = new Chart(ctxBar, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Digg Count',
                data: diggCounts,
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }, {
                label: 'Collect Count',
                data: collectCounts,
                backgroundColor: 'rgba(153, 102, 255, 0.2)',
                borderColor: 'rgba(153, 102, 255, 1)',
                borderWidth: 1
            }]
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

fetchPostingsData();
</script>
@stop
