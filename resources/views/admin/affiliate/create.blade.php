@extends('adminlte::page')

@section('title', 'Add Affiliate Talent')

@section('content_header')
    <h1>Add New Affiliate Talent</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('affiliate.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username') }}">
                            @error('username')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>PIC</label>
                            <input type="text" name="pic" class="form-control @error('pic') is-invalid @enderror" value="{{ old('pic') }}">
                            @error('pic')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>GMV Bottom</label>
                            <input type="number" name="gmv_bottom" class="form-control @error('gmv_bottom') is-invalid @enderror" value="{{ old('gmv_bottom') }}">
                            @error('gmv_bottom')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>GMV Top</label>
                            <input type="number" name="gmv_top" class="form-control @error('gmv_top') is-invalid @enderror" value="{{ old('gmv_top') }}">
                            @error('gmv_top')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Instagram Contact</label>
                            <input type="text" name="contact_ig" class="form-control @error('contact_ig') is-invalid @enderror" value="{{ old('contact_ig') }}">
                            @error('contact_ig')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>WhatsApp Number</label>
                            <input type="text" name="contact_wa_notelp" class="form-control @error('contact_wa_notelp') is-invalid @enderror" value="{{ old('contact_wa_notelp') }}">
                            @error('contact_wa_notelp')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>TikTok Contact</label>
                            <input type="text" name="contact_tiktok" class="form-control @error('contact_tiktok') is-invalid @enderror" value="{{ old('contact_tiktok') }}">
                            @error('contact_tiktok')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="contact_email" class="form-control @error('contact_email') is-invalid @enderror" value="{{ old('contact_email') }}">
                            @error('contact_email')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Platform Menghubungi</label>
                            <input type="text" name="platform_menghubungi" class="form-control @error('platform_menghubungi') is-invalid @enderror" value="{{ old('platform_menghubungi') }}">
                            @error('platform_menghubungi')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>Status Call</label>
                            <select name="status_call" class="form-control @error('status_call') is-invalid @enderror">
                                <option value="">Select Status</option>
                                <option value="Pending">Pending</option>
                                <option value="Called">Called</option>
                                <option value="Not Responded">Not Responded</option>
                            </select>
                            @error('status_call')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>Rate Card</label>
                            <input type="number" name="rate_card" class="form-control @error('rate_card') is-invalid @enderror" value="{{ old('rate_card') }}">
                            @error('rate_card')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>Final Rate Card</label>
                            <input type="number" name="rate_card_final" class="form-control @error('rate_card_final') is-invalid @enderror" value="{{ old('rate_card_final') }}">
                            @error('rate_card_final')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>ROAS</label>
                            <input type="number" step="0.01" name="roas" class="form-control @error('roas') is-invalid @enderror" value="{{ old('roas') }}">
                            @error('roas')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>Keterangan</label>
                            <textarea name="keterangan" class="form-control @error('keterangan') is-invalid @enderror">{{ old('keterangan') }}</textarea>
                            @error('keterangan')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Save</button>
                    <a href="{{ route('affiliate.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@stop