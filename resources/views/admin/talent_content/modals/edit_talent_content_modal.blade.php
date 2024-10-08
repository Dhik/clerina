<div class="modal fade" id="editTalentContentModal" tabindex="-1" aria-labelledby="editTalentContentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
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
                    <div class="form-group">
                        <label for="edit_transfer_date">Transfer Date</label>
                        <input type="date" name="transfer_date" id="edit_transfer_date" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_dealing_upload_date">Dealing Upload Date</label>
                        <input type="date" name="dealing_upload_date" id="edit_dealing_upload_date" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="edit_posting_date">Posting Date</label>
                        <input type="date" name="posting_date" id="edit_posting_date" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="edit_done">Done</label>
                        <select name="done" id="edit_done" class="form-control" required>
                            <option value="">Select Status</option>
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="edit_upload_link">Upload Link</label>
                        <input type="url" name="upload_link" id="edit_upload_link" class="form-control" placeholder="http://example.com/upload">
                    </div>

                    <div class="form-group">
                        <label for="edit_final_rate_card">Final Rate Card</label>
                        <input type="text" name="final_rate_card" id="edit_final_rate_card" class="form-control money" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_pic_code">PIC Code</label>
                        <input type="text" name="pic_code" id="edit_pic_code" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="edit_boost_code">Boost Code</label>
                        <input type="text" name="boost_code" id="edit_boost_code" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="edit_kerkun">Kerkun</label>
                        <select name="kerkun" id="edit_kerkun" class="form-control" required>
                            <option value="">Select Option</option>
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
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
