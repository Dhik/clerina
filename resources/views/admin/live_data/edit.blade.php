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
                        <label for="shift">Shift</label>
                        <select class="form-control" id="shift" name="shift" required>
                            <option value="Pagi" {{ $liveData->shift == 'Pagi' ? 'selected' : '' }}>Pagi</option>
                            <option value="Siang" {{ $liveData->shift == 'Siang' ? 'selected' : '' }}>Siang</option>
                            <option value="Sore" {{ $liveData->shift == 'Sore' ? 'selected' : '' }}>Sore</option>
                            <option value="Malam" {{ $liveData->shift == 'Malam' ? 'selected' : '' }}>Malam</option>
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