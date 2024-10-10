@extends('adminlte::page')

@section('title', 'Talent Payments')

@section('content_header')
    <h1>Talent Payments</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <table id="talentPaymentsTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Talent Name</th>
                                <th>Followers</th>
                                <th>Status Payment</th>
                                <th>Done Payment</th>
                                <th>Final Rate Card</th>
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
                { data: 'final_rate_card', name: 'talent_content.final_rate_card' },
            ],
            order: [[0, 'desc']]
        });
    });

</script>
@stop
