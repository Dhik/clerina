<div class="row">
    <div class="col-12">
        <div class="alert alert-primary">
            <h5><i class="icon fas fa-users-cog"></i> Admin Support</h5>
            <p>Manage talent booking, venue booking, and production scheduling.</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
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

    <div class="col-md-4">
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