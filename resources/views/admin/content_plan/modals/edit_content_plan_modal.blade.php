<div class="modal fade" id="editContentPlanModal" tabindex="-1" role="dialog" aria-labelledby="editContentPlanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editContentPlanModalLabel">Edit Content Plan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editContentPlanForm">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="mb-3">Basic Information</h6>
                            <div class="form-group">
                                <label for="edit_objektif">Objektif</label>
                                <input type="text" class="form-control" name="objektif" id="edit_objektif">
                            </div>
                            <div class="form-group">
                                <label for="edit_jenis_konten">Jenis Konten</label>
                                <select class="form-control" name="jenis_konten" id="edit_jenis_konten">
                                    <option value="">Select Content Type</option>
                                    <option value="image">Image</option>
                                    <option value="video">Video</option>
                                    <option value="carousel">Carousel</option>
                                    <option value="reel">Reel</option>
                                    <option value="story">Story</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="edit_pillar">Pillar</label>
                                <input type="text" class="form-control" name="pillar" id="edit_pillar">
                            </div>
                            <div class="form-group">
                                <label for="edit_platform">Platform</label>
                                <select class="form-control" name="platform" id="edit_platform">
                                    <option value="">Select Platform</option>
                                    <option value="instagram">Instagram</option>
                                    <option value="facebook">Facebook</option>
                                    <option value="tiktok">TikTok</option>
                                    <option value="twitter">Twitter</option>
                                    <option value="linkedin">LinkedIn</option>
                                    <option value="youtube">YouTube</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-3">Additional Details</h6>
                            <div class="form-group">
                                <label for="edit_talent">Talent</label>
                                <input type="text" class="form-control" name="talent" id="edit_talent">
                            </div>
                            <div class="form-group">
                                <label for="edit_venue">Venue</label>
                                <input type="text" class="form-control" name="venue" id="edit_venue">
                            </div>
                            <div class="form-group">
                                <label for="edit_akun">Akun</label>
                                <input type="text" class="form-control" name="akun" id="edit_akun">
                            </div>
                            <div class="form-group">
                                <label for="edit_target_posting_date">Target Posting Date</label>
                                <input type="date" class="form-control" name="target_posting_date" id="edit_target_posting_date">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_caption">Caption</label>
                        <textarea class="form-control" name="caption" id="edit_caption" rows="6"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update Content Plan</button>
                </div>
            </form>
        </div>
    </div>
</div>