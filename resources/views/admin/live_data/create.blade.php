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
                        <label for="shift">Shift</label>
                        <select class="form-control" id="shift" name="shift" required>
                            <option value="Pagi">Pagi</option>
                            <option value="Siang">Siang</option>
                            <option value="Sore">Sore</option>
                            <option value="Malam">Malam</option>
                        </select>
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