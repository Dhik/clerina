@extends('adminlte::page')

@section('title', 'Talent Payments')

@section('content_header')
    <h1>Talent Payments</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <button type="button" class="btn btn-outline-primary exportForm" data-toggle="modal" data-target="#exportForm">
                    <i class="fas fa-file-download"></i> {{ trans('labels.export') }} Form Pengajuan
                    </button>
                </div>
                <div class="card-body">
                    <table id="talentPaymentsTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Talent Name</th>
                                <th>Followers</th>
                                <th>Status Payment</th>
                                <th>Amount Transfer</th>
                                <th>Done Payment</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data will be loaded via DataTables -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @include('admin.talent_payment.modals.edit_payment_modal')
@stop

@section('js')
<script>
    $(document).ready(function() {
        var table = $('#talentPaymentsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('talent_payments.data') }}',
            columns: [
                { data: 'talent_name', name: 'talents.talent_name' },
                { data: 'followers', name: 'talents.followers' },
                { 
                    data: 'status_payment', 
                    name: 'status_payment',
                    render: function(data, type, row) {
                        if (data === "50%") {
                            return '<span style="color: orange;">' + data + '</span>';
                        } else if (data === "Pelunasan") {
                            return '<span style="color: green;">' + data + '</span>';
                        }
                        return data;
                    }
                },
                { data: 'amount_tf', name: 'amount_tf' },
                {
                    data: 'done_payment', 
                    name: 'done_payment',
                    render: function(data, type, row) {
                        if (data) {
                            let date = new Date(data);
                            return ('0' + date.getDate()).slice(-2) + '/' + 
                                   ('0' + (date.getMonth() + 1)).slice(-2) + '/' + 
                                   date.getFullYear();
                        }
                        return '';
                    }
                },
                { data: 'action', name: 'action', orderable: false, searchable: false },
            ],
            order: [[0, 'desc']]
        });

        // Handle delete button click
        $('#talentPaymentsTable').on('click', '.deleteButton', function() {
            var paymentId = $(this).data('id');
            var route = '{{ route('talent_payments.destroy', ':id') }}'.replace(':id', paymentId);

            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: route,
                        type: 'DELETE',
                        data: {
                            '_token': '{{ csrf_token() }}' // Include CSRF token for security
                        },
                        success: function(response) {
                            // Reload the DataTable to reflect the changes
                            table.ajax.reload();
                            Swal.fire(
                                'Deleted!',
                                'Payment has been deleted.',
                                'success'
                            );
                        },
                        error: function(xhr) {
                            Swal.fire(
                                'Error!',
                                'There was an error deleting the payment.',
                                'error'
                            );
                            console.error('Error deleting payment:', xhr);
                        }
                    });
                }
            });
        });
    });
</script>
@stop
