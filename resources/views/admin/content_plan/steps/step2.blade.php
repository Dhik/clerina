{{-- resources/views/admin/content_plan/steps/step2.blade.php --}}
<div class="row">
    <div class="col-12">
        <!-- Display Previous Step Info -->
        <div class="alert alert-info">
            <h5><i class="icon fas fa-info"></i> Content Brief</h5>
            <p><strong>Objektif:</strong> {{ $contentPlan->objektif }}</p>
            <p><strong>Jenis Konten:</strong> {{ $contentPlan->jenis_konten }}</p>
            <p><strong>Hook:</strong> {{ $contentPlan->hook }}</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="form-group">
            <label for="brief_konten">Brief Konten <span class="text-danger">*</span></label>
            <textarea class="form-control @error('brief_konten') is-invalid @enderror" 
                      id="brief_konten" name="brief_konten" rows="6" 
                      placeholder="Enter detailed content brief..." required>{{ old('brief_konten', $contentPlan->brief_konten) }}</textarea>
            @error('brief_konten')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
            <small class="form-text text-muted">Provide detailed instructions for content creation including tone, style, key messages, and any specific requirements.</small>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="form-group">
            <label for="caption">Caption <span class="text-danger">*</span></label>
            <textarea class="form-control @error('caption') is-invalid @enderror" 
                      id="caption" name="caption" rows="8" 
                      placeholder="Write the social media caption..." required>{{ old('caption', $contentPlan->caption) }}</textarea>
            @error('caption')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
            <small class="form-text text-muted">Write the complete caption that will be used for the social media post. Include hashtags, mentions, and call-to-action.</small>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card card-outline card-secondary">
            <div class="card-header">
                <h3 class="card-title">Current Content Details</h3>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td><strong>Pillar:</strong></td>
                        <td>{{ $contentPlan->pillar ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Sub Pillar:</strong></td>
                        <td>{{ $contentPlan->sub_pillar ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Talent:</strong></td>
                        <td>{{ $contentPlan->talent ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Venue:</strong></td>
                        <td>{{ $contentPlan->venue ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Produk:</strong></td>
                        <td>{{ $contentPlan->produk ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Target Date:</strong></td>
                        <td>{{ $contentPlan->target_posting_date ? $contentPlan->target_posting_date->format('Y-m-d') : '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title">Content Writing Guidelines</h3>
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li><i class="fas fa-check text-success"></i> Follow brand voice and tone</li>
                    <li><i class="fas fa-check text-success"></i> Include relevant hashtags</li>
                    <li><i class="fas fa-check text-success"></i> Add call-to-action</li>
                    <li><i class="fas fa-check text-success"></i> Keep within platform character limits</li>
                    <li><i class="fas fa-check text-success"></i> Align with content objective</li>
                </ul>
            </div>
        </div>
    </div>
</div>