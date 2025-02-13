@extends('adminlte::page')

@section('title', 'Affiliate Talents')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1>Affiliate Talents</h1>
        <a href="{{ route('affiliate.create') }}" class="btn btn-primary">Add New Affiliate</a>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="mb-3">
                <input type="text" id="search" class="form-control" placeholder="Search affiliate...">
            </div>
            <table class="table table-bordered table-striped" id="affiliate-table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>PIC</th>
                        <th>GMV Range</th>
                        <th>Rate Card</th>
                        <th>ROAS</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
@stop

@section('js')
<script>
    $(document).ready(function() {
        let table = $('#affiliate-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('affiliate.data') }}",
                data: function(d) {
                    d.search = $('#search').val();
                }
            },
            columns: [
                {data: 'username', name: 'username'},
                {data: 'pic', name: 'pic'},
                {
                    data: null,
                    render: function(data) {
                        return data.gmv_bottom + ' - ' + data.gmv_top;
                    }
                },
                {data: 'rate_card_final', name: 'rate_card_final'},
                {data: 'roas', name: 'roas'},
                {data: 'status_call', name: 'status_call'},
                {
                    data: null,
                    render: function(data) {
                        return `
                            <a href="/admin/affiliate/${data.id}/edit" class="btn btn-sm btn-warning">Edit</a>
                            <button onclick="deleteAffiliate(${data.id})" class="btn btn-sm btn-danger">Delete</button>
                        `;
                    }
                }
            ]
        });

        $('#search').on('keyup', function() {
            table.draw();
        });
    });

    function deleteAffiliate(id) {
        if (confirm('Are you sure you want to delete this affiliate?')) {
            $.ajax({
                url: `/admin/affiliate/${id}`,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function() {
                    $('#affiliate-table').DataTable().ajax.reload();
                    toastr.success('Affiliate deleted successfully');
                }
            });
        }
    }
</script>
@stop