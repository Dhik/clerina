@extends('adminlte::page')

@section('title', 'Create Content Plan')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>Create Content Plan</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="#">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('contentPlan.index') }}">Content Production</a></li>
                <li class="breadcrumb-item active">Create</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Step 1: Social Media Strategist - Initial Planning</h3>
                    <div class="card-tools">
                        <a href="{{ route('contentPlan.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>

                <form action="{{ route('contentPlan.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="objektif">Objektif</label>
                                    <input type="text" class="form-control @error('objektif') is-invalid @enderror" 
                                           id="objektif" name="objektif" value="{{ old('objektif') }}" 
                                           placeholder="Enter content objective">
                                    @error('objektif')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="jenis_konten">Jenis Konten</label>
                                    <select class="form-control @error('jenis_konten') is-invalid @enderror" 
                                            id="jenis_konten" name="jenis_konten">
                                        <option value="">Select Content Type</option>
                                        <option value="image" {{ old('jenis_konten') == 'image' ? 'selected' : '' }}>Image</option>
                                        <option value="video" {{ old('jenis_konten') == 'video' ? 'selected' : '' }}>Video</option>
                                        <option value="carousel" {{ old('jenis_konten') == 'carousel' ? 'selected' : '' }}>Carousel</option>
                                        <option value="reel" {{ old('jenis_konten') == 'reel' ? 'selected' : '' }}>Reel</option>
                                        <option value="story" {{ old('jenis_konten') == 'story' ? 'selected' : '' }}>Story</option>
                                    </select>
                                    @error('jenis_konten')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="pillar">Pillar</label>
                                    <input type="text" class="form-control @error('pillar') is-invalid @enderror" 
                                           id="pillar" name="pillar" value="{{ old('pillar') }}" 
                                           placeholder="Enter content pillar">
                                    @error('pillar')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="sub_pillar">Sub Pillar</label>
                                    <input type="text" class="form-control @error('sub_pillar') is-invalid @enderror" 
                                           id="sub_pillar" name="sub_pillar" value="{{ old('sub_pillar') }}" 
                                           placeholder="Enter sub pillar">
                                    @error('sub_pillar')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="talent">Talent</label>
                                    <input type="text" class="form-control @error('talent') is-invalid @enderror" 
                                           id="talent" name="talent" value="{{ old('talent') }}" 
                                           placeholder="Enter talent name">
                                    @error('talent')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="venue">Venue</label>
                                    <input type="text" class="form-control @error('venue') is-invalid @enderror" 
                                           id="venue" name="venue" value="{{ old('venue') }}" 
                                           placeholder="Enter venue location">
                                    @error('venue')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="produk">Produk</label>
                                    <input type="text" class="form-control @error('produk') is-invalid @enderror" 
                                           id="produk" name="produk" value="{{ old('produk') }}" 
                                           placeholder="Enter product name">
                                    @error('produk')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="referensi">Referensi</label>
                                    <input type="text" class="form-control @error('referensi') is-invalid @enderror" 
                                           id="referensi" name="referensi" value="{{ old('referensi') }}" 
                                           placeholder="Enter reference">
                                    @error('referensi')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="target_posting_date">Target Posting Date</label>
                                    <input type="date" class="form-control @error('target_posting_date') is-invalid @enderror" 
                                           id="target_posting_date" name="target_posting_date" value="{{ old('target_posting_date') }}">
                                    @error('target_posting_date')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="hook">Hook</label>
                                    <textarea class="form-control @error('hook') is-invalid @enderror" 
                                              id="hook" name="hook" rows="4" 
                                              placeholder="Enter content hook or main message">{{ old('hook') }}</textarea>
                                    @error('hook')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save & Continue to Step 2
                        </button>
                        <a href="{{ route('contentPlan.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
    // Auto-resize textarea
    document.getElementById('hook').addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = this.scrollHeight + 'px';
    });
</script>
@stop