<div class="row">
    <div class="col-12">
        <div class="alert alert-dark">
            <h5><i class="icon fas fa-edit"></i> Content Editing</h5>
            <p>Edit and finalize the content, then provide the link to edited materials.</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="form-group">
            <label for="link_hasil_edit">Link Hasil Edit <span class="text-danger">*</span></label>
            <input type="url" class="form-control @error('link_hasil_edit') is-invalid @enderror" 
                   id="link_hasil_edit" name="link_hasil_edit" value="{{ old('link_hasil_edit', $contentPlan->link_hasil_edit) }}" 
                   placeholder="https://drive.google.com/..." required>
            @error('link_hasil_edit')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
            <small class="form-text text-muted">Provide the link to the final edited content (images, videos, etc.).</small>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title">Raw Content</h3>
            </div>
            <div class="card-body">
                <p><strong>Assigned Editor:</strong><br>
                {{ $contentPlan->assignee_content_editor ?? 'Not assigned' }}</p>
                <p><strong>Raw Content Links:</strong><br>
                <small class="text-muted">{{ $contentPlan->link_raw_content ?? 'No links provided' }}</small></p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card card-outline card-secondary">
            <div class="card-header">
                <h3 class="card-title">Content Details</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Platform:</strong> {{ $contentPlan->platform ?? 'Not specified' }}</p>
                        <p><strong>Account:</strong> {{ $contentPlan->akun ?? 'Not specified' }}</p>
                        <p><strong>Content Type:</strong> {{ $contentPlan->jenis_konten ?? 'Not specified' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Target Date:</strong> {{ $contentPlan->target_posting_date ? $contentPlan->target_posting_date->format('Y-m-d') : 'Not set' }}</p>
                        <p><strong>Pillar:</strong> {{ $contentPlan->pillar ?? 'Not specified' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>