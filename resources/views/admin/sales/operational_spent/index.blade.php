@extends('adminlte::page')

@section('title', trans('labels.sales'))

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Operational Spent</h1>
        </div>
        <div class="col-sm-6">
            <div class="float-sm-right">
                <a href="{{ route('sales.net_sales') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>
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
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="id" name="id">
                    <div class="form-group">
                        <label>Month</label>
                        <select class="form-control" id="month" name="month" required>
                            <option value="1">January</option>
                            <option value="2">February</option>
                            <option value="3">March</option>
                            <option value="4">April</option>
                            <option value="5">May</option>
                            <option value="6">June</option>
                            <option value="7">July</option>
                            <option value="8">August</option>
                            <option value="9">September</option>
                            <option value="10">October</option>
                            <option value="11">November</option>
                            <option value="12">December</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Year</label>
                        <input type="number" class="form-control" id="year" name="year" required min="2000">
                    </div>
                    <div class="form-group">
                        <label>Spent</label>
                        <input type="text" name="spent" id="spent" class="form-control money" required>
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
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

let table = $('#operationalSpentTable').DataTable({
    processing: true,
    serverSide: true,
    ajax: "{{ route('operational-spent.get') }}",
    columns: [
        {
            data: 'month',
            name: 'month',
            render: function(data) {
                const months = [
                    'January', 'February', 'March', 'April',
                    'May', 'June', 'July', 'August',
                    'September', 'October', 'November', 'December'
                ];
                return months[data - 1];
            }
        },
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
    
    Swal.fire({
        title: 'Are you sure?',
        text: "You want to save this operational spent data?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, save it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ route('operational-spent.store') }}",
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        Swal.fire(
                            'Saved!',
                            'Operational spent has been saved.',
                            'success'
                        );
                        $('#formModal').modal('hide');
                        table.ajax.reload();
                    }
                },
                error: function(xhr) {
                    let errors = xhr.responseJSON.errors;
                    let errorMessage = '';
                    Object.keys(errors).forEach(function(key) {
                        errorMessage += errors[key][0] + '\n';
                    });
                    Swal.fire(
                        'Error!',
                        errorMessage,
                        'error'
                    );
                }
            });
        }
    });
}

function editData(id) {
    showModal(id);
}
</script>
@stop