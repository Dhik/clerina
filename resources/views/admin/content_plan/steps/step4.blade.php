<div class="row">
    <div class="col-12">
        <div class="alert alert-primary">
            <h5><i class="icon fas fa-users-cog"></i> Admin Support</h5>
            <p>Manage resources, assign content editor, and prepare raw content links.</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="kerkun">Kerkun</label>
            <input type="text" class="form-control @error('kerkun') is-invalid @enderror" 
                   id="kerkun" name="kerkun" value="{{ old('kerkun', $contentPlan->kerkun) }}" 
                   placeholder="Enter kerkun details">
            @error('kerkun')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="assignee_content_editor">Assignee Content Editor <span class="text-danger">*</span></label>
            <select class="form-control @error('assignee_content_editor') is-invalid @enderror" 
                    id="assignee_content_editor" name="assignee_content_editor" required>
                <option value="">Select Content Editor</option>
                <option value="editor1" {{ old('assignee_content_editor', $contentPlan->assignee_content_editor) == 'editor1' ? 'selected' : '' }}>Editor 1</option>
                <option value="editor2" {{ old('assignee_content_editor', $contentPlan->assignee_content_editor) == 'editor2' ? 'selected' : '' }}>Editor 2</option>
                <option value="editor3" {{ old('assignee_content_editor', $contentPlan->assignee_content_editor) == 'editor3' ? 'selected' : '' }}>Editor 3</option>
            </select>
            @error('assignee_content_editor')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label for="link_raw_content">Link Raw Content</label>
            <textarea class="form-control @error('link_raw_content') is-invalid @enderror" 
                      id="link_raw_content" name="link_raw_content" rows="3" 
                      placeholder="Enter raw content links (Google Drive, Dropbox, etc.)">{{ old('link_raw_content', $contentPlan->link_raw_content) }}</textarea>
            @error('link_raw_content')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
            <small class="form-text text-muted">Provide links to raw images, videos, or other content assets.</small>
        </div>
    </div>
</div>