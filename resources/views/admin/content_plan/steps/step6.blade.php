<div class="row">
    <div class="col-12">
        <div class="alert alert-success">
            <h5><i class="icon fas fa-database"></i> Store to Content Bank</h5>
            <p>Final step: Store the completed content to content bank and provide posting link.</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="form-group">
            <label for="input_link_posting">Content Bank / Posting Link <span class="text-danger">*</span></label>
            <input type="url" class="form-control @error('input_link_posting') is-invalid @enderror" 
                   id="input_link_posting" name="input_link_posting" value="{{ old('input_link_posting', $contentPlan->input_link_posting) }}" 
                   placeholder="https://instagram.com/p/... or content bank URL" required>
            @error('input_link_posting')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
            <small class="form-text text-muted">Provide the link to the published post or content bank storage location.</small>
        </div>

        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title">Content Bank Guidelines</h3>
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li><i class="fas fa-check text-success"></i> Ensure content is properly tagged and categorized</li>
                    <li><i class="fas fa-check text-success"></i> Include all metadata (date, platform, talent, etc.)</li>
                    <li><i class="fas fa-check text-success"></i> Verify file quality and format compatibility</li>
                    <li><i class="fas fa-check text-success"></i> Add content to searchable database</li>
                    <li><i class="fas fa-check text-success"></i> Create backup copies if required</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-outline card-success">
            <div class="card-header">
                <h3 class="card-title">Final Content Details</h3>
            </div>
            <div class="card-body">
                <p><strong>Platform:</strong> {{ $contentPlan->platform ?? 'Not specified' }}</p>
                <p><strong>Account:</strong> {{ $contentPlan->akun ?? 'Not specified' }}</p>
                <p><strong>Content Type:</strong> {{ $contentPlan->jenis_konten ?? 'Not specified' }}</p>
                <p><strong>Final Talent:</strong> {{ $contentPlan->talent_fix ?? $contentPlan->talent ?? 'Not specified' }}</p>
                <p><strong>Production Date:</strong><br>
                <small>{{ $contentPlan->production_date ? $contentPlan->production_date->format('Y-m-d H:i') : 'Not set' }}</small></p>
                <p><strong>Target Posting:</strong><br>
                <small>{{ $contentPlan->target_posting_date ? $contentPlan->target_posting_date->format('Y-m-d H:i') : 'Not set' }}</small></p>
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
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">Complete Content Journey</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <h6>Strategy & Planning:</h6>
                        <p class="text-muted"><strong>Objektif:</strong> {{ $contentPlan->objektif ?? 'No objective specified' }}</p>
                        <p class="text-muted"><strong>Hook:</strong> {{ Str::limit($contentPlan->hook, 100) ?? 'No hook specified' }}</p>
                    </div>
                    <div class="col-md-4">
                        <h6>Content Creation:</h6>
                        <p class="text-muted"><strong>Brief:</strong> {{ Str::limit($contentPlan->brief_konten, 100) ?? 'No brief provided' }}</p>
                        <p class="text-muted"><strong>Caption:</strong> {{ Str::limit($contentPlan->caption, 100) ?? 'No caption provided' }}</p>
                    </div>
                    <div class="col-md-4">
                        <h6>Production & Editing:</h6>
                        <p class="text-muted"><strong>Editor:</strong> {{ $contentPlan->assignee_content_editor ?? 'Not assigned' }}</p>
                        <p class="text-muted"><strong>Production:</strong> {{ $contentPlan->production_date ? $contentPlan->production_date->format('M d, Y') : 'Not scheduled' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card card-outline card-warning">
            <div class="card-header">
                <h3 class="card-title">Content Bank Completion Checklist</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Technical Requirements:</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-square text-muted"></i> Content exported in correct format</li>
                            <li><i class="fas fa-square text-muted"></i> File size optimized for platform</li>
                            <li><i class="fas fa-square text-muted"></i> Quality check completed</li>
                            <li><i class="fas fa-square text-muted"></i> Metadata tags applied</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>Content Bank Storage:</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-square text-muted"></i> Uploaded to content management system</li>
                            <li><i class="fas fa-square text-muted"></i> Proper folder structure maintained</li>
                            <li><i class="fas fa-square text-muted"></i> Searchable keywords added</li>
                            <li><i class="fas fa-square text-muted"></i> Access permissions configured</li>
                        </ul>
                    </div>
                </div>
                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Note:</strong> Once this step is completed, the content plan will be marked as "Posted" and archived in the system.
                </div>
            </div>
        </div>
    </div>
</div>