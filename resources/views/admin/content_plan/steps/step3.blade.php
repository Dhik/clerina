<div class="row">
    <div class="col-12">
        <div class="alert alert-warning">
            <h5><i class="icon fas fa-clipboard-check"></i> Creative Review</h5>
            <p>Review the content brief and caption, then specify platform and account details.</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="platform">Platform <span class="text-danger">*</span></label>
            <select class="form-control @error('platform') is-invalid @enderror" 
                    id="platform" name="platform" required>
                <option value="">Select Platform</option>
                <option value="instagram" {{ old('platform', $contentPlan->platform) == 'instagram' ? 'selected' : '' }}>Instagram</option>
                <option value="facebook" {{ old('platform', $contentPlan->platform) == 'facebook' ? 'selected' : '' }}>Facebook</option>
                <option value="tiktok" {{ old('platform', $contentPlan->platform) == 'tiktok' ? 'selected' : '' }}>TikTok</option>
                <option value="twitter" {{ old('platform', $contentPlan->platform) == 'twitter' ? 'selected' : '' }}>Twitter</option>
                <option value="linkedin" {{ old('platform', $contentPlan->platform) == 'linkedin' ? 'selected' : '' }}>LinkedIn</option>
                <option value="youtube" {{ old('platform', $contentPlan->platform) == 'youtube' ? 'selected' : '' }}>YouTube</option>
            </select>
            @error('platform')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="akun">Akun <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('akun') is-invalid @enderror" 
                   id="akun" name="akun" value="{{ old('akun', $contentPlan->akun) }}" 
                   placeholder="Enter account name/handle" required>
            @error('akun')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">Content Summary</h3>
            </div>
            <div class="card-body">
                <p><strong>Brief:</strong></p>
                <p class="text-muted">{{ Str::limit($contentPlan->brief_konten, 200) ?? 'Not yet written' }}</p>
                <p><strong>Caption:</strong></p>
                <p class="text-muted">{{ Str::limit($contentPlan->caption, 150) ?? 'Not yet written' }}</p>
            </div>
        </div>
    </div>
</div>