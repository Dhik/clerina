@extends('adminlte::page')

@section('title', 'Edit Payroll')

@section('content_header')
    <h1>Edit Payroll</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('payroll.update', $payroll->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="form-group">
                                <label for="gaji_pokok">Gaji Pokok</label>
                                <input type="number" name="gaji_pokok" id="gaji_pokok" class="form-control" value="{{ $payroll->gaji_pokok }}">
                            </div>
                            <div class="form-group">
                                <label for="tunjangan_jabatan">Tunjangan Jabatan</label>
                                <input type="number" name="tunjangan_jabatan" id="tunjangan_jabatan" class="form-control" value="{{ $payroll->tunjangan_jabatan }}">
                            </div>
                            <div class="form-group">
                                <label for="insentif_live">Insentif Live</label>
                                <input type="number" name="insentif_live" id="insentif_live" class="form-control" value="{{ $payroll->insentif_live }}">
                            </div>
                            <div class="form-group">
                                <label for="insentif">Insentif</label>
                                <input type="number" name="insentif" id="insentif" class="form-control" value="{{ $payroll->insentif }}">
                            </div>
                            <div class="form-group">
                                <label for="function">Function</label>
                                <input type="number" name="function" id="function" class="form-control" value="{{ $payroll->function }}">
                            </div>
                            <div class="form-group">
                                <label for="bpjs">BPJS</label>
                                <input type="number" name="bpjs" id="bpjs" class="form-control" value="{{ $payroll->BPJS }}">
                            </div>
                            <button type="submit" class="btn btn-success">Update</button>
                            <a href="{{ route('payroll.index') }}" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
