<div class="modal fade" id="addTalentContentModal" tabindex="-1" aria-labelledby="addTalentContentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addTalentContentForm" method="POST" action="{{ route('talent_content.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addTalentContentModalLabel">Add Talent Content</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="transfer_date">Transfer Date</label>
                        <input type="date" name="transfer_date" id="transfer_date" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="talent_id">Talent Name</label>
                        <select name="talent_id" id="talent_id" class="form-control" required>
                            <!-- Options will be populated by AJAX -->
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="dealing_upload_date">Dealing Upload Date</label>
                        <input type="date" name="dealing_upload_date" id="dealing_upload_date" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="posting_date">Posting Date</label>
                        <input type="date" name="posting_date" id="posting_date" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="done">Done</label>
                        <select name="done" id="done" class="form-control" required>
                            <option value="">Select Status</option>
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="upload_link">Upload Link</label>
                        <input type="url" name="upload_link" id="upload_link" class="form-control" placeholder="http://example.com/upload">
                    </div>

                    <div class="form-group">
                        <label for="final_rate_card">Final Rate Card</label>
                        <input type="text" name="final_rate_card" id="final_rate_card" class="form-control money" required>
                    </div>

                    <div class="form-group">
                        <label for="pic_code">PIC Code</label>
                        <input type="text" name="pic_code" id="pic_code" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="boost_code">Boost Code</label>
                        <input type="text" name="boost_code" id="boost_code" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="kerkun">Kerkun</label>
                        <select name="kerkun" id="kerkun" class="form-control" required>
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