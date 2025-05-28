@extends('adminlte::page')

@section('title', 'Live Tiktok')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Live Tiktok</h1>
        <a href="{{ route('live_data.dashboard') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Dashboard Live Data</a>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <a href="{{ route('live_data.create') }}" class="btn btn-primary mb-2">Create Live Data</a>
                <table class="table table-bordered" id="live-data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Date</th>
                            <th>Shift</th>
                            <th>Live Host</th>
                            <th>Sales Channel</th>
                            <th>Dilihat</th>
                            <th>Penonton Tertinggi</th>
                            <th>Rata-rata Durasi</th>
                            <th>Komentar</th>
                            <th>Pesanan</th>
                            <th>Penjualan</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
    $(function() {
        $('#live-data-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('live_data.data') }}',
            columns: [
                { data: 'id', name: 'id' },
                { data: 'date', name: 'date' },
                { data: 'shift', name: 'shift' },
                { data: 'employee_name', name: 'employee_name' },
                { data: 'sales_channel', name: 'sales_channel' },
                { data: 'dilihat', name: 'dilihat' },
                { data: 'penonton_tertinggi', name: 'penonton_tertinggi' },
                { data: 'rata_rata_durasi', name: 'rata_rata_durasi' },
                { data: 'komentar', name: 'komentar' },
                { data: 'pesanan', name: 'pesanan' },
                { data: 'penjualan', name: 'penjualan' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
            ]
        });
    });
</script>
@stop