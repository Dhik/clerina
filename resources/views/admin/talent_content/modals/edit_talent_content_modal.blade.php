<div class="modal fade" id="editTalentContentModal" tabindex="-1" aria-labelledby="editTalentContentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="editTalentContentForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editTalentContentModalLabel">Edit Talent Content</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_dealing_upload_date">Dealing Upload Date</label>
                                <input type="date" name="dealing_upload_date" id="edit_dealing_upload_date" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="edit_posting_date">Posting Date</label>
                                <input type="date" name="posting_date" id="edit_posting_date" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="edit_kerkun">Kerkun</label>
                                <select name="kerkun" id="edit_kerkun" class="form-control" required>
                                    <option value="">Select Option</option>
                                    <option value="0">No</option>
                                    <option value="1">Yes</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="edit_product">Produk</label>
                                <input type="text" name="product" id="edit_product" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_pic_code">PIC</label>
                                <input type="text" name="pic_code" id="edit_pic_code" class="form-control">
                            </div>

                            <div class="form-group">
                                <label for="edit_boost_code">Boost Code</label>
                                <input type="text" name="boost_code" id="edit_boost_code" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="edit_upload_link">Upload Link</label>
                                <input type="text" name="upload_link" id="edit_upload_link" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
