@extends('adminlte::page')

@section('title', 'Edit Live Data')

@section('content_header')
    <h1>Edit Live Data</h1>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('live_data.update', $liveData->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label for="date">Date</label>
                        <input type="date" class="form-control" id="date" name="date" value="{{ $liveData->date->format('Y-m-d') }}" required>
                    </div>
                    <div class="form-group">
                        <label for="shift">Time Range (Shift)</label>
                        <div class="row">
                            <div class="col-5">
                                <input type="time" class="form-control" id="shift_start" required
                                       value="{{ substr($liveData->shift, 0, strpos($liveData->shift, ' - ')) }}">
                            </div>
                            <div class="col-2 text-center pt-2">
                                to
                            </div>
                            <div class="col-5">
                                <input type="time" class="form-control" id="shift_end" required
                                       value="{{ substr($liveData->shift, strpos($liveData->shift, ' - ') + 3) }}">
                            </div>
                        </div>
                        <input type="hidden" name="shift" id="shift_hidden" value="{{ $liveData->shift }}">
                    </div>
                    <div class="form-group">
                        <label for="sales_channel_id">Sales Channel</label>
                        <select class="form-control" id="sales_channel_id" name="sales_channel_id">
                            <option value="">-- Select Sales Channel --</option>
                            @foreach($salesChannels as $channel)
                                <option value="{{ $channel->id }}" {{ $liveData->sales_channel_id == $channel->id ? 'selected' : '' }}>{{ $channel->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="dilihat">Dilihat</label>
                        <input type="number" class="form-control" id="dilihat" name="dilihat" value="{{ $liveData->dilihat }}" required min="0">
                    </div>
                    <div class="form-group">
                        <label for="penonton_tertinggi">Penonton Tertinggi</label>
                        <input type="number" class="form-control" id="penonton_tertinggi" name="penonton_tertinggi" value="{{ $liveData->penonton_tertinggi }}" required min="0">
                    </div>
                    <div class="form-group">
                        <label for="rata_rata_durasi">Rata-rata Durasi (dalam detik)</label>
                        <input type="number" class="form-control" id="rata_rata_durasi" name="rata_rata_durasi" value="{{ $liveData->rata_rata_durasi }}" required min="0">
                    </div>
                    <div class="form-group">
                        <label for="komentar">Komentar</label>
                        <input type="number" class="form-control" id="komentar" name="komentar" value="{{ $liveData->komentar }}" required min="0">
                    </div>
                    <div class="form-group">
                        <label for="pesanan">Pesanan</label>
                        <input type="number" class="form-control" id="pesanan" name="pesanan" value="{{ $liveData->pesanan }}" required min="0">
                    </div>
                    <div class="form-group">
                        <label for="penjualan">Penjualan</label>
                        <input type="number" step="0.01" class="form-control" id="penjualan" name="penjualan" value="{{ $liveData->penjualan }}" required min="0">
                    </div>
                    <button type="submit" class="btn btn-primary">Update</button>
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