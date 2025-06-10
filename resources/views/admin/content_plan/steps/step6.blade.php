<div class="row">
    <div class="col-12">
        <div class="alert alert-success">
            <h5><i class="icon fas fa-share"></i> Final Posting</h5>
            <p>Review all content and publish to the specified platform. Provide the final posting link.</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="form-group">
            <label for="input_link_posting">Input Link Posting <span class="text-danger">*</span></label>
            <input type="url" class="form-control @error('input_link_posting') is-invalid @enderror" 
                   id="input_link_posting" name="input_link_posting" value="{{ old('input_link_posting', $contentPlan->input_link_posting) }}" 
                   placeholder="https://instagram.com/p/..." required>
            @error('input_link_posting')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
            <small class="form-text text-muted">Provide the link to the published post on the social media platform.</small>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-outline card-success">
            <div class="card-header">
                <h3 class="card-title">Publishing Details</h3>
            </div>
            <div class="card-body">
                <p><strong>Platform:</strong> {{ $contentPlan->platform ?? 'Not specified' }}</p>
                <p><strong>Account:</strong> {{ $contentPlan->akun ?? 'Not specified' }}</p>
                <p><strong>Content Type:</strong> {{ $contentPlan->jenis_konten ?? 'Not specified' }}</p>
                <p><strong>Edited Content:</strong><br>
                @if($contentPlan->link_hasil_edit)
                    <a href="{{ $contentPlan->link_hasil_edit }}" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-external-link-alt"></i> View
                    </a>
                @else
                    <small class="text-muted">No edited content</small>
                @endif
                </p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title">Final Content Review</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Content Brief:</h6>
                        <p class="text-muted">{{ $contentPlan->brief_konten ?? 'No brief provided' }}</p>
                    </div>
                    <div class="col-md-6">
                        <h6>Caption:</h6>
                        <p class="text-muted">{{ $contentPlan->caption ?? 'No caption provided' }}</p>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <h6>Objective:</h6>
                        <p class="text-muted">{{ $contentPlan->objektif ?? 'No objective specified' }}</p>
                    </div>
                    <div class="col-md-6">
                        <h6>Hook:</h6>
                        <p class="text-muted">{{ $contentPlan->hook ?? 'No hook specified' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>