<div class="row">
    <div class="col-12">
        <div class="alert alert-primary">
            <h5><i class="icon fas fa-users-cog"></i> Admin Support</h5>
            <p>Manage talent booking, venue booking, production scheduling, and content editor assignment.</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <h6 class="mb-3">Talent & Production Management</h6>
        
        <div class="form-group">
            <label for="talent_fix">Talent Fix <span class="text-danger">*</span></label>
            <select class="form-control @error('talent_fix') is-invalid @enderror" 
                    id="talent_fix" name="talent_fix" required>
                <option value="">Select Final Talent</option>
                <option value="syifa" {{ old('talent_fix', $contentPlan->talent_fix) == 'syifa' ? 'selected' : '' }}>Syifa</option>
                <option value="zinny" {{ old('talent_fix', $contentPlan->talent_fix) == 'zinny' ? 'selected' : '' }}>Zinny</option>
                <option value="putri" {{ old('talent_fix', $contentPlan->talent_fix) == 'putri' ? 'selected' : '' }}>Putri</option>
                <option value="eksternal" {{ old('talent_fix', $contentPlan->talent_fix) == 'eksternal' ? 'selected' : '' }}>Eksternal</option>
                <option value="no_talent" {{ old('talent_fix', $contentPlan->talent_fix) == 'no_talent' ? 'selected' : '' }}>No Talent Required</option>
            </select>
            @error('talent_fix')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
            <small class="form-text text-muted">Select the confirmed talent for this content.</small>
        </div>

        <div class="form-group">
            <label for="booking_talent_date">Booking Talent Date & Time</label>
            <input type="datetime-local" class="form-control @error('booking_talent_date') is-invalid @enderror" 
                   id="booking_talent_date" name="booking_talent_date" 
                   value="{{ old('booking_talent_date', $contentPlan->booking_talent_date ? $contentPlan->booking_talent_date->format('Y-m-d\TH:i') : '') }}">
            @error('booking_talent_date')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
            <small class="form-text text-muted">Schedule the talent booking appointment.</small>
        </div>

        <div class="form-group">
            <label for="booking_venue_date">Booking Venue Date & Time</label>
            <input type="datetime-local" class="form-control @error('booking_venue_date') is-invalid @enderror" 
                   id="booking_venue_date" name="booking_venue_date" 
                   value="{{ old('booking_venue_date', $contentPlan->booking_venue_date ? $contentPlan->booking_venue_date->format('Y-m-d\TH:i') : '') }}">
            @error('booking_venue_date')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
            <small class="form-text text-muted">Schedule the venue booking appointment.</small>
        </div>

        <div class="form-group">
            <label for="production_date">Production Date & Time <span class="text-danger">*</span></label>
            <input type="datetime-local" class="form-control @error('production_date') is-invalid @enderror" 
                   id="production_date" name="production_date" 
                   value="{{ old('production_date', $contentPlan->production_date ? $contentPlan->production_date->format('Y-m-d\TH:i') : '') }}" required>
            @error('production_date')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
            <small class="form-text text-muted">Set the actual content production date and time.</small>
        </div>
    </div>

    <div class="col-md-6">
        <h6 class="mb-3">Resource Management</h6>
        
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
                <option value="cleora_azmi" {{ old('assignee_content_editor', $contentPlan->assignee_content_editor) == 'cleora_azmi' ? 'selected' : '' }}>Desain Grafis Cleora, Azmi Daffa</option>
                <option value="azrina_farhan" {{ old('assignee_content_editor', $contentPlan->assignee_content_editor) == 'azrina_farhan' ? 'selected' : '' }}>Desain Grafis Azrina, Farhan Ridho</option>
                <option value="faddal" {{ old('assignee_content_editor', $contentPlan->assignee_content_editor) == 'faddal' ? 'selected' : '' }}>Videographer & Editor, Faddal</option>
                <option value="hendra" {{ old('assignee_content_editor', $contentPlan->assignee_content_editor) == 'hendra' ? 'selected' : '' }}>Videographer & Editor, Hendra</option>
                <option value="rafi" {{ old('assignee_content_editor', $contentPlan->assignee_content_editor) == 'rafi' ? 'selected' : '' }}>Videographer & Editor, Rafi</option>
                <option value="lukman" {{ old('assignee_content_editor', $contentPlan->assignee_content_editor) == 'lukman' ? 'selected' : '' }}>Photographer & Editor, Lukman Fajar</option>
            </select>
            @error('assignee_content_editor')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="link_raw_content">Link Raw Content</label>
            <textarea class="form-control @error('link_raw_content') is-invalid @enderror" 
                      id="link_raw_content" name="link_raw_content" rows="4" 
                      placeholder="Enter raw content links (Google Drive, Dropbox, etc.)">{{ old('link_raw_content', $contentPlan->link_raw_content) }}</textarea>
            @error('link_raw_content')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
            <small class="form-text text-muted">Provide links to raw images, videos, or other content assets.</small>
        </div>

        <!-- Content Summary Card -->
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title">Content Summary</h3>
            </div>
            <div class="card-body">
                <p><strong>Platform:</strong> {{ $contentPlan->platform ?? 'Not specified' }}</p>
                <p><strong>Account:</strong> {{ $contentPlan->akun ?? 'Not specified' }}</p>
                <p><strong>Target Date:</strong> {{ $contentPlan->target_posting_date ? $contentPlan->target_posting_date->format('Y-m-d H:i') : 'Not set' }}</p>
                <p><strong>Venue:</strong> {{ $contentPlan->venue ?? 'Not specified' }}</p>
                <p><strong>Initial Talent:</strong> {{ $contentPlan->talent ?? 'Not specified' }}</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card card-outline card-secondary">
            <div class="card-header">
                <h3 class="card-title">Content Brief Summary</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Brief:</strong></p>
                        <p class="text-muted">{{ Str::limit($contentPlan->brief_konten, 200) ?? 'Brief not yet written' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Caption:</strong></p>
                        <p class="text-muted">{{ Str::limit($contentPlan->caption, 150) ?? 'Caption not yet written' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>