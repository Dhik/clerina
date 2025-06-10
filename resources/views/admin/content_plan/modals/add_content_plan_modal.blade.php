<div class="modal fade" id="addContentPlanModal" tabindex="-1" role="dialog" aria-labelledby="addContentPlanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addContentPlanModalLabel">Add New Content Plan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addContentPlanForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
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
                                <label for="add_talent">Talent</label>
                                <input type="text" class="form-control" name="talent" id="add_talent">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="add_target_posting_date">Target Posting Date</label>
                                <input type="date" class="form-control" name="target_posting_date" id="add_target_posting_date">
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
                        <textarea class="form-control" name="hook" id="add_hook" rows="4"></textarea>
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