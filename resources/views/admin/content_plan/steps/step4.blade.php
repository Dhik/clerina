<div class="row">
    <div class="col-12">
        <div class="alert alert-warning">
            <h5><i class="icon fas fa-clipboard-check"></i> Creative Review</h5>
            <p>Review all content elements, production details, and approve for content editing phase.</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">Content Strategy</h3>
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

        <div class="card card-outline card-secondary">
            <div class="card-header">
                <h3 class="card-title">Resources</h3>
            </div>
            <div class="card-body">
                <p><strong>Assigned Editor:</strong><br>
                {{ $contentPlan->assignee_content_editor ?? 'Not assigned' }}</p>
                
                <p><strong>Raw Content Links:</strong><br>
                @if($contentPlan->link_raw_content)
                    <small class="text-muted">{{ Str::limit($contentPlan->link_raw_content, 100) }}</small>
                @else
                    <small class="text-muted">No links provided yet</small>
                @endif
                </p>

                <p><strong>Produk:</strong><br>
                {{ $contentPlan->produk ?? 'Not specified' }}</p>

                <p><strong>Referensi:</strong><br>
                {{ $contentPlan->referensi ?? 'Not specified' }}</p>
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
                        <h6>Timeline Review:</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success"></i> Production timeline realistic</li>
                            <li><i class="fas fa-check text-success"></i> Posting date achievable</li>
                            <li><i class="fas fa-check text-success"></i> Buffer time considered</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Optional: Add review comments field if needed -->
<!-- <div class="row">
    <div class="col-12">
        <div class="form-group">
            <label for="review_comments">Review Comments (Optional)</label>
            <textarea class="form-control" id="review_comments" name="review_comments" rows="3" 
                      placeholder="Add any review comments or feedback..."></textarea>
            <small class="form-text text-muted">Optional: Add any specific feedback or approval notes.</small>
        </div>
    </div>
</div> -->