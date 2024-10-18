@extends('adminlte::page')

@section('title', 'Hutang and Piutang')

@section('content_header')
    <h1>Hutang & Piutang Talents</h1>
@stop

@section('content')
    <div class="row">
        <!-- KPI Cards for Totals -->
        <div class="col-4">
            <div class="small-box bg-info">
                <div class="inner">
                    <h4 id="totalSpent">Rp. 0</h4>
                    <p>Total Spent</p>
                </div>
                <div class="icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h4 id="totalHutang">Rp. 0</h4>
                    <p>Total Hutang</p>
                </div>
                <div class="icon">
                    <i class="fas fa-money-bill"></i>
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="small-box bg-success">
                <div class="inner">
                    <h4 id="totalPiutang">Rp. 0</h4>
                    <p>Total Piutang</p>
                </div>
                <div class="icon">
                    <i class="fas fa-hand-holding-usd"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Talent Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Talent Financial Details</h3>
                </div>
                <div class="card-body">
                    <table id="talentTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Talent Name</th>
                                <th>Total Spent</th>
                                <th>Should Get</th>
                                <th>Hutang</th>
                                <th>Piutang</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop


@section('js')
    <script>
        $(function () {
            // Currency formatter for Indonesian Rupiah (Rp.)
            const rupiahFormatter = new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            });

            $('#talentTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('talent_payments.hutang') }}", // Your route here
                    type: 'GET',
                    dataSrc: function (json) {
                        // Populate KPI Cards with total values formatted as currency
                        $('#totalSpent').text(rupiahFormatter.format(json.totals.total_spent));
                        $('#totalHutang').text(rupiahFormatter.format(json.totals.total_hutang));
                        $('#totalPiutang').text(rupiahFormatter.format(json.totals.total_piutang));

                        // Prepare table data with formatted currency
                        let data = [];
                        $.each(json.talents, function (id, talent) {
                            data.push({
                                id: id,
                                talent_name: talent.talent_name,
                                total_spent: rupiahFormatter.format(talent.total_spent),
                                talent_should_get: rupiahFormatter.format(talent.talent_should_get),
                                hutang: rupiahFormatter.format(talent.hutang),
                                piutang: rupiahFormatter.format(talent.piutang)
                            });
                        });
                        return data;
                    }
                },
                columns: [
                    { data: 'talent_name', name: 'Talent Name' },
                    { data: 'total_spent', name: 'Total Spent' },
                    { data: 'talent_should_get', name: 'Should Get' },
                    { data: 'hutang', name: 'Hutang' },
                    { data: 'piutang', name: 'Piutang' }
                ],
                order: [[0, 'asc']]
            });
        });
    </script>
@stop
