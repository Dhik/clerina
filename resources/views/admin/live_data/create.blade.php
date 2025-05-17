@extends('adminlte::page')

@section('title', 'Create Live Data')

@section('content_header')
    <h1>Create Live Data</h1>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('live_data.store') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="date">Date</label>
                        <input type="date" class="form-control" id="date" name="date" required>
                    </div>
                    <div class="form-group">
                        <label for="shift">Time Range (Shift)</label>
                        <div class="row">
                            <div class="col-5">
                                <input type="time" class="form-control" id="shift_start" required>
                            </div>
                            <div class="col-2 text-center pt-2">
                                to
                            </div>
                            <div class="col-5">
                                <input type="time" class="form-control" id="shift_end" required>
                            </div>
                        </div>
                        <input type="hidden" name="shift" id="shift_hidden">
                    </div>
                    <div class="form-group">
                        <label for="dilihat">Dilihat</label>
                        <input type="number" class="form-control" id="dilihat" name="dilihat" required min="0">
                    </div>
                    <div class="form-group">
                        <label for="penonton_tertinggi">Penonton Tertinggi</label>
                        <input type="number" class="form-control" id="penonton_tertinggi" name="penonton_tertinggi" required min="0">
                    </div>
                    <div class="form-group">
                        <label for="rata_rata_durasi">Rata-rata Durasi (dalam detik)</label>
                        <input type="number" class="form-control" id="rata_rata_durasi" name="rata_rata_durasi" required min="0">
                    </div>
                    <div class="form-group">
                        <label for="komentar">Komentar</label>
                        <input type="number" class="form-control" id="komentar" name="komentar" required min="0">
                    </div>
                    <div class="form-group">
                        <label for="pesanan">Pesanan</label>
                        <input type="number" class="form-control" id="pesanan" name="pesanan" required min="0">
                    </div>
                    <div class="form-group">
                        <label for="penjualan">Penjualan</label>
                        <input type="number" step="0.01" class="form-control" id="penjualan" name="penjualan" required min="0">
                    </div>
                    <button type="submit" class="btn btn-primary">Create</button>
                    <a href="{{ route('live_data.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
    $(function() {
        // Handle form submission
        $('form').on('submit', function(e) {
            const startTime = $('#shift_start').val();
            const endTime = $('#shift_end').val();
            
            if (startTime && endTime) {
                $('#shift_hidden').val(startTime + ' - ' + endTime);
            } else {
                e.preventDefault();
                alert('Please specify both start and end times for the shift.');
            }
        });
    });
</script>
@stop