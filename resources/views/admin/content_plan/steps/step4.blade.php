<div class="row">
    <div class="col-12">
        <div class="alert alert-warning">
            <h5><i class="icon fas fa-clipboard-check"></i> Creative Review</h5>
            <p>Review all content elements, production details, assign resources, and approve for content editing phase.</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">Content Strategy Review</h3>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td><strong>Objektif:</strong></td>
                        <td>{{ $contentPlan->objektif ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Jenis Konten:</strong></td>
                        <td>{{ $contentPlan->jenis_konten ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Pillar:</strong></td>
                        <td>{{ $contentPlan->pillar ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Sub Pillar:</strong></td>
                        <td>{{ $contentPlan->sub_pillar ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Platform:</strong></td>
                        <td>{{ $contentPlan->platform ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Account:</strong></td>
                        <td>{{ $contentPlan->akun ?? '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title">Production Details</h3>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td><strong>Final Talent:</strong></td>
                        <td>{{ $contentPlan->talent_fix ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Venue:</strong></td>
                        <td>{{ $contentPlan->venue ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Production Date:</strong></td>
                        <td>{{ $contentPlan->production_date ? $contentPlan->production_date->format('Y-m-d H:i') : '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Talent Booking:</strong></td>
                        <td>{{ $contentPlan->booking_talent_date ? $contentPlan->booking_talent_date->format('Y-m-d H:i') : '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Venue Booking:</strong></td>
                        <td>{{ $contentPlan->booking_venue_date ? $contentPlan->booking_venue_date->format('Y-m-d H:i') : '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Target Posting:</strong></td>
                        <td>{{ $contentPlan->target_posting_date ? $contentPlan->target_posting_date->format('Y-m-d H:i') : '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card card-outline card-success">
            <div class="card-header">
                <h3 class="card-title">Content Details</h3>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Hook:</strong>
                    <p class="text-muted">{{ $contentPlan->hook ?? 'No hook provided' }}</p>
                </div>
                
                <div class="mb-3">
                    <strong>Brief Konten:</strong>
                    <p class="text-muted">{{ $contentPlan->brief_konten ?? 'Brief not yet written' }}</p>
                </div>
                
                <div class="mb-3">
                    <strong>Caption:</strong>
                    <p class="text-muted">{{ $contentPlan->caption ?? 'Caption not yet written' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- NEW: Resource Management Section (moved from Step 3) -->
<div class="row">
    <div class="col-12">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">Resource Management</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="kerkun">Kerkun</label>
                            <input type="text" class="form-control @error('kerkun') is-invalid @enderror" 
                                   id="kerkun" name="kerkun" value="{{ old('kerkun', $contentPlan->kerkun) }}" 
                                   placeholder="Enter kerkun details">
                            @error('kerkun')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-8">
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
                    </div>
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
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card card-outline card-warning">
            <div class="card-header">
                <h3 class="card-title">Creative Review Checklist</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Content Strategy Review:</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success"></i> Objektif aligned with brand goals</li>
                            <li><i class="fas fa-check text-success"></i> Content type suitable for platform</li>
                            <li><i class="fas fa-check text-success"></i> Pillar consistency maintained</li>
                            <li><i class="fas fa-check text-success"></i> Hook is engaging and relevant</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>Production Review:</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success"></i> Talent confirmed and suitable</li>
                            <li><i class="fas fa-check text-success"></i> Venue booking confirmed</li>
                            <li><i class="fas fa-check text-success"></i> Production date scheduled</li>
                            <li><i class="fas fa-check text-success"></i> Content editor assigned</li>
                        </ul>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-md-6">
                        <h6>Content Quality Review:</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success"></i> Brief provides clear direction</li>
                            <li><i class="fas fa-check text-success"></i> Caption follows brand voice</li>
                            <li><i class="fas fa-check text-success"></i> All required elements included</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>Resource Management:</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success"></i> Content editor assigned</li>
                            <li><i class="fas fa-check text-success"></i> Raw content links provided</li>
                            <li><i class="fas fa-check text-success"></i> Kerkun details specified</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>