@extends('adminlte::page')

@section('title', 'View Brief')

@section('content_header')
    <h1>View Brief</h1>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h3><strong>Acc Date:</strong> {{ \Carbon\Carbon::parse($brief->acc_date)->format('d-m-Y') }}</h3>
                <h3><strong>Title:</strong> {{ $brief->title }}</h3>
                <p><strong>Brief:</strong></p>
                <p>{{ $brief->brief }}</p>

                <a href="{{ route('brief.edit', $brief->id) }}" class="btn btn-success">Edit Brief</a>
                <form action="{{ route('brief.destroy', $brief->id) }}" method="POST" style="display:inline-block;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Brief</button>
                </form>

                <!-- Add Link Button -->
                <button type="button" class="btn btn-primary mt-4" data-toggle="modal" data-target="#addLinkModal">
                    Add Link
                </button>

                <!-- Modal -->
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
                                <th>ID</th>
                                <th>Link</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$(function() {
    $('#brief-contents-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{!! route('brief_contents.data', $brief->id) !!}',
        columns: [
            { data: 'id', name: 'id' },
            { data: 'link', name: 'link' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ]
    });
});
</script>
@stop
