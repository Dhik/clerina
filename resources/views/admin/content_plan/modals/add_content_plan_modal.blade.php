<div class="modal fade" id="addContentPlanModal" tabindex="-1" role="dialog" aria-labelledby="addContentPlanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addContentPlanModalLabel">Add New Content Plan - Strategy & Platform</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addContentPlanForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="mb-3">Content Strategy</h6>
                            
                            <div class="form-group">
                                <label for="add_objektif">Objektif <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="objektif" id="add_objektif" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="add_jenis_konten">Jenis Konten</label>
                                <select class="form-control" name="jenis_konten" id="add_jenis_konten">
                                    <option value="">Select Content Type</option>
                                    <option value="image">Image</option>
                                    <option value="video">Video</option>
                                    <option value="carousel">Carousel</option>
                                    <option value="reel">Reel</option>
                                    <option value="story">Story</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="add_pillar">Pillar</label>
                                <input type="text" class="form-control" name="pillar" id="add_pillar">
                            </div>
                            
                            <div class="form-group">
                                <label for="add_sub_pillar">Sub Pillar</label>
                                <input type="text" class="form-control" name="sub_pillar" id="add_sub_pillar">
                            </div>
                            
                            <div class="form-group">
                                <label for="add_talent">Talent</label>
                                <input type="text" class="form-control" name="talent" id="add_talent">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="mb-3">Platform & Scheduling</h6>
                            
                            <div class="form-group">
                                <label for="add_target_posting_date">Target Posting Date & Time <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control" name="target_posting_date" id="add_target_posting_date" required>
                                <small class="form-text text-muted">Set the exact date and time for content posting.</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="add_platform">Platform <span class="text-danger">*</span></label>
                                <select class="form-control" name="platform" id="add_platform" required>
                                    <option value="">Select Platform</option>
                                    <option value="instagram">Instagram</option>
                                    <option value="facebook">Facebook</option>
                                    <option value="tiktok">TikTok</option>
                                    <option value="twitter">Twitter</option>
                                    <option value="linkedin">LinkedIn</option>
                                    <option value="youtube">YouTube</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="add_akun">Akun <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="akun" id="add_akun" placeholder="Enter account name/handle" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="add_venue">Venue</label>
                                <input type="text" class="form-control" name="venue" id="add_venue">
                            </div>
                            
                            <div class="form-group">
                                <label for="add_produk">Produk</label>
                                <input type="text" class="form-control" name="produk" id="add_produk">
                            </div>
                            
                            <div class="form-group">
                                <label for="add_referensi">Referensi</label>
                                <input type="text" class="form-control" name="referensi" id="add_referensi">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="add_hook">Hook</label>
                        <textarea class="form-control" name="hook" id="add_hook" rows="4" placeholder="Enter content hook or main message"></textarea>
                        <small class="form-text text-muted">Describe the main hook or attention-grabbing element for this content.</small>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Content Plan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Platform selection enhancement for modal
    $('#add_platform').on('change', function() {
        const platform = $(this).val();
        const akunField = $('#add_akun');
        
        // Update placeholder based on platform
        switch(platform) {
            case 'instagram':
                akunField.attr('placeholder', '@username');
                break;
            case 'facebook':
                akunField.attr('placeholder', 'Page Name');
                break;
            case 'tiktok':
                akunField.attr('placeholder', '@username');
                break;
            case 'twitter':
                akunField.attr('placeholder', '@username');
                break;
            case 'linkedin':
                akunField.attr('placeholder', 'Company/Profile Name');
                break;
            case 'youtube':
                akunField.attr('placeholder', 'Channel Name');
                break;
            default:
                akunField.attr('placeholder', 'Enter account name/handle');
        }
    });
});
</script>