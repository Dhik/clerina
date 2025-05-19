@extends('adminlte::page')

@section('title', 'View Live Data')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0">Live Data - {{ $liveData->date->format('d-m-Y') }} ({{ $liveData->shift }})</h1>
        <div>
            <a href="{{ route('live_data.edit', $liveData->id) }}" class="btn btn-outline-success mr-1">Edit</a>
            <form action="{{ route('live_data.destroy', $liveData->id) }}" method="POST" style="display:inline-block;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Delete</button>
            </form>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <!-- KPI Cards -->
                        <div class="row mt-4">
                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-info">
                                    <div class="inner">
                                        <h4>{{ number_format($liveData->dilihat) }}</h4>
                                        <p>Dilihat</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-eye"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-purple">
                                    <div class="inner">
                                        <h4>{{ number_format($liveData->penonton_tertinggi) }}</h4>
                                        <p>Penonton Tertinggi</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-users"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-success">
                                    <div class="inner">
                                        <h4>{{ gmdate("H:i:s", $liveData->rata_rata_durasi) }}</h4>
                                        <p>Rata-rata Durasi</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-danger">
                                    <div class="inner">
                                        <h4>{{ number_format($liveData->komentar) }}</h4>
                                        <p>Komentar</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-comments"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-lg-6 col-6">
                                <div class="small-box bg-warning">
                                    <div class="inner">
                                        <h4>{{ number_format($liveData->pesanan) }}</h4>
                                        <p>Pesanan</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-shopping-cart"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 col-6">
                                <div class="small-box bg-primary">
                                    <div class="inner">
                                        <h4>{{ number_format($liveData->penjualan, 2) }}</h4>
                                        <p>Penjualan</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-money-bill"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th style="width: 30%">Date</th>
                                            <td>{{ $liveData->date->format('d-m-Y') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Shift</th>
                                            <td>{{ $liveData->shift }}</td>
                                        </tr>
                                        <tr>
                                            <th>Sales Channel</th>
                                            <td>{{ $liveData->salesChannel ? $liveData->salesChannel->name : 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Dilihat</th>
                                            <td>{{ number_format($liveData->dilihat) }}</td>
                                        </tr>
                                        <tr>
                                            <th>Penonton Tertinggi</th>
                                            <td>{{ number_format($liveData->penonton_tertinggi) }}</td>
                                        </tr>
                                        <tr>
                                            <th>Rata-rata Durasi</th>
                                            <td>{{ gmdate("H:i:s", $liveData->rata_rata_durasi) }}</td>
                                        </tr>
                                        <tr>
                                            <th>Komentar</th>
                                            <td>{{ number_format($liveData->komentar) }}</td>
                                        </tr>
                                        <tr>
                                            <th>Pesanan</th>
                                            <td>{{ number_format($liveData->pesanan) }}</td>
                                        </tr>
                                        <tr>
                                            <th>Penjualan</th>
                                            <td>{{ number_format($liveData->penjualan, 2) }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection