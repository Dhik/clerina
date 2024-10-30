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
                        <label for="talent_id">Talent Name</label>
                        <select name="talent_id" id="talent_id" class="form-control" required>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="campaign_id">Campaign Name</label>
                        <select name="campaign_id" id="campaign_id" class="form-control" required>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="dealing_upload_date">Dealing Upload Date</label>
                        <input type="date" name="dealing_upload_date" id="dealing_upload_date" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="product">Produk</label>
                        <input type="text" name="product" id="product" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="pic_code">Nama PIC</label>
                        <input type="text" name="pic_code" id="pic_code" class="form-control">
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
