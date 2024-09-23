@extends('adminlte::page')

@section('title', 'Briefs')

@section('content_header')
    <h1>Briefs</h1>
@stop

@section('content')
    <a href="{{ route('brief.create') }}" class="btn btn-primary mb-2">Create Brief</a>
    <table class="table table-bordered" id="briefs-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Acc Date</th>
                <th>Title</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
@stop

@section('js')
<script>
    $(function() {
        $('#briefs-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('brief.data') }}',
            columns: [
                { data: 'id', name: 'id' },
                { data: 'acc_date', name: 'acc_date' },
                { data: 'title', name: 'title' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
            ]
        });
    });
</script>
@stop
