@extends('adminlte::page')

@section('title', 'Edit Affiliate Talent')

@section('content_header')
    <h1>Edit Affiliate Talent</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('affiliate.update', $affiliate->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username', $affiliate->username) }}">
                            @error('username')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="{{ route('affiliate.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@stop