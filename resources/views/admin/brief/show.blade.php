@extends('adminlte::page')

@section('title', 'View Brief')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0">{{ $brief->title }} - {{ \Carbon\Carbon::parse($brief->acc_date)->format('d-m-Y') }}</h1>
        <div>
            <a href="{{ route('brief.edit', $brief->id) }}" class="btn btn-outline-success mr-1">Edit Brief</a>
            <form action="{{ route('brief.destroy', $brief->id) }}" method="POST" style="display:inline-block;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">{{ trans('buttons.delete') }}</button>
            </form>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h3><strong>Brief:</strong></h3>
                        <p>{{ $brief->brief }}</p>

                        <!-- KPI Cards -->
                        <div class="row mt-4">
                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-info">
                                    <div class="inner">
                                        <h4 id="totalCPM">0</h4>
                                        <p>CPM</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-chart-line"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-purple">
                                    <div class="inner">
                                        <h4 id="totalViews">0</h4>
                                        <p>Total Views</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-chart-pie"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-success">
                                    <div class="inner">
                                        <h4 id="totalLikes">0</h4>
                                        <p>Total Likes</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-chart-bar"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-danger">
                                    <div class="inner">
                                        <h4 id="totalComments">0</h4>
                                        <p>Total Comments</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-chart-bar"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <canvas id="statisticChartBrief" class="w-100"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="button" class="btn btn-primary mt-4" data-toggle="modal" data-target="#addLinkModal">
                            Add Link
                        </button>

                        <!-- Modal for adding brief content -->
                        <div class="modal fade" id="addLinkModal" tabindex="-1" aria-labelledby="addLinkModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="addLinkModalLabel">Add Brief Content</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form action="{{ route('brief_contents.store') }}" method="POST">
                                        @csrf
                                        <div class="modal-body">
                                            <input type="hidden" name="id_brief" value="{{ $brief->id }}">
                                            <div class="form-group">
                                                <label for="link">Link</label>
                                                <input type="text" class="form-control" id="link" name="link" required>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-primary">Add Link</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Display Brief Contents -->
                        <div class="mt-4">
                            <h4>Brief Contents</h4>
                            <table class="table table-bordered" id="brief-contents-table">
                                <thead>
                                    <tr>
                                        <th>Campaign Title</th>
                                        <th>Username</th>
                                        <th>Task Name</th>
                                        <th>Link</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer">
                        {{ trans('labels.created_by') }} {{ $brief->createdBy->name ?? '' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
$(function() {
    // Fetch KPI data and update cards
    $.ajax({
        url: '{{ route('brief_contents.get-kpi', $brief->id) }}',
        method: 'GET',
        success: function(response) {
            $('#totalCPM').text(response.cpm);
            $('#totalViews').text(response.total_views);
            $('#totalLikes').text(response.total_likes);
            $('#totalComments').text(response.total_comments);
        },
        error: function(xhr, status, error) {
            console.error('Error fetching KPI data:', error);
        }
    });

    $('#brief-contents-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{!! route('brief_contents.data', $brief->id) !!}',
        columns: [
            { data: 'campaign_title', name: 'campaign_title' },
            { data: 'username', name: 'username' },
            { data: 'task_name', name: 'task_name' },
            { data: 'brief_link', name: 'brief_link' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ]
    });
});
</script>

@include('admin.brief.script.script-chart')
@stop
