@extends('adminlte::page')

@section('title', trans('labels.sales'))

@section('content_header')
    <h1>{{ trans('labels.sales') }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Operational Spent</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary" onclick="showModal()">
                            Add New
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <table id="operationalSpentTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th>Year</th>
                                <th>Spent</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="formModal" tabindex="-1" role="dialog" aria-labelledby="formModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="formModalLabel">Operational Spent Form</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="operationalSpentForm" onsubmit="saveData(event)">
                <div class="modal-body">
                    <input type="hidden" id="id" name="id">
                    <div class="form-group">
                        <label>Month</label>
                        <input type="number" class="form-control" id="month" name="month" required min="1" max="12">
                    </div>
                    <div class="form-group">
                        <label>Year</label>
                        <input type="number" class="form-control" id="year" name="year" required min="2000">
                    </div>
                    <div class="form-group">
                        <label>Spent</label>
                        <input type="number" class="form-control" id="spent" name="spent" required min="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
    

@stop

@section('css')
<style>
</style>
@stop

@section('js')
<script>
let table = $('#operationalSpentTable').DataTable({
    processing: true,
    serverSide: true,
    ajax: "{{ route('operational-spent.get') }}",
    columns: [
        {data: 'month', name: 'month'},
        {data: 'year', name: 'year'},
        {data: 'spent', name: 'spent'},
        {data: 'actions', name: 'actions', orderable: false, searchable: false}
    ]
});

function showModal(id = null) {
    if (id) {
        $.get("{{ route('operational-spent.getByDate') }}", { id: id }, function(data) {
            $('#id').val(data.id);
            $('#month').val(data.month);
            $('#year').val(data.year);
            $('#spent').val(data.spent);
            $('#formModal').modal('show');
        });
    } else {
        $('#operationalSpentForm')[0].reset();
        $('#id').val('');
        $('#formModal').modal('show');
    }
}

function saveData(e) {
    e.preventDefault();
    let formData = new FormData(e.target);
    
    $.ajax({
        url: "{{ route('operational-spent.store') }}",
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                $('#formModal').modal('hide');
                table.ajax.reload();
                toastr.success('Data saved successfully');
            }
        },
        error: function(xhr) {
            let errors = xhr.responseJSON.errors;
            Object.keys(errors).forEach(function(key) {
                toastr.error(errors[key][0]);
            });
        }
    });
}

function editData(id) {
    showModal(id);
}
</script>
@stop
