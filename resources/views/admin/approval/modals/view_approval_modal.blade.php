<div class="modal fade" id="viewApprovalModal" tabindex="-1" role="dialog" aria-labelledby="viewApprovalModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewApprovalModalLabel">View Approval</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" class="form-control" id="view_name" readonly>
                </div>
                <div class="form-group">
                    <label>Photo</label>
                    <div>
                        <img id="view_photo" src="" alt="Approval Photo" style="max-width: 200px; max-height: 200px;">
                    </div>
                </div>
                <div class="form-group">
                    <label>Created At</label>
                    <input type="text" class="form-control" id="view_created_at" readonly>
                </div>
                <div class="form-group">
                    <label>Updated At</label>
                    <input type="text" class="form-control" id="view_updated_at" readonly>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>